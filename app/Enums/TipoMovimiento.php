<?php

namespace App\Enums;

/**
 * H-35 — Tipos de movimiento de inventario.
 *
 * Hasta la Fase 3 el stock solo subía: las órdenes de compra lo reponían y
 * nadie lo descontaba. Un supermercado con esa contabilidad acaba con un
 * inventario que no se parece a lo que hay en la estantería.
 */
enum TipoMovimiento: string
{
    case Entrada = 'entrada';
    case Salida = 'salida';
    case Ajuste = 'ajuste';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Salida => 'Salida',
            self::Ajuste => 'Ajuste',
        };
    }

    /** Color de la etiqueta en el kardex. */
    public function color(): string
    {
        return match ($this) {
            self::Entrada => 'green',
            self::Salida => 'red',
            self::Ajuste => 'amber',
        };
    }
}
