<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

/**
 * El catálogo tal y como lo ve el cliente.
 *
 * Aquí solo se consulta. Crear, editar y retirar productos vive en
 * App\Http\Controllers\Admin\ProductoController, detrás de /admin: antes las
 * dos cosas convivían en el mismo controlador y en la misma pantalla, con los
 * botones de gestión pintados sobre la tienda (H-14).
 */
class ProductoController extends Controller
{
    /** Listado con buscador y filtro por categoría. */
    public function index(Request $request)
    {
        $productos = Producto::with('categoria')
            ->when($request->buscar, function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->buscar.'%');
            })
            ->when($request->categoria, function ($q) use ($request) {
                $q->where('categoria_id', $request->categoria);
            })
            ->get();

        $categorias = Categoria::all();

        return view('productos.index', compact('productos', 'categorias'));
    }

    public function show(Producto $producto)
    {
        $producto->load('categoria');

        return view('productos.show', [
            'producto' => $producto,
            // Quién lo surte, desde la base de datos. Antes venía de una hoja
            // de Google publicada: la tercera fuente de proveedores en
            // competencia y la única sin conexión con órdenes ni stock (H-21).
            'proveedores' => $producto->proveedores()->where('activo', true)->get(),
            'productosSimilares' => $this->similaresA($producto),
        ]);
    }

    /** Del mismo pasillo, para que la ficha no sea un callejón sin salida. */
    private function similaresA(Producto $producto)
    {
        return Producto::where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $producto->id)
            ->take(6)
            ->get();
    }

    public function porCategoria(Categoria $categoria)
    {
        $productos = Producto::where('categoria_id', $categoria->id)->get();
        $categorias = Categoria::all();

        return view('productos.index', compact(
            'productos',
            'categorias',
            'categoria'
        ));
    }
}
