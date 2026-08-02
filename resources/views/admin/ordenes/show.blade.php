@extends('layouts.admin')

@section('titulo', 'Orden #'.$orden->id)

@section('content')

    <div class="max-w-4xl">

        <a href="{{ route('admin.ordenes.index') }}" class="text-sm text-gray-500 hover:underline">
            ← Órdenes de compra
        </a>

        <div class="flex items-center justify-between mt-2 mb-6">
            <h1 class="text-2xl font-bold">Orden #{{ $orden->id }}</h1>

            <span class="text-xs px-3 py-1 rounded
                {{ $orden->estaPendiente() ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                {{ $orden->estado->etiqueta() }}
            </span>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">

            <dl class="grid sm:grid-cols-3 gap-4 p-5 border-b text-sm">
                <div>
                    <dt class="text-gray-500">Proveedor</dt>
                    <dd class="font-medium">{{ $orden->proveedor->nombre ?? 'Sin proveedor' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Fecha</dt>
                    <dd class="font-medium">{{ $orden->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Líneas</dt>
                    <dd class="font-medium">{{ $orden->items->count() }}</dd>
                </div>
            </dl>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-5 py-3">Producto</th>
                            <th class="px-5 py-3 text-right">Cantidad</th>
                            <th class="px-5 py-3 text-right">Precio de compra</th>
                            <th class="px-5 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orden->items as $item)
                            <tr class="border-b">
                                <td class="px-5 py-3">
                                    {{-- withTrashed(): un producto retirado del
                                         catálogo sigue apareciendo en la orden
                                         que lo pidió (H-32) --}}
                                    {{ $item->producto->nombre ?? 'Producto retirado' }}
                                </td>
                                <td class="px-5 py-3 text-right">{{ $item->cantidad }}</td>
                                <td class="px-5 py-3 text-right">S/ {{ number_format($item->precio, 2) }}</td>
                                <td class="px-5 py-3 text-right">S/ {{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-5 py-4 text-right font-semibold">Total</td>
                            <td class="px-5 py-4 text-right text-lg font-bold">
                                S/ {{ number_format($orden->total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($orden->estaPendiente())
            <form action="{{ route('admin.ordenes.recibir', $orden) }}" method="POST" class="mt-6"
                  onsubmit="return confirm('Al recibir la orden, el stock de cada producto sube y queda registrado en el kardex. ¿Continuar?')">
                @csrf
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Marcar como recibida
                </button>

                <p class="text-sm text-gray-500 mt-2">
                    Suma la mercancía al inventario y deja un movimiento de entrada por cada producto.
                </p>
            </form>
        @else
            <p class="mt-6 text-sm text-gray-500">
                Esta orden ya se recibió: su mercancía está contabilizada en el inventario.
            </p>
        @endif
    </div>

@endsection
