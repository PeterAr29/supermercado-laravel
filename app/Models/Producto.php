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
        'stock_minimo',
        'unidad_medida',
        'imagen',
        'descripcion',
        'categoria_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'unidad_medida' => UnidadMedida::class,
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /** Kardex del producto: toda entrada, salida y ajuste (H-35). */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class)->latest('id');
    }

    public function bajoMinimo(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    public function hayStock(int $cantidad = 1): bool
    {
        return $this->stock >= $cantidad;
    }

    /**
     * Productos que toca reponer, para el panel.
     *
     * No se llama 'scopeBajoMinimo' porque Eloquent resolvería
     * `Producto::bajoMinimo()` contra el método de instancia de arriba y
     * fallaría con "cannot be called statically".
     */
    public function scopeNecesitaReposicion($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo');
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
