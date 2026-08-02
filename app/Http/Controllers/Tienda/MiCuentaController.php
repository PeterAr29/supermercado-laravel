<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Portada de la cuenta del cliente.
 *
 * Reúne en un sitio lo que hasta ahora estaba disperso entre /profile y nada:
 * sus datos, sus pedidos y cuánto lleva gastado.
 */
class MiCuentaController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        return view('mi-cuenta', [
            'usuario' => $usuario,
            'ultimosPedidos' => $usuario->ventas()->take(5)->get(),
            'totalPedidos' => $usuario->ventas()->count(),
            'totalGastado' => (float) $usuario->ventas()->sum('total'),
        ]);
    }
}
