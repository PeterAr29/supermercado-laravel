@extends('layouts.layout')

@section('content')

<div class="max-w-3xl mx-auto px-4">

    <a href="{{ route('mis-pedidos.index') }}" class="text-sm text-gray-500 hover:underline">
        ← Mis pedidos
    </a>

    <div class="flex items-baseline justify-between mt-2 mb-6">
        <h1 class="text-2xl font-bold">Pedido #{{ $venta->id }}</h1>
        <span class="text-gray-500">{{ $venta->created_at->format('d/m/Y H:i') }}</span>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-5 py-3">Producto</th>
                    <th class="px-5 py-3 text-right">Precio</th>
                    <th class="px-5 py-3 text-right">Cantidad</th>
                    <th class="px-5 py-3 text-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($venta->items as $item)
                    <tr class="border-b">
                        <td class="px-5 py-4">
                            {{-- withTrashed(): un producto retirado del catálogo
                                 sigue apareciendo en el pedido que lo incluyó (H-32) --}}
                            {{ $item->producto->nombre ?? 'Producto retirado' }}
                        </td>

                        <td class="px-5 py-4 text-right">S/ {{ number_format($item->precio, 2) }}</td>
                        <td class="px-5 py-4 text-right">{{ $item->cantidad }}</td>
                        <td class="px-5 py-4 text-right">
                            S/ {{ number_format($item->precio * $item->cantidad, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr class="bg-gray-50">
                    <td colspan="3" class="px-5 py-4 text-right font-semibold">Total</td>
                    <td class="px-5 py-4 text-right text-lg font-bold text-red-600">
                        S/ {{ number_format($venta->total, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
