@extends('layouts.admin')

@section('content')
<div class="container mt-5">

    <h1 class="fw-bold mb-4">Asignar producto a {{ $proveedor->nombre }}</h1>

    <div class="card shadow p-4">
        <form action="{{ route('admin.proveedor.productos.store', $proveedor) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Producto</label>
                <select name="producto_id" class="form-control">
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">
                            {{ $producto->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Precio de compra</label>
                <input type="number" name="precio_compra" step="0.01" 
                       class="form-control">
            </div>

            <button class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Guardar
            </button>
            <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}" class="btn btn-secondary">
                Cancelar
            </a>
        </form>
    </div>

</div>
@endsection
