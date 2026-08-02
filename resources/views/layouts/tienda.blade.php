{{--
    Layout de la tienda: barra de navegación, menú de categorías y contenido.

    Cuelga de 'layouts.base', igual que el panel. Lo que cambia entre los dos
    es la navegación, no la cabecera.

    Las vistas siguen escribiendo `@section('content')` como siempre; aquí se
    reenvía a la sección 'contenido' de la base para no tener que tocar las
    quince vistas que ya la usan.
--}}
@extends('layouts.base')

@section('contenido')

    @include('layouts.navigation')

    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            <x-alerta-flash />
        </div>

        @yield('content')
    </main>

@endsection
