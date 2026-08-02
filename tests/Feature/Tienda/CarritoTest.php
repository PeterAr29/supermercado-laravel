<?php

namespace Tests\Feature\Tienda;

use App\Models\Producto;
use App\Models\User;
use App\Services\CarritoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El carrito (H-24).
 *
 * Cubre lo que rompió en fases anteriores y solo se detectó a mano: la línea
 * de un producto retirado (H-41), el carrito ajeno (H-36) y el aviso de stock
 * (H-35).
 */
class CarritoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_puede_agregar_un_producto_al_carrito(): void
    {
        $producto = Producto::factory()->conStock(10)->create();

        $respuesta = $this->post(route('carrito.agregar'), [
            'producto_id' => $producto->id,
        ]);

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('carrito_items', [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ]);
    }

    public function test_agregar_dos_veces_el_mismo_producto_suma_unidades(): void
    {
        $producto = Producto::factory()->conStock(10)->create();

        $this->post(route('carrito.agregar'), ['producto_id' => $producto->id, 'cantidad' => 2]);
        $this->post(route('carrito.agregar'), ['producto_id' => $producto->id, 'cantidad' => 3]);

        // Una sola línea con 5 unidades, no dos líneas del mismo producto:
        // el índice único de carrito_items(carrito_id, producto_id) lo exige.
        $this->assertDatabaseCount('carrito_items', 1);
        $this->assertDatabaseHas('carrito_items', [
            'producto_id' => $producto->id,
            'cantidad' => 5,
        ]);
    }

    public function test_no_se_puede_agregar_un_producto_retirado_del_catalogo(): void
    {
        // H-41 — 'exists' a secas consultaba la tabla sin el filtro de
        // SoftDeletes: el producto pasaba la validación y la tienda respondía
        // "Producto agregado al carrito" sin agregar nada.
        $producto = Producto::factory()->retirado()->create();

        $respuesta = $this->from(route('productos.index'))
            ->post(route('carrito.agregar'), ['producto_id' => $producto->id]);

        $respuesta->assertSessionHasErrors('producto_id');
        $this->assertDatabaseCount('carrito_items', 0);
    }

    public function test_no_se_pueden_agregar_mas_unidades_de_las_que_hay(): void
    {
        $producto = Producto::factory()->conStock(3)->create();

        $respuesta = $this->post(route('carrito.agregar'), [
            'producto_id' => $producto->id,
            'cantidad' => 5,
        ]);

        $respuesta->assertSessionHas('error');
        $this->assertDatabaseCount('carrito_items', 0);
    }

    public function test_un_usuario_no_puede_borrar_una_linea_del_carrito_de_otro(): void
    {
        // H-36 — 'eliminar()' no comprobaba de quién era la línea: con solo
        // conocer un id se le vaciaba el carrito a cualquiera.
        $victima = User::factory()->create();
        $producto = Producto::factory()->conStock(10)->create();

        $carritos = app(CarritoService::class);
        $linea = $carritos->agregar($carritos->paraUsuario($victima), $producto, 2);

        $this->actingAs(User::factory()->create())
            ->delete(route('carrito.eliminar', $linea->id))
            ->assertNotFound();

        $this->assertDatabaseHas('carrito_items', ['id' => $linea->id]);
    }

    public function test_el_carrito_de_invitado_se_conserva_entre_peticiones(): void
    {
        $producto = Producto::factory()->conStock(10)->create();

        $this->post(route('carrito.agregar'), ['producto_id' => $producto->id]);

        // Mira el HTML, no el código de estado: una pantalla vacía también
        // responde 200 (H-47).
        $this->get(route('carrito.index'))
            ->assertOk()
            ->assertSee($producto->nombre);
    }
}
