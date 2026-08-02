<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoOrdenCompra;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Services\OrdenCompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Abastecimiento: pedir mercancía y darla por recibida (H-24).
 *
 * Todo el módulo de proveedores y órdenes nunca llegó a ejecutarse hasta la
 * Fase 1 (H-29, H-30). Estas pruebas son las que lo habrían dicho el primer día.
 */
class OrdenCompraTest extends TestCase
{
    use RefreshDatabase;

    private function proveedorConCatalogo(Producto $producto, float $precioCompra = 8.00): Proveedor
    {
        $proveedor = Proveedor::factory()->create();

        $proveedor->productos()->attach($producto->id, [
            'stock_proveedor' => 500,
            'precio_compra' => $precioCompra,
        ]);

        return $proveedor;
    }

    public function test_crear_una_orden_calcula_el_total_desde_el_precio_de_compra(): void
    {
        // H-04 — El precio del pivot no llegaba al total y las órdenes salían
        // por 0. El nombre de la columna estaba escrito de dos formas.
        $admin = User::factory()->admin()->create();
        $producto = Producto::factory()->conStock(0)->create();
        $proveedor = $this->proveedorConCatalogo($producto, 8.00);

        $this->actingAs($admin)->post(route('admin.ordenes.store'), [
            'proveedor_id' => $proveedor->id,
            'productos' => [$producto->id],
            'cantidades' => [20],
        ])->assertRedirect(route('admin.ordenes.index'));

        $this->assertDatabaseHas('ordenes_compra', [
            'proveedor_id' => $proveedor->id,
            'estado' => 'pendiente',
            'total' => '160.00',
        ]);
    }

    public function test_recibir_una_orden_repone_stock_y_registra_el_movimiento(): void
    {
        $admin = User::factory()->admin()->create();
        $producto = Producto::factory()->conStock(4)->create();
        $proveedor = $this->proveedorConCatalogo($producto);

        $orden = app(OrdenCompraService::class)->crear($proveedor, [$producto->id => 20]);

        $this->actingAs($admin)
            ->post(route('admin.ordenes.recibir', $orden))
            ->assertRedirect();

        $this->assertSame(24, $producto->fresh()->stock);
        $this->assertSame(EstadoOrdenCompra::Recibido, $orden->fresh()->estado);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'tipo' => 'entrada',
            'cantidad' => 20,
            'stock_resultante' => 24,
            'origen_type' => OrdenCompra::class,
            'origen_id' => $orden->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_una_orden_no_se_puede_recibir_dos_veces(): void
    {
        // H-37 — El cast a enum rompió la comparación del estado, que se hacía
        // contra un string: la orden se podía recibir otra vez y el stock se
        // duplicaba.
        $admin = User::factory()->admin()->create();
        $producto = Producto::factory()->conStock(0)->create();
        $proveedor = $this->proveedorConCatalogo($producto);

        $orden = app(OrdenCompraService::class)->crear($proveedor, [$producto->id => 20]);

        $this->actingAs($admin)->post(route('admin.ordenes.recibir', $orden));
        $this->actingAs($admin)
            ->post(route('admin.ordenes.recibir', $orden))
            ->assertSessionHas('error');

        $this->assertSame(20, $producto->fresh()->stock);
        $this->assertDatabaseCount('movimientos_inventario', 1);
    }

    public function test_no_se_puede_pedir_un_producto_que_el_proveedor_no_surte(): void
    {
        $admin = User::factory()->admin()->create();
        $proveedor = Proveedor::factory()->create();
        $ajeno = Producto::factory()->create();

        $this->actingAs($admin)->post(route('admin.ordenes.store'), [
            'proveedor_id' => $proveedor->id,
            'productos' => [$ajeno->id],
            'cantidades' => [5],
        ])->assertSessionHasErrors('productos');

        $this->assertDatabaseCount('ordenes_compra', 0);
    }
}
