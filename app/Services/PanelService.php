<?php

namespace App\Services;

use App\Enums\EstadoOrdenCompra;
use App\Models\MovimientoInventario;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Venta;

/**
 * Las cifras del panel (H-13).
 *
 * Responde a las tres preguntas de una mañana cualquiera: cuánto se ha
 * vendido, qué falta en la estantería y qué está por llegar. Vive en un
 * servicio y no en el controlador porque son consultas de negocio —qué cuenta
 * como "bajo mínimo", qué cuenta como "de hoy"— y porque así el día que haya
 * un informe o un correo diario, las cifras salen del mismo sitio.
 */
class PanelService
{
    public function resumen(): array
    {
        return $this->ventasDeHoy()
            + $this->reposicion()
            + $this->abastecimiento()
            + ['ultimosMovimientos' => $this->ultimosMovimientos()];
    }

    private function ventasDeHoy(): array
    {
        $ventas = Venta::whereDate('created_at', today());

        return [
            'totalVentasHoy' => (float) $ventas->clone()->sum('total'),
            'numeroVentasHoy' => $ventas->clone()->count(),
        ];
    }

    private function reposicion(): array
    {
        return [
            'bajoMinimo' => Producto::necesitaReposicion()->with('categoria')->orderBy('stock')->take(10)->get(),
            'totalBajoMinimo' => Producto::necesitaReposicion()->count(),
        ];
    }

    private function abastecimiento(): array
    {
        $pendientes = fn () => OrdenCompra::where('estado', EstadoOrdenCompra::Pendiente);

        return [
            'ordenesPendientes' => $pendientes()->with('proveedor')->latest('id')->take(5)->get(),
            'totalOrdenesPendientes' => $pendientes()->count(),
        ];
    }

    private function ultimosMovimientos()
    {
        return MovimientoInventario::with('producto')->latest('id')->take(8)->get();
    }
}
