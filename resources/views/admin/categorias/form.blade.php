{{-- Campos de la categoría, compartidos por crear y editar. --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
    <input type="text" name="nombre" required maxlength="100"
           value="{{ old('nombre', $categoria->nombre ?? '') }}"
           class="w-full border rounded px-3 py-2">
    <p class="text-xs text-gray-500 mt-1">
        Es lo que el cliente ve en el menú de la tienda. No puede repetirse.
    </p>
</div>
