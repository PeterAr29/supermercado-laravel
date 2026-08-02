<?php

namespace App\Services;

use App\Enums\EstadoOrdenCompra;
use App\Exceptions\ProductoNoAsignadoException;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Abastecimiento: pedir mercancía a un proveedor y darla por recibida (H-13).
 *
 * El `store` del controlador tenía 62 líneas y hacía de todo: recorrer dos
 * arrays paralelos del formulario, buscar precios en el pivot, sumar el total
 * y validar. Aquí queda solo la parte que es negocio.
 */
class OrdenCompraService
{
    public function __construct(private readonly InventarioService $inventario) {}

    /**
     * Crea la orden con sus líneas y su total.
     *
     * @param  array<int, int>  $lineas  producto_id => cantidad, ya sin ceros
     *
     * @throws ProductoNoAsignadoException
     */
    public function crear(Proveedor $proveedor, array $lineas): OrdenCompra
    {
        // Una sola consulta al pivot en vez de una por línea, y de paso deja
        // ver de golpe si falta alguna asignación.
        $precios = $proveedor->productos()
            ->whereIn('productos.id', array_keys($lineas))
            ->pluck('proveedor_producto.precio_compra', 'productos.id');

        if ($precios->count() !== count($lineas)) {
            throw new ProductoNoAsignadoException($proveedor);
        }

        return DB::transaction(function () use ($proveedor, $lineas, $precios) {

            $orden = OrdenCompra::create([
                'proveedor_id' => $proveedor->id,
                'estado' => EstadoOrdenCompra::Pendiente,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($lineas as $productoId => $cantidad) {
                $precio = (float) $precios[$productoId];
                $subtotal = $precio * $cantidad;
                $total += $subtotal;

                $orden->items()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $subtotal,
                ]);
            }

            $orden->update(['total' => $total]);

            return $orden;
        });
    }

    /**
     * Da la orden por recibida: la mercancía entra al inventario.
     *
     * Devuelve false si ya estaba recibida. Antes esa comprobación vivía en el
     * controlador y el estado se comparaba como string, lo que permitió
     * recibir una orden dos veces y duplicar el stock (H-37).
     */
    public function recibir(OrdenCompra $orden, ?User $usuario = null): bool
    {
        if (! $orden->estaPendiente()) {
            return false;
        }

        DB::transaction(function () use ($orden, $usuario) {

            $motivo = "Recepción de la orden de compra #{$orden->id} ({$orden->proveedor->nombre})";

            foreach ($orden->items as $item) {
                $this->inventario->entrada($item->producto, $item->cantidad, $motivo, $orden, $usuario);
            }

            $orden->update(['estado' => EstadoOrdenCompra::Recibido]);
        });

        return true;
    }
}
