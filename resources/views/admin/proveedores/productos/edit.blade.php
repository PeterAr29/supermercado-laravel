@extends('layouts.admin')

@section('titulo', 'Editar precio de compra')

@section('content')

    <div class="max-w-xl">
        <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
           class="text-sm text-gray-500 hover:underline">← Catálogo de {{ $proveedor->nombre }}</a>

        <h1 class="text-2xl font-bold mt-2 mb-6">{{ $producto->nombre }}</h1>

        <form action="{{ route('admin.proveedor.productos.update', [$proveedor, $producto]) }}" method="POST"
              class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf
            @method('PUT')

            <dl class="text-sm border-b pb-4">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Precio de venta al público</dt>
                    <dd class="font-medium">S/ {{ number_format($producto->precio, 2) }}</dd>
                </div>
            </dl>

            <x-campo nombre="precio_compra" etiqueta="Precio de compra (S/)" tipo="number"
                     :valor="$producto->pivot->precio_compra"
                     step="0.01" min="0" required />

            <div class="flex items-center gap-4 pt-4 border-t">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Guardar cambios
                </button>

                <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
                   class="text-gray-600 hover:underline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
