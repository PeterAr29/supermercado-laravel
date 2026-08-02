<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(CarritoItem::class);
    }

    /** Nullable: los invitados también tienen carrito, identificado por sesión. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Unidades totales, para el contador de la barra de navegación (H-08). */
    public function totalUnidades(): int
    {
        return (int) $this->items()->whereHas('producto')->sum('cantidad');
    }
}
