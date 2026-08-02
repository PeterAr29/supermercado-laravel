<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProveedorRequest;
use App\Http\Requests\Admin\UpdateProveedorRequest;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Proveedor::class);

        $proveedores = Proveedor::withCount('productos')->orderBy('nombre')->paginate(20);

        return view('admin.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        $this->authorize('create', Proveedor::class);

        return view('admin.proveedores.create');
    }

    public function store(StoreProveedorRequest $request)
    {
        Proveedor::create($request->validated());

        return redirect()->route('admin.proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Proveedor $proveedor)
    {
        $this->authorize('update', $proveedor);

        return view('admin.proveedores.edit', compact('proveedor'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
    {
        $proveedor->update($request->validated());

        return redirect()->route('admin.proveedores.index')
            ->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $this->authorize('delete', $proveedor);

        // Un proveedor con órdenes es historial de abastecimiento: borrarlo
        // dejaría esas órdenes sin decir a quién se le compró.
        if ($proveedor->ordenes()->exists()) {
            return back()->with('error', 'No se puede borrar un proveedor con órdenes de compra.');
        }

        $proveedor->productos()->detach();
        $proveedor->delete();

        return redirect()->route('admin.proveedores.index')
            ->with('success', 'Proveedor eliminado.');
    }
}
