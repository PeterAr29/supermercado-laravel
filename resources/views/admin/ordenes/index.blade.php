@extends('layouts.admin')

@section('titulo', 'Órdenes de compra')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Órdenes de compra</h1>

        <a href="{{ route('admin.ordenes.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded whitespace-nowrap">
            + Nueva orden
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-3">Orden</th>
                        <th class="px-4 py-3">Proveedor</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($ordenes as $orden)
                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">#{{ $orden->id }}</td>

                            <td class="px-4 py-3">{{ $orden->proveedor->nombre ?? 'Sin proveedor' }}</td>

                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded whitespace-nowrap
                                    {{ $orden->estaPendiente() ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $orden->estado->etiqueta() }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $orden->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3 text-right font-medium whitespace-nowrap">
                                S/ {{ number_format($orden->total, 2) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.ordenes.show', $orden) }}"
                                   class="inline-block bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No hay órdenes de compra todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $ordenes->links() }}
    </div>

@endsection
