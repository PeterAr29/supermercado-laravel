<?php

namespace App\Exceptions;

use App\Models\Proveedor;
use RuntimeException;

/**
 * Se pide a un proveedor un producto que no está en su catálogo.
 *
 * El formulario solo ofrece los productos asignados, así que llegar aquí exige
 * un POST manipulado o una asignación borrada entre que se abrió el formulario
 * y se envió. Sin esta comprobación, el precio de compra saldría de un pivot
 * inexistente y la orden se guardaría con importes en blanco.
 */
class ProductoNoAsignadoException extends RuntimeException
{
    public function __construct(public readonly Proveedor $proveedor)
    {
        parent::__construct("Hay productos que no están asignados a «{$proveedor->nombre}».");
    }
}
