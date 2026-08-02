<?php

namespace App\Http\Controllers;

use App\Enums\EstadoOrdenCompra;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'proveedor_id' => 'required|exists:proveedores,id',
            'productos' => 'required|array',
            'cantidades' => 'required|array',
        ]);

        $proveedor = Proveedor::findOrFail($request->proveedor_id);

        DB::transaction(function () use ($request, $proveedor) {

            $orden = OrdenCompra::create([
                'proveedor_id' => $proveedor->id,
                'estado' => EstadoOrdenCompra::Pendiente,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($request->productos as $index => $producto_id) {

                // El formulario envía todos los productos del proveedor,
                // con cantidad 0 los que no se piden.
                $cantidad = (int) ($request->cantidades[$index] ?? 0);

                if ($cantidad <= 0) {
                    continue;
                }

                // El precio de compra vive en el pivot proveedor-producto
                $asignado = $proveedor->productos()->where('producto_id', $producto_id)->first();

                if (! $asignado) {
                    throw ValidationException::withMessages([
                        'productos' => 'Hay productos que no están asignados a este proveedor.',
                    ]);
                }

                $precio = $asignado->pivot->precio_compra;
                $subtotal = $precio * $cantidad;
                $total += $subtotal;

                OrdenCompraItem::create([
                    'orden_id' => $orden->id,
                    'producto_id' => $producto_id,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $subtotal,
                ]);
            }

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'cantidades' => 'Indica una cantidad mayor que cero en al menos un producto.',
                ]);
            }

            $orden->update(['total' => $total]);
        });

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
        if ($orden->estaRecibida()) {
            return back()->with('error', 'La orden ya fue recibida.');
        }

        DB::transaction(function () use ($orden) {
            foreach ($orden->items as $item) {
                $item->producto->increment('stock', $item->cantidad);
            }

            $orden->update(['estado' => EstadoOrdenCompra::Recibido]);
        });

        return back()->with('success', 'Orden marcada como recibida y stock actualizado.');
    }
}
