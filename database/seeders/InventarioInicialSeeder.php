<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Database\Seeder;

/**
 * Abre el kardex de los productos que ya tenían stock (H-35).
 *
 * Va el último porque necesita el catálogo ya sembrado, y es idempotente: al
 * repetirlo no duplica nada, porque solo escribe cuando el kardex y el stock
 * no coinciden. Eso lo hace también útil como herramienta de reconciliación
 * si alguna vez se toca el stock por fuera del servicio.
 */
class InventarioInicialSeeder extends Seeder
{
    public function run(InventarioService $inventario): void
    {
        $abiertos = 0;

        Producto::query()->each(function (Producto $producto) use ($inventario, &$abiertos) {
            if ($inventario->conciliar($producto)) {
                $abiertos++;
            }
        });

        $this->command->info("Kardex abierto para {$abiertos} productos.");
    }
}
