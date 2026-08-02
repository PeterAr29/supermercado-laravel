{{--
    El <head> del proyecto. Uno solo.

    Antes había cuatro layouts con cuatro cabeceras distintas (H-15) y cada una
    cargaba sus assets por su cuenta desde un CDN (H-17): `layout`, `admin`,
    `layoutCenter` y `super`. Cambiar el tipo de letra o añadir un meta obligaba
    a acordarse de los cuatro, y bastaba olvidar uno para que una pantalla se
    viera distinta sin que nadie supiera por qué.

    Las secciones que definen tienda y panel:
      - 'titulo'      texto que va antes de la marca en la pestaña
      - 'cuerpo'      clases extra del <body>
      - 'contenido'   la página
--}}
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Una sola marca, desde la configuración (H-18). Antes convivían
         "PlazaKing", "Tattos Market", "Supermercado" y "Laravel". --}}
    <title>@hasSection('titulo')@yield('titulo') · @endif{{ config('app.name') }}</title>

    {{-- Todo el CSS y el JS salen del bundle de Vite. Ninguna vista referencia
         un CDN: eso era H-17, y además dejaba la aplicación sin estilos en
         cuanto se cayera la red. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="h-full bg-gray-100 font-sans antialiased @yield('cuerpo')">

    @yield('contenido')

    @stack('scripts')
</body>
</html>
