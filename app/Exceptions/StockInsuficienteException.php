<?php

namespace App\Exceptions;

use App\Models\Producto;
use RuntimeException;

/**
 * H-35 — No hay bastante stock para la salida pedida.
 *
 * Es una excepción y no un `return false` a propósito: ocurre dentro de una
 * transacción de venta, y lo que tiene que pasar es que la venta entera se
 * deshaga. Un booleano que alguien olvide comprobar deja media venta escrita.
 */
class StockInsuficienteException extends RuntimeException
{
    public function __construct(
        public readonly Producto $producto,
        public readonly int $solicitado,
        public readonly int $disponible,
    ) {
        parent::__construct(
            "No hay stock suficiente de «{$producto->nombre}»: se piden {$solicitado} y quedan {$disponible}."
        );
    }
}
