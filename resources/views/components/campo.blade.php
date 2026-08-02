@props([
    'nombre',
    'etiqueta',
    'tipo' => 'text',
    'valor' => null,
    'ayuda' => null,
])

{{--
    Campo de formulario del panel.

    Cada formulario traía su propio `<label>` + `<input>` con clases distintas,
    y ninguno mostraba el error del campo: los errores solo salían arriba, en
    bloque, sin decir cuál era el que fallaba.

    `old()` manda siempre sobre el valor: un fallo de validación no debe borrar
    lo que ya estaba escrito.
--}}
<div>
    <label for="{{ $nombre }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $etiqueta }}
    </label>

    <input type="{{ $tipo }}"
           name="{{ $nombre }}"
           id="{{ $nombre }}"
           value="{{ old($nombre, $valor) }}"
           {{ $attributes->class([
               'w-full border rounded px-3 py-2',
               'border-gray-300' => ! $errors->has($nombre),
               'border-red-500 bg-red-50' => $errors->has($nombre),
           ]) }}>

    @error($nombre)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror

    @if($ayuda && ! $errors->has($nombre))
        <p class="text-xs text-gray-500 mt-1">{{ $ayuda }}</p>
    @endif
</div>
