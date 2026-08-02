@extends('layouts.layout')

@section('content')
<div class="max-w-7xl mx-auto mt-10">

    {{-- MENSAJES --}}
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-200 text-red-800 p-3 mb-4 rounded">
            {{ session('error') }}
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
            {{--
                El botón "+ Nuevo producto" ya no está aquí: crear, editar y
                retirar se hace en /admin/productos, y esta pantalla es la
                tienda. Antes cualquier registrado veía los botones de gestión
                sobre el catálogo público (H-14).
            --}}
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Productos</h1>

                @auth
                    @if(auth()->user()->esAdmin())
                        <a href="{{ route('admin.productos.index') }}"
                           class="text-sm text-gray-600 hover:underline">
                            Gestionar el catálogo →
                        </a>
                    @endif
                @endauth
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
                            <th class="p-3">Disponible</th>
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

                                {{-- Decirlo aquí evita que el cliente descubra
                                     que no hay stock al llegar a la caja (H-35) --}}
                                <td class="p-3">
                                    @if($producto->stock > 0)
                                        <span class="text-green-700">{{ $producto->stock }} {{ $producto->unidad_medida->sufijo() }}</span>
                                    @else
                                        <span class="text-gray-500">Agotado</span>
                                    @endif
                                </td>

                                <td class="p-3 text-center">

                                    {{-- CARRITO --}}
                                    <form action="{{ route('carrito.agregar') }}"
                                          method="POST"
                                          class="inline"
                                          onclick="event.stopPropagation()">
                                        @csrf
                                        <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                                        <button class="bg-green-600 text-white px-3 py-1 rounded disabled:bg-gray-300"
                                                @disabled($producto->stock <= 0)>
                                            Agregar
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-6 text-gray-500">
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
