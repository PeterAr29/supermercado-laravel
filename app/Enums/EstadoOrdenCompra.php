<?php

namespace App\Enums;

/**
 * H-25 — El estado era un string libre y su catálogo vivía en un comentario
 * de la migración. Un typo pasaba desapercibido hasta que algo dejaba de
 * cuadrar en producción.
 */
enum EstadoOrdenCompra: string
{
    case Pendiente = 'pendiente';
    case Enviado = 'enviado';
    case Recibido = 'recibido';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Enviado => 'Enviada',
            self::Recibido => 'Recibida',
        };
    }
}
