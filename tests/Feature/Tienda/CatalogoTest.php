<?php

namespace Tests\Feature\Tienda;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El catálogo público y su paginación (H-22).
 *
 * `index` traía el catálogo entero con `->get()`. Con miles de SKU la página
 * se caía: el log ya registraba dos "Allowed memory size exhausted".
 */
class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_catalogo_pagina_y_pinta_los_enlaces(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(15)->create(['categoria_id' => $categoria->id]);

        $respuesta = $this->get(route('productos.index'));

        $respuesta->assertOk();
        $respuesta->assertViewHas('productos', fn ($productos) => $productos->count() === 12
            && $productos->total() === 15
        );

        // El enlace tiene que estar en el HTML: sin `->links()` en la vista la
        // paginación existe pero no hay forma de llegar a la página 2, y la
        // pantalla responde 200 igualmente (H-47).
        $respuesta->assertSee(route('productos.index').'?page=2', escape: false);
    }

    public function test_la_segunda_pagina_trae_el_resto_del_catalogo(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(15)->create(['categoria_id' => $categoria->id]);

        $ordenados = Producto::orderBy('nombre')->pluck('nombre');

        $this->get(route('productos.index', ['page' => 2]))
            ->assertOk()
            ->assertSee($ordenados->last())
            ->assertDontSee($ordenados->first());
    }

    public function test_el_filtro_de_categoria_sobrevive_al_cambio_de_pagina(): void
    {
        // withQueryString(): sin él, pasar de página tira el filtro y el
        // cliente vuelve al catálogo entero sin saber por qué.
        $abarrotes = Categoria::factory()->create(['nombre' => 'Abarrotes']);
        Producto::factory()->count(14)->create(['categoria_id' => $abarrotes->id]);
        Producto::factory()->count(5)->create();

        $this->get(route('productos.index', ['categoria' => $abarrotes->id]))
            ->assertOk()
            ->assertSee('categoria='.$abarrotes->id.'&amp;page=2', escape: false);
    }

    public function test_el_listado_por_categoria_tambien_pagina(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(20)->create(['categoria_id' => $categoria->id]);

        $this->get(route('productos.categoria', $categoria))
            ->assertOk()
            ->assertViewHas('productos', fn ($productos) => $productos->count() === 12);
    }
}
