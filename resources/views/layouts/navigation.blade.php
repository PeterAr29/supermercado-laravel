<nav class="bg-white border-b border-gray-200 relative z-50">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">

        {{-- IZQUIERDA --}}
        <div class="flex items-center space-x-4">

            {{-- BOTÓN MENÚ --}}
            <button onclick="toggleMenu()" class="text-2xl">
                ☰
            </button>

            {{-- LOGO --}}
            <a href="{{ route('home') }}" class="text-xl font-bold">
                Tattos Market
            </a>
        </div>

        {{-- BUSCADOR (luego será en tiempo real) --}}
        <form action="{{ route('productos.index') }}" method="GET" class="flex-1 mx-6">
            <input
                type="text"
                name="buscar"
                value="{{ request('buscar') }}"
                placeholder="Buscar productos..."
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:border-red-500"
            >
        </form>


        {{-- DERECHA --}}
        <div class="flex items-center space-x-5">

            {{-- CARRITO --}}
            <a href="{{ route('carrito.index') }}" class="relative text-xl">
                🛒
                @if($carritoCount > 0)
                    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs px-2 rounded-full">
                        {{ $carritoCount }}
                    </span>
                @endif
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-black">
                    Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-gray-700 hover:text-black">
                        Cerrar sesión
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-black">Login</a>
                <a href="{{ route('register') }}" class="text-gray-700 hover:text-black">Registro</a>
            @endauth
        </div>
    </div>
</nav>

<!-- MENÚ LATERAL -->
<div id="menuLateral"
     class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg transform -translate-x-full transition-transform z-50">

    <div class="p-4 border-b font-bold text-lg">
        Categorías
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

<!-- OVERLAY -->
<div id="overlay"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"
     onclick="toggleMenu()"></div>


<script>
    function toggleMenu() {
        document.getElementById('menuLateral').classList.toggle('-translate-x-full');
        document.getElementById('overlay').classList.toggle('hidden');
    }
</script>
