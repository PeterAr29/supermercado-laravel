<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;

/**
 * H-14 — La única política que no pregunta por el rol, sino por la propiedad.
 *
 * "Mis pedidos" es la primera pantalla donde un cliente ve datos suyos por id
 * en la URL. La Fase 2 ya enseñó cómo acaba eso si nadie comprueba de quién
 * son (H-36): con solo cambiar el número se leía lo de otro.
 */
class VentaPolicy
{
    public function view(User $user, Venta $venta): bool
    {
        // El administrador ve cualquier venta desde el panel; el cliente,
        // solo las suyas. Una venta de invitado (user_id null) no es de nadie.
        return $user->esAdmin() || ($venta->user_id !== null && $venta->user_id === $user->id);
    }
}
