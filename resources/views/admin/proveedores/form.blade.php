{{-- Campos del proveedor, compartidos por crear y editar. --}}
@php $p = $proveedor ?? null; @endphp

<div class="grid gap-4 md:grid-cols-2">

    <x-campo nombre="nombre" etiqueta="Nombre" :valor="$p?->nombre" required />

    <x-campo nombre="ruc" etiqueta="RUC" :valor="$p?->ruc" required
             ayuda="Once dígitos. Identifica a la empresa, así que no puede repetirse."
             inputmode="numeric" maxlength="11" />

    <x-campo nombre="telefono" etiqueta="Teléfono" :valor="$p?->telefono" required />

    <x-campo nombre="email" etiqueta="Email" tipo="email" :valor="$p?->email" required />

    <div class="md:col-span-2">
        <x-campo nombre="direccion" etiqueta="Dirección" :valor="$p?->direccion" required />
    </div>

    <x-campo nombre="contacto_nombre" etiqueta="Nombre del contacto"
             :valor="$p?->contacto_nombre" required />

    <x-campo nombre="contacto_telefono" etiqueta="Teléfono del contacto"
             :valor="$p?->contacto_telefono" required />
</div>
