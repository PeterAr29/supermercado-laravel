<?php

namespace App\Services;

use App\Enums\TipoMovimiento;
use App\Exceptions\StockInsuficienteException;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * H-35 — El único sitio del proyecto que modifica `productos.stock`.
 *
 * Antes lo tocaba directamente `OrdenCompraController::recibir()` con un
 * increment(), y la venta no lo tocaba en absoluto: el inventario solo subía.
 * Concentrarlo aquí es lo que hace posible el kardex, porque no hay forma de
 * mover stock sin dejar la línea que lo explica.
 *
 * Todo pasa por `mover()`, que:
 *  1. bloquea la fila del producto (dos cajas cobrando a la vez leerían el
 *     mismo stock y venderían las mismas unidades dos veces);
 *  2. calcula el stock resultante;
 *  3. escribe el producto y el movimiento en la misma transacción.
 *
 * La Fase 4 (H-13) construirá CheckoutService y OrdenCompraService encima de
 * este servicio; se adelanta aquí porque H-35 lo necesita en dos flujos y
 * escribirlo dos veces para unificarlo después sería trabajo tirado.
 */
class InventarioService
{
    /** Reposición: llega mercancía del proveedor. */
    public function entrada(Producto $producto, int $cantidad, string $motivo, ?Model $origen = null, ?User $usuario = null): MovimientoInventario
    {
        $this->exigirPositiva($cantidad);

        return $this->mover($producto, $cantidad, TipoMovimiento::Entrada, $motivo, $origen, $usuario);
    }

    /**
     * Venta: sale mercancía.
     *
     * @throws StockInsuficienteException si no hay bastante
     */
    public function salida(Producto $producto, int $cantidad, string $motivo, ?Model $origen = null, ?User $usuario = null): MovimientoInventario
    {
        $this->exigirPositiva($cantidad);

        return $this->mover($producto, -$cantidad, TipoMovimiento::Salida, $motivo, $origen, $usuario);
    }

    /**
     * Corrección manual: se cuenta la estantería y se pone el stock real.
     *
     * Recibe el stock que debe quedar, no la diferencia: quien hace un
     * inventario físico cuenta unidades, no calcula deltas. El motivo es
     * obligatorio porque un ajuste sin explicación es un descuadre con permiso.
     */
    public function ajustar(Producto $producto, int $stockReal, string $motivo, ?User $usuario = null): MovimientoInventario
    {
        if ($stockReal < 0) {
            throw new InvalidArgumentException('El stock real no puede ser negativo.');
        }

        if (trim($motivo) === '') {
            throw new InvalidArgumentException('Un ajuste de inventario necesita un motivo.');
        }

        return DB::transaction(function () use ($producto, $stockReal, $motivo, $usuario) {
            $bloqueado = $this->bloquear($producto);
            $diferencia = $stockReal - $bloqueado->stock;

            if ($diferencia === 0) {
                throw new InvalidArgumentException('El stock indicado es el que ya está registrado.');
            }

            return $this->mover($producto, $diferencia, TipoMovimiento::Ajuste, $motivo, null, $usuario);
        });
    }

    /** Stock actual reconstruido desde el kardex, para cuadrarlo con el declarado. */
    public function stockSegunKardex(Producto $producto): int
    {
        return (int) MovimientoInventario::where('producto_id', $producto->id)->sum('cantidad');
    }

    /**
     * Abre el kardex de un producto que ya tenía existencias.
     *
     * El kardex nace hoy; el stock lleva meses en la tabla. Sin esta línea de
     * apertura, todo producto anterior a la Fase 3 arrancaría con un descuadre
     * igual a su stock, y el criterio "entradas − salidas ± ajustes = stock"
     * sería imposible de cumplir por construcción.
     *
     * No mueve stock: solo hace que el historial explique el que ya hay. Es
     * idempotente — si el kardex ya cuadra, no escribe nada.
     */
    public function conciliar(Producto $producto, string $motivo = 'Saldo inicial de inventario'): ?MovimientoInventario
    {
        $diferencia = $producto->stock - $this->stockSegunKardex($producto);

        if ($diferencia === 0) {
            return null;
        }

        return MovimientoInventario::create([
            'producto_id' => $producto->id,
            'tipo' => TipoMovimiento::Ajuste,
            'cantidad' => $diferencia,
            'stock_resultante' => $producto->stock,
            'motivo' => $motivo,
            'user_id' => Auth::id(),
        ]);
    }

    private function mover(Producto $producto, int $cantidad, TipoMovimiento $tipo, string $motivo, ?Model $origen, ?User $usuario): MovimientoInventario
    {
        return DB::transaction(function () use ($producto, $cantidad, $tipo, $motivo, $origen, $usuario) {

            $bloqueado = $this->bloquear($producto);
            $resultante = $bloqueado->stock + $cantidad;

            if ($resultante < 0) {
                throw new StockInsuficienteException($bloqueado, abs($cantidad), $bloqueado->stock);
            }

            $bloqueado->stock = $resultante;
            $bloqueado->save();

            // La instancia que trajo quien llama queda al día sin tener que
            // acordarse de refrescarla.
            $producto->stock = $resultante;

            return MovimientoInventario::create([
                'producto_id' => $bloqueado->id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_resultante' => $resultante,
                'motivo' => $motivo,
                'origen_type' => $origen ? $origen->getMorphClass() : null,
                'origen_id' => $origen?->getKey(),
                'user_id' => $usuario?->id ?? Auth::id(),
            ]);
        });
    }

    /**
     * Relee el producto con la fila bloqueada hasta el fin de la transacción.
     *
     * withTrashed() porque una orden pendiente puede recibirse después de que
     * el producto se retire del catálogo: la mercancía llega igual (H-32).
     */
    private function bloquear(Producto $producto): Producto
    {
        return Producto::withTrashed()->lockForUpdate()->findOrFail($producto->id);
    }

    private function exigirPositiva(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad de un movimiento debe ser mayor que cero.');
        }
    }
}
