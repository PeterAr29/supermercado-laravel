<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraItem extends Model
{
    protected $fillable = [
        'orden_id',
        'producto_id',
        'cantidad',
        'precio',
        'subtotal'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
