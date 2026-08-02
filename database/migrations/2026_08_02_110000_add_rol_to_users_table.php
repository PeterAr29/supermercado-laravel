<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-14 — Rol del usuario.
 *
 * Por defecto 'cliente': quien se registra en la tienda no gestiona nada.
 * El administrador se marca a mano o desde UserSeeder.
 *
 * Los usuarios que ya existen se quedan como clientes salvo el correo de
 * trabajo del seeder: hasta hoy todos ellos eran administradores de facto,
 * y ese es justamente el agujero que cierra esta migración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol', 20)->default('cliente')->after('email');
        });

        DB::table('users')
            ->where('email', 'admin@supermercado.test')
            ->update(['rol' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rol');
        });
    }
};
