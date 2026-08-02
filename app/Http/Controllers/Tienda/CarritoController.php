<?php

namespace App\Http\Controllers\Tienda;

use App\Exceptions\CarritoVacioException;
use App\Exceptions\StockInsuficienteException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\AgregarAlCarritoRequest;
use App\Models\Carrito;
use App\Models\Producto;
use App\Services\CarritoService;
use App\Services\CheckoutService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Carrito y pago.
 *
 * Tras la Fase 4 este controlador no calcula, no valida y no toca la base:
 * resuelve de qué carrito hablamos, se lo pasa al servicio y traduce lo que
 * salga en una redirección. Lo único que sigue siendo suyo es la sesión, que
 * es HTTP y de nadie más.
 */
class CarritoController extends Controller
{
    public function __construct(
        private readonly CarritoService $carritos,
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * El carrito de quien esté navegando.
     *
     * Autenticado: el suyo, que sobrevive al cierre de sesión. Invitado: uno
     * identificado por la sesión (H-11).
     */
    private function carritoActual(): Carrito
    {
        if (Auth::check()) {
            return $this->carritos->paraUsuario(Auth::user());
        }

        $carrito = $this->carritos->paraInvitado(Session::get('carrito_id'));
        Session::put('carrito_id', $carrito->id);

        return $carrito;
    }

    public function index()
    {
        $items = $this->carritos->itemsVigentes($this->carritoActual());

        // El total viaja calculado: la vista lo imprime, no lo suma (H-13).
        return view('carrito.index', [
            'items' => $items,
            'total' => $this->carritos->total($items),
        ]);
    }

    public function agregar(AgregarAlCarritoRequest $request)
    {
        $producto = Producto::findOrFail($request->producto_id);

        try {
            $this->carritos->agregar($this->carritoActual(), $producto, $request->cantidad());
        } catch (StockInsuficienteException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Producto agregado al carrito');
    }

    public function eliminar(int $id)
    {
        $this->carritos->eliminarLinea($this->carritoActual(), $id);

        return back()->with('success', 'Producto eliminado del carrito');
    }

    public function vaciar()
    {
        $this->carritos->vaciar($this->carritoActual());

        return back()->with('success', 'Carrito vaciado correctamente');
    }

    public function mostrarPago()
    {
        $items = $this->carritos->itemsVigentes($this->carritoActual());

        if ($problema = $this->problemaAntesDePagar($items)) {
            return redirect()->route('carrito.index')->with('error', $problema);
        }

        return view('pago.confirmar', [
            'items' => $items,
            'total' => $this->carritos->total($items),
        ]);
    }

    public function procesarPago()
    {
        try {
            $venta = $this->checkout->cobrar($this->carritoActual(), Auth::user());
        } catch (CarritoVacioException|StockInsuficienteException $e) {
            return redirect()->route('carrito.index')->with('error', $e->getMessage());
        }

        return redirect()->route('pago.exito')->with('venta_id', $venta->id);
    }

    public function pagoExitoso()
    {
        return view('pago.exito', ['venta_id' => session('venta_id')]);
    }

    /**
     * Motivo por el que no se puede pasar por caja, o null si todo está bien.
     *
     * Un carrito puede quedarse días abierto mientras el stock se agota. Mejor
     * decirlo antes de la pantalla de pago que después de cobrar (H-35).
     */
    private function problemaAntesDePagar(Collection $items): ?string
    {
        if ($items->isEmpty()) {
            return 'El carrito está vacío.';
        }

        $sinStock = $this->carritos->lineasSinStock($items);

        return $sinStock->isEmpty() ? null : $this->avisoSinStock($sinStock);
    }

    private function avisoSinStock(Collection $lineas): string
    {
        $detalle = $lineas->map(
            fn ($linea) => "{$linea->producto->nombre} (quedan {$linea->producto->stock})"
        )->implode(', ');

        return "Sin stock suficiente: {$detalle}.";
    }
}
