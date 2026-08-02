<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->decimal('total', 10, 2);
            $table->string('estado')->default('pendiente'); // pendiente, enviado, recibido
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ordenes_compra');
    }
};
