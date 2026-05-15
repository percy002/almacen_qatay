<?php

use App\Models\Product;
use App\Models\User;

test('creating a product also creates its original variant', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->from(route('products.create'))
        ->post(route('products.store'), [
            'name' => 'Producto base',
            'description' => 'Producto con variante inicial automática',
            'min_stock' => 1,
            'status' => 'activo',
        ]);

    $response->assertRedirect();

    $product = Product::query()->with('variants')->sole();

    expect($product->variants)->toHaveCount(1)
        ->and($product->variants->first()->variant_name)->toBe('Original')
        ->and($product->variants->first()->current_stock)->toBe(0)
        ->and($product->variants->first()->sku)->not->toBeNull();
});
