<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UnidadMedida;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestión del catálogo (H-14).
 *
 * Antes esto vivía en el mismo controlador que la tienda, y sus botones se
 * pintaban sobre el listado público. Aquí solo entra quien tiene rol de
 * administrador.
 */
class ProductoController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Producto::class);

        $productos = Producto::with('categoria')
            ->when($request->buscar, fn ($q) => $q->where('nombre', 'like', '%'.$request->buscar.'%'))
            ->when($request->categoria, fn ($q) => $q->where('categoria_id', $request->categoria))
            ->when($request->boolean('reponer'), fn ($q) => $q->necesitaReposicion())
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $this->authorize('create', Producto::class);

        return view('admin.productos.create', [
            'categorias' => Categoria::orderBy('nombre')->get(),
            'unidades' => UnidadMedida::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Producto::class);

        $producto = Producto::create($this->validar($request, conStock: true));

        // Un producto puede darse de alta con existencias. Sin esta línea de
        // apertura entraría al catálogo con un kardex que no explica su stock.
        $this->inventario->conciliar($producto, 'Alta del producto en el catálogo');

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $this->authorize('update', $producto);

        return view('admin.productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'unidades' => UnidadMedida::cases(),
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $this->authorize('update', $producto);

        // El formulario de edición no envía 'stock' y aquí tampoco se valida:
        // el inventario solo se mueve desde InventarioService, y desde el panel
        // con un ajuste que exige motivo (H-35). Editar la ficha de un producto
        // no es la forma de corregir existencias.
        $producto->update($this->validar($request, conStock: false));

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

    /**
     * Reglas del formulario.
     *
     * 'stock' y 'stock_minimo' entran aquí porque el formulario nunca los
     * enviaba: todo producto creado desde la aplicación nacía con 0 unidades
     * y había que corregirlo por fuera (H-42). La Fase 4 mueve esto a un
     * Form Request (H-12).
     *
     * $conStock distingue el alta de la edición: al editar, el stock ni se
     * envía ni se valida, porque solo se mueve con un ajuste.
     */
    private function validar(Request $request, bool $conStock): array
    {
        $reglas = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'stock_minimo' => 'required|integer|min:0',
            // El select existía en el formulario desde diciembre, pero el
            // controlador nunca lo recogía: no se guardaba nunca (H-38).
            'unidad_medida' => ['required', Rule::enum(UnidadMedida::class)],
        ];

        if ($conStock) {
            $reglas['stock'] = 'required|integer|min:0';
        }

        return $request->validate($reglas);
    }
}
