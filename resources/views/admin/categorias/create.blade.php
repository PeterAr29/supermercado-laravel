@extends('layouts.admin')

@section('titulo', 'Nueva categoría')

@section('content')

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Nueva categoría</h1>

        <form action="{{ route('admin.categorias.store') }}" method="POST"
              class="bg-white rounded-lg shadow p-6">
            @csrf

            @include('admin.categorias.form', ['categoria' => null])

            <div class="flex items-center gap-4 mt-6 pt-6 border-t">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Guardar
                </button>

                <a href="{{ route('admin.categorias.index') }}" class="text-gray-600 hover:underline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

@endsection
