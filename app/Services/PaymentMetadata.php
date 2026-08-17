<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

final class PaymentMetadata
{
    public static function ensureSchema(): bool
    {
        static $ready = null;
        if ($ready !== null) return $ready;
        try {
            Database::db()->query('SELECT payment_data FROM orders LIMIT 0');
            return $ready = true;
        } catch (Throwable) {
            try {
                Database::db()->exec('ALTER TABLE orders ADD COLUMN payment_data JSON NULL AFTER payment_reference');
                return $ready = true;
            } catch (Throwable $exception) {
                error_log('AstroSport payment metadata schema: '.$exception->getMessage());
                return $ready = false;
            }
        }
    }

    public static function store(int $orderId, array $response): void
    {
        if (!self::ensureSchema()) return;
        $card = is_array($response['card_detail'] ?? null) ? $response['card_detail'] : [];
        $safe = [
            'status' => (string) ($response['status'] ?? ''),
            'buy_order' => (string) ($response['buy_order'] ?? ''),
            'session_id' => (string) ($response['session_id'] ?? ''),
            'amount' => (int) round((float) ($response['amount'] ?? 0)),
            'authorization_code' => (string) ($response['authorization_code'] ?? ''),
            'payment_type_code' => (string) ($response['payment_type_code'] ?? ''),
            'response_code' => isset($response['response_code']) ? (int) $response['response_code'] : null,
            'installments_number' => (int) ($response['installments_number'] ?? 0),
            'transaction_date' => (string) ($response['transaction_date'] ?? ''),
            'card_number' => substr(preg_replace('/\D+/', '', (string) ($card['card_number'] ?? '')), -4),
        ];
        try {
            Database::db()->prepare('UPDATE orders SET payment_data=? WHERE id=?')->execute([
                json_encode($safe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $orderId,
            ]);
        } catch (Throwable $exception) {
            error_log('AstroSport payment metadata store: '.$exception->getMessage());
        }
    }

    public static function storeStart(int $orderId, string $buyOrder, string $sessionId, int $amount): bool
    {
        if (!self::ensureSchema()) return false;
        try {
            $statement = Database::db()->prepare('UPDATE orders SET payment_data=? WHERE id=?');
            $statement->execute([
                json_encode([
                    'status' => 'INITIALIZED',
                    'buy_order' => $buyOrder,
                    'session_id' => $sessionId,
                    'amount' => $amount,
                ], JSON_UNESCAPED_SLASHES),
                $orderId,
            ]);
            return $statement->rowCount() === 1;
        } catch (Throwable $exception) {
            error_log('AstroSport payment metadata start: '.$exception->getMessage());
            return false;
        }
    }

    public static function fromOrder(array $order): array
    {
        $data = json_decode((string) ($order['payment_data'] ?? ''), true);
        return is_array($data) ? $data : [];
    }
}
