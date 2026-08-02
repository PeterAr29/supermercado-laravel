@extends('layouts.admin')

@section('titulo', 'Inventario')

@section('content')

    <h1 class="text-2xl font-bold mb-6">Inventario</h1>

    <form method="GET" class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-sm text-gray-600 mb-1">Buscar</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Nombre del producto"
                   class="w-full border rounded px-3 py-2">
        </div>

        <label class="flex items-center gap-2 pb-2">
            <input type="checkbox" name="reponer" value="1" @checked(request()->boolean('reponer'))>
            <span class="text-sm text-gray-700">Solo bajo mínimo</span>
        </label>

        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded">Filtrar</button>

        @if(request()->hasAny(['buscar', 'reponer']))
            <a href="{{ route('admin.inventario.index') }}" class="px-4 py-2 text-gray-600 hover:underline">Limpiar</a>
        @endif
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Categoría</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-right">Mínimo</th>
                        <th class="px-4 py-3 text-right">Kardex</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($productos as $producto)
                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $producto->nombre }}</td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ $producto->categoria->nombre ?? 'Sin categoría' }}
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <span class="font-bold {{ $producto->bajoMinimo() ? 'text-amber-600' : '' }}">
                                    {{ $producto->stock }}
                                </span>
                                <span class="text-gray-400 text-xs">{{ $producto->unidad_medida->sufijo() }}</span>
                            </td>

                            <td class="px-4 py-3 text-right text-gray-500">{{ $producto->stock_minimo }}</td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.inventario.show', $producto) }}"
                                   class="inline-block bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded">
                                    Ver y ajustar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                No hay productos que coincidan.
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
