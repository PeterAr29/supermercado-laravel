<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * H-02 — Un producto referenciado por ventas históricas no debe desaparecer.
 * Con SoftDeletes se retira del catálogo sin romper los documentos ya emitidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
