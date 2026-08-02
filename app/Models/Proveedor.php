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
            ->withPivot('stock_proveedor', 'precio_compra')
            // Sin esto las columnas que añade H-25 al pivot se quedan en NULL:
            // attach() solo las escribe si la relación las declara.
            ->withTimestamps();
    }

    public function ordenes()
    {
        return $this->hasMany(OrdenCompra::class);
    }
}
