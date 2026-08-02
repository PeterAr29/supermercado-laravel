@extends('layouts.layout')

@section('content')
<div class="max-w-6xl mx-auto mt-10">

    <h1 class="text-2xl font-bold mb-6 text-center">
        Proveedores (Administrados desde Google Sheets)
    </h1>

    <table class="w-full bg-white shadow rounded overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-3">Nombre</th>
                <th class="p-3">Dirección</th>
                <th class="p-3">Teléfono</th>
                <th class="p-3">Categoría</th>
                <th class="p-3">Estado</th>
            </tr>
        </thead>

        <tbody>
            @foreach($proveedores as $p)
                @if($p['activo'] === 'SI')
                <tr class="border-b">
                    <td class="p-3 font-semibold">{{ $p['nombre'] }}</td>
                    <td class="p-3">{{ $p['direccion'] }}</td>
                    <td class="p-3">{{ $p['telefono'] }}</td>
                    <td class="p-3">{{ $p['categoria'] }}</td>
                    <td class="p-3 text-green-600 font-bold">Activo</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection
