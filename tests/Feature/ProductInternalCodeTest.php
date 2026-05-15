<?php

use App\Models\Product;
use App\Models\User;

test('internal code is generated automatically when creating a product without internal code', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->from(route('products.create'))
        ->post(route('products.store'), [
            'name' => 'Producto sin código',
            'description' => 'Prueba de generación automática',
            'min_stock' => 2,
            'status' => 'activo',
        ]);

    $response->assertRedirect();

    $product = Product::query()->sole();

    expect($product->internal_code)
        ->not->toBeNull()
        ->and($product->internal_code)->toStartWith('PRD-PROD-')
        ->and($product->internal_code)->toMatch('/^PRD-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
});
