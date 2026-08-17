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
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
        $photos = Photo::ids($ids);
        $set = PhotoSet::find($setId);
        $valid = $set && $ids && count($photos) === count($ids);
        foreach ($photos as $photo) if ((int) $photo['set_id'] !== $setId) $valid = false;
        if (!$valid) {
            $_SESSION['error'] = 'La selección de fotografías no es válida.';
            redirect('/foto?id='.$returnPhotoId);
        }
        $pack = PhotoPack::matching($setId, count($ids));
        if ($pack) {
            ShopCart::addPack($setId, $ids);
        } elseif (!empty($set['individual_enabled'])) {
            foreach ($ids as $id) ShopCart::add('photo', $id);
        } else {
            $_SESSION['error'] = 'Selecciona una cantidad correspondiente a uno de los combos disponibles.';
            redirect('/foto?id='.$returnPhotoId);
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

    private function json(bool $ok = true): never
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
        echo json_encode(['ok' => $ok, 'count' => count($result), 'total' => $total, 'total_formatted' => money($total), 'items' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
