@extends('layouts.layout')

@section('content')
<div class="max-w-5xl mx-auto mt-10">

    <h1 class="text-2xl font-bold mb-6">🛒 Tu Carrito de Compras</h1>

    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($items->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
            Tu carrito está vacío.
        </div>

        <a href="{{ route('productos.index') }}" 
           class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded">
            Ver productos
        </a>
    @else

        <table class="w-full bg-white shadow rounded overflow-hidden mb-5">
            <thead class="bg-gray-300">
                <tr>
                    <th class="p-3">Producto</th>
                    <th class="p-3">Cantidad</th>
                    <th class="p-3">Precio</th>
                    <th class="p-3">Subtotal</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @php $total = 0; @endphp

                @foreach($items as $item)
                    @php
                        $subtotal = $item->cantidad * $item->producto->precio;
                        $total += $subtotal;
                    @endphp

                    <tr class="border-b">
                        <td class="p-3">
                            <strong>{{ $item->producto->nombre }}</strong>
                        </td>

                        <td class="p-3">
                            {{ $item->cantidad }}
                        </td>

                        <td class="p-3 text-red-600">
                            S/ {{ number_format($item->producto->precio, 2) }}
                        </td>

                        <td class="p-3 font-bold">
                            S/ {{ number_format($subtotal, 2) }}
                        </td>

                        <td class="p-3">

                            {{-- Eliminar item --}}
                            <form action="{{ route('carrito.eliminar', $item->id) }}" 
                                  method="POST" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-600 text-white px-3 py-1 rounded">
                                    Eliminar
                                </button>
                            </form>

                        </td>
                    </tr>

                @endforeach
            </tbody>
        </table>

        <div class="text-right text-xl font-bold mb-5">
            Total a pagar: <span class="text-green-700">S/ {{ number_format($total, 2) }}</span>
        </div>

        {{-- Botón para vaciar carrito --}}
        <form action="{{ route('carrito.vaciar') }}" method="POST" class="inline-block">
            @csrf
            <button type="submit" 
                    class="bg-red-700 text-white px-4 py-2 rounded mr-3">
                Vaciar carrito
            </button>
        </form>

        {{-- Ir a productos --}}
        <a href="{{ route('productos.index') }}" 
           class="bg-gray-700 text-white px-4 py-2 rounded">
            Seguir comprando
        </a>

        <a href="{{ route('pago.confirmar') }}" class="btn btn-success">
            Proceder al Pago
        </a>

    @endif
</div>
@endsection
