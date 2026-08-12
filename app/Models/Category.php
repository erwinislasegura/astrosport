<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class Category
{
    public static function ensureSchema(): bool
    {
        static $ready = null;
        if ($ready !== null) return $ready;
        $db = Database::db();
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS categories(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,slug VARCHAR(120) NOT NULL UNIQUE,status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            try { $db->query('SELECT category_id FROM photo_sets LIMIT 0'); }
            catch (Throwable $e) { $db->exec('ALTER TABLE photo_sets ADD COLUMN category_id INT UNSIGNED NULL AFTER event_id, ADD INDEX idx_photo_sets_category(category_id)'); }
            if ((int)$db->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 0) {
                $db->exec("INSERT INTO categories(name,slug,status) VALUES ('Atletismo','atletismo','active'),('Ciclismo','ciclismo','active'),('Fútbol','futbol','active'),('Running','running','active'),('Tenis de mesa','tenis-de-mesa','active'),('Otros deportes','otros-deportes','active')");
                $db->exec("UPDATE photo_sets ps JOIN events e ON e.id=ps.event_id LEFT JOIN categories c ON LOWER(c.name)=LOWER(e.sport) SET ps.category_id=COALESCE(c.id,(SELECT id FROM categories WHERE slug='otros-deportes' LIMIT 1)) WHERE ps.category_id IS NULL");
            }
            return $ready = true;
        } catch (Throwable $e) { return $ready = false; }
    }

    public static function all(bool $onlyActive = false): array
    {
        if (!self::ensureSchema()) return [];
        $sql = "SELECT c.*,COUNT(ps.id) sets_count FROM categories c LEFT JOIN photo_sets ps ON ps.category_id=c.id";
        if ($onlyActive) $sql .= " WHERE c.status='active'";
        $sql .= ' GROUP BY c.id ORDER BY c.name';
        return Database::db()->query($sql)->fetchAll();
    }

    public static function save(array $data): void
    {
        self::ensureSchema();
        $db = Database::db();
        $id = (int)($data['id'] ?? 0);
        $name = trim((string)($data['name'] ?? ''));
        $slug = self::slug($name);
        $status = ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        if ($id) $db->prepare('UPDATE categories SET name=?,slug=?,status=? WHERE id=?')->execute([$name,$slug,$status,$id]);
        else $db->prepare('INSERT INTO categories(name,slug,status) VALUES(?,?,?)')->execute([$name,$slug,$status]);
    }

    public static function delete(int $id): bool
    {
        self::ensureSchema();
        $db = Database::db();
        $s = $db->prepare('SELECT COUNT(*) FROM photo_sets WHERE category_id=?');
        $s->execute([$id]);
        if ((int)$s->fetchColumn() > 0) return false;
        $db->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
        return true;
    }

    private static function slug(string $value): string
    {
        $value = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value) ?: $value;
        return strtolower(trim((string)preg_replace('/[^a-z0-9]+/i','-',$value),'-'));
    }
}
