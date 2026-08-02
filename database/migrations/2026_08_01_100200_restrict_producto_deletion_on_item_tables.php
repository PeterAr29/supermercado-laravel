<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * H-02 — 'order_items.producto_id' estaba declarada con onDelete('cascade'):
 * borrar un producto borraba sus líneas en ventas ya cerradas y descuadraba
 * los totales. Los datos históricos nunca se borran en cascada.
 *
 * 'orden_compra_items.producto_id' ya era RESTRICT por defecto; se declara
 * de forma explícita para que ambas tablas hermanas expresen el mismo criterio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });

        Schema::table('orden_compra_items', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
        });

        Schema::table('orden_compra_items', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }
};
