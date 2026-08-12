<?php
namespace App\Models;

use App\Core\Database;

final class Photo
{
    private static function select(): string
    {
        return "SELECT p.*,e.name event_name,e.event_date,COALESCE(ps.individual_enabled,1) individual_enabled,COALESCE(ps.set_enabled,0) set_enabled,ps.set_price,ps.name set_name,ps.category_id,c.name category_name,c.slug category_slug FROM photos p JOIN events e ON e.id=p.event_id LEFT JOIN photo_sets ps ON ps.id=p.set_id LEFT JOIN categories c ON c.id=ps.category_id";
    }

    private static function publicWhere(): string
    {
        return "((ps.id IS NOT NULL AND ps.status='active') OR (ps.id IS NULL AND p.status='active' AND e.status='published'))";
    }

    public static function all(string $q = ''): array
    {
        Category::ensureSchema();
        $db = Database::db();
        $sql = self::select().' WHERE '.self::publicWhere();
        $args = [];
        if ($q !== '') {
            $sql .= ' AND (p.bib_number LIKE ? OR e.name LIKE ? OR ps.name LIKE ?)';
            $args = ["%$q%","%$q%","%$q%"];
        }
        $sql .= ' ORDER BY p.id DESC LIMIT 48';
        $s = $db->prepare($sql);
        $s->execute($args);
        return $s->fetchAll();
    }

    public static function find(int $id): ?array
    {
        Category::ensureSchema();
        $s = Database::db()->prepare(self::select().' WHERE p.id=? AND '.self::publicWhere());
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function ids(array $ids): array
    {
        Category::ensureSchema();
        if (!$ids) return [];
        $marks = implode(',',array_fill(0,count($ids),'?'));
        $s = Database::db()->prepare(self::select().' WHERE '.self::publicWhere()." AND p.id IN ($marks)");
        $s->execute($ids);
        return $s->fetchAll();
    }
}
