<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\OrdenCompraController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\Admin\ProveedorController;
use App\Http\Controllers\Admin\ProveedorProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tienda\CarritoController;
use App\Http\Controllers\Tienda\HomeController;
use App\Http\Controllers\Tienda\MiCuentaController;
use App\Http\Controllers\Tienda\ProductoController;
use App\Http\Controllers\Tienda\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel de administración — /admin
|--------------------------------------------------------------------------
| Toda la gestión cuelga de aquí desde la Fase 3. Antes vivía en la raíz,
| mezclada con la tienda, y bastaba con registrarse para entrar (H-14).
|
| El middleware 'admin' va siempre detrás de 'auth': primero identificarse,
| después comprobar el rol.
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Catálogo — sin 'show': la ficha pública ya existe en la tienda
        Route::resource('productos', AdminProductoController::class)->except(['show']);

        // Categorías — era alcance de la Fase 3 y se quedó fuera: hasta ahora
        // solo existían las que sembraba CategoriaSeeder.
        Route::resource('categorias', CategoriaController::class)
            ->except(['show'])
            ->parameters(['categorias' => 'categoria']);

        // Proveedores — sin 'show': no existe ficha de detalle y el resource
        // registraba una ruta que reventaba con 500 (H-07).
        //
        // parameters() no es cosmético: Laravel singulariza 'proveedores' como
        // '{proveedore}', que no casa con el argumento `Proveedor $proveedor`
        // del controlador. El binding no se resolvía, llegaba un modelo vacío y
        // editar un proveedor devolvía 500 (H-44). Mismo error que H-29, ahora
        // en las rutas: dejar que el framework adivine el español.
        Route::resource('proveedores', ProveedorController::class)
            ->except(['show'])
            ->parameters(['proveedores' => 'proveedor']);

        // Catálogo de cada proveedor (N:N)
        Route::prefix('proveedores/{proveedor}')->name('proveedor.productos.')->group(function () {
            Route::get('productos', [ProveedorProductoController::class, 'index'])->name('index');
            Route::get('productos/asignar', [ProveedorProductoController::class, 'create'])->name('create');
            Route::post('productos/asignar', [ProveedorProductoController::class, 'store'])->name('store');
            Route::get('productos/{producto}/editar', [ProveedorProductoController::class, 'edit'])->name('edit');
            Route::put('productos/{producto}', [ProveedorProductoController::class, 'update'])->name('update');
            Route::delete('productos/{producto}', [ProveedorProductoController::class, 'destroy'])->name('destroy');
        });

        // Órdenes de compra. Sin 'edit'/'update'/'destroy': una orden emitida
        // no se retoca, se recibe — y eso es una acción con nombre propio,
        // porque es la única que mueve stock (H-19).
        Route::resource('ordenes', OrdenCompraController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['ordenes' => 'orden']);

        Route::post('/ordenes/{orden}/recibir', [OrdenCompraController::class, 'recibir'])
            ->name('ordenes.recibir');

        // Peticiones del navegador que devuelven JSON, agrupadas bajo 'ajax/'
        // para que se vea de un vistazo que no son pantallas (H-19).
        Route::prefix('ajax')->name('ajax.')->group(function () {
            Route::get('/proveedores/{proveedor}/productos', [OrdenCompraController::class, 'productosProveedor'])
                ->name('proveedor.productos');
        });

        // Inventario y kardex (H-35)
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::get('/inventario/{producto}', [InventarioController::class, 'show'])->name('inventario.show');
        Route::post('/inventario/{producto}/ajustar', [InventarioController::class, 'ajustar'])->name('inventario.ajustar');
    });

/*
|--------------------------------------------------------------------------
| Zona del usuario — cualquier rol, con sesión iniciada
|--------------------------------------------------------------------------
| Son datos del propio usuario, no de la gestión: un cliente entra aquí.
*/

Route::middleware('auth')->group(function () {

    Route::get('/mi-cuenta', [MiCuentaController::class, 'index'])->name('mi-cuenta');
    Route::get('/mis-pedidos', [VentaController::class, 'index'])->name('mis-pedidos.index');
    Route::get('/mis-pedidos/{venta}', [VentaController::class, 'show'])->name('mis-pedidos.show');

    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');

    // Perfil — el controlador y las vistas existían, pero las rutas nunca
    // llegaron a registrarse: /profile devolvía 404 (H-31).
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Tienda — pública
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Catálogo (solo consulta)
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');
Route::get('/categoria/{categoria}', [ProductoController::class, 'porCategoria'])->name('productos.categoria');

// Carrito
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
Route::post('/carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');

// Pago
Route::get('/pagar', [CarritoController::class, 'mostrarPago'])->name('pago.confirmar');
Route::post('/pagar/procesar', [CarritoController::class, 'procesarPago'])->name('pago.procesar');
Route::get('/pagar/exito', [CarritoController::class, 'pagoExitoso'])->name('pago.exito');

/*
|--------------------------------------------------------------------------
| Autenticación Breeze
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
