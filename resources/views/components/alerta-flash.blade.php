{{--
    Mensajes flash y errores de validación.

    Estaba copiado en seis vistas con seis combinaciones de color distintas, y
    en varias faltaba el bloque de 'error', así que un fallo se tragaba en
    silencio: el usuario volvía a la misma pantalla sin saber por qué.
--}}
@php
    $avisos = [
        ['clave' => 'success', 'fondo' => 'bg-green-100 border-green-300 text-green-800'],
        ['clave' => 'error',   'fondo' => 'bg-red-100 border-red-300 text-red-800'],
        ['clave' => 'aviso',   'fondo' => 'bg-amber-100 border-amber-300 text-amber-800'],
    ];
@endphp

@foreach($avisos as $aviso)
    @if(session($aviso['clave']))
        <div class="border px-4 py-3 rounded mb-6 {{ $aviso['fondo'] }}">
            {{ session($aviso['clave']) }}
        </div>
    @endif
@endforeach

@if($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-6">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
