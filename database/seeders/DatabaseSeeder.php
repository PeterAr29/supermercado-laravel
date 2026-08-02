<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Reconstruye una base de trabajo completa.
     *
     * El orden importa: los productos necesitan categorías, y el catálogo de
     * cada proveedor necesita productos.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategoriaSeeder::class,
            ProductoSeeder::class,
            ProveedorSeeder::class,
        ]);
    }
}
