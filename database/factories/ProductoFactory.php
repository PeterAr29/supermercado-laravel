<?php

namespace Database\Factories;

use App\Enums\UnidadMedida;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => ucfirst($this->faker->unique()->words(3, true)),
            'precio' => $this->faker->randomFloat(2, 1, 200),
            'stock' => $this->faker->numberBetween(10, 200),
            'stock_minimo' => 5,
            'unidad_medida' => $this->faker->randomElement(UnidadMedida::cases()),
            'imagen' => $this->faker->imageUrl(300, 300, 'food'),
            'descripcion' => $this->faker->sentence(),
            'categoria_id' => Categoria::factory(),
        ];
    }

    /** Agotado: el catálogo lo muestra, pero no se puede añadir al carrito. */
    public function sinStock(): static
    {
        return $this->state(['stock' => 0]);
    }

    /** Con las existencias justas para probar el límite del checkout. */
    public function conStock(int $unidades): static
    {
        return $this->state(['stock' => $unidades]);
    }

    /** Por debajo del mínimo: es lo que el panel avisa que hay que reponer. */
    public function bajoMinimo(): static
    {
        return $this->state(['stock' => 2, 'stock_minimo' => 10]);
    }

    /** Retirado del catálogo (SoftDeletes, H-02). */
    public function retirado(): static
    {
        return $this->state(['deleted_at' => now()]);
    }
}
