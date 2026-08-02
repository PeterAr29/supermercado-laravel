@extends('layouts.layoutCenter')

@section('content')

<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-lg border-0" style="width: 600px; border-radius: 20px;">

        <div class="card-header text-white text-center"
             style="background: linear-gradient(135deg, #007bff, #0056b3); border-top-left-radius: 20px; border-top-right-radius: 20px;">
            <h2 class="py-2 mb-0">
                <i class="bi bi-shield-check"></i> Confirmación de Pago
            </h2>
        </div>

        <div class="card-body p-4 text-center">

            <p class="text-muted mb-4">
                Revisa tu pedido antes de procesar el pago. Asegúrate de que todo esté correcto.
            </p>

            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td class="text-start">
                            <strong>{{ $item->producto->nombre }}</strong><br>
                            <small class="text-muted">
                                S/ {{ number_format($item->producto->precio, 2) }} c/u
                            </small>
                        </td>
                        <td>{{ $item->cantidad }}</td>
                        <td>S/ {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 p-4 rounded"
                 style="background: #f8f9fa; border: 2px solid #e3e3e3;">
                <h4>Total a pagar</h4>
                <h1 class="text-success fw-bold">
                    S/ {{ number_format($total, 2) }}
                </h1>
            </div>

            <form action="{{ route('pago.procesar') }}" method="POST" class="mt-4">
                @csrf
                <button class="btn btn-success w-100 py-3 fs-4" style="border-radius: 12px;">
                    <i class="bi bi-credit-card-fill"></i> Confirmar Pago
                </button>
            </form>

        </div>

    </div>
</div>

@endsection
