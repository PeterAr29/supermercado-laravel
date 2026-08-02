<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AjustarInventarioRequest;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Inventario y kardex (H-35).
 *
 * Aquí se ve de dónde viene y a dónde va cada unidad, y se corrige el stock
 * cuando la estantería no coincide con la base de datos. Es la única pantalla
 * desde la que se puede tocar el stock a mano, y exige motivo siempre.
 */
class InventarioController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    /** Existencias de todo el catálogo, lo más escaso primero. */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Producto::class);

        $productos = Producto::with('categoria')
            ->when($request->buscar, fn ($q) => $q->where('nombre', 'like', '%'.$request->buscar.'%'))
            ->when($request->boolean('reponer'), fn ($q) => $q->necesitaReposicion())
            ->orderBy('stock')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventario.index', compact('productos'));
    }

    /** Kardex de un producto. */
    public function show(Producto $producto)
    {
        $this->authorize('view', $producto);

        return view('admin.inventario.kardex', [
            'producto' => $producto,
            'movimientos' => $producto->movimientos()->with(['user', 'origen'])->paginate(30),
            // Se muestra al lado del stock declarado: si no coinciden, algo ha
            // movido existencias sin pasar por el servicio.
            'stockSegunKardex' => $this->inventario->stockSegunKardex($producto),
        ]);
    }

    /** Ajuste manual: se cuenta la estantería y se corrige. */
    public function ajustar(AjustarInventarioRequest $request, Producto $producto)
    {
        try {
            $this->inventario->ajustar($producto, $request->stock_real, $request->motivo);
        } catch (InvalidArgumentException $e) {
            // El caso típico: teclear el stock que ya estaba registrado.
            throw ValidationException::withMessages(['stock_real' => $e->getMessage()]);
        }

        return redirect()->route('admin.inventario.show', $producto)
            ->with('success', 'Stock ajustado y movimiento registrado.');
    }
}
