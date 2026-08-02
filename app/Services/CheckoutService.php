<?php

namespace App\Services;

use App\Exceptions\CarritoVacioException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Carrito;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

/**
 * Cobrar el carrito (H-13).
 *
 * Es la operación más delicada del proyecto: crea la venta, escribe sus
 * líneas, saca la mercancía del inventario y vacía el carrito. O hace las
 * cuatro cosas, o no hace ninguna.
 *
 * Hasta la Fase 3 no tocaba el stock en absoluto (H-35). Ahora se apoya en
 * InventarioService, que bloquea la fila del producto dentro de esta misma
 * transacción: entre comprobar el stock y descontarlo no cabe otra venta.
 */
class CheckoutService
{
    public function __construct(
        private readonly CarritoService $carritos,
        private readonly InventarioService $inventario,
    ) {}

    /**
     * @throws CarritoVacioException si no queda ninguna línea vigente
     * @throws StockInsuficienteException si falta stock de cualquier línea
     */
    public function cobrar(Carrito $carrito, ?User $comprador = null): Venta
    {
        $items = $this->carritos->itemsVigentes($carrito);

        if ($items->isEmpty()) {
            throw new CarritoVacioException;
        }

        return DB::transaction(function () use ($carrito, $items, $comprador) {

            $venta = Venta::create([
                // Nullable: la tienda permite comprar como invitado (H-10)
                'user_id' => $comprador?->id,
                'total' => $this->carritos->total($items),
            ]);

            foreach ($items as $item) {
                $venta->items()->create([
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    // El precio se congela en la línea: si mañana sube, la
                    // venta de hoy sigue diciendo lo que se cobró.
                    'precio' => $item->producto->precio,
                ]);

                $this->inventario->salida(
                    $item->producto,
                    $item->cantidad,
                    "Venta #{$venta->id}",
                    $venta,
                    $comprador
                );
            }

            $this->carritos->vaciar($carrito);

            return $venta;
        });
    }
}
