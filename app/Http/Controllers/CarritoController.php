<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Order;
use App\Models\Producto;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    private function obtenerCarrito()
    {
        if (! Session::has('carrito_id')) {
            $carrito = Carrito::create();
            Session::put('carrito_id', $carrito->id);
        }

        return Carrito::find(Session::get('carrito_id'));
    }

    /**
     * Líneas cuyo producto sigue en el catálogo.
     *
     * Un producto retirado (SoftDeletes, H-02) deja de resolverse desde su línea:
     * sin este filtro el carrito calcularía su subtotal como 0 y permitiría
     * pagar 0 por un producto que ya no se vende.
     */
    private function itemsVigentes($carrito)
    {
        return $carrito->items()->whereHas('producto')->with('producto')->get();
    }

    public function index()
    {
        $carrito = $this->obtenerCarrito();
        $items = $this->itemsVigentes($carrito);

        return view('carrito.index', compact('items'));
    }

    public function agregar()
    {
        // ⛔ IMPORTANTE → Debe llamarse 'producto_id'
        $producto_id = request('producto_id');

        if (! $producto_id) {
            return redirect()->back()->with('error', 'Error: No se recibió producto_id.');
        }

        $producto = Producto::findOrFail($producto_id);
        $carrito = $this->obtenerCarrito();

        // Busca si ya existe
        $item = CarritoItem::where('carrito_id', $carrito->id)
            ->where('producto_id', $producto_id)
            ->first();

        if ($item) {
            $item->cantidad += 1;
            $item->save();
        } else {
            CarritoItem::create([
                'carrito_id' => $carrito->id,
                'producto_id' => $producto_id,
                'cantidad' => 1,
            ]);
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito');
    }

    public function eliminar($id)
    {
        $item = CarritoItem::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Producto eliminado del carrito');
    }

    public function vaciar()
    {
        $carrito = $this->obtenerCarrito();

        // Eliminar todos los items del carrito
        CarritoItem::where('carrito_id', $carrito->id)->delete();

        return redirect()->back()->with('success', 'Carrito vaciado correctamente');
    }

    public function mostrarPago()
    {
        $carrito = $this->obtenerCarrito();
        $items = $this->itemsVigentes($carrito);

        if ($items->isEmpty()) {
            return redirect('/carrito')->with('error', 'El carrito está vacío.');
        }

        $total = $items->sum(fn ($i) => $i->cantidad * $i->producto->precio);

        return view('pago.confirmar', compact('items', 'total'));
    }

    public function procesarPago()
    {
        $carrito = $this->obtenerCarrito();
        $items = $this->itemsVigentes($carrito);

        if ($items->isEmpty()) {
            return redirect('/carrito')->with('error', 'El carrito está vacío.');
        }

        $total = $items->sum(fn ($i) => $i->cantidad * $i->producto->precio);

        // Registrar orden
        $order = Order::create([
            'total' => $total,
        ]);

        // Registrar items
        foreach ($items as $item) {
            $order->items()->create([
                'producto_id' => $item->producto_id,
                'cantidad' => $item->cantidad,
                'precio' => $item->producto->precio,
            ]);
        }

        // Vaciar carrito
        $carrito->items()->delete();

        return redirect()->route('pago.exito')->with('order_id', $order->id);
    }

    public function pagoExitoso()
    {
        $order_id = session('order_id');

        return view('pago.exito', compact('order_id'));
    }
}
