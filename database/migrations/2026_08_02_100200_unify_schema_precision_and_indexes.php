<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-25 — Inconsistencias de esquema acumuladas:
 *
 *  - 'productos.precio' era decimal(8,2) mientras las líneas de venta y de
 *    orden usaban decimal(10,2). El mismo importe se truncaba distinto según
 *    la tabla.
 *  - Ninguna relación N:N tenía índice único, así que se podía asignar dos
 *    veces el mismo producto al mismo proveedor, o duplicar una línea de
 *    carrito y contarla dos veces en el total.
 *  - El pivot proveedor-producto no tenía timestamps.
 *
 * MODIFY en SQL directo porque change() necesita doctrine/dbal.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Precisión uniforme para todos los importes
        DB::statement('ALTER TABLE `productos` MODIFY `precio` DECIMAL(10,2) NOT NULL');

        // 2. Deduplicar antes de imponer los índices únicos.
        //    Conserva la fila de menor id de cada par repetido.
        DB::statement('
            DELETE pp FROM `proveedor_producto` pp
            INNER JOIN `proveedor_producto` dup
                ON pp.proveedor_id = dup.proveedor_id
               AND pp.producto_id  = dup.producto_id
               AND pp.id > dup.id
        ');

        DB::statement('
            DELETE ci FROM `carrito_items` ci
            INNER JOIN `carrito_items` dup
                ON ci.carrito_id = dup.carrito_id
               AND ci.producto_id = dup.producto_id
               AND ci.id > dup.id
        ');

        Schema::table('proveedor_producto', function (Blueprint $table) {
            $table->unique(['proveedor_id', 'producto_id'], 'proveedor_producto_unico');
            $table->timestamps();
        });

        Schema::table('carrito_items', function (Blueprint $table) {
            $table->unique(['carrito_id', 'producto_id'], 'carrito_item_unico');
        });
    }

    public function down(): void
    {
        Schema::table('carrito_items', function (Blueprint $table) {
            $table->dropUnique('carrito_item_unico');
        });

        Schema::table('proveedor_producto', function (Blueprint $table) {
            $table->dropUnique('proveedor_producto_unico');
            $table->dropTimestamps();
        });

        DB::statement('ALTER TABLE `productos` MODIFY `precio` DECIMAL(8,2) NOT NULL');
    }
};
