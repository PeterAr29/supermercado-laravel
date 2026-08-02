<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraController extends Controller
{
    // Listado de órdenes
    public function index()
    {
        $ordenes = OrdenCompra::with('proveedor')->orderBy('id', 'desc')->paginate(10);
        return view('ordenes.index', compact('ordenes'));
    }

    // Crear nueva orden
    public function create()
    {
        $proveedores = Proveedor::all();
        return view('ordenes.create', compact('proveedores'));
    }

    // Productos del proveedor (AJAX)
    public function productosProveedor($proveedor_id)
    {
        $proveedor = Proveedor::findOrFail($proveedor_id);
        return response()->json($proveedor->productos);
    }

    // Guardar orden de compra
    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required',
            'productos' => 'required|array',
            'cantidades' => 'required|array',
        ]);

        DB::beginTransaction();

        $orden = OrdenCompra::create([
            'proveedor_id' => $request->proveedor_id,
            'estado' => 'pendiente',
            'total' => 0
        ]);

        $total = 0;

        foreach ($request->productos as $index => $producto_id) {

            // Buscar el producto con su información del pivot proveedor-producto
            $producto = Producto::findOrFail($producto_id);
            $proveedor = Proveedor::find($request->proveedor_id);

            // Obtener el precio del producto del pivot proveedor-producto
            $precio = $proveedor->productos()
                        ->where('producto_id', $producto_id)
                        ->first()
                        ->pivot->precio;

            $cantidad = $request->cantidades[$index];
            $subtotal = $precio * $cantidad;
            $total += $subtotal;

            // Crear item correcto usando tus columnas reales
            OrdenCompraItem::create([
                'orden_id' => $orden->id,   // <- corregido
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'precio' => $precio,       // <- corregido
                'subtotal' => $subtotal
            ]);
        }

        $orden->update(['total' => $total]);

        DB::commit();

        return redirect()->route('ordenes.index')->with('success', 'Orden creada correctamente.');
    }

    // Ver detalles
    public function show(OrdenCompra $orden)
    {
        $orden->load('items.producto', 'proveedor');
        return view('ordenes.show', compact('orden'));
    }

    // Marcar como recibida + actualizar stock
    public function recibir(OrdenCompra $orden)
    {
        if ($orden->estado == 'recibido') {
            return back()->with('error', 'La orden ya fue recibida.');
        }

        foreach ($orden->items as $item) {
            $producto = $item->producto;
            $producto->stock += $item->cantidad;
            $producto->save();
        }

        $orden->estado = 'recibido';
        $orden->save();

        return back()->with('success', 'Orden marcada como recibida y stock actualizado.');
    }
}
