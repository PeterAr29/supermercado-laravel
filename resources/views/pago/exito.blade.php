@extends('layouts.layoutCenter')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 90vh;">

    <div class="card shadow-lg border-0 text-center p-5" 
         style="width: 550px; border-radius: 25px;">

        <i class="bi bi-check-circle-fill text-success mb-3" style="font-size: 5rem;"></i>

        <h1 class="fw-bold">¡Pago Exitoso!</h1>

        <p class="text-muted fs-5 mt-2">
            Tu compra fue procesada correctamente.
        </p>

        <div class="mt-4 p-4 rounded" 
             style="background: #f8f9fa; border: 1px solid #e0e0e0;">
            <p class="mb-1 fs-5">Número de venta:</p>
            <h2 class="text-primary fw-bold mb-0">#{{ $venta_id }}</h2>
        </div>

        <a href="/productos" class="btn btn-primary mt-4 py-3 fs-5 w-100" 
           style="border-radius: 12px;">
            <i class="bi bi-cart-plus"></i> Seguir comprando
        </a>

    </div>

</div>
@endsection
