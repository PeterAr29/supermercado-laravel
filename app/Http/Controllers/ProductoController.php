<?php

namespace App\Http\Controllers;

use App\Enums\UnidadMedida;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\ProveedorSheetService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    // ===============================
    // LISTADO + BUSCADOR + FILTRO
    // ===============================
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

    // ===============================
    // FORMULARIO CREAR
    // ===============================
    public function create()
    {
        $categorias = Categoria::all();

        return view('productos.create', compact('categorias'));
    }

    // ===============================
    // GUARDAR PRODUCTO
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'imagen' => 'required',
            'categoria_id' => 'required|exists:categorias,id',
            // El select existía en el formulario desde diciembre, pero el
            // controlador nunca lo recogía: no se guardaba nunca (H-25).
            'unidad_medida' => ['required', Rule::enum(UnidadMedida::class)],
        ]);

        Producto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $request->imagen,
            'categoria_id' => $request->categoria_id,
            'unidad_medida' => $request->unidad_medida,
        ]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    // ===============================
    // FORMULARIO EDITAR
    // ===============================
    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();

        return view('productos.edit', compact('producto', 'categorias'));
    }

    // ===============================
    // ACTUALIZAR PRODUCTO
    // ===============================
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'imagen' => 'required',
            'categoria_id' => 'required|exists:categorias,id',
            // El select existía en el formulario desde diciembre, pero el
            // controlador nunca lo recogía: no se guardaba nunca (H-25).
            'unidad_medida' => ['required', Rule::enum(UnidadMedida::class)],
        ]);

        $producto->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $request->imagen,
            'categoria_id' => $request->categoria_id,
            'unidad_medida' => $request->unidad_medida,
        ]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // ===============================
    // ELIMINAR
    // ===============================
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    // ===============================
    // VER PRODUCTO + PROVEEDORES + SIMILARES
    // ===============================
    public function show($id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);

        // Proveedores desde Google Sheets
        $proveedores = (new ProveedorSheetService)
            ->obtenerPorProducto($producto->id);

        // Productos similares (misma categoría, excluyendo el actual)
        $productosSimilares = Producto::where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $producto->id)
            ->take(6)
            ->get();

        return view('productos.show', compact(
            'producto',
            'proveedores',
            'productosSimilares'
        ));
    }

    // ===============================
    // PRODUCTOS POR CATEGORÍA
    // ===============================
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
