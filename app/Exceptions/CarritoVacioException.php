<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * No hay nada que cobrar.
 *
 * Ocurre con el carrito vacío, pero también cuando todas sus líneas apuntan a
 * productos retirados del catálogo: la pantalla enseñaba líneas y al pagar no
 * quedaba ninguna vigente (H-32).
 */
class CarritoVacioException extends RuntimeException
{
    public function __construct(string $mensaje = 'El carrito está vacío.')
    {
        parent::__construct($mensaje);
    }
}
