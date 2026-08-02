<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Venta al cliente.
 *
 * Antes se llamaba 'Order', nombre casi idéntico a 'OrdenCompra' (la compra
 * al proveedor) pese a ser el concepto opuesto (H-09).
 */
class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'user_id',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(VentaItem::class);
    }

    /** Nullable: la tienda permite comprar como invitado (H-10). */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
