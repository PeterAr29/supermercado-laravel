<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-09 — 'Order' (venta al cliente) y 'OrdenCompra' (compra al proveedor) son
 * conceptos OPUESTOS con nombres casi idénticos en idiomas distintos. Era la
 * mayor fuente de confusión al leer el código.
 *
 * La venta pasa a llamarse 'Venta' y todo el dominio queda en español.
 *
 * El renombrado de columna va en SQL directo: renameColumn() necesita
 * doctrine/dbal, que no está instalado y no merece añadirse por esto.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Las claves foráneas se sueltan antes de renombrar y se rehacen después
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['producto_id']);
        });

        Schema::rename('orders', 'ventas');
        Schema::rename('order_items', 'venta_items');

        DB::statement('ALTER TABLE `venta_items` CHANGE `order_id` `venta_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('venta_items', function (Blueprint $table) {
            // La venta y sus líneas son una unidad: si se borra la venta, se van con ella
            $table->foreign('venta_id')->references('id')->on('ventas')->cascadeOnDelete();
            // El producto es histórico: nunca se borra en cascada (H-02)
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            $table->dropForeign(['venta_id']);
            $table->dropForeign(['producto_id']);
        });

        Schema::rename('ventas', 'orders');
        Schema::rename('venta_items', 'order_items');

        DB::statement('ALTER TABLE `order_items` CHANGE `venta_id` `order_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });
    }
};
