<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;

/**
 * "Mis pedidos": el historial de compras del cliente.
 *
 * Existe gracias a `ventas.user_id` de la Fase 2 (H-10). Antes una venta no
 * sabía de quién era, así que esta pantalla no se podía escribir.
 */
class VentaController extends Controller
{
    public function index()
    {
        // Se consulta desde la relación del usuario, no desde Venta::where():
        // así no hay forma de escribir la consulta que devuelva las de otro.
        $ventas = Auth::user()->ventas()->withCount('items')->paginate(10);

        return view('mis-pedidos.index', compact('ventas'));
    }

    public function show(Venta $venta)
    {
        // Segunda barrera, por si algún día se llega aquí por otra ruta (H-36).
        $this->authorize('view', $venta);

        $venta->load('items.producto');

        return view('mis-pedidos.show', compact('venta'));
    }
}
