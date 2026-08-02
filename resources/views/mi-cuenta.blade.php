@extends('layouts.layout')

@section('content')

<div class="max-w-4xl mx-auto px-4">

    <h1 class="text-2xl font-bold mb-6">Mi cuenta</h1>

    <div class="grid gap-6 md:grid-cols-3">

        {{-- DATOS --}}
        <div class="bg-white rounded-lg shadow p-5 md:col-span-1">
            <h2 class="font-semibold mb-3">Mis datos</h2>

            <dl class="text-sm space-y-2">
                <div>
                    <dt class="text-gray-500">Nombre</dt>
                    <dd class="font-medium">{{ $usuario->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Correo</dt>
                    <dd class="font-medium break-all">{{ $usuario->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Tipo de cuenta</dt>
                    <dd class="font-medium">{{ $usuario->rol->etiqueta() }}</dd>
                </div>
            </dl>

            <a href="{{ route('profile.edit') }}"
               class="inline-block mt-4 text-sm text-blue-600 hover:underline">
                Editar mis datos
            </a>
        </div>

        {{-- RESUMEN + ÚLTIMOS PEDIDOS --}}
        <div class="md:col-span-2 space-y-6">

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-500">Pedidos realizados</p>
                    <p class="text-3xl font-bold mt-1">{{ $totalPedidos }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-500">Total gastado</p>
                    <p class="text-3xl font-bold mt-1 text-red-600">
                        S/ {{ number_format($totalGastado, 2) }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-3 border-b flex items-center justify-between">
                    <h2 class="font-semibold">Últimos pedidos</h2>
                    <a href="{{ route('mis-pedidos.index') }}" class="text-sm text-blue-600 hover:underline">
                        Ver todos
                    </a>
                </div>

                <table class="w-full text-sm">
                    <tbody>
                        @forelse($ultimosPedidos as $venta)
                            <tr class="border-b last:border-0">
                                <td class="px-5 py-3">
                                    <a href="{{ route('mis-pedidos.show', $venta) }}"
                                       class="font-medium hover:underline">Pedido #{{ $venta->id }}</a>
                                </td>
                                <td class="px-5 py-3 text-gray-500">
                                    {{ $venta->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-red-600">
                                    S/ {{ number_format($venta->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-8 text-center text-gray-500">
                                    Todavía no has hecho ningún pedido.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
