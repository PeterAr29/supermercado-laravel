<?php

namespace App\Models;

use App\Enums\EstadoOrdenCompra;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = 'ordenes_compra';

    protected $fillable = [
        'proveedor_id',
        'total',
        'estado',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'estado' => EstadoOrdenCompra::class,
    ];

    public function estaPendiente(): bool
    {
        return $this->estado === EstadoOrdenCompra::Pendiente;
    }

    public function estaRecibida(): bool
    {
        return $this->estado === EstadoOrdenCompra::Recibido;
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function items()
    {
        return $this->hasMany(OrdenCompraItem::class, 'orden_id');
    }
}
