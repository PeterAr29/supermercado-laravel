<nav class="bg-red-600 text-white shadow-md">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">

        <!-- LOGO -->
        <a href="{{ route('dashboard') }}" class="text-2xl font-bold flex items-center gap-2">
            <span class="text-yellow-300">🛒</span> PlazaKing
        </a>

        <!-- SEARCH BAR -->
        <div class="hidden md:flex flex-1 mx-6">
            <input type="text"
                   placeholder="¿Qué estás buscando hoy?"
                   class="w-full px-4 py-2 rounded-lg text-black outline-none" />
        </div>

        <!-- CARRO + USUARIO -->
        <div class="flex items-center gap-4">

            <!-- CARRITO -->
            <a href="#" class="relative">
                <span class="text-2xl">🛒</span>
                <span class="absolute -top-2 -right-2 bg-yellow-300 text-red-700 font-bold px-2 py-0.5 text-xs rounded-full">
                    0
                </span>
            </a>

            <!-- USER -->
            @auth
                <div class="flex items-center gap-2">
                    <span class="font-medium hidden sm:block">{{ Auth::user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="bg-white text-red-600 font-bold px-3 py-1 rounded hover:bg-gray-200">
                            Salir
                        </button>
                    </form>
                </div>
            @endauth

            @guest
                <a href="{{ route('login') }}"
                   class="bg-white text-red-600 px-3 py-1 rounded font-semibold hover:bg-gray-200">
                    Iniciar sesión
                </a>
            @endguest

        </div>
    </div>

    <!-- CATEGORY BAR -->
    <div class="bg-red-700 py-2 shadow-inner">
        <div class="max-w-7xl mx-auto flex gap-6 px-4 overflow-x-auto text-sm">
            <a class="hover:text-yellow-300 cursor-pointer">Supermercado</a>
            <a class="hover:text-yellow-300 cursor-pointer">Ofertas</a>
            <a class="hover:text-yellow-300 cursor-pointer">Verduras</a>
            <a class="hover:text-yellow-300 cursor-pointer">Abarrotes</a>
            <a class="hover:text-yellow-300 cursor-pointer">Bebidas</a>
            <a class="hover:text-yellow-300 cursor-pointer">Limpieza</a>
            <a class="hover:text-yellow-300 cursor-pointer">Hogar</a>
            <a class="hover:text-yellow-300 cursor-pointer">Tecnología</a>
        </div>
    </div>
</nav>
