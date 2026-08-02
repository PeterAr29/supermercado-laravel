<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaItem extends Model
{
    protected $table = 'venta_items';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * withTrashed: una venta ya emitida debe seguir resolviendo su producto
     * aunque este se haya retirado del catálogo (H-32).
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }

    /** Importe de la línea. El total de la venta es la suma de estos. */
    public function subtotal(): float
    {
        return (float) $this->precio * $this->cantidad;
    }
}
