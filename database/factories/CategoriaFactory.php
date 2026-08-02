<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return [
            // unique() porque una prueba que crea dos categorías con el mismo
            // nombre no está probando lo que cree estar probando.
            'nombre' => $this->faker->unique()->randomElement([
                'Abarrotes', 'Lácteos', 'Verduras', 'Frutas', 'Carnes',
                'Hogar', 'Snacks', 'Licores', 'Panadería', 'Limpieza',
            ]),
        ];
    }
}
