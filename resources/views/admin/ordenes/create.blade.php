@extends('layouts.admin')

@section('content')
<div class="container mt-5">

    <h2 class="mb-4">Crear Orden de Compra</h2>

    <form action="{{ route('admin.ordenes.store') }}" method="POST">
        @csrf

        <!-- Proveedor -->
        <div class="mb-3">
            <label class="form-label fw-bold">Proveedor</label>
            <select class="form-select" name="proveedor_id" id="proveedor_id" required>
                <option value="">Seleccione un proveedor</option>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                @endforeach
            </select>
        </div>

        <hr>

        <h4>Productos del proveedor</h4>
        <p class="text-muted">Seleccione cantidades para cada producto.</p>

        <table class="table table-bordered" id="tabla-productos" style="display:none;">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Compra</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody id="productos-body"></tbody>
        </table>

        <h4 class="mt-4">Total: S/ <span id="total">0.00</span></h4>

        <button class="btn btn-success mt-3" id="btn-guardar" style="display:none;">
            Guardar Orden
        </button>
    </form>

</div>

<script>
// ðŸ”¹ Cargar productos cuando se selecciona un proveedor
document.getElementById('proveedor_id').addEventListener('change', function () {

    let proveedor_id = this.value;

    if (!proveedor_id) return;

    fetch('/admin/proveedor/' + proveedor_id + '/productos')
        .then(response => response.json())
        .then(productos => {
            let body = document.getElementById('productos-body');
            body.innerHTML = '';
            let tabla = document.getElementById('tabla-productos');
            let btn = document.getElementById('btn-guardar');
            let totalHTML = document.getElementById('total');

            if (productos.length === 0) {
                tabla.style.display = 'none';
                btn.style.display = 'none';
                totalHTML.innerText = '0.00';
                return;
            }

            tabla.style.display = 'table';
            btn.style.display = 'block';

            productos.forEach(prod => {

                body.innerHTML += `
                    <tr>
                        <td>${prod.nombre}
                            <input type="hidden" name="productos[]" value="${prod.id}">
                        </td>
                        <td>S/ ${prod.pivot.precio_compra}</td>
                        <td>
                            <input type="number" name="cantidades[]" 
                                   class="form-control cantidad" 
                                   min="0" value="0" 
                                   data-precio="${prod.pivot.precio_compra}">
                        </td>
                        <td class="subtotal">S/ 0.00</td>
                    </tr>
                `;
            });

            actualizarSubtotales();
        });
});


// ðŸ”¹ Recalcular subtotales y total
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('cantidad')) {
        actualizarSubtotales();
    }
});

function actualizarSubtotales() {
    let total = 0;

    document.querySelectorAll('.cantidad').forEach(input => {
        let cantidad = parseInt(input.value || 0);
        let precio = parseFloat(input.dataset.precio);
        let subtotal = cantidad * precio;

        // Mostrar subtotal
        input.closest('tr').querySelector('.subtotal').innerText = `S/ ${subtotal.toFixed(2)}`;

        total += subtotal;
    });

    document.getElementById('total').innerText = total.toFixed(2);
}
</script>
@endsection
