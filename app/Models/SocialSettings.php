<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use Throwable;

final class SocialSettings
{
    private static ?array $cached = null;

    public static function ensureSchema(): bool
    {
        static $ready = null;
        if ($ready !== null) return $ready;

        try {
            Database::db()->query('SELECT id FROM social_settings LIMIT 0');
            return $ready = true;
        } catch (Throwable) {
            // Instalaciones existentes reciben el módulo automáticamente.
        }

        try {
            Database::db()->exec("CREATE TABLE IF NOT EXISTS social_settings (
                id TINYINT UNSIGNED PRIMARY KEY,
                footer_active TINYINT(1) NOT NULL DEFAULT 1,
                instagram_url VARCHAR(500) NULL,
                instagram_active TINYINT(1) NOT NULL DEFAULT 0,
                facebook_url VARCHAR(500) NULL,
                facebook_active TINYINT(1) NOT NULL DEFAULT 0,
                tiktok_url VARCHAR(500) NULL,
                tiktok_active TINYINT(1) NOT NULL DEFAULT 0,
                youtube_url VARCHAR(500) NULL,
                youtube_active TINYINT(1) NOT NULL DEFAULT 0,
                x_url VARCHAR(500) NULL,
                x_active TINYINT(1) NOT NULL DEFAULT 0,
                whatsapp_number VARCHAR(30) NULL,
                whatsapp_message VARCHAR(300) NOT NULL DEFAULT 'Hola AstroSport, necesito ayuda con mis fotografías.',
                whatsapp_label VARCHAR(80) NOT NULL DEFAULT '¿Necesitas ayuda?',
                whatsapp_active TINYINT(1) NOT NULL DEFAULT 0,
                whatsapp_position ENUM('left','right') NOT NULL DEFAULT 'right',
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            Database::db()->exec("INSERT IGNORE INTO social_settings(id) VALUES(1)");
            return $ready = true;
        } catch (Throwable $exception) {
            error_log('AstroSport social settings schema: '.$exception->getMessage());
            return $ready = false;
        }
    }

    public static function get(): array
    {
        if (self::$cached !== null) return self::$cached;
        $defaults = self::defaults();
        if (!self::ensureSchema()) return self::$cached = $defaults;

        try {
            $row = Database::db()->query('SELECT * FROM social_settings WHERE id=1')->fetch() ?: [];
            return self::$cached = array_merge($defaults, $row);
        } catch (Throwable $exception) {
            error_log('AstroSport social settings read: '.$exception->getMessage());
            return self::$cached = $defaults;
        }
    }

    public static function save(array $settings): void
    {
        if (!self::ensureSchema()) {
            throw new \RuntimeException('No fue posible preparar la configuración de redes sociales.');
        }

        $sql = "INSERT INTO social_settings(
                    id,footer_active,instagram_url,instagram_active,facebook_url,facebook_active,
                    tiktok_url,tiktok_active,youtube_url,youtube_active,x_url,x_active,
                    whatsapp_number,whatsapp_message,whatsapp_label,whatsapp_active,whatsapp_position
                ) VALUES(1,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                    footer_active=VALUES(footer_active),instagram_url=VALUES(instagram_url),instagram_active=VALUES(instagram_active),
                    facebook_url=VALUES(facebook_url),facebook_active=VALUES(facebook_active),tiktok_url=VALUES(tiktok_url),
                    tiktok_active=VALUES(tiktok_active),youtube_url=VALUES(youtube_url),youtube_active=VALUES(youtube_active),
                    x_url=VALUES(x_url),x_active=VALUES(x_active),whatsapp_number=VALUES(whatsapp_number),
                    whatsapp_message=VALUES(whatsapp_message),whatsapp_label=VALUES(whatsapp_label),
                    whatsapp_active=VALUES(whatsapp_active),whatsapp_position=VALUES(whatsapp_position)";
        Database::db()->prepare($sql)->execute([
            $settings['footer_active'],
            $settings['instagram_url'], $settings['instagram_active'],
            $settings['facebook_url'], $settings['facebook_active'],
            $settings['tiktok_url'], $settings['tiktok_active'],
            $settings['youtube_url'], $settings['youtube_active'],
            $settings['x_url'], $settings['x_active'],
            $settings['whatsapp_number'], $settings['whatsapp_message'], $settings['whatsapp_label'],
            $settings['whatsapp_active'], $settings['whatsapp_position'],
        ]);
        self::$cached = null;
    }

    public static function networkLinks(?array $settings = null): array
    {
        $settings ??= self::get();
        if (empty($settings['footer_active'])) return [];
        $networks = [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'youtube' => 'YouTube',
            'x' => 'X',
        ];
        $links = [];
        foreach ($networks as $key => $label) {
            $url = trim((string) ($settings[$key.'_url'] ?? ''));
            if (!empty($settings[$key.'_active']) && self::isPublicUrl($url)) {
                $links[$key] = ['label' => $label, 'url' => $url];
            }
        }
        return $links;
    }

    public static function whatsappUrl(?array $settings = null): ?string
    {
        $settings ??= self::get();
        if (empty($settings['whatsapp_active'])) return null;
        $number = preg_replace('/\D+/', '', (string) ($settings['whatsapp_number'] ?? ''));
        if (strlen($number) < 8 || strlen($number) > 15) return null;
        $message = trim((string) ($settings['whatsapp_message'] ?? ''));
        return 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    public static function isPublicUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    public static function defaults(): array
    {
        return [
            'id' => 1,
            'footer_active' => 1,
            'instagram_url' => '', 'instagram_active' => 0,
            'facebook_url' => '', 'facebook_active' => 0,
            'tiktok_url' => '', 'tiktok_active' => 0,
            'youtube_url' => '', 'youtube_active' => 0,
            'x_url' => '', 'x_active' => 0,
            'whatsapp_number' => '',
            'whatsapp_message' => 'Hola AstroSport, necesito ayuda con mis fotografías.',
            'whatsapp_label' => '¿Necesitas ayuda?',
            'whatsapp_active' => 0,
            'whatsapp_position' => 'right',
        ];
    }
}
