@extends('layouts.admin')

@section('content')
<div class="container mt-5">

    <h1 class="fw-bold mb-4">Registrar proveedor</h1>

    <div class="card shadow p-4">
        <form action="{{ route('admin.proveedores.store') }}" method="POST">
            @csrf

            @include('admin.proveedores.form')

            <button class="btn btn-success mt-3">
                <i class="bi bi-check2-circle"></i> Guardar
            </button>

            <a href="{{ route('admin.proveedores.index') }}" class="btn btn-secondary mt-3">
                Cancelar
            </a>

        </form>
    </div>
</div>
@endsection
