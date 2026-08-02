<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Supermercado</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    {{-- ✅ SWIPER (Carrusel de promociones) --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

</head>
<body class="bg-gray-100">

    {{-- Barra de navegación --}}
    @include('layouts.navigation')

    {{-- Contenido principal --}}
    <main class="py-6">
        @yield('content')
    </main>

    {{-- ✅ SWIPER JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- Aquí luego pondremos los scripts de cada página --}}
    @stack('scripts')

</body>
</html>
