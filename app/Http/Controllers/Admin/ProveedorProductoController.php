<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorProductoController extends Controller
{
    // Lista de productos que vende ese proveedor
    public function index(Proveedor $proveedor)
    {
        $productos = $proveedor->productos()->paginate(10);

        return view('admin.proveedores.productos.index', compact('proveedor', 'productos'));
    }

    // Formulario para asignar un producto
    public function create(Proveedor $proveedor)
    {
        // Mostrar solo productos NO asignados
        $productos = Producto::whereDoesntHave('proveedores', function ($q) use ($proveedor) {
            $q->where('proveedor_id', $proveedor->id);
        })->get();

        return view('admin.proveedores.productos.create', compact('proveedor', 'productos'));
    }

    // Guardar la relación en la tabla pivot
    public function store(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'producto_id' => [
                'required',
                Rule::exists('productos', 'id')->whereNull('deleted_at'),
                // El índice único que añadió H-25 convirtió el attach()
                // repetido en un error 500 de la base. Que la base lo rechace
                // es lo correcto; lo que faltaba era que el controlador lo
                // previera y respondiera con un mensaje (H-40).
                Rule::unique('proveedor_producto', 'producto_id')
                    ->where('proveedor_id', $proveedor->id),
            ],
            'precio_compra' => 'required|numeric|min:0',
        ], [
            'producto_id.unique' => 'Ese producto ya está asignado a este proveedor.',
        ]);

        $proveedor->productos()->attach($request->producto_id, [
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()
            ->route('admin.proveedor.productos.index', $proveedor)
            ->with('success', 'Producto asignado correctamente');
    }

    // Editar precio de compra
    public function edit(Proveedor $proveedor, $producto_id)
    {
        $producto = $proveedor->productos()->where('producto_id', $producto_id)->first();

        return view('admin.proveedores.productos.edit', compact('proveedor', 'producto'));
    }

    public function update(Request $request, Proveedor $proveedor, $producto_id)
    {
        $request->validate([
            'precio_compra' => 'required|numeric',
        ]);

        $proveedor->productos()->updateExistingPivot($producto_id, [
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()->route('admin.proveedor.productos.index', $proveedor)
            ->with('success', 'Precio actualizado');
    }

    // Quitar producto del proveedor
    public function destroy(Proveedor $proveedor, $producto_id)
    {
        $proveedor->productos()->detach($producto_id);

        return redirect()
            ->route('admin.proveedor.productos.index', $proveedor)
            ->with('success', 'Producto retirado del proveedor');
    }
}
