<?php

namespace App\Http\Requests\Admin;

use App\Models\Proveedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta de proveedor (H-12).
 *
 * El RUC pasa a ser único: identifica a la empresa, y dos filas con el mismo
 * RUC son el mismo proveedor dado de alta dos veces. Antes solo se validaba
 * que fuera numérico.
 */
class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Proveedor::class);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'digits:11', Rule::unique('proveedores', 'ruc')],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'contacto_nombre' => ['required', 'string', 'max:255'],
            'contacto_telefono' => ['required', 'string', 'max:30'],
        ];
    }

    public function attributes(): array
    {
        return [
            'contacto_nombre' => 'nombre del contacto',
            'contacto_telefono' => 'teléfono del contacto',
        ];
    }
}
