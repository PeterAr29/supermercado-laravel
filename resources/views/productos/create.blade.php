@extends('layouts.layout')

@section('content')
<div class="max-w-xl mx-auto mt-10">

    <h1 class="text-2xl font-bold mb-4">Nuevo Producto</h1>

    <form action="{{ route('productos.store') }}" method="POST">
        @csrf

        {{-- Nombre --}}
        <label class="block mb-2">Nombre</label>
        <input type="text" name="nombre"
               value="{{ old('nombre') }}"
               class="w-full p-2 border rounded mb-4">

        {{-- Descripción --}}
        <label class="block mb-2">Descripción</label>
        <textarea name="descripcion"
                  class="w-full p-2 border rounded mb-4">{{ old('descripcion') }}</textarea>

        {{-- Precio --}}
        <label class="block mb-2">Precio</label>
        <input type="number" step="0.01" name="precio"
               value="{{ old('precio') }}"
               class="w-full p-2 border rounded mb-4">

        {{-- Imagen --}}
        <label class="block mb-2">URL de la imagen</label>
        <input type="text" name="imagen"
               value="{{ old('imagen') }}"
               class="w-full p-2 border rounded mb-4">

        {{-- Categoría (REAL DESDE BD) --}}
        <label class="block mb-2">Categoría</label>
        <select name="categoria_id" class="w-full p-2 border rounded mb-4" required>
            <option value="">-- Seleccione una categoría --</option>

            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}"
                    {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>

        {{-- medida --}}
        <label class="block font-semibold mt-3">Unidad de venta</label>
            <select name="unidad_medida" class="w-full border rounded p-2">
                <option value="und">Unidad</option>
                <option value="kg">Kilogramo</option>
            </select>

        {{-- Botones --}}
        <button class="bg-green-600 text-white px-4 py-2 rounded">
            Guardar
        </button>

        <a href="{{ route('productos.index') }}" class="ml-4">Cancelar</a>

    </form>
</div>
@endsection
