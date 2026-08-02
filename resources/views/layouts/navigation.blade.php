{{--
    Barra de la tienda.

    La marca sale de config('app.name') y no escrita a mano: convivían
    "PlazaKing", "Tattos Market", "Supermercado" y "Laravel" según la
    pantalla (H-18).
--}}
<nav class="bg-white border-b border-gray-200 relative z-50">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-4">

        <button type="button" onclick="alternarMenuCategorias()"
                class="text-2xl leading-none px-1" aria-label="Categorías">
            ☰
        </button>

        <a href="{{ route('home') }}" class="text-xl font-bold whitespace-nowrap">
            {{ config('app.name') }}
        </a>

        <form action="{{ route('productos.index') }}" method="GET" class="flex-1 min-w-0">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar productos..."
                   class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:border-red-500">
        </form>

        <div class="flex items-center gap-4 text-sm whitespace-nowrap">

            <a href="{{ route('carrito.index') }}" class="relative text-xl" aria-label="Carrito">
                🛒
                @if($carritoCount > 0)
                    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs px-2 rounded-full">
                        {{ $carritoCount }}
                    </span>
                @endif
            </a>

            @auth
                {{-- El enlace al panel solo existe para el administrador (H-14) --}}
                @if(auth()->user()->esAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                       class="bg-gray-900 text-white px-3 py-1.5 rounded hover:bg-gray-700">
                        Panel
                    </a>
                @endif

                <a href="{{ route('mis-pedidos.index') }}" class="text-gray-700 hover:text-black hidden sm:inline">
                    Mis pedidos
                </a>

                <a href="{{ route('mi-cuenta') }}" class="text-gray-700 hover:text-black">
                    Mi cuenta
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-gray-700 hover:text-black">Cerrar sesión</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-black">Entrar</a>
                <a href="{{ route('register') }}" class="text-gray-700 hover:text-black">Registro</a>
            @endauth
        </div>
    </div>
</nav>

{{-- MENÚ LATERAL DE CATEGORÍAS --}}
<div id="menu-categorias"
     class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg transform -translate-x-full transition-transform z-50">

    <div class="p-4 border-b font-bold text-lg flex items-center justify-between">
        Categorías
        <button type="button" onclick="alternarMenuCategorias()" class="text-gray-400 hover:text-black">✕</button>
    </div>

    <ul>
        @foreach($categorias as $categoria)
            <li>
                <a href="{{ route('productos.index', ['categoria' => $categoria->id]) }}"
                   class="block p-4 hover:bg-gray-100">
                    {{ $categoria->nombre }}
                </a>
            </li>
        @endforeach
    </ul>
</div>

<div id="fondo-menu" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"
     onclick="alternarMenuCategorias()"></div>

@push('scripts')
<script>
    function alternarMenuCategorias() {
        document.getElementById('menu-categorias').classList.toggle('-translate-x-full');
        document.getElementById('fondo-menu').classList.toggle('hidden');
    }
</script>
@endpush
