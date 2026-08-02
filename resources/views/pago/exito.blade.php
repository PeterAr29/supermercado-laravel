@extends('layouts.tienda')

@section('titulo', 'Compra realizada')

@section('content')

<div class="max-w-lg mx-auto px-4">

    <div class="bg-white rounded-xl shadow p-8 text-center">

        <div class="w-16 h-16 mx-auto rounded-full bg-green-100 flex items-center justify-center">
            <span class="text-green-600 text-4xl leading-none">✓</span>
        </div>

        <h1 class="text-2xl font-bold mt-4">¡Compra realizada!</h1>

        <p class="text-gray-500 mt-2">
            El pedido se registró correctamente y el stock ya está actualizado.
        </p>

        @if($venta_id)
            <div class="mt-6 bg-gray-50 border rounded-lg py-4">
                <p class="text-sm text-gray-500">Número de pedido</p>
                <p class="text-2xl font-bold mt-1">#{{ $venta_id }}</p>
            </div>
        @endif

        <div class="mt-6 space-y-3">
            @auth
                <a href="{{ route('mis-pedidos.index') }}"
                   class="block w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold">
                    Ver mis pedidos
                </a>
            @endauth

            <a href="{{ route('productos.index') }}"
               class="block w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-semibold">
                Seguir comprando
            </a>
        </div>
    </div>
</div>

@endsection
