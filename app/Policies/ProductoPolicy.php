<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;

/**
 * H-14 — Quién puede tocar el catálogo.
 *
 * Consultar no aparece aquí a propósito: el catálogo es la tienda y lo ve
 * cualquiera, incluso sin sesión iniciada. Lo que esta política gobierna es
 * crear, editar y retirar.
 *
 * El middleware 'admin' ya cubre la zona /admin entera; las políticas son la
 * segunda línea, la que sigue en pie si mañana una ruta se registra fuera del
 * grupo. La Fase 2 dejó claro por qué hace falta (H-36).
 */
class ProductoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esAdmin();
    }

    public function view(User $user, Producto $producto): bool
    {
        return $user->esAdmin();
    }

    public function create(User $user): bool
    {
        return $user->esAdmin();
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->esAdmin();
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->esAdmin();
    }
}
