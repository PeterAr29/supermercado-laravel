<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * H-10 — La tabla de ventas solo guardaba 'total' y timestamps: no había forma
 * de saber quién compró. Sin esto no existe "Mis pedidos" ni panel de cliente.
 *
 * Nullable a propósito: la tienda permite comprar como invitado. Y nullOnDelete
 * para que dar de baja a un usuario no borre el historial de ventas (H-02).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
