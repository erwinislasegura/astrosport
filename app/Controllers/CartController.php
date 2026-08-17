<?php
namespace App\Controllers;

use App\Models\Photo;
use App\Models\PhotoPack;
use App\Models\PhotoSet;
use App\Models\ShopCart;

final class CartController
{
    public function index(): void
    {
        $items = ShopCart::items();
        view('store/cart', ['items' => $items, 'pageTitle' => 'Carrito | AstroSport', 'flowPage' => true, 'bodyClass' => 'inner', 'toplineLeft' => 'COMPRA SEGURA', 'toplineRight' => 'ENTREGA DIGITAL']);
    }

    public function add(): never
    {
        verify_csrf();
        $type = ($_POST['type'] ?? 'photo') === 'set' ? 'set' : 'photo';
        $id = (int) ($_POST['id'] ?? 0);
        $valid = $type === 'set' ? PhotoSet::find($id) : Photo::find($id);
        if ($valid) ShopCart::add($type, $id);
        if ($this->wantsJson()) $this->json();
        redirect('/carrito');
    }

    public function addMany(): never
    {
        verify_csrf();
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
        foreach ($ids as $id) {
            $photo = Photo::find($id);
            if ($photo && ($photo['individual_enabled'] ?? 1)) ShopCart::add('photo', $id);
        }
        if ($this->wantsJson()) $this->json();
        redirect('/carrito');
    }

    public function addPack(): never
    {
        verify_csrf();
        $setId = (int) ($_POST['set_id'] ?? 0);
        $returnPhotoId = (int) ($_POST['return_photo_id'] ?? 0);
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
        $set = PhotoSet::find($setId);
        $photos = Photo::ids($ids);
        $valid = $set && PhotoPack::matching($setId, count($ids)) && count($photos) === count($ids);
        foreach ($photos as $photo) if ((int) $photo['set_id'] !== $setId) $valid = false;
        if ($valid) ShopCart::addPack($setId, $ids);
        else $_SESSION['error'] = 'La cantidad seleccionada no corresponde a un combo activo.';
        if ($this->wantsJson()) $this->json((bool) $valid);
        redirect($valid ? '/carrito' : '/foto?id='.$returnPhotoId);
    }

    public function addSelection(): never
    {
        verify_csrf();
        $setId = (int) ($_POST['set_id'] ?? 0);
        $returnPhotoId = (int) ($_POST['return_photo_id'] ?? 0);
        $mode = (string) ($_POST['purchase_mode'] ?? 'auto');
        $packQuantity = (int) ($_POST['pack_quantity'] ?? 0);
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
        $set = PhotoSet::find($setId);

        if (!$set) {
            $this->selectionError('El set seleccionado no está disponible.', $returnPhotoId);
        }

        if ($mode === 'set') {
            if (empty($set['set_enabled'])) {
                $this->selectionError('La compra del set completo no está habilitada.', $returnPhotoId);
            }
            ShopCart::add('set', $setId);
            if ($this->wantsJson()) $this->json();
            redirect('/carrito');
        }

        $photos = Photo::ids($ids);
        $validPhotos = $ids && count($photos) === count($ids);
        foreach ($photos as $photo) {
            if ((int) $photo['set_id'] !== $setId) $validPhotos = false;
        }
        if (!$validPhotos) {
            $this->selectionError('Selecciona fotografías válidas del mismo set.', $returnPhotoId);
        }

        if ($mode === 'pack') {
            $pack = PhotoPack::matching($setId, $packQuantity);
            if (!$pack || count($ids) !== $packQuantity) {
                $this->selectionError('Debes seleccionar exactamente '.$packQuantity.' fotografías para este combo.', $returnPhotoId);
            }
            ShopCart::addPack($setId, $ids);
        } elseif ($mode === 'individual') {
            if (empty($set['individual_enabled'])) {
                $this->selectionError('La compra de fotografías individuales no está habilitada.', $returnPhotoId);
            }
            foreach ($ids as $id) ShopCart::add('photo', $id);
        } else {
            // Compatibilidad con formularios anteriores: un número exacto aplica combo;
            // cualquier otra selección utiliza valor individual cuando está habilitado.
            $pack = PhotoPack::matching($setId, count($ids));
            if ($pack) ShopCart::addPack($setId, $ids);
            elseif (!empty($set['individual_enabled'])) foreach ($ids as $id) ShopCart::add('photo', $id);
            else $this->selectionError('Selecciona una cantidad correspondiente a un combo activo.', $returnPhotoId);
        }
        if ($this->wantsJson()) $this->json();
        redirect('/carrito');
    }

    public function remove(): never
    {
        verify_csrf();
        ShopCart::remove((string) ($_POST['key'] ?? 'photo:'.(int) ($_POST['id'] ?? 0)));
        if ($this->wantsJson()) $this->json();
        redirect('/carrito');
    }

    private function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    private function selectionError(string $message, int $returnPhotoId): never
    {
        if ($this->wantsJson()) $this->json(false, $message);
        $_SESSION['error'] = $message;
        redirect('/foto?id='.$returnPhotoId);
    }

    private function json(bool $ok = true, ?string $error = null): never
    {
        $items = ShopCart::items();
        $result = [];
        foreach ($items as $item) {
            $meta = $item['type'] === 'set' ? $item['photos_count'].' fotografías · Set completo' : ($item['type'] === 'pack' ? $item['photos_count'].' fotografías · Combo personalizado' : 'Fotografía individual');
            $result[] = ['key' => $item['key'], 'type' => $item['type'], 'title' => $item['title'], 'event_name' => $item['event_name'], 'price' => $item['price'], 'price_formatted' => money($item['price']), 'image' => preview_url(['id' => $item['cover_id'] ?? $item['photo_id'], 'preview_path' => $item['preview_path']]), 'meta' => $meta];
        }
        $total = (int) array_sum(array_column($items, 'price'));
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => $ok, 'error' => $error, 'count' => count($result), 'total' => $total, 'total_formatted' => money($total), 'items' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
