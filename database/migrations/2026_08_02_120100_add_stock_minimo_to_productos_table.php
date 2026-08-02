<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock mínimo por producto.
 *
 * El panel de administración avisa de lo que hay que reponer, y para eso hace
 * falta un umbral. Se guarda por producto porque no es lo mismo quedarse a 5
 * unidades de arroz que a 5 de un producto que se vende una vez al mes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedInteger('stock_minimo')->default(5)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('stock_minimo');
        });
    }
};
