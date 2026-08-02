<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Venta;
use App\Policies\CategoriaPolicy;
use App\Policies\OrdenCompraPolicy;
use App\Policies\ProductoPolicy;
use App\Policies\ProveedorPolicy;
use App\Policies\VentaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * Se declaran explícitamente en vez de confiar en el descubrimiento
     * automático: este proyecto ya se llevó un disgusto por dejar que Laravel
     * adivinara nombres (H-29, `Proveedor` → tabla `proveedors`).
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Categoria::class => CategoriaPolicy::class,
        Producto::class => ProductoPolicy::class,
        Proveedor::class => ProveedorPolicy::class,
        OrdenCompra::class => OrdenCompraPolicy::class,
        Venta::class => VentaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
