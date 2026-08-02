<?php

namespace App\Http\Requests\Admin;

use App\Enums\UnidadMedida;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Edición de un producto (H-12).
 *
 * Igual que el alta salvo por 'stock', que no aparece: editar la ficha de un
 * producto no es la forma de corregir existencias. Para eso está el ajuste del
 * kardex, que deja constancia de quién lo cambió y por qué (H-35).
 *
 * No hereda de StoreProductoRequest a propósito: la herencia haría que añadir
 * una regla al alta la colara también en la edición sin que nadie lo decida.
 */
class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('producto'));
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'imagen' => ['required', 'string'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'unidad_medida' => ['required', Rule::enum(UnidadMedida::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'categoria_id' => 'categoría',
            'stock_minimo' => 'stock mínimo',
            'unidad_medida' => 'unidad de venta',
        ];
    }
}
