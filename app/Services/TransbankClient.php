<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class TransbankClient
{
    private array $settings;

    public function __construct()
    {
        $this->settings = (array) config('transbank');
        $saved = PaymentSettings::transbank();
        if ($saved) {
            $this->settings['commerce_code'] = (string) $saved['api_key'];
            $this->settings['api_key_secret'] = (string) $saved['secret_key'];
            $this->settings['environment'] = (string) $saved['environment'];
            $this->settings['active'] = (bool) $saved['active'];
        }
        $environment = ($this->settings['environment'] ?? 'sandbox') === 'production' ? 'production' : 'sandbox';
        $this->settings['environment'] = $environment;
        $this->settings['api_url'] = $environment === 'production'
            ? 'https://webpay3g.transbank.cl'
            : 'https://webpay3gint.transbank.cl';
        if (!$this->configured()) {
            throw new RuntimeException('Transbank no está configurado o se encuentra inactivo.');
        }
    }

    public function configured(): bool
    {
        return ($this->settings['active'] ?? true)
            && preg_match('/^\d{8,20}$/', trim((string) ($this->settings['commerce_code'] ?? ''))) === 1
            && trim((string) ($this->settings['api_key_secret'] ?? '')) !== '';
    }

    public function environment(): string
    {
        return (string) $this->settings['environment'];
    }

    public function create(string $buyOrder, string $sessionId, int $amount, string $returnUrl): array
    {
        if ($amount <= 0) throw new RuntimeException('El monto de Transbank debe ser mayor que cero.');
        if (strlen($buyOrder) > 26 || strlen($sessionId) > 61 || strlen($returnUrl) > 256) {
            throw new RuntimeException('La orden o URL de retorno excede el largo permitido por Transbank.');
        }
        if ($this->environment() === 'production' && !$this->isPublicHttpsUrl($returnUrl)) {
            throw new RuntimeException('Transbank Producción requiere una APP_URL pública con HTTPS.');
        }
        $response = $this->request('POST', '', [
            'buy_order' => $buyOrder,
            'session_id' => $sessionId,
            'amount' => $amount,
            'return_url' => $returnUrl,
        ]);
        $url = trim((string) ($response['url'] ?? ''));
        $token = trim((string) ($response['token'] ?? ''));
        if (!$this->isTransbankUrl($url) || !$this->validToken($token)) {
            throw new RuntimeException('Transbank no entregó una respuesta de inicio válida.');
        }
        return ['url' => $url, 'token' => $token];
    }

    public function commit(string $token): array
    {
        $this->assertToken($token);
        return $this->request('PUT', '/'.rawurlencode($token));
    }

    public function status(string $token): array
    {
        $this->assertToken($token);
        return $this->request('GET', '/'.rawurlencode($token));
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        if (!function_exists('curl_init')) throw new RuntimeException('La extensión cURL de PHP es obligatoria para procesar pagos.');
        $url = rtrim((string) $this->settings['api_url'], '/').'/rswebpaytransaction/api/webpay/v1.2/transactions'.$path;
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Tbk-Api-Key-Id: '.trim((string) $this->settings['commerce_code']),
            'Tbk-Api-Key-Secret: '.trim((string) $this->settings['api_key_secret']),
        ];
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => (int) ($this->settings['http_timeout'] ?? 15),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_SLASHES);
        } elseif ($method === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $options[CURLOPT_POSTFIELDS] = '{}';
        }
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $error = curl_error($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($body === false) throw new RuntimeException('No fue posible conectar con Transbank: '.$error);
        $data = json_decode($body, true);
        if ($code < 200 || $code >= 300 || !is_array($data)) {
            $message = is_array($data) ? (string) ($data['error_message'] ?? $data['message'] ?? '') : '';
            throw new RuntimeException('Transbank rechazó la solicitud'.($message !== '' ? ': '.$message : '.'));
        }
        return $data;
    }

    private function assertToken(string $token): void
    {
        if (!$this->validToken($token)) throw new RuntimeException('Token de Transbank inválido.');
    }

    private function validToken(string $token): bool
    {
        return strlen($token) >= 20 && strlen($token) <= 160 && preg_match('/^[A-Za-z0-9._-]+$/', $token) === 1;
    }

    private function isTransbankUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') return false;
        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), ['webpay3g.transbank.cl', 'webpay3gint.transbank.cl'], true);
    }

    private function isPublicHttpsUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') return false;
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        return $host !== '' && !in_array($host, ['localhost', '127.0.0.1', '::1'], true) && !str_ends_with($host, '.local');
    }
}
