<?php

namespace App\Http\Controllers;

use App\Exceptions\StockInsuficienteException;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CarritoController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    /**
     * Carrito activo.
     *
     * Autenticado: el carrito persistente del usuario, que sobrevive al cierre
     * de sesión. Invitado: uno identificado por la sesión (H-11).
     *
     * Antes 'user_id' existía en la tabla pero no se escribía nunca: al iniciar
     * sesión, el usuario perdía lo que hubiera añadido.
     */
    private function obtenerCarrito(): Carrito
    {
        if (Auth::check()) {
            return Auth::user()->carrito()->firstOrCreate([]);
        }

        if (Session::has('carrito_id')) {
            $carrito = Carrito::whereNull('user_id')->find(Session::get('carrito_id'));

            if ($carrito) {
                return $carrito;
            }
        }

        $carrito = Carrito::create();
        Session::put('carrito_id', $carrito->id);

        return $carrito;
    }

    /**
     * Líneas cuyo producto sigue en el catálogo.
     *
     * Un producto retirado (SoftDeletes, H-02) deja de resolverse desde su línea:
     * sin este filtro el carrito calcularía su subtotal como 0 y permitiría
     * pagar 0 por un producto que ya no se vende (H-32).
     */
    private function itemsVigentes(Carrito $carrito)
    {
        return $carrito->items()->whereHas('producto')->with('producto')->get();
    }

    private function calcularTotal($items): float
    {
        return (float) $items->sum(fn ($i) => $i->cantidad * $i->producto->precio);
    }

    public function index()
    {
        $items = $this->itemsVigentes($this->obtenerCarrito());

        return view('carrito.index', compact('items'));
    }

    public function agregar(Request $request)
    {
        $datos = $request->validate([
            // 'exists' a secas consulta la tabla en crudo, sin el filtro de
            // SoftDeletes: un producto retirado pasaba la validación y salía
            // "Producto agregado al carrito" sin agregar nada (H-41).
            'producto_id' => ['required', Rule::exists('productos', 'id')->whereNull('deleted_at')],
            'cantidad' => 'nullable|integer|min:1|max:99',
        ]);

        $producto = Producto::findOrFail($datos['producto_id']);
        $carrito = $this->obtenerCarrito();
        $cantidad = $datos['cantidad'] ?? 1;

        $item = $carrito->items()->where('producto_id', $producto->id)->first();
        $solicitado = $cantidad + ($item->cantidad ?? 0);

        // Avisar aquí y no en la caja (H-35). El descuento real se comprueba
        // otra vez al pagar, con la fila bloqueada: esto es cortesía, no
        // garantía — entre añadir y pagar, otro puede haberse llevado la última.
        if (! $producto->hayStock($solicitado)) {
            return redirect()->back()->with(
                'error',
                "Solo quedan {$producto->stock} unidades de «{$producto->nombre}»."
            );
        }

        if ($item) {
            $item->increment('cantidad', $cantidad);
        } else {
            $carrito->items()->create([
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
            ]);
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito');
    }

    /**
     * Solo se pueden borrar líneas del propio carrito.
     *
     * Antes bastaba con conocer el id para borrar la línea de cualquiera (H-36).
     */
    public function eliminar($id)
    {
        $carrito = $this->obtenerCarrito();

        $item = CarritoItem::where('carrito_id', $carrito->id)->findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Producto eliminado del carrito');
    }

    public function vaciar()
    {
        $this->obtenerCarrito()->items()->delete();

        return redirect()->back()->with('success', 'Carrito vaciado correctamente');
    }

    public function mostrarPago()
    {
        $items = $this->itemsVigentes($this->obtenerCarrito());

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'El carrito está vacío.');
        }

        // Un carrito puede quedarse días abierto mientras el stock se agota.
        // Mejor decirlo antes de la pantalla de pago que después de cobrar.
        $sinStock = $items->filter(fn ($item) => ! $item->producto->hayStock($item->cantidad));

        if ($sinStock->isNotEmpty()) {
            return redirect()->route('carrito.index')->with(
                'error',
                'Sin stock suficiente: '.$sinStock->map(
                    fn ($i) => "{$i->producto->nombre} (quedan {$i->producto->stock})"
                )->implode(', ').'.'
            );
        }

        $total = $this->calcularTotal($items);

        return view('pago.confirmar', compact('items', 'total'));
    }

    /**
     * Cobra el carrito y saca la mercancía del inventario.
     *
     * Hasta la Fase 3 esto creaba la venta y no tocaba el stock: se podía
     * vender indefinidamente un producto del que no quedaba ni una unidad
     * (H-35). Ahora cada línea genera su movimiento de salida, y si falta
     * stock de una sola, la transacción deshace la venta entera.
     */
    public function procesarPago()
    {
        $carrito = $this->obtenerCarrito();
        $items = $this->itemsVigentes($carrito);

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'El carrito está vacío.');
        }

        try {
            $venta = DB::transaction(function () use ($carrito, $items) {

                $venta = Venta::create([
                    // Nullable: la tienda permite comprar como invitado (H-10)
                    'user_id' => Auth::id(),
                    'total' => $this->calcularTotal($items),
                ]);

                foreach ($items as $item) {
                    $venta->items()->create([
                        'producto_id' => $item->producto_id,
                        'cantidad' => $item->cantidad,
                        'precio' => $item->producto->precio,
                    ]);

                    // El servicio bloquea la fila y comprueba el stock dentro
                    // de esta misma transacción: entre la comprobación y el
                    // descuento no cabe otra venta.
                    $this->inventario->salida(
                        $item->producto,
                        $item->cantidad,
                        "Venta #{$venta->id}",
                        $venta,
                        Auth::user()
                    );
                }

                $carrito->items()->delete();

                return $venta;
            });
        } catch (StockInsuficienteException $e) {
            return redirect()->route('carrito.index')->with('error', $e->getMessage());
        }

        return redirect()->route('pago.exito')->with('venta_id', $venta->id);
    }

    public function pagoExitoso()
    {
        return view('pago.exito', ['venta_id' => session('venta_id')]);
    }
}
