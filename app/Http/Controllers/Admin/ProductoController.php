<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UnidadMedida;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductoRequest;
use App\Http\Requests\Admin\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\Request;

/**
 * Gestión del catálogo (H-14).
 *
 * La validación y la autorización viven en los Form Requests; aquí solo queda
 * buscar, delegar y redirigir.
 */
class ProductoController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Producto::class);

        return view('admin.productos.index', [
            'productos' => Producto::with('categoria')
                ->when($request->buscar, fn ($q) => $q->where('nombre', 'like', '%'.$request->buscar.'%'))
                ->when($request->categoria, fn ($q) => $q->where('categoria_id', $request->categoria))
                ->when($request->boolean('reponer'), fn ($q) => $q->necesitaReposicion())
                ->orderBy('nombre')
                ->paginate(20)
                ->withQueryString(),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Producto::class);

        return view('admin.productos.create', $this->opcionesDelFormulario());
    }

    public function store(StoreProductoRequest $request)
    {
        $producto = Producto::create($request->validated());

        // Un producto puede darse de alta con existencias. Sin esta línea de
        // apertura entraría al catálogo con un kardex que no explica su stock.
        $this->inventario->conciliar($producto, 'Alta del producto en el catálogo');

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $this->authorize('update', $producto);

        return view('admin.productos.edit', $this->opcionesDelFormulario(['producto' => $producto]));
    }

    /**
     * El stock no entra por aquí: el Form Request de edición no lo acepta.
     * Se corrige con un ajuste del kardex, que exige motivo (H-35).
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $producto->update($request->validated());

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $this->authorize('delete', $producto);

        // SoftDeletes: las ventas anteriores conservan sus líneas (H-02).
        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto retirado del catálogo.');
    }

    private function opcionesDelFormulario(array $extra = []): array
    {
        return $extra + [
            'categorias' => Categoria::orderBy('nombre')->get(),
            'unidades' => UnidadMedida::cases(),
        ];
    }
}
