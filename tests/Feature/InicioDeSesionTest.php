<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dónde aterriza cada rol al iniciar sesión (H-48).
 *
 * Los dos caían en `/dashboard`, el marcador de posición de Breeze: «Dashboard»
 * y «You're logged in!», en inglés y sin nada detrás. H-47 arregló que esa
 * pantalla *pintara* —antes salía en blanco—, pero nunca se escribió qué debía
 * decir, porque no era de nadie.
 */
class InicioDeSesionTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_administrador_aterriza_en_el_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_el_cliente_aterriza_en_la_tienda(): void
    {
        $cliente = User::factory()->cliente()->create();

        $this->post(route('login'), [
            'email' => $cliente->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }

    public function test_quien_se_registra_acaba_en_la_tienda(): void
    {
        $this->post(route('register'), [
            'name' => 'Nueva clienta',
            'email' => 'nueva@supermercado.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('home'));
    }

    public function test_el_dashboard_de_la_plantilla_ya_no_existe(): void
    {
        $cliente = User::factory()->cliente()->create();

        $this->actingAs($cliente)
            ->get('/dashboard')
            ->assertNotFound();
    }

    public function test_quien_ya_tiene_sesion_no_vuelve_al_formulario_de_acceso(): void
    {
        // El middleware 'guest' lo devuelve a su sitio, que ya no es el mismo
        // para los dos roles.
        $admin = User::factory()->admin()->create();
        $cliente = User::factory()->cliente()->create();

        $this->actingAs($admin)->get(route('login'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($cliente)->get(route('login'))->assertRedirect(route('home'));
    }

    public function test_la_ficha_no_pide_datos_que_nadie_recoge(): void
    {
        // H-50 — El textarea quedaba fuera del <form>: el cliente escribía su
        // indicación y se evaporaba sin avisar.
        $producto = Producto::factory()->conStock(5)->create();

        $respuesta = $this->get(route('productos.show', $producto));

        $respuesta->assertOk()
            ->assertSee($producto->nombre)
            ->assertDontSee('¿Qué debemos considerar')
            ->assertDontSee('<textarea', escape: false);
    }
}
