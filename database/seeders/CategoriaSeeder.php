<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        $categorias = [
            'Abarrotes',
            'Lácteos',
            'Verduras',
            'Hogar',
            'Snacks',
            'Licores',
            'Mascotas',
            'Bebidas',
        ];

        // firstOrCreate para poder re-sembrar sin duplicar
        foreach ($categorias as $nombre) {
            Categoria::firstOrCreate(['nombre' => $nombre]);
        }

        $this->command->info('Categorías: '.Categoria::count().'.');
    }
}
