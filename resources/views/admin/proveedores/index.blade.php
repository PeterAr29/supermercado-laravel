@extends('layouts.admin')

@section('titulo', 'Proveedores')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Proveedores</h1>

        <a href="{{ route('admin.proveedores.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Nuevo proveedor
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">RUC</th>
                        <th class="px-4 py-3">Teléfono</th>
                        <th class="px-4 py-3">Contacto</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3 text-right">Productos</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($proveedores as $proveedor)
                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $proveedor->nombre }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $proveedor->ruc }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $proveedor->telefono }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $proveedor->contacto_nombre }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $proveedor->email }}</td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
                                   class="text-blue-600 hover:underline">
                                    {{ $proveedor->productos_count }}
                                </a>
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}"
                                   class="inline-block bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded">
                                    Catálogo
                                </a>

                                <a href="{{ route('admin.proveedores.edit', $proveedor) }}"
                                   class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Editar
                                </a>

                                <form action="{{ route('admin.proveedores.destroy', $proveedor) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('¿Eliminar el proveedor «{{ $proveedor->nombre }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No hay proveedores todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $proveedores->links() }}
    </div>

@endsection
