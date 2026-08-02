@extends('layouts.admin')

@section('titulo', 'Nueva orden de compra')

@section('content')

    <div class="max-w-4xl">

        <a href="{{ route('admin.ordenes.index') }}" class="text-sm text-gray-500 hover:underline">
            ← Órdenes de compra
        </a>

        <h1 class="text-2xl font-bold mt-2 mb-6">Nueva orden de compra</h1>

        <form action="{{ route('admin.ordenes.store') }}" method="POST"
              class="bg-white rounded-lg shadow p-6">
            @csrf

            <div class="max-w-md">
                <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Proveedor
                </label>

                <select name="proveedor_id" id="proveedor_id" required
                        class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">— Seleccione un proveedor —</option>
                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div id="sin-proveedor" class="mt-8 text-gray-500 text-sm">
                Elija un proveedor para ver qué productos nos vende y a qué precio.
            </div>

            <div id="bloque-productos" class="mt-8 hidden">
                <h2 class="font-semibold mb-1">Qué se pide</h2>
                <p class="text-sm text-gray-500 mb-4">
                    Indique la cantidad de los productos que entran en esta orden. Los que se
                    queden a cero no se piden.
                </p>

                <div class="overflow-x-auto border rounded">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-4 py-2">Producto</th>
                                <th class="px-4 py-2 text-right">Precio de compra</th>
                                <th class="px-4 py-2 text-right w-32">Cantidad</th>
                                <th class="px-4 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody id="productos-body"></tbody>
                    </table>
                </div>

                <div id="sin-catalogo" class="hidden text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded p-3 mt-4">
                    Este proveedor no tiene productos asignados todavía.
                </div>

                <div class="flex items-center justify-between mt-6 pt-6 border-t">
                    <p class="text-lg">
                        Total: <span class="font-bold">S/ <span id="total">0.00</span></span>
                    </p>

                    <button id="btn-guardar"
                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                        Guardar orden
                    </button>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    const selectorProveedor = document.getElementById('proveedor_id');
    const cuerpoTabla       = document.getElementById('productos-body');
    const bloqueProductos   = document.getElementById('bloque-productos');
    const sinProveedor      = document.getElementById('sin-proveedor');
    const sinCatalogo       = document.getElementById('sin-catalogo');
    const totalHTML         = document.getElementById('total');

    // La URL se genera desde la ruta con nombre y se sustituye el marcador:
    // escrita a mano, cualquier reorganización de rutas la rompe en silencio.
    const urlProductos = '{{ route('admin.ajax.proveedor.productos', ['proveedor' => 'PROVEEDOR_ID']) }}';

    selectorProveedor.addEventListener('change', function () {
        const proveedorId = this.value;

        if (!proveedorId) {
            bloqueProductos.classList.add('hidden');
            sinProveedor.classList.remove('hidden');
            return;
        }

        fetch(urlProductos.replace('PROVEEDOR_ID', proveedorId))
            .then(respuesta => respuesta.json())
            .then(productos => {
                cuerpoTabla.innerHTML = '';
                sinProveedor.classList.add('hidden');
                bloqueProductos.classList.remove('hidden');
                sinCatalogo.classList.toggle('hidden', productos.length > 0);

                productos.forEach(producto => {
                    const precio = producto.pivot.precio_compra;

                    cuerpoTabla.insertAdjacentHTML('beforeend', `
                        <tr class="border-t">
                            <td class="px-4 py-2">
                                ${producto.nombre}
                                <input type="hidden" name="productos[]" value="${producto.id}">
                            </td>
                            <td class="px-4 py-2 text-right">S/ ${Number(precio).toFixed(2)}</td>
                            <td class="px-4 py-2 text-right">
                                <input type="number" name="cantidades[]" min="0" value="0"
                                       data-precio="${precio}"
                                       class="cantidad w-24 border border-gray-300 rounded px-2 py-1 text-right">
                            </td>
                            <td class="px-4 py-2 text-right subtotal">S/ 0.00</td>
                        </tr>
                    `);
                });

                actualizarTotales();
            });
    });

    document.addEventListener('input', function (evento) {
        if (evento.target.classList.contains('cantidad')) {
            actualizarTotales();
        }
    });

    function actualizarTotales() {
        let total = 0;

        document.querySelectorAll('.cantidad').forEach(campo => {
            const subtotal = (parseInt(campo.value) || 0) * parseFloat(campo.dataset.precio);

            campo.closest('tr').querySelector('.subtotal').innerText = `S/ ${subtotal.toFixed(2)}`;
            total += subtotal;
        });

        totalHTML.innerText = total.toFixed(2);
    }
</script>
@endpush
