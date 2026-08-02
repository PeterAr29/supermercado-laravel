@extends('layouts.admin')

@section('content')
<div class="container mt-5">

    <h1 class="fw-bold mb-4">Editar proveedor</h1>

    <div class="card shadow p-4">
        <form action="{{ route('admin.proveedores.update', $proveedor) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.proveedores.form')

            <button class="btn btn-warning mt-3">
                <i class="bi bi-pencil-square"></i> Actualizar
            </button>

            <a href="{{ route('admin.proveedores.index') }}" class="btn btn-secondary mt-3">
                Cancelar
            </a>
        </form>
    </div>

</div>
@endsection
