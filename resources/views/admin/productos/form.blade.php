{{--
    Campos del producto, compartidos por crear y editar.

    Recibe $producto (o null al crear) y $unidades. `old()` manda siempre, para
    que un fallo de validación no borre lo que ya se había escrito.
--}}
@php $p = $producto ?? null; @endphp

<div class="grid gap-4 md:grid-cols-2">

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
        <input type="text" name="nombre" required
               value="{{ old('nombre', $p?->nombre) }}"
               class="w-full border rounded px-3 py-2">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
        <textarea name="descripcion" rows="3" required
                  class="w-full border rounded px-3 py-2">{{ old('descripcion', $p?->descripcion) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Precio de venta (S/)</label>
        <input type="number" step="0.01" min="0" name="precio" required
               value="{{ old('precio', $p?->precio) }}"
               class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de venta</label>
        <select name="unidad_medida" class="w-full border rounded px-3 py-2">
            @foreach($unidades as $unidad)
                <option value="{{ $unidad->value }}"
                    @selected(old('unidad_medida', $p?->unidad_medida?->value) === $unidad->value)>
                    {{ $unidad->etiqueta() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        {{--
            Al editar, el stock no se toca aquí: se corrige con un ajuste que
            deja constancia en el kardex (H-35). El campo solo aparece al dar
            de alta, cuando el producto todavía no tiene historial.
        --}}
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Stock inicial
        </label>

        @if($p)
            <div class="flex items-center gap-3">
                <input type="number" value="{{ $p->stock }}" disabled
                       class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500">
                <a href="{{ route('admin.inventario.show', $p) }}"
                   class="text-sm text-blue-600 hover:underline whitespace-nowrap">Ajustar</a>
            </div>
            <p class="text-xs text-gray-500 mt-1">El stock se corrige desde el kardex, con motivo.</p>
        @else
            <input type="number" min="0" name="stock" required
                   value="{{ old('stock', 0) }}"
                   class="w-full border rounded px-3 py-2">
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Stock mínimo</label>
        <input type="number" min="0" name="stock_minimo" required
               value="{{ old('stock_minimo', $p?->stock_minimo ?? 5) }}"
               class="w-full border rounded px-3 py-2">
        <p class="text-xs text-gray-500 mt-1">Por debajo de esta cifra, el panel avisa de que hay que reponer.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
        <select name="categoria_id" required class="w-full border rounded px-3 py-2">
            <option value="">— Seleccione una categoría —</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}"
                    @selected(old('categoria_id', $p?->categoria_id) == $categoria->id)>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">URL de la imagen</label>
        <input type="text" name="imagen" required
               value="{{ old('imagen', $p?->imagen) }}"
               class="w-full border rounded px-3 py-2">
    </div>
</div>
