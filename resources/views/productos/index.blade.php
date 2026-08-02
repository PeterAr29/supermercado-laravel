@extends('layouts.layout')

@section('content')
<div class="max-w-7xl mx-auto mt-10">

    {{-- MENSAJE --}}
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6">

        {{-- ================= MENÚ LATERAL ================= --}}
        <div class="col-span-3">
            <div class="bg-white shadow rounded p-4">
                <h2 class="text-lg font-bold mb-3">Categorías</h2>

                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('productos.index') }}"
                           class="block hover:text-red-600">
                            Todos
                        </a>
                    </li>

                    @foreach($categorias as $categoria)
                        <li>
                            <a href="{{ route('productos.index', ['categoria' => $categoria->id]) }}"
                               class="block hover:text-red-600">
                                {{ $categoria->nombre }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- ================= CONTENIDO ================= --}}
        <div class="col-span-9">

            {{-- ENCABEZADO --}}
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Lista de Productos</h1>

                <a href="{{ route('productos.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded">
                    + Nuevo Producto
                </a>
            </div>

        
            {{-- TABLA --}}
            <div id="contenedor-productos">
                <table class="w-full bg-white shadow rounded overflow-hidden">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-3">Imagen</th>
                            <th class="p-3">Nombre</th>
                            <th class="p-3">Precio</th>
                            <th class="p-3">Categoría</th>
                            <th class="p-3 text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($productos as $producto)
                            <tr
                                class="border-b hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('productos.show', $producto->id) }}'"
                            >

                                <td class="p-3">
                                    <img src="{{ $producto->imagen }}"
                                         class="w-16 h-16 object-cover rounded">
                                </td>

                                <td class="p-3 font-semibold">
                                    {{ $producto->nombre }}
                                </td>

                                <td class="p-3 text-red-600 font-bold">
                                    S/ {{ number_format($producto->precio, 2) }}
                                </td>

                                <td class="p-3">
                                    {{ $producto->categoria->nombre ?? 'Sin categoría' }}
                                </td>

                                <td class="p-3 text-center space-x-1">

                                    {{-- EDITAR --}}
                                    <a href="{{ route('productos.edit', $producto) }}"
                                       onclick="event.stopPropagation()"
                                       class="bg-yellow-500 text-white px-3 py-1 rounded">
                                        Editar
                                    </a>

                                    {{-- ELIMINAR --}}
                                    <form action="{{ route('productos.destroy', $producto) }}"
                                          method="POST"
                                          class="inline"
                                          onclick="event.stopPropagation()">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-600 text-white px-3 py-1 rounded">
                                            Eliminar
                                        </button>
                                    </form>

                                    {{-- CARRITO --}}
                                    <form action="{{ route('carrito.agregar') }}"
                                          method="POST"
                                          class="inline"
                                          onclick="event.stopPropagation()">
                                        @csrf
                                        <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                                        <button class="bg-green-600 text-white px-3 py-1 rounded">
                                            Agregar
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-6 text-gray-500">
                                    No se encontraron productos
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
