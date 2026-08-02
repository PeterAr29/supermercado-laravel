<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * H-04 — El precio al que un proveedor vende un producto no existía como columna,
 * pero tres capas distintas creían que sí, con tres nombres diferentes:
 *   ProveedorProductoController -> attach('precio_compra')  (SQL error)
 *   OrdenCompraController       -> pivot->precio            (null)
 *   ordenes/create.blade.php    -> pivot.precio_compra      (undefined)
 *
 * Se adopta 'precio_compra' como nombre único en toda la pila.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedor_producto', function (Blueprint $table) {
            $table->decimal('precio_compra', 10, 2)->default(0)->after('producto_id');
        });
    }

    public function down(): void
    {
        Schema::table('proveedor_producto', function (Blueprint $table) {
            $table->dropColumn('precio_compra');
        });
    }
};
