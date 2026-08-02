{{--
    Pantallas de acceso: entrar, registrarse, recuperar contraseña.

    Viene de Breeze, y traía su propia cabecera con la fuente Figtree desde
    fonts.bunny.net — la quinta copia del <head> y otro CDN (H-15, H-17). Ahora
    cuelga de la base como todo lo demás.

    La fuente se cae a la del sistema, que es lo que ya usaba el resto de la
    aplicación: Figtree solo se cargaba aquí, así que estas tres pantallas se
    veían con una tipografía distinta a las demás.
--}}
@extends('layouts.base')

@section('contenido')

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

        <a href="{{ route('home') }}" class="text-2xl font-bold">
            {{ config('app.name') }}
        </a>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

@endsection
