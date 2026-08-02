@extends('layouts.tienda')

@section('titulo', 'Confirmar pago')

@section('content')

<div class="max-w-2xl mx-auto px-4">

    <div class="bg-white rounded-xl shadow overflow-hidden">

        <div class="bg-gray-900 text-white px-6 py-5 text-center">
            <h1 class="text-xl font-bold">Confirmación de pago</h1>
            <p class="text-gray-300 text-sm mt-1">
                Revisa el pedido antes de pagar.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-6 py-3">Producto</th>
                        <th class="px-6 py-3 text-right">Cantidad</th>
                        <th class="px-6 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($items as $item)
                        <tr class="border-b">
                            <td class="px-6 py-4">
                                <span class="font-medium">{{ $item->producto->nombre }}</span>
                                <span class="block text-gray-500 text-xs mt-0.5">
                                    S/ {{ number_format($item->producto->precio, 2) }}
                                    por {{ $item->producto->unidad_medida->etiqueta() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">{{ $item->cantidad }}</td>
                            <td class="px-6 py-4 text-right">S/ {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 border-t px-6 py-5 text-center">
            <p class="text-gray-500 text-sm">Total a pagar</p>
            {{-- El total llega calculado por CarritoService: la vista no suma (H-13) --}}
            <p class="text-3xl font-bold text-green-700 mt-1">S/ {{ number_format($total, 2) }}</p>
        </div>

        <div class="p-6">
            <form action="{{ route('pago.procesar') }}" method="POST">
                @csrf
                <button class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg text-lg font-semibold">
                    Confirmar pago
                </button>
            </form>

            <a href="{{ route('carrito.index') }}"
               class="block text-center text-gray-500 hover:underline text-sm mt-4">
                Volver al carrito
            </a>
        </div>
    </div>
</div>

@endsection
