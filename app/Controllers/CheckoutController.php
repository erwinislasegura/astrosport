<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Customer;
use App\Models\ShopCart;
use App\Services\FlowClient;
use App\Services\OrderMailer;
use App\Services\PaymentMetadata;
use App\Services\TransbankClient;
use Throwable;

final class CheckoutController
{
    public function index(): void
    {
        $items = ShopCart::items();
        if (!$items) redirect('/carrito');
        view('store/checkout', [
            'items' => $items,
            'customer' => customer_user(),
            'gateways' => $this->availableGateways(),
            'pageTitle' => 'Finalizar compra | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'inner',
            'toplineLeft' => 'CHECKOUT SEGURO',
            'toplineRight' => 'PAGO SEGURO CERTIFICADO',
        ]);
    }

    public function process(): never
    {
        verify_csrf();
        $items = ShopCart::items();
        if (!$items) redirect('/carrito');

        $provider = (string) ($_POST['payment_provider'] ?? '');
        $gateways = $this->availableGateways();
        if (!isset($gateways[$provider])) {
            $_SESSION['error'] = 'Selecciona una pasarela de pago activa.';
            redirect('/checkout');
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $_SESSION['error'] = 'Ingresa un correo válido';
            redirect('/checkout');
        }

        $customer = customer_user();
        if (!$customer && isset($_POST['create_account'])) {
            $existing = Customer::email($email);
            if ($existing) {
                $_SESSION['error'] = 'Ya existe una cuenta con ese correo. Inicia sesión antes de comprar para asociar el pedido.';
                redirect('/mi-cuenta/login');
            }
            $password = (string) ($_POST['account_password'] ?? '');
            if (strlen($password) < 8) {
                $_SESSION['error'] = 'La contraseña de la cuenta debe tener al menos 8 caracteres.';
                redirect('/checkout');
            }
            $customer = Customer::create([
                'name' => trim((string) ($_POST['name'] ?? 'Cliente')),
                'email' => $email,
                'password' => $password,
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'rut' => trim((string) ($_POST['rut'] ?? '')),
            ]);
            $_SESSION['customer_user'] = $customer;
        }

        $total = (int) array_sum(array_column($items, 'price'));
        if ($total <= 0 || ($provider === 'flow' && $total < 350)) {
            $_SESSION['error'] = $provider === 'flow' ? 'El total mínimo permitido por Flow es $350.' : 'El total del pedido no es válido.';
            redirect('/checkout');
        }

        $db = Database::db();
        try {
            $db->beginTransaction();
            $downloadToken = bin2hex(random_bytes(24));
            $statement = $db->prepare("INSERT INTO orders(customer_id,customer_name,customer_email,phone,rut,total,status,payment_provider,download_token) VALUES(?,?,?,?,?,?,'pending',?,?)");
            $statement->execute([
                $customer['id'] ?? null,
                trim((string) ($_POST['name'] ?? $customer['name'] ?? 'Cliente')),
                $email,
                trim((string) ($_POST['phone'] ?? '')),
                trim((string) ($_POST['rut'] ?? '')),
                $total,
                $provider,
                $downloadToken,
            ]);
            $orderId = (int) $db->lastInsertId();
            $itemStatement = $db->prepare('INSERT INTO order_items(order_id,photo_id,set_id,item_type,item_title,selected_photo_ids,unit_price) VALUES(?,?,?,?,?,?,?)');
            foreach ($items as $item) {
                $itemStatement->execute([
                    $orderId,
                    $item['photo_id'],
                    $item['set_id'],
                    $item['type'],
                    $item['title'],
                    $item['type'] === 'pack' ? json_encode($item['selected_photo_ids']) : null,
                    $item['price'],
                ]);
            }
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Checkout order create: '.$exception->getMessage());
            $_SESSION['error'] = 'No fue posible registrar el pedido.';
            redirect('/checkout');
        }

        try {
            if ($provider === 'transbank') $this->startTransbank($orderId, $total);
            $this->startFlow($orderId, $total, (string) $email);
        } catch (Throwable $exception) {
            $db->prepare("UPDATE orders SET status='failed' WHERE id=? AND status='pending'")->execute([$orderId]);
            error_log(ucfirst($provider).' create: '.$exception->getMessage());
            $_SESSION['error'] = 'No fue posible iniciar el pago con '.$gateways[$provider]['name'].'.';
            redirect('/checkout');
        }
    }

