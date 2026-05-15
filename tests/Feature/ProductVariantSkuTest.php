<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

test('sku is generated automatically when creating a variant without sku', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela premium',
        'internal_code' => 'TPR-001',
        'description' => 'Producto de prueba',
        'min_stock' => 3,
        'status' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('products.edit', $product))
        ->post(route('products.variants.store', $product), [
            'variant_name' => 'Negro / XL',
            'current_stock' => 999,
        ]);

    $response->assertRedirect(route('products.edit', $product));

    $variant = ProductVariant::query()->sole();

    expect($variant->sku)
        ->not->toBeNull()
        ->and($variant->sku)->toStartWith('SKU-TPR-001-')
        ->and($variant->sku)->toMatch('/^SKU-TPR-001-[A-Z0-9]{4}$/')
        ->and($variant->current_stock)->toBe(0);
});
