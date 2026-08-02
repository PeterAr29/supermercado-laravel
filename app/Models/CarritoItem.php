<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarritoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrito_id', 'producto_id', 'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    /**
     * Lo que cuesta esta línea.
     *
     * Existe para que las vistas no multipliquen: `$item->subtotal` en vez de
     * `$item->cantidad * $item->producto->precio` repetido en cada tabla. La
     * suma de todas ellas la hace CarritoService, que es quien tiene la única
     * fórmula del total (H-13).
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->cantidad * $this->producto->precio);
    }

    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
