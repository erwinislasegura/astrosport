<?php
namespace App\Models;

use App\Core\Database;
use Throwable;

final class PhotoPack
{
    public static function ensureSchema(): bool
    {
        static $ready = null;
        if ($ready !== null) return $ready;
        $db = Database::db();
        try {
            $columns = [
                'pack_enabled' => "ALTER TABLE photo_sets ADD COLUMN pack_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER set_enabled",
                'pack_quantity' => "ALTER TABLE photo_sets ADD COLUMN pack_quantity SMALLINT UNSIGNED NOT NULL DEFAULT 5 AFTER pack_enabled",
                'pack_price' => "ALTER TABLE photo_sets ADD COLUMN pack_price INT UNSIGNED NOT NULL DEFAULT 14990 AFTER pack_quantity",
            ];
            foreach ($columns as $column => $sql) {
                $check = $db->query("SHOW COLUMNS FROM photo_sets LIKE ".$db->quote($column));
                if (!$check->fetch()) $db->exec($sql);
            }
            $db->exec("CREATE TABLE IF NOT EXISTS photo_pack_options (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,set_id BIGINT UNSIGNED NOT NULL,slot TINYINT UNSIGNED NOT NULL,quantity SMALLINT UNSIGNED NOT NULL,price INT UNSIGNED NOT NULL,active TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY uq_pack_slot(set_id,slot),UNIQUE KEY uq_pack_quantity(set_id,quantity),CONSTRAINT fk_pack_set FOREIGN KEY(set_id) REFERENCES photo_sets(id) ON DELETE CASCADE) ENGINE=InnoDB");
            $selected = $db->query("SHOW COLUMNS FROM order_items LIKE 'selected_photo_ids'");
            if (!$selected->fetch()) $db->exec("ALTER TABLE order_items ADD COLUMN selected_photo_ids TEXT NULL AFTER item_title");
            $itemType = $db->query("SHOW COLUMNS FROM order_items LIKE 'item_type'");
            $definition = $itemType->fetch();
            if (!$definition || !str_contains((string) ($definition['Type'] ?? ''), "'pack'")) {
                $db->exec("ALTER TABLE order_items MODIFY item_type ENUM('photo','set','pack') NOT NULL DEFAULT 'photo'");
            }
            return $ready = true;
        } catch (Throwable $e) {
            error_log('AstroSport packs schema: '.$e->getMessage());
            return $ready = false;
        }
    }

    public static function forSet(int $setId, bool $onlyActive = true): array
    {
        if ($setId < 1) return [];
        if (!self::ensureSchema()) return self::legacyOptions($setId, $onlyActive);
        try {
            $sql = 'SELECT * FROM photo_pack_options WHERE set_id=?';
            if ($onlyActive) $sql .= ' AND active=1';
            $sql .= ' ORDER BY slot';
            $s = Database::db()->prepare($sql);
            $s->execute([$setId]);
            $options = $s->fetchAll();
            return $options ?: self::legacyOptions($setId, $onlyActive);
        } catch (Throwable $e) {
            return self::legacyOptions($setId, $onlyActive);
        }
    }

    public static function matching(int $setId, int $quantity): ?array
    {
        if ($setId < 1 || $quantity < 1) return null;
        if (!self::ensureSchema()) {
            $legacy = self::legacyOptions($setId);
            return (int)($legacy[0]['quantity'] ?? 0) === $quantity ? $legacy[0] : null;
        }
        try {
            $s = Database::db()->prepare('SELECT * FROM photo_pack_options WHERE set_id=? AND quantity=? AND active=1 ORDER BY slot LIMIT 1');
            $s->execute([$setId, $quantity]);
            $option = $s->fetch();
            if ($option) return $option;
            $legacy = self::legacyOptions($setId);
            return (int)($legacy[0]['quantity'] ?? 0) === $quantity ? $legacy[0] : null;
        } catch (Throwable $e) {
            $legacy = self::legacyOptions($setId);
            return (int)($legacy[0]['quantity'] ?? 0) === $quantity ? $legacy[0] : null;
        }
    }

    private static function legacyOptions(int $setId, bool $onlyActive = true): array
    {
        try {
            $s = Database::db()->prepare('SELECT id set_id,pack_enabled active,pack_quantity quantity,pack_price price FROM photo_sets WHERE id=? LIMIT 1');
            $s->execute([$setId]);
            $option = $s->fetch();
            if (!$option || ($onlyActive && empty($option['active'])) || (int)$option['quantity'] < 1) return [];
            $option['slot'] = 1;
            return [$option];
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function saveOptions(int $setId, array $options): void
    {
        if (!self::ensureSchema()) throw new \RuntimeException('No fue posible preparar los combos de fotografías.');
        $db = Database::db();
        $ownsTransaction = !$db->inTransaction();
        if ($ownsTransaction) $db->beginTransaction();
        try {
            $db->prepare('DELETE FROM photo_pack_options WHERE set_id=?')->execute([$setId]);
            $q = $db->prepare('INSERT INTO photo_pack_options(set_id,slot,quantity,price,active) VALUES(?,?,?,?,?)');
            foreach ([1,2,3] as $slot) {
                $o = $options[$slot] ?? [];
                $q->execute([$setId,$slot,max(2,(int)($o['quantity']??($slot*5))),max(0,(int)($o['price']??0)),!empty($o['active'])?1:0]);
            }
            if ($ownsTransaction) $db->commit();
        } catch (Throwable $e) {
            if ($ownsTransaction && $db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public static function fromPost(): array
    {
        $result = [];
        foreach ([1,2,3] as $slot) {
            $result[$slot] = [
                'active' => isset($_POST['pack_active'][$slot]),
                'quantity' => max(2,(int)($_POST['pack_quantity'][$slot]??$slot*5)),
                'price' => max(0,(int)($_POST['pack_price'][$slot]??0)),
            ];
        }
        return $result;
    }
}
