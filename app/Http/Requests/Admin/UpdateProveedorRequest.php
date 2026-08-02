<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Edición de proveedor (H-12).
 *
 * La única diferencia con el alta es el `ignore()` del RUC: sin él, guardar el
 * formulario sin tocar nada fallaría por chocar contra su propia fila.
 */
class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('proveedor'));
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'digits:11', Rule::unique('proveedores', 'ruc')->ignore($this->route('proveedor'))],
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
