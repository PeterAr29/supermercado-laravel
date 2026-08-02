<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * H-14 — Puerta de la zona de gestión.
 *
 * Se aplica siempre junto a 'auth', que es quien manda al login al invitado.
 * Aquí ya hay sesión iniciada, así que un cliente no es alguien "por
 * identificar" sino alguien que no tiene permiso: 403, no redirección al
 * login, que solo le haría dar vueltas.
 */
class EsAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->esAdmin(), 403, 'Esta zona es solo para administradores.');

        return $next($request);
    }
}
