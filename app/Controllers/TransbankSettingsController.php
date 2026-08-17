<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Services\PaymentMetadata;
use App\Services\PaymentSettings;
use Throwable;

final class TransbankSettingsController
{
    public function index(): void
    {
        PaymentMetadata::ensureSchema();
        $settings = PaymentSettings::transbank() ?: [
            'active' => 0,
            'environment' => 'sandbox',
            'api_key' => '',
            'secret_key' => '',
        ];
        admin_view('admin/transbank-settings', [
            'settings' => $settings,
            'pageTitle' => 'Pasarela Transbank',
            'adminSection' => 'transbank',
        ]);
    }

    public function save(): never
    {
        verify_csrf();
        $environment = ($_POST['environment'] ?? 'sandbox') === 'production' ? 'production' : 'sandbox';
        $commerceCode = preg_replace('/\s+/', '', trim((string) ($_POST['commerce_code'] ?? '')));
        $secret = trim((string) ($_POST['api_key_secret'] ?? ''));
        $current = PaymentSettings::transbank();

        if (preg_match('/^\d{8,20}$/', $commerceCode) !== 1) {
            $_SESSION['error'] = 'Ingresa un Tbk-Api-Key-Id válido, compuesto únicamente por números.';
            redirect('/admin/transbank');
        }
        if ($secret === '' && empty($current['secret_key'])) {
            $_SESSION['error'] = 'Debes ingresar la Tbk-Api-Key-Secret de Transbank.';
            redirect('/admin/transbank');
        }
        if ($secret !== '' && (strlen($secret) < 16 || strlen($secret) > 255)) {
            $_SESSION['error'] = 'La Tbk-Api-Key-Secret no tiene un largo válido.';
            redirect('/admin/transbank');
        }

        try {
            PaymentMetadata::ensureSchema();
            $encrypted = $secret !== '' ? PaymentSettings::encrypt($secret) : (string) $current['secret_key_encrypted'];
            $statement = Database::db()->prepare("INSERT INTO payment_settings(provider,active,environment,api_key,secret_key_encrypted) VALUES('transbank',?,?,?,?) ON DUPLICATE KEY UPDATE active=VALUES(active),environment=VALUES(environment),api_key=VALUES(api_key),secret_key_encrypted=VALUES(secret_key_encrypted)");
            $statement->execute([
                isset($_POST['active']) ? 1 : 0,
                $environment,
                $commerceCode,
                $encrypted,
            ]);
            $_SESSION['success'] = 'Configuración de Transbank guardada correctamente.';
        } catch (Throwable $exception) {
            error_log('Transbank settings: '.$exception->getMessage());
            $_SESSION['error'] = 'No fue posible guardar la configuración de Transbank.';
        }
        redirect('/admin/transbank');
    }
}
