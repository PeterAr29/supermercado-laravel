@extends('layouts.layout')

@section('content')
<div class="max-w-xl mx-auto mt-10">

    <h1 class="text-2xl font-bold mb-4">Editar Producto</h1>

    <form action="{{ route('productos.update', $producto) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <label class="block mb-2">Nombre</label>
        <input type="text" name="nombre"
               value="{{ old('nombre', $producto->nombre) }}"
               class="w-full p-2 border rounded mb-4">

        {{-- Descripción --}}
        <label class="block mb-2">Descripción</label>
        <textarea name="descripcion"
                  class="w-full p-2 border rounded mb-4">{{ old('descripcion', $producto->descripcion) }}</textarea>

        {{-- Precio --}}
        <label class="block mb-2">Precio</label>
        <input type="number" step="0.01" name="precio"
               value="{{ old('precio', $producto->precio) }}"
               class="w-full p-2 border rounded mb-4">

        {{-- Imagen --}}
        <label class="block mb-2">URL de la imagen</label>
        <input type="text" name="imagen"
               value="{{ old('imagen', $producto->imagen) }}"
               class="w-full p-2 border rounded mb-4">

        {{-- Categoría (DESDE BD) --}}
        <label class="block mb-2">Categoría</label>
        <select name="categoria_id" class="w-full p-2 border rounded mb-4" required>
            <option value="">-- Seleccione una categoría --</option>

            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}"
                    {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>

        {{-- medida --}}
        <label class="block font-semibold mt-3">Unidad de venta</label>
            {{-- Preselecciona la unidad actual: sin esto, editar un producto
                 vendido por kg lo cambiaba a unidad en silencio. --}}
            <select name="unidad_medida" class="w-full border rounded p-2">
                @foreach(App\Enums\UnidadMedida::cases() as $unidad)
                    <option value="{{ $unidad->value }}"
                        @selected(old('unidad_medida', $producto->unidad_medida?->value) === $unidad->value)>
                        {{ $unidad->etiqueta() }}
                    </option>
                @endforeach
            </select>
            
        {{-- Botones --}}
        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Actualizar
        </button>

        <a href="{{ route('productos.index') }}" class="ml-3">Cancelar</a>

    </form>
</div>
@endsection
