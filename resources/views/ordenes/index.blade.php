@extends('layouts.layout')

@section('content')
<div class="container mt-5">

    <h2 class="mb-4">Órdenes de Compra</h2>

    <a href="{{ route('ordenes.create') }}" class="btn btn-primary mb-3">
        Crear nueva orden
    </a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Proveedor</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach($ordenes as $orden)
            <tr>
                <td>{{ $orden->id }}</td>
                <td>{{ $orden->proveedor->nombre }}</td>
                <td>S/ {{ number_format($orden->total, 2) }}</td>
                <td>
                    <span class="badge bg-{{ $orden->estado == 'pendiente' ? 'warning' : 'success' }}">
                        {{ $orden->estado }}
                    </span>
                </td>
                <td>{{ $orden->created_at->format('d/m/Y') }}</td>

                <td>
                    <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-sm btn-info">Ver</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $ordenes->links() }}

</div>
@endsection
