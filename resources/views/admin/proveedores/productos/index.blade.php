@extends('layouts.admin')

@section('titulo', 'Catálogo de '.$proveedor->nombre)

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.proveedores.index') }}" class="text-sm text-gray-500 hover:underline">
                ← Proveedores
            </a>
            <h1 class="text-2xl font-bold">Qué nos vende {{ $proveedor->nombre }}</h1>
        </div>

        <a href="{{ route('admin.proveedor.productos.create', $proveedor) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded whitespace-nowrap">
            + Asignar producto
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3 text-right">Precio de compra</th>
                        <th class="px-4 py-3 text-right">Precio de venta</th>
                        <th class="px-4 py-3 text-right">Margen</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($productos as $producto)
                        @php
                            $compra = (float) $producto->pivot->precio_compra;
                            $margen = $compra > 0 ? (($producto->precio - $compra) / $compra) * 100 : null;
                        @endphp

                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $producto->nombre }}</td>

                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                S/ {{ number_format($compra, 2) }}
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap text-gray-600">
                                S/ {{ number_format($producto->precio, 2) }}
                            </td>

                            {{-- Lo que se gana con cada unidad. Estaba a la vista
                                 el precio de compra y el de venta, pero había que
                                 hacer la resta de cabeza. --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if($margen === null)
                                    <span class="text-gray-400">—</span>
                                @else
                                    <span class="{{ $margen < 0 ? 'text-red-600 font-bold' : 'text-green-700' }}">
                                        {{ number_format($margen, 0) }}%
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.proveedor.productos.edit', [$proveedor, $producto]) }}"
                                   class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Editar precio
                                </a>

                                <form action="{{ route('admin.proveedor.productos.destroy', [$proveedor, $producto]) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('¿Quitar «{{ $producto->nombre }}» del catálogo de este proveedor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        Quitar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Este proveedor todavía no tiene productos asignados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $productos->links() }}
    </div>

@endsection
