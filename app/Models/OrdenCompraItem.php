<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraItem extends Model
{
    // La tabla 'orden_compra_items' se creó sin created_at/updated_at (H-30)
    public $timestamps = false;

    protected $fillable = [
        'orden_id',
        'producto_id',
        'cantidad',
        'precio',
        'subtotal',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
