<?php

namespace Tests\Feature\Tienda;

use App\Models\Producto;
use App\Models\User;
use App\Services\CarritoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El cobro del carrito (H-24).
 *
 * La prueba que faltaba desde el principio: hasta la Fase 3 se podían vender
 * 50 unidades y el stock seguía intacto, porque la venta no lo tocaba (H-35).
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_checkout_descuenta_stock_y_registra_el_movimiento(): void
    {
        $cliente = User::factory()->cliente()->create();
        $producto = Producto::factory()->conStock(10)->create();

        $this->actingAs($cliente)
            ->post(route('carrito.agregar'), ['producto_id' => $producto->id, 'cantidad' => 3]);

        $this->actingAs($cliente)
            ->post(route('pago.procesar'))
            ->assertRedirect(route('pago.exito'));

        $this->assertSame(7, $producto->fresh()->stock);

        // El kardex explica el descuento: sin su línea, el stock habría bajado
        // sin que nada diga por qué (H-35).
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'tipo' => 'salida',
            'cantidad' => -3,
            'stock_resultante' => 7,
            'user_id' => $cliente->id,
        ]);
    }

    public function test_la_venta_queda_asociada_a_quien_compro(): void
    {
        // H-10 — Antes las ventas no sabían de quién eran, y "Mis pedidos"
        // no podía existir.
        $cliente = User::factory()->cliente()->create();
        $producto = Producto::factory()->conStock(10)->create(['precio' => 12.50]);

        $this->actingAs($cliente)
            ->post(route('carrito.agregar'), ['producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($cliente)->post(route('pago.procesar'));

        $this->assertDatabaseHas('ventas', [
            'user_id' => $cliente->id,
            'total' => '25.00',
        ]);

        $this->actingAs($cliente)
            ->get(route('mis-pedidos.index'))
            ->assertOk()
            ->assertSee('25.00');
    }

    public function test_el_carrito_queda_vacio_despues_de_cobrar(): void
    {
        $cliente = User::factory()->cliente()->create();
        $producto = Producto::factory()->conStock(10)->create();

        $this->actingAs($cliente)
            ->post(route('carrito.agregar'), ['producto_id' => $producto->id]);

        $this->actingAs($cliente)->post(route('pago.procesar'));

        $this->assertDatabaseCount('carrito_items', 0);
    }

    public function test_no_se_puede_cobrar_un_carrito_vacio(): void
    {
        $cliente = User::factory()->cliente()->create();

        $this->actingAs($cliente)
            ->post(route('pago.procesar'))
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_no_se_cobra_si_el_stock_se_agoto_despues_de_llenar_el_carrito(): void
    {
        // Un carrito puede quedarse días abierto mientras el stock se agota.
        // Ni se cobra, ni se queda el inventario en negativo.
        $cliente = User::factory()->cliente()->create();
        $producto = Producto::factory()->conStock(5)->create();

        $carritos = app(CarritoService::class);
        $carritos->agregar($carritos->paraUsuario($cliente), $producto, 5);

        $producto->update(['stock' => 1]);

        $this->actingAs($cliente)
            ->post(route('pago.procesar'))
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(1, $producto->fresh()->stock);
    }
}
