@extends('layouts.layout')

@section('content')

{{-- ================= CARRUSEL DE PROMOCIONES ================= --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<div class="w-full">

    <div class="swiper promocionesSwiper">

        <div class="swiper-wrapper">

            {{-- SLIDE 1 --}}
            <div class="swiper-slide">
                <img src="https://formatodigital.net/wp-content/uploads/2019/02/flyers-que-son-1024x683.jpg"
                     class="w-full h-[420px] object-cover">
            </div>

            {{-- SLIDE 2 --}}
            <div class="swiper-slide">
                <img src="https://www.shutterstock.com/image-vector/merry-christmas-50-percent-off-260nw-2393670303.jpg"
                     class="w-full h-[420px] object-cover">
            </div>

            {{-- SLIDE 3 --}}
            <div class="swiper-slide">
                <img src="https://fantasiasmiguel.com/cdn/shop/collections/promociones_navidenas.jpg?v=1639694906&width=1920"
                     class="w-full h-[420px] object-cover">
            </div>

        </div>

        {{-- Flechas --}}
        <div class="swiper-button-next text-white"></div>
        <div class="swiper-button-prev text-white"></div>

        {{-- Paginación --}}
        <div class="swiper-pagination"></div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    new Swiper('.promocionesSwiper', {
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>

{{-- ================= PRODUCTOS ================= --}}
<div id="productos" class="max-w-7xl mx-auto px-4 mt-16">

    <h2 class="text-2xl font-bold mb-6">Productos Destacados</h2>

    @if($productos->count() === 0)
        <p class="text-gray-500">No hay productos registrados aún.</p>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

        @foreach ($productos as $producto)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">

                {{-- IMAGEN --}}
                <a href="{{ route('productos.show', $producto->id) }}">
                    <div class="relative p-4">
                        <img
                            src="{{($producto->imagen) }}"
                            alt="{{ $producto->nombre }}"
                            class="w-full h-40 object-contain group-hover:scale-105 transition"
                        >

                        {{-- BADGE --}}
                        <span class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                            Online
                        </span>
                    </div>
                </a>

                {{-- INFO --}}
                <div class="px-4 pb-4">
                    <h3 class="text-sm font-semibold text-gray-800 leading-tight line-clamp-2">
                        {{ $producto->nombre }}
                    </h3>

                    <p class="text-xs text-gray-500 mt-1">Precio por Kg</p>

                    <div class="mt-2">
                        <span class="text-lg font-bold text-red-600">
                            S/ {{ number_format($producto->precio, 2) }}
                        </span>
                    </div>

                    {{-- BOTÓN --}}
                    <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST" class="mt-3">
                        @csrf
                        <button
                            type="submit"
                            class="w-full bg-red-600 text-white py-2 rounded-full text-sm font-semibold hover:bg-red-700 transition"
                        >
                            Agregar
                        </button>
                    </form>
                </div>

            </div>
        @endforeach

    </div>

</div>


@endsection
