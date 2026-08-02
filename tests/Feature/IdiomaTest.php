<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Una sola voz (H-49).
 *
 * El paginador y los mensajes de error salían en inglés sobre pantallas
 * íntegramente en español. Es el mismo problema que cerró H-18 con la marca:
 * antes convivían cuatro marcas, después convivían dos idiomas.
 *
 * Ninguna de estas comprobaciones la habría hecho un test de la Fase 6: las
 * pantallas respondían 200 y decían lo que el código mandaba decir.
 */
class IdiomaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_paginacion_del_catalogo_esta_en_espanol(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(15)->create(['categoria_id' => $categoria->id]);

        $this->get(route('productos.index'))
            ->assertOk()
            ->assertSee('Siguiente')
            ->assertDontSee('Showing')
            ->assertDontSee('results');
    }

    public function test_la_paginacion_del_panel_tambien(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(25)->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->get(route('admin.productos.index'))
            ->assertOk()
            ->assertSee('Siguiente')
            ->assertDontSee('Showing');
    }

    public function test_los_mensajes_de_validacion_estan_en_espanol_y_bien_escritos(): void
    {
        // Con ':attribute es obligatorio' el mensaje salía «producto es
        // obligatorio.»: sin artículo y en minúscula. El sustituto se inserta
        // tal cual, así que el artículo tiene que estar en la traducción.
        $this->post(route('carrito.agregar'), [])
            ->assertSessionHasErrors([
                'producto_id' => 'El campo producto es obligatorio.',
            ]);
    }

    public function test_el_error_de_credenciales_esta_en_espanol(): void
    {
        User::factory()->create(['email' => 'alguien@supermercado.test']);

        $this->post(route('login'), [
            'email' => 'alguien@supermercado.test',
            'password' => 'la-que-no-es',
        ])->assertSessionHasErrors([
            'email' => 'Estas credenciales no coinciden con nuestros registros.',
        ]);
    }
}
