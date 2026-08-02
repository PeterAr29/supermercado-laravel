<?php

namespace App\Listeners;

use App\Models\Carrito;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * H-11 — Al iniciar sesión, lo que el usuario había añadido como invitado se
 * perdía: el carrito de sesión y el del usuario eran dos mundos separados.
 *
 * Este listener traslada las líneas del carrito de invitado al del usuario y
 * suma las cantidades cuando el mismo producto está en los dos (necesario
 * ahora que carrito_items tiene índice único por carrito+producto, H-25).
 */
class FusionarCarritoInvitado
{
    public function handle(Login $event): void
    {
        $carritoId = Session::get('carrito_id');

        if (! $carritoId) {
            return;
        }

        Session::forget('carrito_id');

        $invitado = Carrito::whereNull('user_id')->with('items')->find($carritoId);

        if (! $invitado || $invitado->items->isEmpty()) {
            $invitado?->delete();

            return;
        }

        DB::transaction(function () use ($invitado, $event) {

            $destino = $event->user->carrito()->firstOrCreate([]);

            foreach ($invitado->items as $item) {
                $existente = $destino->items()->where('producto_id', $item->producto_id)->first();

                if ($existente) {
                    $existente->increment('cantidad', $item->cantidad);
                    $item->delete();
                } else {
                    $item->update(['carrito_id' => $destino->id]);
                }
            }

            $invitado->delete();
        });
    }
}
