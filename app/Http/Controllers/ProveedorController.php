<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::all();

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required',
            'ruc' => 'required|numeric',
            'telefono' => 'required',
            'email' => 'required|email',
            'direccion' => 'required',
            'contacto_nombre' => 'required',
            'contacto_telefono' => 'required',
        ]);

        Proveedor::create($datos);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $datos = $request->validate([
            'nombre' => 'required',
            'ruc' => 'required|numeric',
            'telefono' => 'required',
            'email' => 'required|email',
            'direccion' => 'required',
            'contacto_nombre' => 'required',
            'contacto_telefono' => 'required',
        ]);

        $proveedor->update($datos);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado.');
    }
}
