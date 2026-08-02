<?php

namespace App\Models;

use App\Enums\TipoMovimiento;
use Illuminate\Database\Eloquent\Model;

/**
 * Una línea del kardex (H-35).
 *
 * Los movimientos no se editan ni se borran: si algo se contabilizó mal, se
 * corrige con un ajuste, que deja su propia línea. Por eso no hay update() ni
 * delete() en el servicio que los escribe.
 */
class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'stock_resultante',
        'motivo',
        'origen_type',
        'origen_id',
        'user_id',
    ];

    protected $casts = [
        'tipo' => TipoMovimiento::class,
        'cantidad' => 'integer',
        'stock_resultante' => 'integer',
    ];

    /**
     * El producto, incluso retirado del catálogo.
     *
     * withTrashed() por la misma razón que VentaItem::producto(): esto es un
     * documento histórico y debe resolver siempre (H-32).
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }

    /** Venta, OrdenCompra o nada, si fue un ajuste manual. */
    public function origen()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quién firma el movimiento, para la columna «Quién» del kardex (H-51).
     *
     * Sin `user_id` hay **dos** casos distintos, y tratarlos igual hacía que
     * los asientos de apertura —que escribe un seeder por consola— salieran
     * firmados por «Invitado», que en un libro de inventario se lee como una
     * persona que entró en la tienda y movió stock.
     *
     * Con una venta detrás sí hubo alguien: un comprador sin cuenta, que la
     * tienda permite (H-10). Sin documento ninguno no lo hizo nadie a mano.
     */
    public function autor(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->origen instanceof Venta ? 'Invitado' : 'Sistema';
    }

    public function esEntrada(): bool
    {
        return $this->tipo === TipoMovimiento::Entrada;
    }

    public function esSalida(): bool
    {
        return $this->tipo === TipoMovimiento::Salida;
    }

    public function esAjuste(): bool
    {
        return $this->tipo === TipoMovimiento::Ajuste;
    }
}
