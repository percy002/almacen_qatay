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
        Product::factory(8)->create()->each(function ($product) {
            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'variant_name' => 'Original',
            ]);

            // Para cada producto, crear entre 0 y 2 variantes adicionales
            $variants = collect(['S', 'M', 'L', 'XL']);
            $colors = ['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco'];
            $numVariants = rand(0, 2);
            $used = ['Original'];

            for ($i = 0; $i < $numVariants; $i++) {
                do {
                    $size = $variants->random();
                    $color = $colors[array_rand($colors)];
                    $key = $size.'-'.$color;
                } while (in_array($key, $used));
                $used[] = $key;

                ProductVariant::factory()->create([
                    'product_id' => $product->id,
                    'variant_name' => "Talla $size - Color $color",
                ]);
            }
        });
    }
}
