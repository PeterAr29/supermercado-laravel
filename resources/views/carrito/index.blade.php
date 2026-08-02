@extends('layouts.tienda')

@section('titulo', 'Carrito')

@section('content')

<div class="max-w-5xl mx-auto px-4">

    <h1 class="text-2xl font-bold mb-6">Tu carrito</h1>

    {{-- Los mensajes flash los pinta <x-alerta-flash> desde el layout: estaban
         copiados en seis vistas con seis combinaciones de color distintas. --}}

    @if($items->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">Tu carrito está vacío.</p>

            <a href="{{ route('productos.index') }}"
               class="inline-block mt-4 bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded">
                Ver productos
            </a>
        </div>
    @else

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-5 py-3">Producto</th>
                            <th class="px-5 py-3 text-right">Precio</th>
                            <th class="px-5 py-3 text-right">Cantidad</th>
                            <th class="px-5 py-3 text-right">Subtotal</th>
                            <th class="px-5 py-3 text-right"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $item)
                            <tr class="border-b last:border-0">
                                <td class="px-5 py-4">
                                    <a href="{{ route('productos.show', $item->producto) }}"
                                       class="font-medium hover:underline">
                                        {{ $item->producto->nombre }}
                                    </a>

                                    @if(! $item->producto->hayStock($item->cantidad))
                                        <span class="block text-xs text-red-600 mt-0.5">
                                            Solo quedan {{ $item->producto->stock }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right whitespace-nowrap text-gray-600">
                                    S/ {{ number_format($item->producto->precio, 2) }}
                                </td>

                                <td class="px-5 py-4 text-right">{{ $item->cantidad }}</td>

                                {{-- El subtotal lo da el modelo y el total el
                                     servicio: la vista no multiplica ni suma (H-13) --}}
                                <td class="px-5 py-4 text-right font-medium whitespace-nowrap">
                                    S/ {{ number_format($item->subtotal, 2) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <form action="{{ route('carrito.eliminar', $item->id) }}"
                                          method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">Quitar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-5 py-4 text-right font-semibold">Total a pagar</td>
                            <td class="px-5 py-4 text-right text-lg font-bold text-green-700 whitespace-nowrap">
                                S/ {{ number_format($total, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 mt-6">
            <a href="{{ route('pago.confirmar') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded font-semibold">
                Proceder al pago
            </a>

            <a href="{{ route('productos.index') }}"
               class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded">
                Seguir comprando
            </a>

            <form action="{{ route('carrito.vaciar') }}" method="POST" class="ml-auto"
                  onsubmit="return confirm('¿Vaciar el carrito entero?')">
                @csrf
                <button class="text-red-600 hover:underline">Vaciar carrito</button>
            </form>
        </div>
    @endif
</div>

@endsection
