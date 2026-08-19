<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\Category;
use App\Models\Event;
use App\Models\Photo;
use App\Models\PhotoSet;
use Throwable;

final class StoreController
{
    public function index(): void
    {
        $db = Database::db();
        $this->ensureHeroMediaFields($db);
        Category::ensureSchema();
        $categories = Category::all(true);
        $this->upgradeLegacyCta($db);
        $cta = $db->query("SELECT * FROM homepage_cta WHERE id=1 AND active=1")->fetch() ?: null;
        $hero = null;
        try {
            $hero = $db->query("SELECT * FROM homepage_hero WHERE id=1")->fetch() ?: null;
        } catch (Throwable $e) {
        }

        [$coverExpression, $previewOrder] = $this->coverSql($db);
        $events = Event::publishedEvents(6);
        $featuredFilter = PhotoSet::ensureFeaturedHomeColumn() ? 'ps.featured_home=1' : '0=1';
        $sets = $db->query("SELECT ps.*,e.name event_name,COUNT(p.id) photos_count,$coverExpression cover_id,MIN(p.preview_path) preview_path
            FROM photo_sets ps
            JOIN events e ON e.id=ps.event_id
            JOIN photos p ON p.set_id=ps.id
            WHERE ps.status='active' AND ps.set_enabled=1 AND $featuredFilter AND e.status='published'
            GROUP BY ps.id ORDER BY ps.id DESC")->fetchAll();

        $query = trim($_GET['q'] ?? '');
        $catalogSql = "SELECT ps.*,e.name event_name,c.name category_name,c.slug category_slug,COUNT(p.id) photos_count,$coverExpression cover_id,MIN(p.preview_path) preview_path,MIN(p.price) individual_price
            FROM photo_sets ps
            JOIN events e ON e.id=ps.event_id
            LEFT JOIN categories c ON c.id=ps.category_id
            JOIN photos p ON p.set_id=ps.id
            WHERE ps.status='active' AND e.status='published'";
        $catalogArgs = [];
        if ($query !== '') {
            $catalogSql .= ' AND (ps.bib_number LIKE ? OR ps.name LIKE ? OR e.name LIKE ?)';
            $catalogArgs = ["%$query%", "%$query%", "%$query%"];
        }
        $categorySlug = trim($_GET['category'] ?? '');
        if ($categorySlug !== '') {
            $catalogSql .= ' AND c.slug=?';
            $catalogArgs[] = $categorySlug;
        }
        $catalogSql .= ' GROUP BY ps.id ORDER BY ps.id DESC';
        $catalogQuery = $db->prepare($catalogSql);
        $catalogQuery->execute($catalogArgs);
        $catalogSets = $catalogQuery->fetchAll();

        $setPreviews = [];
        $rows = $db->query("SELECT p.id,p.set_id,p.preview_path FROM photos p
            JOIN photo_sets ps ON ps.id=p.set_id
            JOIN events e ON e.id=ps.event_id
            WHERE ps.status='active' AND e.status='published'
            ORDER BY $previewOrder")->fetchAll();
        foreach ($rows as $row) {
            if (count($setPreviews[$row['set_id']] ?? []) < 4) {
                $setPreviews[$row['set_id']][] = $row;
            }
        }

        $bodyClass = 'home-page';
        view('store/index', compact('events', 'categories', 'categorySlug', 'sets', 'catalogSets', 'setPreviews', 'cta', 'hero', 'bodyClass'));
    }

    public function heroImage(): never
    {
        $hero = Database::db()->query("SELECT background_url,updated_at FROM homepage_hero WHERE id=1")->fetch();
        $path = trim((string)($hero['background_url'] ?? ''));
        if ($path === '' || preg_match('~^https?://~i', $path)) {
            http_response_code(404);
            exit('Imagen del hero no disponible');
        }

        $storageRoot = realpath(ROOT.'/storage/hero');
        $file = realpath(ROOT.'/'.ltrim($path, '/'));
        if (
            !$storageRoot ||
            !$file ||
            !str_starts_with($file, $storageRoot.DIRECTORY_SEPARATOR) ||
            !is_file($file)
        ) {
            http_response_code(404);
            exit('Imagen del hero no disponible');
        }

        $mime = mime_content_type($file) ?: 'application/octet-stream';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(415);
            exit('Formato de imagen no permitido');
        }

        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($file));
        header('Content-Disposition: inline; filename="hero.'.pathinfo($file, PATHINFO_EXTENSION).'"');
        header('Cache-Control: public, max-age=300, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        readfile($file);
        exit;
    }

    public function events(): void
    {
        view('store/events', [
            'events' => Event::publishedEvents(),
            'pageTitle' => 'Eventos deportivos | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'inner events-page',
            'toplineRight' => 'COLECCIONES FOTOGRÁFICAS',
        ]);
    }

    public function event(): void
    {
        $identifier = trim($_GET['slug'] ?? '');
        if ($identifier === '') {
            $identifier = (int)($_GET['id'] ?? 0);
        }
        $event = Event::findPublic($identifier);
        if (!$event) {
            http_response_code(404);
            exit('Evento no encontrado');
        }
        $sets = Event::publicSets((int)$event['id']);
        $setPreviews = Event::previewsForSets($sets);
        if (!$sets) {
            http_response_code(404);
            exit('Este evento todavía no tiene sets publicados');
        }
        view('store/event', [
            'event' => $event,
            'sets' => $sets,
            'setPreviews' => $setPreviews,
            'pageTitle' => $event['name'].' | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'inner',
            'toplineRight' => count($sets).' SETS DISPONIBLES',
        ]);
    }

    public function photo(): void
    {
        Category::ensureSchema();
        $photo = Photo::find((int)($_GET['id'] ?? 0));
        if (!$photo) {
            http_response_code(404);
            exit('Foto no encontrada');
        }
        $related = $photo['set_id'] ? PhotoSet::photos((int)$photo['set_id']) : [$photo];
        view('store/photo', [
            'photo' => $photo,
            'related' => $related,
            'pageTitle' => 'Detalle de fotografía | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'inner photo-page',
            'toplineRight' => 'SELECCIONA TUS FOTOS',
        ]);
    }

    public function faq(): void
    {
        view('store/faq', [
            'pageTitle' => 'Preguntas frecuentes | AstroSport',
            'flowPage' => true,
            'bodyClass' => 'inner',
            'faqPage' => true,
        ]);
    }

    private function coverSql(\PDO $db): array
    {
        try {
            $db->query('SELECT cover_photo_id FROM photo_sets LIMIT 0');
            return [
                'COALESCE(MAX(CASE WHEN p.id=ps.cover_photo_id THEN p.id END),MIN(p.id))',
                'p.set_id,(p.id=ps.cover_photo_id) DESC,p.id',
            ];
        } catch (Throwable $e) {
            return ['MIN(p.id)', 'p.set_id,p.id'];
        }
    }

    private function ensureHeroMediaFields(\PDO $db): void
    {
        try {
            $columns = $db->query('SHOW COLUMNS FROM homepage_hero')->fetchAll(\PDO::FETCH_COLUMN);
            if (!in_array('media_type', $columns, true)) {
                $db->exec("ALTER TABLE homepage_hero ADD media_type VARCHAR(20) NOT NULL DEFAULT 'youtube' AFTER button_text");
            }
            if (!in_array('video_url', $columns, true)) {
                $db->exec("ALTER TABLE homepage_hero ADD video_url VARCHAR(500) NULL AFTER media_type");
            }
            $db->exec("UPDATE homepage_hero SET video_url='https://www.youtube.com/watch?v=1MieluM0c6c' WHERE media_type='youtube' AND (video_url IS NULL OR video_url='')");
        } catch (Throwable $exception) {
            error_log('Hero schema: '.$exception->getMessage());
        }
    }

    private function upgradeLegacyCta(\PDO $db): void
    {
        try {
            $statement = $db->prepare("UPDATE homepage_cta SET event_id=NULL,eyebrow=?,title=?,description='',button_text=?,button_url=?,active=1 WHERE id=1 AND title='TRAIL VOLCÁN ANTUCO 2026'");
            $statement->execute(['Para clubes y organizaciones', '¿Necesitas cobertura para tu evento?', 'Solicitar propuesta', 'mailto:contacto@astrosport.cl']);
        } catch (Throwable $exception) {
            error_log('CTA migration: '.$exception->getMessage());
        }
    }
}
