<?php

namespace App\Enums;

/**
 * H-14 — Hasta la Fase 3 no había roles: la Fase 1 cerró la puerta a los
 * anónimos, pero cualquiera que se registrara entraba con acceso total de
 * administrador (crear productos, borrar el catálogo, recibir órdenes).
 */
enum RolUsuario: string
{
    case Cliente = 'cliente';
    case Admin = 'admin';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Cliente => 'Cliente',
            self::Admin => 'Administrador',
        };
    }
}
