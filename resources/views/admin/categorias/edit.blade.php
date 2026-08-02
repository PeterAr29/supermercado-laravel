@extends('layouts.admin')

@section('titulo', 'Editar categoría')

@section('content')

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Editar «{{ $categoria->nombre }}»</h1>

        <form action="{{ route('admin.categorias.update', $categoria) }}" method="POST"
              class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            @include('admin.categorias.form')

            <div class="flex items-center gap-4 mt-6 pt-6 border-t">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Guardar cambios
                </button>

                <a href="{{ route('admin.categorias.index') }}" class="text-gray-600 hover:underline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

@endsection
