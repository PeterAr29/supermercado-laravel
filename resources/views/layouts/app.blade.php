{{--
    Layout de las pantallas de Breeze (`<x-app-layout>`): perfil y dashboard.

    Es la versión "de componente" del layout de tienda: la diferencia con
    `layouts.tienda` es que aquí el contenido llega por `{{ $slot }}` y no por
    `@yield('content')`.

    Esa diferencia es justo lo que estaba roto (H-47): el componente AppLayout
    apuntaba a `layouts.layout`, que solo tenía `@yield('content')`. Como el
    contenido de `<x-app-layout>` viaja en `$slot` y nadie lo imprimía, **el
    perfil y el dashboard se pintaban vacíos**: barra de navegación y nada más.
--}}
@extends('layouts.base')

@section('contenido')

    @include('layouts.navigation')

    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-alerta-flash />
        </div>

        {{ $slot }}
    </main>

@endsection
