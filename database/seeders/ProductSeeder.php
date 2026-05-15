<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Product::factory(8)->create()->each(function ($product) {
            // Para cada producto, crear entre 2 y 4 variantes
            $variants = collect(['S', 'M', 'L', 'XL']);
            $colors = ['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco'];
            $numVariants = rand(2, 4);
            $used = [];
            for ($i = 0; $i < $numVariants; $i++) {
                do {
                    $size = $variants->random();
                    $color = $colors[array_rand($colors)];
                    $key = $size.'-'.$color;
                } while (in_array($key, $used));
                $used[] = $key;
                \App\Models\ProductVariant::factory()->create([
                    'product_id' => $product->id,
                    'variant_name' => "Talla $size - Color $color",
                ]);
            }
        });
    }
}
