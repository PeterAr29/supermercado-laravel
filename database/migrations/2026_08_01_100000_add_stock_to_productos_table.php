<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * H-03 — La columna 'stock' nunca se creó, pero OrdenCompraController::recibir()
 * la escribía. El flujo de reposición de inventario fallaba con "Column not found".
 */
return new class extends Migration
{
    public function up(): void
    {
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
