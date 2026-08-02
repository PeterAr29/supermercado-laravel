<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoriaRequest;
use App\Http\Requests\Admin\UpdateCategoriaRequest;
use App\Models\Categoria;

/**
 * Gestión de categorías.
 *
 * Era alcance de la Fase 3 y se quedó fuera: hasta ahora las únicas categorías
 * que existían eran las que sembraba `CategoriaSeeder`, y para añadir una había
 * que tocar la base a mano.
 */
class CategoriaController extends Controller
{
    public function index()
    {
        $this->authorize('create', Categoria::class);

        $categorias = Categoria::withCount('productos')->orderBy('nombre')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        $this->authorize('create', Categoria::class);

        return view('admin.categorias.create');
    }

    public function store(StoreCategoriaRequest $request)
    {
        Categoria::create($request->validated());

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        $this->authorize('update', $categoria);

        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría actualizada.');
    }

    /**
     * `productos.categoria_id` es RESTRICT: si la categoría tiene productos,
     * la política dice que no y el usuario ve un mensaje en vez de un 500.
     */
    public function destroy(Categoria $categoria)
    {
        $this->authorize('delete', $categoria);

        if (! $categoria->sePuedeBorrar()) {
            return back()->with('error', 'No se puede borrar una categoría que todavía tiene productos.');
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada.');
    }
}
