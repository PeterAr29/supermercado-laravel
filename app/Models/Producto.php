<?php

namespace App\Models;

use App\Enums\UnidadMedida;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'precio',
        'stock',
        'unidad_medida',
        'imagen',
        'descripcion',
        'categoria_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'integer',
        'unidad_medida' => UnidadMedida::class,
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedores()
    {
        return $this->belongsToMany(Proveedor::class, 'proveedor_producto')
            ->withPivot('stock_proveedor', 'precio_compra')
            // Sin esto las columnas que añade H-25 al pivot se quedan en NULL:
            // attach() solo las escribe si la relación las declara.
            ->withTimestamps();
    }
}
