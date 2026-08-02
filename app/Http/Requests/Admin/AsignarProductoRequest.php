<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Asignar un producto al catálogo de un proveedor (H-12).
 */
class AsignarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('proveedor'));
    }

    public function rules(): array
    {
        return [
            'producto_id' => [
                'required',
                // Sin el filtro de SoftDeletes se podría asignar un producto
                // retirado del catálogo (mismo fallo que H-41 en el carrito).
                Rule::exists('productos', 'id')->whereNull('deleted_at'),
                // El índice único que añadió H-25 convirtió el attach() repetido
                // en un error 500 de la base. Que la base lo rechace es lo
                // correcto; lo que faltaba era preverlo aquí (H-40).
                Rule::unique('proveedor_producto', 'producto_id')
                    ->where('proveedor_id', $this->route('proveedor')->id),
            ],
            'precio_compra' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.unique' => 'Ese producto ya está asignado a este proveedor.',
        ];
    }

    public function attributes(): array
    {
        return [
            'producto_id' => 'producto',
            'precio_compra' => 'precio de compra',
        ];
    }
}
