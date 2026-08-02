<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = "ordenes_compra";

    protected $fillable = [
        'proveedor_id',
        'total',
        'estado'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function items()
    {
        return $this->hasMany(OrdenCompraItem::class, 'orden_id');
    }
}
