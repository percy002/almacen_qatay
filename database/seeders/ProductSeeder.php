<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Poncho rayas', 'min_stock' => 20],
            ['name' => 'Poncho buena vista bucle', 'min_stock' => 20],
            ['name' => 'Capa espiga', 'min_stock' => 20],
            ['name' => 'Capa bucle', 'min_stock' => 15],
            ['name' => 'Capa PAYAY', 'min_stock' => 20],
            ['name' => 'Chullo renacimiento', 'min_stock' => 50],
            ['name' => 'Chullo tumi', 'min_stock' => 20],
            ['name' => 'Chullo crocus', 'min_stock' => 10],
            ['name' => 'GORRO BUCLE', 'min_stock' => 10],
            ['name' => 'Gorro bucle targeta', 'min_stock' => 10],
            ['name' => 'Cuellera tarjeta', 'min_stock' => 20],
            ['name' => 'cuellera targeta doble', 'min_stock' => 20],
            ['name' => 'Miton tarjeta', 'min_stock' => 40],
            ['name' => 'Miton llano', 'min_stock' => 50],
        ];

        foreach ($products as $prod) {
            $product = Product::create([
                'name' => $prod['name'],
                'internal_code' => null,
                'description' => null,
                'status' => 'activo',
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'variant_name' => 'Original',
                'sku' => null,
                'current_stock' => 0,
                'min_stock' => $prod['min_stock'],
            ]);
        }
    }
}
