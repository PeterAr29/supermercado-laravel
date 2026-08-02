<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('categoria'));
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                // ignore(): sin él, guardar sin cambiar el nombre chocaría
                // contra su propia fila.
                Rule::unique('categorias', 'nombre')->ignore($this->route('categoria')),
            ],
        ];
    }
}
