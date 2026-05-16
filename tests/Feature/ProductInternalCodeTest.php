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

test('internal code is generated automatically when updating a product with empty internal code', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $product = Product::create([
        'name' => 'Producto editable',
        'internal_code' => 'PRD-EDIT-0001',
        'description' => 'Antes de editar',
        'min_stock' => 3,
        'status' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('products.edit', $product))
        ->put(route('products.update', $product), [
            'name' => 'Producto editado',
            'internal_code' => '',
            'description' => 'Después de editar',
            'min_stock' => 4,
            'status' => 'activo',
        ]);

    $response->assertRedirect(route('products.show', $product));

    $product->refresh();

    expect($product->internal_code)
        ->not->toBeNull()
        ->and($product->internal_code)->not->toBe('PRD-EDIT-0001')
        ->and($product->internal_code)->toMatch('/^PRD-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
});
