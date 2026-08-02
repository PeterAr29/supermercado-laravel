@extends('layouts.admin')

@section('titulo', 'Categorías')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Categorías</h1>

        <a href="{{ route('admin.categorias.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Nueva categoría
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3 text-right">Productos</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($categorias as $categoria)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $categoria->nombre }}</td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.productos.index', ['categoria' => $categoria->id]) }}"
                               class="text-blue-600 hover:underline">
                                {{ $categoria->productos_count }}
                            </a>
                        </td>

                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <a href="{{ route('admin.categorias.edit', $categoria) }}"
                               class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                Editar
                            </a>

                            {{-- Sin productos dentro no se puede borrar: la clave
                                 foránea es RESTRICT y responderÍa con un 500 --}}
                            @if($categoria->sePuedeBorrar())
                                <form action="{{ route('admin.categorias.destroy', $categoria) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('¿Borrar la categoría «{{ $categoria->nombre }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        Borrar
                                    </button>
                                </form>
                            @else
                                <span class="inline-block px-3 py-1 text-gray-400"
                                      title="Tiene productos asignados">
                                    Borrar
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            No hay categorías todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
