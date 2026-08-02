<?php

namespace App\Policies;

use App\Models\Proveedor;
use App\Models\User;

/**
 * H-14 — Los proveedores son datos internos: ni siquiera se consultan desde
 * la tienda. Todo el ciclo, incluido leer, es de administración.
 */
class ProveedorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esAdmin();
    }

    public function view(User $user, Proveedor $proveedor): bool
    {
        return $user->esAdmin();
    }

    public function create(User $user): bool
    {
        return $user->esAdmin();
    }

    public function update(User $user, Proveedor $proveedor): bool
    {
        return $user->esAdmin();
    }

    public function delete(User $user, Proveedor $proveedor): bool
    {
        return $user->esAdmin();
    }
}
