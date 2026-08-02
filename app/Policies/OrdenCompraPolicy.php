<?php

namespace App\Policies;

use App\Models\OrdenCompra;
use App\Models\User;

/**
 * H-14 — Abastecimiento: solo administración.
 *
 * 'recibir' es un permiso aparte y no un alias de 'update' porque es la acción
 * que mueve stock de verdad. Dejarla suelta dentro de 'update' escondería la
 * única operación de esta clase con efecto sobre el inventario.
 */
class OrdenCompraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esAdmin();
    }

    public function view(User $user, OrdenCompra $orden): bool
    {
        return $user->esAdmin();
    }

    public function create(User $user): bool
    {
        return $user->esAdmin();
    }

    public function recibir(User $user, OrdenCompra $orden): bool
    {
        return $user->esAdmin() && $orden->estaPendiente();
    }
}
