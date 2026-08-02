@extends('layouts.admin')

@section('titulo', 'Productos')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Productos</h1>

        <a href="{{ route('admin.productos.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Nuevo producto
        </a>
    </div>

    {{-- FILTROS --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-sm text-gray-600 mb-1">Buscar</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Nombre del producto"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Categoría</label>
            <select name="categoria" class="border rounded px-3 py-2">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(request('categoria') == $categoria->id)>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <label class="flex items-center gap-2 pb-2">
            <input type="checkbox" name="reponer" value="1" @checked(request()->boolean('reponer'))>
            <span class="text-sm text-gray-700">Solo bajo mínimo</span>
        </label>

        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded">Filtrar</button>

        @if(request()->hasAny(['buscar', 'categoria', 'reponer']))
            <a href="{{ route('admin.productos.index') }}" class="px-4 py-2 text-gray-600 hover:underline">Limpiar</a>
        @endif
    </form>

    {{-- TABLA --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Categoría</th>
                        <th class="px-4 py-3 text-right">Precio</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($productos as $producto)
                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $producto->imagen }}" alt=""
                                         class="w-10 h-10 object-cover rounded bg-gray-100">
                                    <span class="font-medium">{{ $producto->nombre }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ $producto->categoria->nombre ?? 'Sin categoría' }}
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                S/ {{ number_format($producto->precio, 2) }}
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <span class="{{ $producto->bajoMinimo() ? 'text-amber-600 font-bold' : '' }}">
                                    {{ $producto->stock }}
                                </span>
                                <span class="text-gray-400 text-xs">
                                    {{ $producto->unidad_medida->sufijo() }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.inventario.show', $producto) }}"
                                   class="inline-block bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded">
                                    Kardex
                                </a>

                                <a href="{{ route('admin.productos.edit', $producto) }}"
                                   class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Editar
                                </a>

                                <form action="{{ route('admin.productos.destroy', $producto) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('¿Retirar «{{ $producto->nombre }}» del catálogo? Las ventas anteriores se conservan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        Retirar
                                    </button>
                                </form>
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
