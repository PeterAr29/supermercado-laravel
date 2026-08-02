<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * H-03 — Ninguna migración creaba 'stock', pero OrdenCompraController::recibir()
 * la escribía. En una base recién migrada el flujo de reposición fallaba con
 * "Column not found".
 *
 * En la base de desarrollo la columna se había añadido a mano, así que el
 * esquema real y las migraciones habían derivado. La comprobación hasColumn()
 * permite que esta migración funcione en ambos casos y deja las dos alineadas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('productos', 'stock')) {
            return;
        }

        Schema::table('productos', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('precio');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
