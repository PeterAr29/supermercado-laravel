{{--
    Layout del panel de administración.

    Cuelga de 'layouts.base' desde la Fase 5: antes traía su propia cabecera
    con Tailwind por CDN, que era la cuarta copia del mismo <head> (H-15, H-17).

    Sigue separado del de la tienda porque el panel no tiene carrito, ni
    buscador de catálogo, ni menú de categorías. Compartir layout obligaba a
    pintar la barra de la tienda sobre las pantallas de gestión.
--}}
@extends('layouts.base')

@section('contenido')

    <nav class="bg-gray-900 text-gray-200">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">

            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="font-bold text-white whitespace-nowrap">
                    {{ config('app.name') }} <span class="text-gray-400 font-normal">· Panel</span>
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
                <a href="{{ route('home') }}" class="text-gray-400 hover:text-white whitespace-nowrap">Ver la tienda</a>

                <span class="text-gray-600">|</span>

                <span class="text-gray-400 hidden sm:inline">{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="hover:text-white whitespace-nowrap">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <x-alerta-flash />

        @yield('content')
    </main>

@endsection
