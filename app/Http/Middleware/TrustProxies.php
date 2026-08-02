<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Proxies de confianza.
     *
     * '*' porque en un despliegue con TLS terminado por delante —Railway y
     * cualquier PaaS— la aplicación recibe la petición por HTTP y solo se
     * entera de que el cliente venía por HTTPS leyendo `X-Forwarded-Proto`.
     * Sin esto, `url()` y `route()` generan enlaces `http://` dentro de una
     * página servida por `https://`: el navegador bloquea los assets por
     * contenido mixto y los redirects salen del canal seguro.
     *
     * Es seguro aquí porque el único camino hasta el contenedor pasa por el
     * proxy de la plataforma; no está expuesto directamente a internet.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
