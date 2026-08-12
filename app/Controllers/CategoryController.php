<?php
namespace App\Controllers;

use App\Models\Category;
use Throwable;

final class CategoryController
{
    public function index(): void
    {
        admin_view('admin/categories',['categories'=>Category::all(),'pageTitle'=>'Categorías','adminSection'=>'categories']);
    }

    public function save(): never
    {
        verify_csrf();
        if (trim((string)($_POST['name'] ?? '')) === '') {
            $_SESSION['error'] = 'Escribe un nombre para la categoría.';
            redirect('/admin/categorias');
        }
        try { Category::save($_POST); $_SESSION['success'] = 'Categoría guardada correctamente.'; }
        catch (Throwable $e) { $_SESSION['error'] = 'No fue posible guardar la categoría. Revisa que el nombre no esté repetido.'; }
        redirect('/admin/categorias');
    }

    public function delete(): never
    {
        verify_csrf();
        $deleted = Category::delete((int)($_POST['id'] ?? 0));
        $_SESSION[$deleted ? 'success' : 'error'] = $deleted ? 'Categoría eliminada.' : 'No puedes eliminar una categoría asignada a uno o más sets.';
        redirect('/admin/categorias');
    }
}
