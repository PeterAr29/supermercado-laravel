<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * El carrito: resolverlo, llenarlo, vaciarlo y —sobre todo— sumarlo (H-13).
 *
 * El total se calculaba en TRES sitios con tres fórmulas escritas a mano: el
 * controlador para la pantalla de pago, el controlador otra vez al cobrar, y
 * un `@php $total += ...` dentro de la tabla de `carrito/index.blade.php`.
 * Tres oportunidades de que el precio que se enseña no sea el que se cobra.
 * Aquí hay una sola.
 *
 * No conoce la sesión: recibe el usuario o el id del carrito de invitado y
 * devuelve el modelo. Quién guarda ese id en la sesión es asunto del
 * controlador, que es el único que habla HTTP.
 */
class CarritoService
{
    /**
     * Carrito persistente del usuario, que sobrevive al cierre de sesión (H-11).
     *
     * Con el índice único de H-39 detrás, firstOrCreate() es seguro: si dos
     * peticiones simultáneas intentan crearlo a la vez, la base rechaza la
     * segunda y Eloquent vuelve a leer la fila que ganó.
     */
    public function paraUsuario(User $usuario): Carrito
    {
        return $usuario->carrito()->firstOrCreate([]);
    }

    /** Carrito de invitado, identificado por la sesión. Lo crea si hace falta. */
    public function paraInvitado(?int $carritoId): Carrito
    {
        if ($carritoId) {
            $carrito = Carrito::whereNull('user_id')->find($carritoId);

            if ($carrito) {
                return $carrito;
            }
        }

        return Carrito::create();
    }

    /**
     * Líneas cuyo producto sigue en el catálogo.
     *
     * Un producto retirado (SoftDeletes, H-02) deja de resolverse desde su
     * línea: sin este filtro el carrito calcularía su subtotal como 0 y
     * permitiría pagar 0 por algo que ya no se vende (H-32).
     */
    public function itemsVigentes(Carrito $carrito): Collection
    {
        return $carrito->items()->whereHas('producto')->with('producto')->get();
    }

    /** La única fórmula del total en todo el proyecto. */
    public function total(Collection $items): float
    {
        return (float) $items->sum(fn (CarritoItem $item) => $item->subtotal);
    }

    /** Líneas que piden más unidades de las que quedan. */
    public function lineasSinStock(Collection $items): Collection
    {
        return $items->filter(fn (CarritoItem $item) => ! $item->producto->hayStock($item->cantidad));
    }

    /**
     * Añade unidades, sumando si el producto ya estaba dentro.
     *
     * Avisar aquí es cortesía, no garantía: entre añadir y pagar, otro cliente
     * puede haberse llevado la última unidad. La comprobación que manda es la
     * de InventarioService al cobrar, con la fila bloqueada.
     *
     * @throws StockInsuficienteException
     */
    public function agregar(Carrito $carrito, Producto $producto, int $cantidad = 1): CarritoItem
    {
        $item = $carrito->items()->where('producto_id', $producto->id)->first();
        $solicitado = $cantidad + ($item->cantidad ?? 0);

        if (! $producto->hayStock($solicitado)) {
            throw new StockInsuficienteException($producto, $solicitado, $producto->stock);
        }

        if ($item) {
            $item->increment('cantidad', $cantidad);

            return $item->refresh();
        }

        return $carrito->items()->create([
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
        ]);
    }

    /**
     * Borra una línea del carrito indicado.
     *
     * El `where` por carrito no es decorativo: antes bastaba con conocer el id
     * de la línea para vaciarle el carrito a cualquiera (H-36).
     */
    public function eliminarLinea(Carrito $carrito, int $itemId): void
    {
        CarritoItem::where('carrito_id', $carrito->id)->findOrFail($itemId)->delete();
    }

    public function vaciar(Carrito $carrito): void
    {
        $carrito->items()->delete();
    }
}
