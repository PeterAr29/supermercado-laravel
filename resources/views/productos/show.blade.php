@extends('layouts.tienda')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- ================== PRODUCTO PRINCIPAL ================== --}}
    <div class="grid grid-cols-12 gap-8 bg-white rounded-xl shadow p-6">

        {{-- IMAGEN --}}
        <div class="col-span-12 md:col-span-5 flex justify-center">
            <img
                src="{{ $producto->imagen }}"
                class="w-full max-w-md rounded-xl object-cover"
                alt="{{ $producto->nombre }}"
            >
        </div>

        {{-- INFO --}}
        <div class="col-span-12 md:col-span-4">
            <span class="text-sm text-gray-500 uppercase">
                {{ $producto->categoria->nombre ?? 'Producto' }}
            </span>

            <h1 class="text-2xl font-bold mt-2">
                {{ $producto->nombre }}
            </h1>

            {{-- PRECIO --}}
            <div class="mt-4">
                <span class="text-3xl font-bold text-red-600">
                    S/ {{ number_format($producto->precio, 2) }}
                </span>
                <span class="text-gray-500 text-sm">
                    x {{ $producto->unidad_medida?->sufijo() ?? 'und' }}
                </span>
            </div>

            {{-- INPUT OPCIONAL --}}
            <div class="mt-6">
                <label class="font-semibold text-sm">
                    ¿Qué debemos considerar al comprar este producto?
                </label>
                <textarea
                    class="w-full border rounded-lg p-3 mt-2 focus:ring focus:ring-red-200"
                    rows="3"
                    placeholder="Ej: Tipo de corte, tamaño, color..."
                ></textarea>
            </div>

            {{-- DISPONIBILIDAD --}}
            <p class="mt-4 text-sm">
                @if($producto->stock > 0)
                    <span class="text-green-700 font-medium">
                        {{ $producto->stock }} {{ $producto->unidad_medida?->sufijo() ?? 'und' }} disponibles
                    </span>
                @else
                    <span class="text-gray-500 font-medium">Agotado</span>
                @endif
            </p>

            {{-- BOTÓN --}}
            {{--
                El id del producto va en un campo del formulario, no en la URL:
                'carrito.agregar' no recibe parámetros de ruta, así que el id se
                perdía y la validación rechazaba el envío. Este botón nunca
                llegó a funcionar (H-43).
            --}}
            <form action="{{ route('carrito.agregar') }}" method="POST" class="mt-6">
                @csrf
                <input type="hidden" name="producto_id" value="{{ $producto->id }}">

                <button
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-full transition disabled:bg-gray-300"
                    @disabled($producto->stock <= 0)
                >
                    Agregar al carrito
                </button>
            </form>

            {{-- CARACTERÍSTICAS --}}
            <div class="mt-6">
                <h3 class="font-semibold mb-2">Características</h3>

                @if($producto->descripcion)
                    <ul class="list-disc ml-5 text-gray-600 text-sm">
                        <li>{{ $producto->descripcion }}</li>
                    </ul>
                @else
                    <p class="text-sm text-gray-500">
                        No hay descripción disponible para este producto.
                    </p>
                @endif
            </div>
        </div>

        {{-- PANEL LATERAL --}}
        <div class="col-span-12 md:col-span-3 space-y-4">

            <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="font-semibold">Vendido y despachado por</h4>
                <p class="text-gray-600">{{ config('app.name') }}</p>
            </div>

            {{--
                Proveedores desde la base de datos. Antes venían de una hoja de
                Google publicada, con nombres de campo propios: la BD es la
                única fuente desde la Fase 3 (H-21).
            --}}
            <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="font-semibold">Proveedores</h4>

                @forelse($proveedores as $proveedor)
                    @if($loop->first)<ul class="mt-2 space-y-1 text-sm text-gray-600">@endif
                        <li>• {{ $proveedor->nombre }}</li>
                    @if($loop->last)</ul>@endif
                @empty
                    <p class="text-sm text-gray-500 mt-2">
                        No hay proveedores disponibles
                    </p>
                @endforelse
            </div>

            <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="font-semibold">Entrega</h4>
                <p class="text-gray-600">Delivery disponible</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="font-semibold">Métodos de pago</h4>
                <p class="text-gray-600">Tarjeta • Yape</p>
            </div>

        </div>
    </div>

    {{-- ================== PRODUCTOS SIMILARES ================== --}}
    @if(isset($productosSimilares) && count($productosSimilares))
        <div class="mt-12">
            <h2 class="text-xl font-bold mb-6">Productos similares</h2>

            {{-- La misma tarjeta que el home: estaba duplicada con otra
                 maquetación y otro botón (H-16) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                @foreach($productosSimilares as $item)
                    <x-producto-card :producto="$item" />
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection
