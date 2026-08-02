<?php

namespace App\Http\Requests\Admin;

use App\Models\OrdenCompra;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Crear una orden de compra a un proveedor (H-12).
 *
 * El formulario envía DOS arrays paralelos —'productos' y 'cantidades'— unidos
 * por el índice. Aquí se valida cada posición, cosa que el `store` no hacía:
 * se fiaba de que llegaran bien emparejados.
 *
 * Que el producto pertenezca al proveedor NO se comprueba aquí: eso es una
 * regla de negocio y la responde OrdenCompraService, que es quien conoce el
 * pivot y sus precios.
 */
class StoreOrdenCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', OrdenCompra::class);
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*' => ['required', 'integer', 'exists:productos,id'],
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ];
    }

    /**
     * Una orden sin ninguna cantidad no es una orden.
     *
     * El formulario envía todos los productos del proveedor, así que 'productos'
     * nunca viene vacío: lo que hay que comprobar es que alguna cantidad sea
     * mayor que cero. Antes esto se descubría a mitad de la transacción, ya con
     * la orden escrita, y había que deshacerla.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isEmpty() && $this->lineas() === []) {
                    $validator->errors()->add(
                        'cantidades',
                        'Indica una cantidad mayor que cero en al menos un producto.'
                    );
                }
            },
        ];
    }

    /**
     * Las líneas realmente pedidas: producto => cantidad, sin los ceros.
     *
     * El formulario manda todos los productos del proveedor, con cantidad 0 los
     * que no se piden. Aplanarlo aquí evita que el servicio tenga que saber
     * cómo estaba montado el formulario.
     *
     * @return array<int, int>
     */
    public function lineas(): array
    {
        $cantidades = $this->input('cantidades', []);

        return collect($this->input('productos', []))
            ->mapWithKeys(fn ($productoId, $i) => [(int) $productoId => (int) ($cantidades[$i] ?? 0)])
            ->filter(fn ($cantidad) => $cantidad > 0)
            ->all();
    }

    public function attributes(): array
    {
        return [
            'proveedor_id' => 'proveedor',
            'cantidades.*' => 'cantidad',
        ];
    }
}
