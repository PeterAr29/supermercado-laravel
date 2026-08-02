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
| Zona privada — requiere sesión iniciada
|--------------------------------------------------------------------------
| Se declara ANTES que la zona pública a propósito: 'productos/create' debe
| registrarse antes que 'productos/{producto}', o la segunda capturaría la
| palabra "create" como si fuera un id.
*/

Route::middleware('auth')->group(function () {

    // Productos — todo salvo consultar
    Route::resource('productos', ProductoController::class)->except(['index', 'show']);

    // Proveedores — sin 'show': no existe ficha de detalle y el resource
    // registraba una ruta que reventaba con 500 (H-07).
    Route::resource('proveedores', ProveedorController::class)->except(['show']);

    // Productos por proveedor (N:N)
    Route::prefix('proveedores/{proveedor}')->name('proveedor.productos.')->group(function () {
        Route::get('productos', [ProveedorProductoController::class, 'index'])->name('index');
        Route::get('productos/asignar', [ProveedorProductoController::class, 'create'])->name('create');
        Route::post('productos/asignar', [ProveedorProductoController::class, 'store'])->name('store');
        Route::get('productos/{producto}/editar', [ProveedorProductoController::class, 'edit'])->name('edit');
        Route::put('productos/{producto}', [ProveedorProductoController::class, 'update'])->name('update');
        Route::delete('productos/{producto}', [ProveedorProductoController::class, 'destroy'])->name('destroy');
    });

    // Órdenes de compra
    Route::get('/ordenes', [OrdenCompraController::class, 'index'])->name('ordenes.index');
    Route::get('/ordenes/create', [OrdenCompraController::class, 'create'])->name('ordenes.create');
    Route::post('/ordenes', [OrdenCompraController::class, 'store'])->name('ordenes.store');
    Route::get('/ordenes/{orden}', [OrdenCompraController::class, 'show'])->name('ordenes.show');
    Route::post('/ordenes/{orden}/recibir', [OrdenCompraController::class, 'recibir'])->name('ordenes.recibir');

    // AJAX — productos de un proveedor (usado por ordenes/create)
    // La URL se reorganiza en la Fase 3 (H-19); aquí solo se protege.
    Route::get('/proveedor/{id}/productos', [OrdenCompraController::class, 'productosProveedor']);

    // Proveedores publicados en Google Sheets
    Route::get('/proveedores-sheet', [ProveedorSheetController::class, 'index'])->name('proveedores.sheet');

    // Panel Breeze
    Route::get('/dashboard', fn () => view('dashboard'))->middleware('verified')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Zona pública — tienda
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
