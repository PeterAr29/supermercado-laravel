<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Reconstruye una base de trabajo completa.
     *
     * El orden importa: los productos necesitan categorías, el catálogo de
     * cada proveedor necesita productos, y el kardex necesita el catálogo
     * entero para poder abrir el saldo inicial de cada uno.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategoriaSeeder::class,
            ProductoSeeder::class,
            ProveedorSeeder::class,
            InventarioInicialSeeder::class,
        ]);
    }
}
