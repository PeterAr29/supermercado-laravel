@extends('layouts.layout')

@section('content')
<div class="container mt-5">

    <h2 class="mb-4">Orden #{{ $orden->id }}</h2>

    <p><strong>Proveedor:</strong> {{ $orden->proveedor->nombre }}</p>
    <p><strong>Estado:</strong> {{ $orden->estado->etiqueta() }}</p>

    <hr>

    <h4>Productos</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Compra</th>
                <th>Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @foreach($orden->items as $item)
            <tr>
                <td>{{ $item->producto->nombre }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>S/ {{ number_format($item->precio, 2) }}</td>
                <td>S/ {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: S/ {{ number_format($orden->total, 2) }}</h3>

    @if($orden->estaPendiente())
        <form action="{{ route('ordenes.recibir', $orden) }}" method="POST">
            @csrf
            <button class="btn btn-success mt-3">
                Marcar como Recibida
            </button>
        </form>
    @endif

</div>
@endsection
