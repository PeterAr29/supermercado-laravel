<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * H-35 — Kardex: de dónde viene y a dónde va cada unidad.
 *
 * Decisiones:
 *
 *  - 'cantidad' es un entero CON SIGNO (entrada positiva, salida negativa,
 *    ajuste cualquiera de los dos). Así el kardex cuadra con una suma y no
 *    con un condicional por tipo, que es donde se cuelan los errores.
 *  - 'stock_resultante' guarda el stock que quedó tras el movimiento. Es
 *    redundante a propósito: sin él, un descuadre no se puede situar en el
 *    tiempo, solo constatar.
 *  - 'origen' es polimórfico: una entrada viene de una OrdenCompra y una
 *    salida de una Venta. Nullable porque un ajuste manual no tiene documento.
 *  - producto_id es RESTRICT: el kardex es historial, y borrar un producto no
 *    puede llevárselo por delante (misma regla que las líneas de venta, H-02).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();

            $table->string('tipo', 20);
            $table->integer('cantidad');
            $table->integer('stock_resultante');
            $table->string('motivo');

            $table->nullableMorphs('origen');

            // Quién lo provocó. Nullable: una venta de invitado no tiene
            // usuario, y borrar una cuenta no debe borrar el historial.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // El kardex siempre se consulta por producto y en orden temporal.
            $table->index(['producto_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
