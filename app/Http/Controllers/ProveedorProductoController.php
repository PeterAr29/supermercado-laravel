<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorProductoController extends Controller
{
    // Lista de productos que vende ese proveedor
    public function index(Proveedor $proveedor)
    {
        $productos = $proveedor->productos()->paginate(10);

        return view('proveedores.productos.index', compact('proveedor', 'productos'));
    }

    // Formulario para asignar un producto
    public function create(Proveedor $proveedor)
    {
        // Mostrar solo productos NO asignados
        $productos = Producto::whereDoesntHave('proveedores', function ($q) use ($proveedor) {
            $q->where('proveedor_id', $proveedor->id);
        })->get();

        return view('proveedores.productos.create', compact('proveedor', 'productos'));
    }

    // Guardar relación en la tabla pivot
    public function store(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'producto_id' => 'required',
            'precio_compra' => 'required|numeric',
        ]);

        $proveedor->productos()->attach($request->producto_id, [
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()
            ->route('proveedor.productos.index', $proveedor)
            ->with('success', 'Producto asignado correctamente');
    }

    // Editar precio de compra
    public function edit(Proveedor $proveedor, $producto_id)
    {
        $producto = $proveedor->productos()->where('producto_id', $producto_id)->first();

        return view('proveedores.productos.edit', compact('proveedor', 'producto'));
    }

    public function update(Request $request, Proveedor $proveedor, $producto_id)
    {
        $request->validate([
            'precio_compra' => 'required|numeric',
        ]);

        $proveedor->productos()->updateExistingPivot($producto_id, [
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()->route('proveedor.productos.index', $proveedor)
            ->with('success', 'Precio actualizado');
    }

    // Quitar producto del proveedor
    public function destroy(Proveedor $proveedor, $producto_id)
    {
        $proveedor->productos()->detach($producto_id);

        return redirect()
            ->route('proveedor.productos.index', $proveedor)
            ->with('success', 'Producto retirado del proveedor');
    }
}
