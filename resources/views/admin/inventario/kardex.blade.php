@extends('layouts.admin')

@section('titulo', 'Kardex de '.$producto->nombre)

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.inventario.index') }}" class="text-sm text-gray-500 hover:underline">
                ← Inventario
            </a>
            <h1 class="text-2xl font-bold">{{ $producto->nombre }}</h1>
        </div>

        <a href="{{ route('admin.productos.edit', $producto) }}"
           class="text-sm text-blue-600 hover:underline">Editar ficha</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- ================= COLUMNA IZQUIERDA ================= --}}
        <div class="space-y-6">

            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-sm text-gray-500">Stock actual</p>
                <p class="text-4xl font-bold mt-1 {{ $producto->bajoMinimo() ? 'text-amber-600' : '' }}">
                    {{ $producto->stock }}
                    <span class="text-base font-normal text-gray-400">{{ $producto->unidad_medida->sufijo() }}</span>
                </p>
                <p class="text-sm text-gray-500 mt-1">Mínimo: {{ $producto->stock_minimo }}</p>

                {{--
                    Si estas dos cifras no coinciden, algo movió existencias sin
                    pasar por InventarioService. Es el chivato del criterio de
                    aceptación: stock = entradas − salidas ± ajustes.
                --}}
                @if($stockSegunKardex !== $producto->stock)
                    <div class="mt-4 bg-red-100 border border-red-300 text-red-800 text-sm px-3 py-2 rounded">
                        <strong>Descuadre:</strong> el kardex suma {{ $stockSegunKardex }}
                        y el producto declara {{ $producto->stock }}.
                    </div>
                @else
                    <p class="mt-4 text-sm text-green-700">✓ El kardex cuadra con el stock declarado.</p>
                @endif
            </div>

            {{-- AJUSTE MANUAL --}}
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-semibold mb-1">Ajustar stock</h2>
                <p class="text-sm text-gray-500 mb-4">
                    Se cuenta la estantería y se escribe lo que hay de verdad.
                    El motivo es obligatorio: un ajuste sin explicación es un
                    descuadre con permiso.
                </p>

                <form action="{{ route('admin.inventario.ajustar', $producto) }}" method="POST"
                      class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock real contado</label>
                        <input type="number" min="0" name="stock_real" required
                               value="{{ old('stock_real') }}"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                        <input type="text" name="motivo" required minlength="5" maxlength="255"
                               value="{{ old('motivo') }}"
                               placeholder="Recuento físico, merma, rotura…"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <button class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded">
                        Registrar ajuste
                    </button>
                </form>
            </div>
        </div>

        {{-- ================= KARDEX ================= --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-3 border-b">
                    <h2 class="font-semibold">Movimientos</h2>
                    <p class="text-sm text-gray-500">De dónde viene y a dónde va cada unidad.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2">Motivo</th>
                                <th class="px-4 py-2">Quién</th>
                                <th class="px-4 py-2 text-right">Cantidad</th>
                                <th class="px-4 py-2 text-right">Quedan</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($movimientos as $movimiento)
                                <tr class="border-b last:border-0">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                                        {{ $movimiento->created_at->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="text-xs px-2 py-0.5 rounded bg-{{ $movimiento->tipo->color() }}-100 text-{{ $movimiento->tipo->color() }}-800">
                                            {{ $movimiento->tipo->etiqueta() }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-gray-700">{{ $movimiento->motivo }}</td>

                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $movimiento->user->name ?? 'Invitado' }}
                                    </td>

                                    <td class="px-4 py-3 text-right font-bold whitespace-nowrap {{ $movimiento->cantidad > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ $movimiento->cantidad }}
                                    </td>

                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ $movimiento->stock_resultante }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        Este producto todavía no tiene movimientos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $movimientos->links() }}
            </div>
        </div>
    </div>

@endsection
