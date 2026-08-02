<?php

namespace Tests\Feature\Admin;

use App\Models\Producto;
use App\Models\User;
use App\Services\CarritoService;
use App\Services\CheckoutService;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Quién firma cada asiento del kardex (H-51).
 *
 * La vista hacía `$movimiento->user->name ?? 'Invitado'`, y con eso los
 * asientos de apertura —que escribe un seeder por consola— salían firmados
 * por «Invitado», que en un libro de inventario se lee como una persona.
 */
class KardexTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_movimiento_hecho_por_un_usuario_lleva_su_nombre(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Rosa Quispe']);
        $producto = Producto::factory()->conStock(10)->create();

        $movimiento = app(InventarioService::class)
            ->ajustar($producto, 15, 'Recuento físico', $admin);

        $this->assertSame('Rosa Quispe', $movimiento->autor());
    }

    public function test_el_saldo_de_apertura_lo_firma_el_sistema(): void
    {
        // Sin usuario y sin documento de origen: lo escribió un seeder por
        // consola, no lo movió nadie a mano.
        $producto = Producto::factory()->conStock(120)->create();

        $movimiento = app(InventarioService::class)->conciliar($producto);

        $this->assertNull($movimiento->user_id);
        $this->assertNull($movimiento->origen_type);
        $this->assertSame('Sistema', $movimiento->autor());
    }

    public function test_la_venta_de_un_invitado_si_la_firma_un_invitado(): void
    {
        // Sin usuario pero CON venta detrás: aquí sí hubo alguien, un
        // comprador sin cuenta, que la tienda permite (H-10). El otro caso
        // que había que distinguir.
        $producto = Producto::factory()->conStock(10)->create();

        $carritos = app(CarritoService::class);
        $carrito = $carritos->paraInvitado(null);
        $carritos->agregar($carrito, $producto, 2);

        app(CheckoutService::class)->cobrar($carrito, null);

        $movimiento = $producto->movimientos()->first();

        $this->assertNull($movimiento->user_id);
        $this->assertNotNull($movimiento->origen_type);
        $this->assertSame('Invitado', $movimiento->autor());
    }

    public function test_el_kardex_no_atribuye_la_apertura_a_un_invitado(): void
    {
        $admin = User::factory()->admin()->create();
        $producto = Producto::factory()->conStock(120)->create();

        app(InventarioService::class)->conciliar($producto);

        // Mira el HTML, que es donde se leía mal (H-47).
        $this->actingAs($admin)
            ->get(route('admin.inventario.show', $producto))
            ->assertOk()
            ->assertSee('Sistema')
            ->assertDontSee('Invitado');
    }
}
