<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ProductoNoAsignadoException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrdenCompraRequest;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Services\OrdenCompraService;
use Illuminate\Validation\ValidationException;

/**
 * Órdenes de compra al proveedor.
 *
 * `store` tenía 62 líneas y hacía de todo: recorrer los dos arrays paralelos
 * del formulario, buscar precios en el pivot, sumar el total y validar. El
 * formulario lo aplana ahora el Form Request, y el negocio vive en
 * OrdenCompraService.
 */
class OrdenCompraController extends Controller
{
    public function __construct(private readonly OrdenCompraService $ordenes) {}

    public function index()
    {
        $this->authorize('viewAny', OrdenCompra::class);

        $ordenes = OrdenCompra::with('proveedor')->latest('id')->paginate(10);

        return view('admin.ordenes.index', compact('ordenes'));
    }

    public function create()
    {
        $this->authorize('create', OrdenCompra::class);

        return view('admin.ordenes.create', ['proveedores' => Proveedor::orderBy('nombre')->get()]);
    }

    /** Catálogo de un proveedor, para el selector de la pantalla de creación. */
    public function productosProveedor(Proveedor $proveedor)
    {
        $this->authorize('create', OrdenCompra::class);

        return response()->json($proveedor->productos);
    }

    public function store(StoreOrdenCompraRequest $request)
    {
        $proveedor = Proveedor::findOrFail($request->proveedor_id);

        try {
            $this->ordenes->crear($proveedor, $request->lineas());
        } catch (ProductoNoAsignadoException $e) {
            throw ValidationException::withMessages(['productos' => $e->getMessage()]);
        }

        return redirect()->route('admin.ordenes.index')
            ->with('success', 'Orden creada correctamente.');
    }

    public function show(OrdenCompra $orden)
    {
        $this->authorize('view', $orden);

        $orden->load('items.producto', 'proveedor');

        return view('admin.ordenes.show', compact('orden'));
    }

    /** Da la orden por recibida: la mercancía entra al inventario (H-35). */
    public function recibir(OrdenCompra $orden)
    {
        if (! $orden->estaPendiente()) {
            return back()->with('error', 'La orden ya fue recibida.');
        }

        $this->authorize('recibir', $orden);

        $this->ordenes->recibir($orden, request()->user());

        return back()->with('success', 'Orden marcada como recibida y stock actualizado.');
    }
}