    public function confirmation(): void
    {
        $token = trim((string) ($_POST['token'] ?? ''));
        if ($token === '') {
            http_response_code(400);
            echo 'TOKEN_REQUIRED';
            return;
        }
        try {
            $this->synchronizeFlow($token);
            echo 'OK';
        } catch (Throwable $exception) {
            error_log('Flow confirmation: '.$exception->getMessage());
            http_response_code(500);
            echo 'RETRY';
        }
    }

    public function paymentReturn(): never
    {
        $token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));
        if ($token === '') {
            $_SESSION['payment_error'] = 'Flow no entregó el identificador.';
            redirect('/pago/resultado');
        }
        try {
            $order = $this->synchronizeFlow($token);
            if ($order['status'] === 'paid') {
                $_SESSION['cart'] = [];
                redirect('/gracias?token='.$order['download_token']);
            }
            $_SESSION['payment_order'] = $order['id'];
        } catch (Throwable $exception) {
            error_log('Flow return: '.$exception->getMessage());
            $_SESSION['payment_error'] = 'No pudimos consultar todavía el pago.';
        }
        redirect('/pago/resultado');
    }

    public function transbankReturn(): never
    {
        $token = trim((string) ($_GET['token_ws'] ?? $_POST['token_ws'] ?? ''));
        if ($token === '') {
            $this->handleTransbankAbort();
            redirect('/pago/resultado');
        }

        $db = Database::db();
        $statement = $db->prepare("SELECT * FROM orders WHERE payment_provider='transbank' AND payment_reference=? LIMIT 1");
        $statement->execute([$token]);
        $order = $statement->fetch();
        if (!$order) {
            $_SESSION['payment_error'] = 'No encontramos el pedido asociado a la respuesta de Transbank.';
            redirect('/pago/resultado');
        }
        if ($order['status'] === 'paid') {
            $_SESSION['cart'] = [];
            redirect('/gracias?token='.$order['download_token']);
        }

        try {
            $response = (new TransbankClient())->commit($token);
            $this->validateTransbankResponse($response, $order);
            PaymentMetadata::store((int) $order['id'], $response);
            $approved = array_key_exists('response_code', $response)
                && (int) $response['response_code'] === 0
                && strtoupper((string) ($response['status'] ?? '')) === 'AUTHORIZED';
            if ($approved) {
                $db->prepare("UPDATE orders SET status='paid',paid_at=NOW(),download_expires_at=DATE_ADD(NOW(),INTERVAL 15 DAY) WHERE id=? AND status<>'paid'")->execute([$order['id']]);
                (new OrderMailer())->sendPaid((int) $order['id']);
            } else {
                $db->prepare("UPDATE orders SET status='failed' WHERE id=? AND status<>'paid'")->execute([$order['id']]);
            }
            $statement->execute([$token]);
            $order = $statement->fetch();
            if ($order['status'] === 'paid') {
                $_SESSION['cart'] = [];
                redirect('/gracias?token='.$order['download_token']);
            }
            $_SESSION['payment_order'] = $order['id'];
        } catch (Throwable $exception) {
            error_log('Transbank return: '.$exception->getMessage());
            $_SESSION['payment_order'] = $order['id'];
            $_SESSION['payment_error'] = 'No pudimos confirmar el pago con Transbank. El pedido permanece bloqueado hasta verificarlo.';
        }
        redirect('/pago/resultado');
    }

    public function result(): void
    {
        $order = null;
        if (!empty($_SESSION['payment_order'])) {
            $statement = Database::db()->prepare('SELECT * FROM orders WHERE id=?');
            $statement->execute([$_SESSION['payment_order']]);
            $order = $statement->fetch();
            if ($order && $order['status'] === 'pending' && $order['payment_provider'] === 'transbank' && !empty($order['payment_reference'])) {
                try {
                    $order = $this->synchronizeTransbankStatus($order);
                    if ($order['status'] === 'paid') {
                        $_SESSION['cart'] = [];
                        redirect('/gracias?token='.$order['download_token']);
                    }
                } catch (Throwable $exception) {
                    error_log('Transbank status: '.$exception->getMessage());
                }
            }
        }
        view('store/payment-result', [
            'order' => $order,
            'error' => $_SESSION['payment_error'] ?? null,
            'pageTitle' => 'Estado del pago | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'inner',
        ]);
        unset($_SESSION['payment_error']);
    }

    public function thanks(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        $statement = Database::db()->prepare("SELECT o.*,i.id item_id,i.item_type,i.photo_id,i.set_id,COALESCE(p.title,i.item_title,ps.name) title FROM orders o JOIN order_items i ON i.order_id=o.id LEFT JOIN photos p ON p.id=i.photo_id LEFT JOIN photo_sets ps ON ps.id=i.set_id WHERE o.download_token=? AND o.status='paid' AND o.download_expires_at IS NOT NULL AND NOW()<=o.download_expires_at");
        $statement->execute([$token]);
        $items = $statement->fetchAll();
        view('store/thanks', [
            'items' => $items,
            'token' => $token,
            'pageTitle' => 'Compra aprobada | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'success',
            'hideChrome' => true,
        ]);
    }

    private function startFlow(int $orderId, int $total, string $email): never
    {
        $result = (new FlowClient())->createPayment([
            'id' => $orderId,
            'commerce_order' => 'UMD'.$orderId,
            'subject' => 'Fotografías deportivas AstroSport · Pedido AST-'.$orderId,
            'amount' => $total,
            'email' => $email,
            'confirmation_url' => url('pago/flow/confirmacion'),
            'return_url' => url('pago/flow/retorno'),
        ]);
        if (empty($result['url']) || empty($result['token']) || empty($result['flowOrder'])) {
            throw new \RuntimeException('Flow no entregó una URL válida.');
        }
        Database::db()->prepare('UPDATE orders SET payment_reference=? WHERE id=?')->execute([(string) $result['flowOrder'], $orderId]);
        header('Location: '.$result['url'].'?token='.rawurlencode((string) $result['token']), true, 303);
        exit;
    }

    private function startTransbank(int $orderId, int $total): never
    {
        $buyOrder = 'AST'.$orderId;
        $sessionId = 'AST-'.$orderId.'-'.bin2hex(random_bytes(8));
        $result = (new TransbankClient())->create(
            $buyOrder,
            $sessionId,
            $total,
            url('pago/transbank/retorno')
        );
        Database::db()->prepare('UPDATE orders SET payment_reference=? WHERE id=?')->execute([$result['token'], $orderId]);
        if (!PaymentMetadata::storeStart($orderId, $buyOrder, $sessionId, $total)) {
            throw new \RuntimeException('No fue posible proteger la sesión de pago de Transbank.');
        }
        view('store/transbank-redirect', [
            'transbankUrl' => $result['url'],
            'transbankToken' => $result['token'],
            'pageTitle' => 'Conectando con Transbank | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'success transbank-redirect-page',
            'hideChrome' => true,
        ]);
        exit;
    }

    private function synchronizeFlow(string $token): array
    {
        $status = (new FlowClient())->getStatus($token);
        if (!preg_match('/^UMD(\d+)$/', (string) ($status['commerceOrder'] ?? ''), $matches)) {
            throw new \RuntimeException('Orden inválida.');
        }
        $db = Database::db();
        $statement = $db->prepare("SELECT * FROM orders WHERE id=? AND payment_provider='flow'");
        $statement->execute([(int) $matches[1]]);
        $order = $statement->fetch();
        if (!$order || (int) ($status['amount'] ?? -1) !== (int) $order['total'] || ($status['currency'] ?? '') !== 'CLP') {
            throw new \RuntimeException('Pedido, monto o moneda inválidos.');
        }
        $state = (int) ($status['status'] ?? 0);
        if ($order['status'] !== 'paid') {
            if ($state === 2) {
                $db->prepare("UPDATE orders SET status='paid',paid_at=NOW(),download_expires_at=DATE_ADD(NOW(),INTERVAL 15 DAY),payment_reference=? WHERE id=?")->execute([(string) $status['flowOrder'], $order['id']]);
            } elseif (in_array($state, [3, 4], true)) {
                $db->prepare("UPDATE orders SET status='failed' WHERE id=? AND status<>'paid'")->execute([$order['id']]);
            }
        }
        (new OrderMailer())->sendPaid((int) $order['id']);
        $statement->execute([(int) $matches[1]]);
        return $statement->fetch();
    }

    private function validateTransbankResponse(array $response, array $order): void
    {
        $payment = PaymentMetadata::fromOrder($order);
        if (($payment['buy_order'] ?? '') === '' || (string) ($response['buy_order'] ?? '') !== (string) $payment['buy_order']) {
            throw new \RuntimeException('La orden de compra retornada por Transbank no coincide.');
        }
        if (($payment['session_id'] ?? '') === '' || !hash_equals((string) $payment['session_id'], (string) ($response['session_id'] ?? ''))) {
            throw new \RuntimeException('La sesión retornada por Transbank no coincide.');
        }
        if ((int) round((float) ($response['amount'] ?? -1)) !== (int) $order['total']) {
            throw new \RuntimeException('El monto retornado por Transbank no coincide.');
        }
    }

    private function synchronizeTransbankStatus(array $order): array
    {
        $response = (new TransbankClient())->status((string) $order['payment_reference']);
        $this->validateTransbankResponse($response, $order);
        PaymentMetadata::store((int) $order['id'], $response);
        $status = strtoupper((string) ($response['status'] ?? ''));
        $approved = array_key_exists('response_code', $response) && (int) $response['response_code'] === 0 && $status === 'AUTHORIZED';
        if ($approved) {
            Database::db()->prepare("UPDATE orders SET status='paid',paid_at=COALESCE(paid_at,NOW()),download_expires_at=COALESCE(download_expires_at,DATE_ADD(NOW(),INTERVAL 15 DAY)) WHERE id=? AND status<>'paid'")->execute([$order['id']]);
            (new OrderMailer())->sendPaid((int) $order['id']);
        } elseif (in_array($status, ['FAILED', 'REVERSED', 'NULLIFIED'], true)) {
            Database::db()->prepare("UPDATE orders SET status='failed' WHERE id=? AND status='pending'")->execute([$order['id']]);
        }
        $statement = Database::db()->prepare('SELECT * FROM orders WHERE id=?');
        $statement->execute([$order['id']]);
        return $statement->fetch();
    }

    private function handleTransbankAbort(): void
    {
        $token = trim((string) ($_GET['TBK_TOKEN'] ?? $_POST['TBK_TOKEN'] ?? ''));
        $order = null;
        if ($token !== '') {
            $statement = Database::db()->prepare("SELECT * FROM orders WHERE payment_provider='transbank' AND payment_reference=? LIMIT 1");
            $statement->execute([$token]);
            $order = $statement->fetch();
        }
        if (!$order) {
            $buyOrder = trim((string) ($_GET['TBK_ORDEN_COMPRA'] ?? $_POST['TBK_ORDEN_COMPRA'] ?? ''));
            $sessionId = trim((string) ($_GET['TBK_ID_SESION'] ?? $_POST['TBK_ID_SESION'] ?? ''));
            if (preg_match('/^AST(\d+)$/', $buyOrder, $matches) && $sessionId !== '') {
                $statement = Database::db()->prepare("SELECT * FROM orders WHERE id=? AND payment_provider='transbank' LIMIT 1");
                $statement->execute([(int) $matches[1]]);
                $candidate = $statement->fetch();
                $payment = $candidate ? PaymentMetadata::fromOrder($candidate) : [];
                if ($candidate && ($payment['buy_order'] ?? '') === $buyOrder && isset($payment['session_id']) && hash_equals((string) $payment['session_id'], $sessionId)) {
                    $order = $candidate;
                }
            }
        }
        if ($order) {
            Database::db()->prepare("UPDATE orders SET status='failed' WHERE id=? AND status='pending'")->execute([$order['id']]);
            $_SESSION['payment_order'] = $order['id'];
        }
        $_SESSION['payment_error'] = 'El pago con Transbank fue cancelado o no pudo completarse.';
    }

    private function availableGateways(): array
    {
        $gateways = [];
        try {
            $flow = new FlowClient();
            if ($flow->configured()) $gateways['flow'] = ['name' => 'Flow', 'description' => 'Tarjetas y medios habilitados por Flow.'];
        } catch (Throwable) {
        }
        try {
            $transbank = new TransbankClient();
            if ($transbank->configured()) $gateways['transbank'] = ['name' => 'Webpay Plus', 'description' => 'Crédito, débito y prepago mediante Transbank.'];
        } catch (Throwable) {
        }
        return $gateways;
    }
}
