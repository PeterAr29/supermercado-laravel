@extends('layouts.admin')

@section('titulo', 'Editar producto')

@section('content')

    <div class="max-w-3xl">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Editar «{{ $producto->nombre }}»</h1>

            <a href="{{ route('admin.inventario.show', $producto) }}"
               class="text-sm text-blue-600 hover:underline">Ver kardex</a>
        </div>

        <form action="{{ route('admin.productos.update', $producto) }}" method="POST"
              class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            @include('admin.productos.form')

            <div class="flex items-center gap-4 mt-6 pt-6 border-t">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Guardar cambios
                </button>

                <a href="{{ route('admin.productos.index') }}" class="text-gray-600 hover:underline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

@endsection
