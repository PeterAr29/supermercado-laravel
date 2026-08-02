@extends('layouts.layout')

@section('content')
<div class="container mt-5">

    <h1 class="fw-bold mb-4">Productos de {{ $proveedor->nombre }}</h1>

    <a href="{{ route('proveedor.productos.create', $proveedor) }}" class="btn btn-primary mb-4">
        <i class="bi bi-plus-circle"></i> Asignar producto
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Precio compra</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre }}</td>
                        <td>S/ {{ number_format($producto->pivot->precio_compra, 2) }}</td>

                        <td>
                            <a href="{{ route('proveedor.productos.edit', [$proveedor, $producto->id]) }}" 
                               class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <form action="{{ route('proveedor.productos.destroy', [$proveedor, $producto->id]) }}"
                                  method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm" onclick="return confirm('¿Quitar producto?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $productos->links() }}
        </div>
    </div>

</div>
@endsection
