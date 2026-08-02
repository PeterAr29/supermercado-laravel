<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Cambiar el precio al que un proveedor nos vende un producto (H-12).
 */
class ActualizarPrecioCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('proveedor'));
    }

    public function rules(): array
    {
        return [
            'precio_compra' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return ['precio_compra' => 'precio de compra'];
    }
}
