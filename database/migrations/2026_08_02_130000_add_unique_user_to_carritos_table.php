<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-39 — Un usuario, un carrito.
 *
 * `User::carrito()` es un hasOne y `obtenerCarrito()` hace firstOrCreate(),
 * que no es atómico: dos peticiones simultáneas del mismo usuario (dos
 * pestañas, un doble clic) podían crear dos filas. A partir de ahí hasOne
 * devolvía siempre una, y la otra quedaba huérfana con líneas dentro: al
 * usuario le desaparecían productos que sí había añadido.
 *
 * La Fase 2 puso índices únicos en el pivot y en las líneas de carrito, pero
 * dejó fuera este tercer sitio.
 *
 * Además faltaba la clave foránea: borrar un usuario dejaba su carrito
 * apuntando a un id inexistente.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Fusionar los carritos duplicados que ya existan.
        //    Se conserva el de menor id y se le llevan las líneas de los demás.
        $duplicados = DB::table('carritos')
            ->select('user_id', DB::raw('MIN(id) as principal'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $grupo) {
            $sobrantes = DB::table('carritos')
                ->where('user_id', $grupo->user_id)
                ->where('id', '!=', $grupo->principal)
                ->pluck('id');

            // Las líneas que ya estén en el carrito principal no se pueden
            // mover: el índice único de H-25 las rechazaría. Se descartan.
            DB::table('carrito_items')
                ->whereIn('carrito_id', $sobrantes)
                ->whereNotExists(function ($q) use ($grupo) {
                    $q->select(DB::raw(1))
                        ->from('carrito_items as existentes')
                        ->whereColumn('existentes.producto_id', 'carrito_items.producto_id')
                        ->where('existentes.carrito_id', $grupo->principal);
                })
                ->update(['carrito_id' => $grupo->principal]);

            DB::table('carrito_items')->whereIn('carrito_id', $sobrantes)->delete();
            DB::table('carritos')->whereIn('id', $sobrantes)->delete();
        }

        // 2. Carritos que apuntan a un usuario que ya no existe: sin esto la
        //    clave foránea no se puede crear.
        DB::table('carritos')
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->delete();

        Schema::table('carritos', function (Blueprint $table) {
            $table->unique('user_id', 'carrito_usuario_unico');

            // cascadeOnDelete: el carrito es un dato accesorio, no histórico.
            // Al borrar la cuenta se va con ella; las ventas, no (H-02).
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('carrito_usuario_unico');
        });
    }
};
