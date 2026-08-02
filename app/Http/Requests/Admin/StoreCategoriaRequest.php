<?php

namespace App\Http\Requests\Admin;

use App\Models\Categoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta de categoría.
 *
 * El nombre es único: dos «Lácteos» parten el catálogo en dos y el cliente
 * solo ve la mitad en cada una.
 */
class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Categoria::class);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('categorias', 'nombre')],
        ];
    }
}
