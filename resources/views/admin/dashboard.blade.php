@extends('layouts.admin')

@section('titulo', 'Resumen')

@section('content')

    <h1 class="text-2xl font-bold mb-6">Resumen del día</h1>

    {{-- Las tres cifras de la mañana: qué se ha vendido, qué falta, qué llega --}}
    <div class="grid gap-4 md:grid-cols-3 mb-8">

        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Ventas de hoy</p>
            <p class="text-3xl font-bold mt-1">S/ {{ number_format($totalVentasHoy, 2) }}</p>
            <p class="text-sm text-gray-500 mt-1">
                {{ $numeroVentasHoy }} {{ $numeroVentasHoy === 1 ? 'venta' : 'ventas' }}
            </p>
        </div>

        <a href="{{ route('admin.inventario.index', ['reponer' => 1]) }}"
           class="bg-white rounded-lg shadow p-5 block hover:shadow-md transition">
            <p class="text-sm text-gray-500">Productos bajo mínimo</p>
            <p class="text-3xl font-bold mt-1 {{ $totalBajoMinimo > 0 ? 'text-amber-600' : '' }}">
                {{ $totalBajoMinimo }}
            </p>
            <p class="text-sm text-gray-500 mt-1">Hay que reponerlos</p>
        </a>

        <a href="{{ route('admin.ordenes.index') }}"
           class="bg-white rounded-lg shadow p-5 block hover:shadow-md transition">
            <p class="text-sm text-gray-500">Órdenes pendientes</p>
            <p class="text-3xl font-bold mt-1 {{ $totalOrdenesPendientes > 0 ? 'text-blue-600' : '' }}">
                {{ $totalOrdenesPendientes }}
            </p>
            <p class="text-sm text-gray-500 mt-1">Mercancía por llegar</p>
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- BAJO MÍNIMO --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-5 py-3 border-b flex items-center justify-between">
                <h2 class="font-semibold">Hay que reponer</h2>
                <a href="{{ route('admin.inventario.index', ['reponer' => 1]) }}"
                   class="text-sm text-blue-600 hover:underline">Ver todo</a>
            </div>

            <table class="w-full text-sm">
                <tbody>
                    @forelse($bajoMinimo as $producto)
                        <tr class="border-b last:border-0">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.inventario.show', $producto) }}"
                                   class="font-medium hover:underline">{{ $producto->nombre }}</a>
                                <span class="text-gray-500">· {{ $producto->categoria->nombre ?? 'Sin categoría' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <span class="font-bold text-amber-600">{{ $producto->stock }}</span>
                                <span class="text-gray-500">/ {{ $producto->stock_minimo }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-6 text-gray-500">Todo por encima del mínimo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ÓRDENES PENDIENTES --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-5 py-3 border-b flex items-center justify-between">
                <h2 class="font-semibold">Órdenes por recibir</h2>
                <a href="{{ route('admin.ordenes.index') }}"
                   class="text-sm text-blue-600 hover:underline">Ver todo</a>
            </div>

            <table class="w-full text-sm">
                <tbody>
                    @forelse($ordenesPendientes as $orden)
                        <tr class="border-b last:border-0">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.ordenes.show', $orden->id) }}"
                                   class="font-medium hover:underline">Orden #{{ $orden->id }}</a>
                                <span class="text-gray-500">· {{ $orden->proveedor->nombre ?? 'Sin proveedor' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                S/ {{ number_format($orden->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-6 text-gray-500">No hay órdenes pendientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ÚLTIMOS MOVIMIENTOS --}}
        <div class="bg-white rounded-lg shadow overflow-hidden lg:col-span-2">
            <div class="px-5 py-3 border-b">
                <h2 class="font-semibold">Últimos movimientos de inventario</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody>
                        @forelse($ultimosMovimientos as $movimiento)
                            <tr class="border-b last:border-0">
                                <td class="px-5 py-3 whitespace-nowrap text-gray-500">
                                    {{ $movimiento->created_at->format('d/m H:i') }}
                                </td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.inventario.show', $movimiento->producto_id) }}"
                                       class="hover:underline">{{ $movimiento->producto->nombre }}</a>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded bg-{{ $movimiento->tipo->color() }}-100 text-{{ $movimiento->tipo->color() }}-800">
                                        {{ $movimiento->tipo->etiqueta() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $movimiento->motivo }}</td>
                                <td class="px-5 py-3 text-right font-bold whitespace-nowrap {{ $movimiento->cantidad > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ $movimiento->cantidad }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-500 whitespace-nowrap">
                                    quedan {{ $movimiento->stock_resultante }}
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-6 text-gray-500">Todavía no hay movimientos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
