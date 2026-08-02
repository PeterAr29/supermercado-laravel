@extends('layouts.admin')

@section('titulo', 'Asignar producto')

@section('content')

    <div class="max-w-xl">
        <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
           class="text-sm text-gray-500 hover:underline">← Catálogo de {{ $proveedor->nombre }}</a>

        <h1 class="text-2xl font-bold mt-2 mb-6">Asignar producto</h1>

        @if($productos->isEmpty())
            <div class="bg-white rounded-lg shadow p-6 text-gray-600">
                Este proveedor ya tiene asignados todos los productos del catálogo.

                <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
                   class="text-blue-600 hover:underline">Volver</a>
            </div>
        @else
            <form action="{{ route('admin.proveedor.productos.store', $proveedor) }}" method="POST"
                  class="bg-white rounded-lg shadow p-6 space-y-4">
                @csrf

                <div>
                    <label for="producto_id" class="block text-sm font-medium text-gray-700 mb-1">Producto</label>

                    {{-- Solo los que este proveedor NO vende todavía: asignar dos
                         veces el mismo choca contra el índice único (H-40). --}}
                    <select name="producto_id" id="producto_id" required
                            class="w-full border rounded px-3 py-2 {{ $errors->has('producto_id') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" @selected(old('producto_id') == $producto->id)>
                                {{ $producto->nombre }}
                            </option>
                        @endforeach
                    </select>

                    @error('producto_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-campo nombre="precio_compra" etiqueta="Precio de compra (S/)" tipo="number"
                         step="0.01" min="0" required
                         ayuda="Lo que nos cuesta a nosotros, no lo que se cobra en tienda." />

                <div class="flex items-center gap-4 pt-4 border-t">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                        Guardar
                    </button>

                    <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
                       class="text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        @endif
    </div>

@endsection
