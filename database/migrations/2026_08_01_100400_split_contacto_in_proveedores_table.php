<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-05 — El formulario enviaba 'contacto_nombre' y 'contacto_telefono' y el
 * controlador los exigía, pero la tabla solo tenía 'contacto'. Al no estar en
 * $fillable, create($request->all()) los descartaba SIN error: el proveedor se
 * guardaba, se redirigía con "creado correctamente" y los datos se perdían.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('contacto_nombre')->nullable()->after('direccion');
            $table->string('contacto_telefono')->nullable()->after('contacto_nombre');
        });

        // Conserva lo que hubiera en la columna antigua
        DB::table('proveedores')->whereNotNull('contacto')->update([
            'contacto_nombre' => DB::raw('contacto'),
        ]);

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn('contacto');
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('contacto')->nullable()->after('direccion');
        });

        DB::table('proveedores')->whereNotNull('contacto_nombre')->update([
            'contacto' => DB::raw('contacto_nombre'),
        ]);

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn(['contacto_nombre', 'contacto_telefono']);
        });
    }
};
