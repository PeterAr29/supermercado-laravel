<?php

namespace App\Http\Requests\Tienda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Añadir un producto al carrito (H-12).
 *
 * Sin authorize(): la tienda es pública y un invitado también compra (H-10).
 * Que haya stock suficiente no se comprueba aquí — depende del carrito actual
 * y de lo que ya haya dentro, y eso lo sabe CarritoService.
 */
class AgregarAlCarritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'exists' a secas consulta la tabla en crudo, sin el filtro de
            // SoftDeletes: un producto retirado pasaba la validación y salía
            // "Producto agregado al carrito" sin agregar nada (H-41).
            'producto_id' => ['required', Rule::exists('productos', 'id')->whereNull('deleted_at')],
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function cantidad(): int
    {
        return (int) ($this->input('cantidad') ?: 1);
    }

    public function attributes(): array
    {
        return ['producto_id' => 'producto'];
    }
}
