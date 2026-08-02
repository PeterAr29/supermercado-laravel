<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Quién entra al panel y quién no (H-24).
 *
 * Las dos preguntas que costaron la Fase 1 y la Fase 3: un anónimo podía
 * borrar el catálogo entero (H-01) y, una vez cerrado eso, cualquiera que se
 * registrara entraba con acceso total de administrador (H-14).
 */
class AccesoPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_puede_borrar_productos(): void
    {
        $producto = Producto::factory()->create();

        $this->delete(route('admin.productos.destroy', $producto))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'deleted_at' => null,
        ]);
    }

    public function test_un_cliente_no_puede_entrar_en_admin(): void
    {
        $cliente = User::factory()->cliente()->create();

        foreach ([
            route('admin.dashboard'),
            route('admin.productos.index'),
            route('admin.proveedores.index'),
            route('admin.ordenes.index'),
            route('admin.inventario.index'),
        ] as $url) {
            $this->actingAs($cliente)->get($url)->assertForbidden();
        }
    }

    public function test_un_cliente_no_puede_borrar_productos(): void
    {
        $cliente = User::factory()->cliente()->create();
        $producto = Producto::factory()->create();

        $this->actingAs($cliente)
            ->delete(route('admin.productos.destroy', $producto))
            ->assertForbidden();

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'deleted_at' => null,
        ]);
    }

    public function test_el_administrador_ve_el_panel(): void
    {
        $admin = User::factory()->admin()->create();

        // Comprueba el contenido, no el 200: el perfil respondía 200 pintando
        // una página en blanco y el test lo daba por bueno (H-47).
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Panel');
    }

    public function test_registrarse_en_la_tienda_crea_siempre_un_cliente(): void
    {
        // H-14 — 'rol' está fuera de $fillable justamente para que un
        // 'rol=admin' colado en el formulario no sirva de nada.
        $this->post(route('register'), [
            'name' => 'Nuevo cliente',
            'email' => 'nuevo@supermercado.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'rol' => 'admin',
        ]);

        $usuario = User::where('email', 'nuevo@supermercado.test')->firstOrFail();

        $this->assertSame(RolUsuario::Cliente, $usuario->rol);
        $this->assertFalse($usuario->esAdmin());
    }
}
