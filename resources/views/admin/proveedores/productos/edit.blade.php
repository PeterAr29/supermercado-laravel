@extends('layouts.admin')

@section('content')
<div class="container mt-5">

    <h1 class="fw-bold mb-4">Editar precio de compra</h1>

    <div class="card shadow p-4">

        <form action="{{ route('admin.proveedor.productos.update', [$proveedor, $producto->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <p><strong>Producto:</strong> {{ $producto->nombre }}</p>

            <div class="mb-3">
                <label>Precio de compra</label>
                <input type="number" step="0.01" name="precio_compra" 
                       class="form-control"
                       value="{{ $producto->pivot->precio_compra }}">
            </div>

            <button class="btn btn-warning">Actualizar</button>
            <a href="{{ route('admin.proveedor.productos.index', $proveedor) }}" class="btn btn-secondary">
                Cancelar
            </a>
        </form>

    </div>
</div>
@endsection
