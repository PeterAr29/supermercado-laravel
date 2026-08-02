<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ajuste manual de stock (H-12, H-35).
 *
 * El motivo pide 5 caracteres como mínimo para que "ok" o "-" no cuenten como
 * explicación. Un ajuste sin motivo es un descuadre con permiso.
 */
class AjustarInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('producto'));
    }

    public function rules(): array
    {
        return [
            'stock_real' => ['required', 'integer', 'min:0'],
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['stock_real' => 'stock real contado'];
    }
}
