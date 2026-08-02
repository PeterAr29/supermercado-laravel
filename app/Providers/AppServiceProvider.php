<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CarritoItem;
use App\Models\Categoria;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            // 🔹 CONTADOR DEL CARRITO
            $carritoCount = 0;

            if (Auth::check() && Auth::user()->carrito) {
                $carritoCount = CarritoItem::where('carrito_id', Auth::user()->carrito->id)
                                           ->sum('cantidad');
            }

            // 🔹 CATEGORÍAS PARA EL MENÚ LATERAL
            $categorias = Categoria::orderBy('nombre')->get();

            // 🔹 COMPARTIR VARIABLES CON TODAS LAS VISTAS
            $view->with([
                'carritoCount' => $carritoCount,
                'categorias'   => $categorias
            ]);
        });
    }
}
