<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Catálogo de trabajo para desarrollo.
 *
 * Cada producto es [nombre, precio venta, stock, unidad, descripción].
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            'Abarrotes' => [
                ['Arroz Extra Superior 5 kg', 24.90, 120, 'und', 'Grano largo seleccionado, saco de 5 kg.'],
                ['Aceite Vegetal 1 L', 9.50, 200, 'und', 'Aceite de soya refinado, botella de 1 litro.'],
                ['Azúcar Rubia 1 kg', 4.80, 180, 'kg', 'Azúcar rubia de caña.'],
                ['Fideos Spaghetti 500 g', 3.20, 240, 'und', 'Pasta de sémola de trigo.'],
                ['Atún en Aceite 170 g', 6.40, 150, 'und', 'Filete de atún en aceite vegetal.'],
                ['Lentejas 500 g', 5.60, 90, 'und', 'Lenteja seleccionada, bolsa de 500 g.'],
            ],
            'Lácteos' => [
                ['Leche Evaporada 400 g', 4.30, 300, 'und', 'Leche evaporada entera.'],
                ['Yogurt Fresa 1 L', 7.90, 80, 'und', 'Yogurt bebible sabor fresa.'],
                ['Queso Fresco', 22.00, 35, 'kg', 'Queso fresco artesanal, precio por kilo.'],
                ['Mantequilla 200 g', 11.50, 60, 'und', 'Mantequilla con sal.'],
                ['Leche Fresca UHT 1 L', 5.20, 140, 'und', 'Leche entera ultrapasteurizada.'],
            ],
            'Verduras' => [
                ['Papa Blanca', 3.50, 250, 'kg', 'Papa blanca fresca, precio por kilo.'],
                ['Tomate Italiano', 4.20, 120, 'kg', 'Tomate fresco de temporada.'],
                ['Cebolla Roja', 3.80, 160, 'kg', 'Cebolla roja seleccionada.'],
                ['Zanahoria', 3.00, 110, 'kg', 'Zanahoria fresca.'],
                ['Limón', 6.50, 70, 'kg', 'Limón sutil.'],
                ['Palta Fuerte', 12.90, 45, 'kg', 'Palta fuerte en su punto.'],
            ],
            'Hogar' => [
                ['Detergente en Polvo 2 kg', 21.90, 70, 'und', 'Detergente multiusos.'],
                ['Papel Higiénico x12', 18.50, 95, 'und', 'Paquete de 12 rollos doble hoja.'],
                ['Lejía 1 L', 4.60, 130, 'und', 'Hipoclorito de sodio al 5%.'],
                ['Jabón de Lavar 250 g', 3.40, 150, 'und', 'Jabón en barra para ropa.'],
                ['Bolsas de Basura x30', 8.90, 85, 'und', 'Bolsas de 50 litros.'],
            ],
            'Snacks' => [
                ['Papas Fritas 145 g', 5.90, 200, 'und', 'Papas fritas clásicas.'],
                ['Galletas de Soda 6 pack', 4.50, 180, 'und', 'Paquete de 6 unidades.'],
                ['Chocolate con Leche 100 g', 7.20, 110, 'und', 'Tableta de chocolate con leche.'],
                ['Maní Salado 200 g', 6.80, 95, 'und', 'Maní tostado con sal.'],
                ['Chizitos 120 g', 4.20, 140, 'und', 'Snack de maíz con queso.'],
            ],
            'Licores' => [
                ['Cerveza Lager 355 ml', 4.50, 300, 'und', 'Cerveza rubia, lata de 355 ml.'],
                ['Pisco Quebranta 700 ml', 45.00, 40, 'und', 'Pisco puro de uva quebranta.'],
                ['Vino Tinto Borgoña 750 ml', 26.90, 55, 'und', 'Vino tinto semiseco.'],
                ['Ron Añejo 750 ml', 38.50, 30, 'und', 'Ron añejado 5 años.'],
            ],
            'Mascotas' => [
                ['Alimento para Perro 2 kg', 28.90, 50, 'und', 'Croquetas para perro adulto.'],
                ['Alimento para Gato 1 kg', 19.90, 45, 'und', 'Alimento seco para gato adulto.'],
                ['Arena Sanitaria 4 kg', 16.50, 40, 'und', 'Arena aglomerante para gatos.'],
                ['Snack Dental para Perro', 12.90, 60, 'und', 'Premios dentales, bolsa de 7 unidades.'],
            ],
            'Bebidas' => [
                ['Gaseosa Cola 3 L', 8.90, 160, 'und', 'Bebida gaseosa sabor cola.'],
                ['Agua Mineral sin Gas 2.5 L', 4.20, 220, 'und', 'Agua de mesa sin gas.'],
                ['Jugo de Naranja 1 L', 6.50, 100, 'und', 'Jugo de naranja pasteurizado.'],
                ['Té Helado Limón 500 ml', 3.50, 130, 'und', 'Bebida de té con limón.'],
                ['Bebida Rehidratante 500 ml', 4.80, 115, 'und', 'Bebida isotónica sabor tropical.'],
            ],
        ];

        foreach ($catalogo as $nombreCategoria => $productos) {
            $categoria = Categoria::where('nombre', $nombreCategoria)->first();

            if (! $categoria) {
                $this->command->warn("Categoría '{$nombreCategoria}' no encontrada, se omite.");

                continue;
            }

            foreach ($productos as [$nombre, $precio, $stock, $unidad, $descripcion]) {
                if (Producto::where('nombre', $nombre)->exists()) {
                    continue;
                }

                Producto::create([
                    'nombre' => $nombre,
                    'precio' => $precio,
                    'stock' => $stock,
                    'unidad_medida' => $unidad,
                    'descripcion' => $descripcion,
                    'categoria_id' => $categoria->id,
                    'imagen' => 'https://placehold.co/400x400/e2e8f0/475569?text='.urlencode($nombre),
                ]);
            }
        }

        $this->command->info('Catálogo: '.Producto::count().' productos.');
    }
}
