<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

/**
 * Proveedores de trabajo y su catálogo de compra.
 *
 * Cada proveedor abastece las categorías que le corresponden. El precio de
 * compra se calcula como un margen sobre el precio de venta, para que las
 * órdenes de compra den totales realistas.
 */
class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Distribuidora Andina S.A.C.',
                'ruc' => '20100047218',
                'telefono' => '014567890',
                'email' => 'ventas@andina.com.pe',
                'direccion' => 'Av. Argentina 2450, Callao',
                'contacto_nombre' => 'Rosa Quispe',
                'contacto_telefono' => '987654321',
                'categoria' => 'Abarrotes',
                'categorias' => ['Abarrotes', 'Snacks'],
                'margen' => 0.70,
            ],
            [
                'nombre' => 'Lácteos del Valle E.I.R.L.',
                'ruc' => '20512345678',
                'telefono' => '014112233',
                'email' => 'pedidos@lacteosdelvalle.pe',
                'direccion' => 'Jr. Los Nogales 145, Lurín',
                'contacto_nombre' => 'Carlos Mendoza',
                'contacto_telefono' => '956231478',
                'categoria' => 'Lácteos',
                'categorias' => ['Lácteos'],
                'margen' => 0.72,
            ],
            [
                'nombre' => 'Mercado Mayorista Frutas y Verduras',
                'ruc' => '20487654321',
                'telefono' => '013344556',
                'email' => 'contacto@mayoristafv.pe',
                'direccion' => 'Av. La Marina 890, Santa Anita',
                'contacto_nombre' => 'Julia Ramos',
                'contacto_telefono' => '941887566',
                'categoria' => 'Verduras',
                'categorias' => ['Verduras'],
                'margen' => 0.65,
            ],
            [
                'nombre' => 'Limpieza Total Perú S.A.',
                'ruc' => '20556677889',
                'telefono' => '016677889',
                'email' => 'comercial@limpiezatotal.pe',
                'direccion' => 'Av. Colonial 1200, Lima',
                'contacto_nombre' => 'Miguel Ávila',
                'contacto_telefono' => '923445671',
                'categoria' => 'Hogar',
                'categorias' => ['Hogar'],
                'margen' => 0.68,
            ],
            [
                'nombre' => 'Bebidas y Licores del Sur',
                'ruc' => '20601122334',
                'telefono' => '015566778',
                'email' => 'ventas@licoresdelsur.pe',
                'direccion' => 'Av. Tomás Marsano 3400, Surco',
                'contacto_nombre' => 'Elena Torres',
                'contacto_telefono' => '978112233',
                'categoria' => 'Bebidas',
                'categorias' => ['Bebidas', 'Licores'],
                'margen' => 0.75,
            ],
            [
                'nombre' => 'PetShop Distribuciones',
                'ruc' => '20655443322',
                'telefono' => '012233445',
                'email' => 'mayorista@petshopdist.pe',
                'direccion' => 'Av. Universitaria 5600, Los Olivos',
                'contacto_nombre' => 'Diego Salas',
                'contacto_telefono' => '911223344',
                'categoria' => 'Mascotas',
                'categorias' => ['Mascotas'],
                'margen' => 0.70,
            ],
        ];

        foreach ($proveedores as $datos) {
            $categorias = $datos['categorias'];
            $margen = $datos['margen'];
            unset($datos['categorias'], $datos['margen']);

            $proveedor = Proveedor::firstOrCreate(['ruc' => $datos['ruc']], $datos);

            $ids = Categoria::whereIn('nombre', $categorias)->pluck('id');

            foreach (Producto::whereIn('categoria_id', $ids)->get() as $producto) {
                if ($proveedor->productos()->where('producto_id', $producto->id)->exists()) {
                    continue;
                }

                $proveedor->productos()->attach($producto->id, [
                    'precio_compra' => round($producto->precio * $margen, 2),
                    'stock_proveedor' => rand(50, 500),
                ]);
            }
        }

        $this->command->info('Proveedores: '.Proveedor::count().'.');
    }
}
