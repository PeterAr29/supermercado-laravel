<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProveedorProductoController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ProveedorSheetController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Productos
Route::resource('productos', ProductoController::class);


// Carrito
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');

Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])
    ->name('carrito.eliminar');

Route::post('/carrito/vaciar', [CarritoController::class, 'vaciar'])
    ->name('carrito.vaciar');



// Pago
Route::get('/pagar', [CarritoController::class, 'mostrarPago'])->name('pago.confirmar');
Route::post('/pagar/procesar', [CarritoController::class, 'procesarPago'])->name('pago.procesar');
Route::get('/pagar/exito', [CarritoController::class, 'pagoExitoso'])->name('pago.exito');


// Proveedores
Route::resource('proveedores', App\Http\Controllers\ProveedorController::class);
   
// Productos por proveedor (N:N)
Route::prefix('proveedores/{proveedor}')->group(function () {

    Route::get('productos', 
        [ProveedorProductoController::class, 'index'])
        ->name('proveedor.productos.index');

    Route::get('productos/asignar', 
        [ProveedorProductoController::class, 'create'])
        ->name('proveedor.productos.create');

    Route::post('productos/asignar', 
        [ProveedorProductoController::class, 'store'])
        ->name('proveedor.productos.store');

    Route::get('productos/{producto}/editar', 
        [ProveedorProductoController::class, 'edit'])
        ->name('proveedor.productos.edit');

    Route::put('productos/{producto}', 
        [ProveedorProductoController::class, 'update'])
        ->name('proveedor.productos.update');

    Route::delete('productos/{producto}', 
        [ProveedorProductoController::class, 'destroy'])
        ->name('proveedor.productos.destroy');
});


// Órdenes de compra
Route::get('/ordenes', [OrdenCompraController::class, 'index'])->name('ordenes.index');
Route::get('/ordenes/create', [OrdenCompraController::class, 'create'])->name('ordenes.create');
Route::post('/ordenes', [OrdenCompraController::class, 'store'])->name('ordenes.store');
Route::get('/ordenes/{orden}', [OrdenCompraController::class, 'show'])->name('ordenes.show');
Route::post('/ordenes/{orden}/recibir', [OrdenCompraController::class, 'recibir'])->name('ordenes.recibir');

// AJAX — productos del proveedor
Route::get('/proveedor/{id}/productos', [OrdenCompraController::class, 'productosProveedor']);

// Proveedores EN LINEA
Route::get('/proveedores-sheet', [ProveedorSheetController::class, 'index'])
    ->name('proveedores.sheet');

// Filtrar Categoria
Route::get('/categoria/{categoria}', [ProductoController::class, 'porCategoria'])
    ->name('productos.categoria');

Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');

/*
|--------------------------------------------------------------------------
| Autenticación Breeze
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
