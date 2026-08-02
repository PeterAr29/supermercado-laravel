<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    // Sin esto Eloquent pluraliza 'Proveedor' como 'proveedors' (H-29)
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'ruc',
        'telefono',
        'email',
        'direccion',
        'contacto_nombre',
        'contacto_telefono',
        'categoria',
        'activo',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'proveedor_producto')
            ->withPivot('stock_proveedor', 'precio_compra');
    }

    public function ordenes()
    {
        return $this->hasMany(OrdenCompra::class);
    }
}
