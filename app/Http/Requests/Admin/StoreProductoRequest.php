<?php

namespace App\Http\Requests\Admin;

use App\Enums\UnidadMedida;
use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta de un producto (H-12).
 *
 * 'stock' solo se acepta aquí, en el alta. Al editar no se envía ni se valida:
 * el inventario se mueve con un ajuste que exige motivo (H-35).
 */
class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Producto::class);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'imagen' => ['required', 'string'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            // El select existía en el formulario desde diciembre, pero el
            // controlador nunca lo recogía: no se guardaba nunca (H-38).
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
