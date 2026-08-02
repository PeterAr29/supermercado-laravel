@extends('layouts.admin')

@section('titulo', 'Editar proveedor')

@section('content')

    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Editar «{{ $proveedor->nombre }}»</h1>

            <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
               class="text-sm text-blue-600 hover:underline">Ver su catálogo</a>
        </div>

        <form action="{{ route('admin.proveedores.update', $proveedor) }}" method="POST"
              class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            @include('admin.proveedores.form')

            <div class="flex items-center gap-4 mt-6 pt-6 border-t">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Guardar cambios
                </button>

                <a href="{{ route('admin.proveedores.index') }}" class="text-gray-600 hover:underline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

@endsection
