<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            // El RUC es único y de exactamente 11 dígitos (H-46). numerify()
            // sobre un patrón fijo lo garantiza; unique() evita el choque con
            // el índice cuando una prueba crea varios proveedores.
            'ruc' => $this->faker->unique()->numerify('20#########'),
            'telefono' => $this->faker->numerify('01#######'),
            'email' => $this->faker->companyEmail(),
            'direccion' => $this->faker->address(),
            'contacto_nombre' => $this->faker->name(),
            'contacto_telefono' => $this->faker->numerify('9########'),
            'categoria' => $this->faker->randomElement(['Abarrotes', 'Lácteos', 'Verduras', 'Hogar']),
            'activo' => true,
        ];
    }

    /** Dado de baja: la ficha de producto no lo lista entre quienes lo surten. */
    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
