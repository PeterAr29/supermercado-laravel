<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;

/**
 * Las categorías las ve cualquiera —son el menú de la tienda— pero solo las
 * gestiona el administrador. Por eso no hay 'viewAny' ni 'view' aquí.
 */
class CategoriaPolicy
{
    public function create(User $user): bool
    {
        return $user->esAdmin();
    }

    public function update(User $user, Categoria $categoria): bool
    {
        return $user->esAdmin();
    }

    /**
     * Solo responde por el permiso. Que la categoría tenga productos dentro no
     * es una cuestión de quién eres, sino del estado del dato: eso lo comprueba
     * el controlador, y así el usuario recibe un mensaje en vez de un 403 que
     * le haría pensar que le falta permiso.
     */
    public function delete(User $user, Categoria $categoria): bool
    {
        return $user->esAdmin();
    }
}
