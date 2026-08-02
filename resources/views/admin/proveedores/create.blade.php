@extends('layouts.admin')

@section('titulo', 'Nuevo proveedor')

@section('content')

    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold mb-6">Registrar proveedor</h1>

        <form action="{{ route('admin.proveedores.store') }}" method="POST"
              class="bg-white rounded-lg shadow p-6">
            @csrf

            @include('admin.proveedores.form', ['proveedor' => null])

            <div class="flex items-center gap-4 mt-6 pt-6 border-t">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    Guardar
                </button>

                <a href="{{ route('admin.proveedores.index') }}" class="text-gray-600 hover:underline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

@endsection
