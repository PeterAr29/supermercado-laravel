{{--
    Layout del panel de administración (Fase 3).

    Separado del de la tienda a propósito: el panel no tiene carrito, ni
    buscador de catálogo, ni menú de categorías. Compartir layout obligaba a
    pintar la barra de la tienda sobre las pantallas de gestión.

    Sigue usando el CDN de Tailwind como el resto del proyecto; el cambio a
    @vite es de la Fase 5 (H-17), y hacerlo solo aquí dejaría dos sistemas de
    assets conviviendo, que es justo lo que esa fase viene a arreglar.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('titulo', 'Panel') · Tattos Market</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-gray-900 text-gray-200">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">

            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="font-bold text-white">
                    Tattos Market <span class="text-gray-400 font-normal">· Panel</span>
                </a>

                <div class="hidden md:flex items-center gap-1 text-sm">
                    @php
                        $enlaces = [
                            ['admin.dashboard', 'Resumen'],
                            ['admin.productos.index', 'Productos'],
                            ['admin.categorias.index', 'Categorías'],
                            ['admin.inventario.index', 'Inventario'],
                            ['admin.proveedores.index', 'Proveedores'],
                            ['admin.ordenes.index', 'Órdenes'],
                        ];
                    @endphp

                    @foreach($enlaces as [$ruta, $texto])
                        <a href="{{ route($ruta) }}"
                           class="px-3 py-1.5 rounded {{ request()->routeIs(str_replace('.index', '.*', $ruta)) ? 'bg-gray-700 text-white' : 'hover:bg-gray-800' }}">
                            {{ $texto }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="text-gray-400 hover:text-white">Ver la tienda</a>

                <span class="text-gray-500">|</span>

                <span class="text-gray-400">{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="hover:text-white">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
