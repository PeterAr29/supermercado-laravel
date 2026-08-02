<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActualizarPrecioCompraRequest;
use App\Http\Requests\Admin\AsignarProductoRequest;
use App\Models\Producto;
use App\Models\Proveedor;

/**
 * Qué productos vende cada proveedor, y a qué precio nos los vende.
 *
 * Los métodos recibían `$producto_id` suelto y hacían el `find` a mano; ahora
 * el modelo llega resuelto por route model binding (H-20).
 */
class ProveedorProductoController extends Controller
{
    public function index(Proveedor $proveedor)
    {
        $this->authorize('view', $proveedor);

        $productos = $proveedor->productos()->orderBy('nombre')->paginate(10);

        return view('admin.proveedores.productos.index', compact('proveedor', 'productos'));
    }

    public function create(Proveedor $proveedor)
    {
        $this->authorize('update', $proveedor);

        // Solo los que aún no vende, para que el formulario no ofrezca algo
        // que la validación va a rechazar después (H-40).
        $productos = Producto::whereDoesntHave('proveedores', fn ($q) => $q->where('proveedor_id', $proveedor->id))
            ->orderBy('nombre')
            ->get();

        return view('admin.proveedores.productos.create', compact('proveedor', 'productos'));
    }

    public function store(AsignarProductoRequest $request, Proveedor $proveedor)
    {
        $proveedor->productos()->attach($request->producto_id, [
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()->route('admin.proveedor.productos.index', $proveedor)
            ->with('success', 'Producto asignado correctamente');
    }

    public function edit(Proveedor $proveedor, Producto $producto)
    {
        $this->authorize('update', $proveedor);

        // La línea del pivot, no el producto suelto: la vista necesita el
        // precio de compra, que vive en la relación.
        $asignado = $proveedor->productos()->findOrFail($producto->id);

        return view('admin.proveedores.productos.edit', [
            'proveedor' => $proveedor,
            'producto' => $asignado,
        ]);
    }

    public function update(ActualizarPrecioCompraRequest $request, Proveedor $proveedor, Producto $producto)
    {
        $proveedor->productos()->updateExistingPivot($producto->id, [
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()->route('admin.proveedor.productos.index', $proveedor)
            ->with('success', 'Precio actualizado');
    }

    public function destroy(Proveedor $proveedor, Producto $producto)
    {
        $this->authorize('update', $proveedor);

        $proveedor->productos()->detach($producto->id);

        return redirect()->route('admin.proveedor.productos.index', $proveedor)
            ->with('success', 'Producto retirado del proveedor');
    }
}
