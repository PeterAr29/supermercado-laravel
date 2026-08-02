<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoOrdenCompra;
use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Venta;

/**
 * La portada del panel: qué pasó hoy y qué hay que atender.
 *
 * Las tres cifras contestan a las tres preguntas de una mañana cualquiera:
 * cuánto se ha vendido, qué falta en la estantería y qué está por llegar.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $ventasHoy = Venta::whereDate('created_at', today());

        return view('admin.dashboard', [
            'totalVentasHoy' => (float) $ventasHoy->clone()->sum('total'),
            'numeroVentasHoy' => $ventasHoy->clone()->count(),

            'bajoMinimo' => Producto::necesitaReposicion()
                ->with('categoria')
                ->orderBy('stock')
                ->take(10)
                ->get(),
            'totalBajoMinimo' => Producto::necesitaReposicion()->count(),

            'ordenesPendientes' => OrdenCompra::with('proveedor')
                ->where('estado', EstadoOrdenCompra::Pendiente)
                ->latest('id')
                ->take(5)
                ->get(),
            'totalOrdenesPendientes' => OrdenCompra::where('estado', EstadoOrdenCompra::Pendiente)->count(),

            'ultimosMovimientos' => MovimientoInventario::with('producto')
                ->latest('id')
                ->take(8)
                ->get(),
        ]);
    }
}
