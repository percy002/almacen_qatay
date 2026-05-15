<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sizes = ['S', 'M', 'L', 'XL'];
        $colors = ['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco'];
        $size = $this->faker->randomElement($sizes);
        $color = $this->faker->randomElement($colors);
        $variantName = "Talla $size - Color $color";

        return [
            'product_id' => null, // Se asigna en el seeder
            'variant_name' => $variantName,
            'sku' => null,
            'current_stock' => $this->faker->numberBetween(0, 50),
        ];
    }
}
