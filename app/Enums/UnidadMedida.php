<?php

namespace App\Enums;

/**
 * H-25 — La columna existe como enum en la base desde diciembre, pero el
 * código la trataba como texto suelto.
 */
enum UnidadMedida: string
{
    case Kilogramo = 'kg';
    case Unidad = 'und';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Kilogramo => 'Kilogramo',
            self::Unidad => 'Unidad',
        };
    }

    /** Sufijo corto para mostrar junto al precio: "S/ 3.50 / kg". */
    public function sufijo(): string
    {
        return $this->value;
    }
}
