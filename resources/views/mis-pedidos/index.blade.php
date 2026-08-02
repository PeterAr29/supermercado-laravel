@extends('layouts.layout')

@section('content')

<div class="max-w-4xl mx-auto px-4">

    <h1 class="text-2xl font-bold mb-6">Mis pedidos</h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 mb-4 rounded">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-5 py-3">Pedido</th>
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3 text-right">Productos</th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>

            <tbody>
                @forelse($ventas as $venta)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-5 py-4 font-medium">#{{ $venta->id }}</td>

                        <td class="px-5 py-4 text-gray-600">
                            {{ $venta->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-5 py-4 text-right text-gray-600">
                            {{ $venta->items_count }}
                        </td>

                        <td class="px-5 py-4 text-right font-bold text-red-600">
                            S/ {{ number_format($venta->total, 2) }}
                        </td>

                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('mis-pedidos.show', $venta) }}"
                               class="text-blue-600 hover:underline">Ver detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                            Todavía no has hecho ningún pedido.
                            <a href="{{ route('productos.index') }}" class="text-blue-600 hover:underline">
                                Ver el catálogo
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ventas->links() }}
    </div>
</div>

@endsection
