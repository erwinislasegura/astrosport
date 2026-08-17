<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\SocialSettings;
use Throwable;

final class SocialSettingsController
{
    private const NETWORKS = ['instagram', 'facebook', 'tiktok', 'youtube', 'x'];

    public function index(): void
    {
        admin_view('admin/social-settings', [
            'settings' => SocialSettings::get(),
            'pageTitle' => 'Redes sociales y WhatsApp',
            'adminSection' => 'social',
        ]);
    }

    public function save(): never
    {
        verify_csrf();
        $settings = SocialSettings::defaults();
        $settings['footer_active'] = isset($_POST['footer_active']) ? 1 : 0;

        foreach (self::NETWORKS as $network) {
            $url = $this->normalizeUrl((string) ($_POST[$network.'_url'] ?? ''));
            $active = isset($_POST[$network.'_active']);
            if (strlen($url) > 500) {
                $_SESSION['error'] = 'El enlace de '.ucfirst($network).' supera el largo permitido.';
                redirect('/admin/redes-sociales');
            }
            if ($url !== '' && !SocialSettings::isPublicUrl($url)) {
                $_SESSION['error'] = 'Revisa la dirección configurada para '.ucfirst($network).'.';
                redirect('/admin/redes-sociales');
            }
            if ($active && $url === '') {
                $_SESSION['error'] = 'Ingresa el enlace de '.ucfirst($network).' antes de activarlo.';
                redirect('/admin/redes-sociales');
            }
            $settings[$network.'_url'] = $url;
            $settings[$network.'_active'] = $active ? 1 : 0;
        }

        $number = preg_replace('/\D+/', '', (string) ($_POST['whatsapp_number'] ?? ''));
        $whatsappActive = isset($_POST['whatsapp_active']);
        if ($whatsappActive && (strlen($number) < 8 || strlen($number) > 15)) {
            $_SESSION['error'] = 'Ingresa el número de WhatsApp con código de país, solo con dígitos.';
            redirect('/admin/redes-sociales');
        }
        $message = trim((string) ($_POST['whatsapp_message'] ?? ''));
        $label = trim((string) ($_POST['whatsapp_label'] ?? ''));
        if ($this->textLength($message) > 300 || $this->textLength($label) > 80) {
            $_SESSION['error'] = 'El mensaje o el texto visible de WhatsApp supera el largo permitido.';
            redirect('/admin/redes-sociales');
        }
        if ($whatsappActive && $label === '') $label = '¿Necesitas ayuda?';

        $settings['whatsapp_number'] = $number;
        $settings['whatsapp_message'] = $message;
        $settings['whatsapp_label'] = $label;
        $settings['whatsapp_active'] = $whatsappActive ? 1 : 0;
        $settings['whatsapp_position'] = ($_POST['whatsapp_position'] ?? 'right') === 'left' ? 'left' : 'right';

        try {
            SocialSettings::save($settings);
            $_SESSION['success'] = 'Redes sociales y WhatsApp actualizados correctamente.';
        } catch (Throwable $exception) {
            error_log('AstroSport social settings save: '.$exception->getMessage());
            $_SESSION['error'] = 'No fue posible guardar la configuración de redes sociales.';
        }
        redirect('/admin/redes-sociales');
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url !== '' && !preg_match('~^https?://~i', $url)) $url = 'https://'.$url;
        return $url;
    }

    private function textLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
