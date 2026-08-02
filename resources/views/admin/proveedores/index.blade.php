@extends('layouts.admin')

@section('content')
<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">
        <h1 class="fw-bold">Proveedores</h1>
        <a href="{{ route('admin.proveedores.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo proveedor
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>RUC</th>
                        <th>TelÃ©fono</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($proveedores as $proveedor)
                    <tr>
                        <td>{{ $proveedor->nombre }}</td>
                        <td>{{ $proveedor->ruc }}</td>
                        <td>{{ $proveedor->telefono }}</td>
                        <td>{{ $proveedor->contacto_nombre }}</td>
                        <td>{{ $proveedor->email }}</td>

                        <td>
                            <a href="{{ route('admin.proveedores.edit', $proveedor) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('admin.proveedores.destroy', $proveedor) }}" 
                                  method="POST" 
                                  style="display:inline-block;">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-danger" onclick="return confirm('Â¿Eliminar proveedor?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
