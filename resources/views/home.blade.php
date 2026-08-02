@extends('layouts.tienda')

@section('content')

    {{-- ================= CARRUSEL DE PROMOCIONES ================= --}}
    {{--
        Swiper viene del bundle de Vite y se arranca desde resources/js/app.js.
        Antes la vista traía su propio <link> y su propio <script src="...">
        apuntando a un CDN (H-17).
    --}}
    <div class="w-full mb-16">
        <div class="swiper promociones-swiper">
            <div class="swiper-wrapper">
                @foreach([
                    'https://formatodigital.net/wp-content/uploads/2019/02/flyers-que-son-1024x683.jpg',
                    'https://www.shutterstock.com/image-vector/merry-christmas-50-percent-off-260nw-2393670303.jpg',
                    'https://fantasiasmiguel.com/cdn/shop/collections/promociones_navidenas.jpg?v=1639694906&width=1920',
                ] as $promocion)
                    <div class="swiper-slide">
                        <img src="{{ $promocion }}" alt="" class="w-full h-[420px] object-cover">
                    </div>
                @endforeach
            </div>

            <div class="swiper-button-next text-white"></div>
            <div class="swiper-button-prev text-white"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    {{-- ================= PRODUCTOS DESTACADOS ================= --}}
    <div id="productos" class="max-w-7xl mx-auto px-4">

        <h2 class="text-2xl font-bold mb-6">Productos destacados</h2>

        @if($productos->isEmpty())
            <p class="text-gray-500">No hay productos registrados aún.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($productos as $producto)
                    <x-producto-card :producto="$producto" />
                @endforeach
            </div>
        @endif
    </div>

@endsection
