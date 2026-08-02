<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    // Explícito por convención: Eloquent pluraliza en inglés y aquí acierta de
    // casualidad. Dejarlo al automatismo costó que el CRUD de proveedores no
    // funcionara nunca (H-29).
    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * ¿Se puede retirar del catálogo?
     *
     * No, mientras tenga productos: `productos.categoria_id` es RESTRICT, así
     * que la base lo rechazaría con un 500 en vez de con un mensaje.
     */
    public function sePuedeBorrar(): bool
    {
        return ! $this->productos()->exists();
    }
}
