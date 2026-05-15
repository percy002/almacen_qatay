<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('sku is generated automatically when creating a variant without sku', function () {
    Storage::fake('public');

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
            'images' => [
                UploadedFile::fake()->image('variant-1.jpg'),
                UploadedFile::fake()->image('variant-2.jpg'),
            ],
        ]);

    $response->assertRedirect(route('products.edit', $product));

    $variant = ProductVariant::query()->sole();

    expect($variant->sku)
        ->not->toBeNull()
        ->and($variant->sku)->toStartWith('SKU-TPR-001-')
        ->and($variant->sku)->toMatch('/^SKU-TPR-001-[A-Z0-9]{4}$/')
        ->and($variant->current_stock)->toBe(0);

    expect($variant->gallery_paths)->toHaveCount(2);
    Storage::disk('public')->assertExists($variant->gallery_paths[0]);
    Storage::disk('public')->assertExists($variant->gallery_paths[1]);
});

test('variant images are required when creating a variant from form', function () {
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
        ]);

    $response
        ->assertRedirect(route('products.edit', $product))
        ->assertSessionHasErrors(['images']);
});

test('removing a variant image deletes it from storage on update', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela premium',
        'internal_code' => 'TPR-001',
        'description' => 'Producto de prueba',
        'min_stock' => 3,
        'status' => 'activo',
    ]);

    $this->actingAs($admin)
        ->from(route('products.edit', $product))
        ->post(route('products.variants.store', $product), [
            'variant_name' => 'Negro / XL',
            'images' => [
                UploadedFile::fake()->image('variant-1.jpg'),
                UploadedFile::fake()->image('variant-2.jpg'),
            ],
        ])
        ->assertRedirect(route('products.edit', $product));

    $variant = ProductVariant::query()->sole();
    $firstImage = $variant->gallery_paths[0];
    $secondImage = $variant->gallery_paths[1];

    $response = $this->actingAs($admin)
        ->from(route('products.edit', $product))
        ->put(route('variants.update', $variant), [
            'variant_name' => $variant->variant_name,
            'sku' => $variant->sku,
            'original_images' => [$firstImage, $secondImage, null],
            'current_images' => [null, $secondImage, null],
            'images' => [null, null, null],
        ]);

    $response->assertRedirect(route('products.edit', $product));

    $variant->refresh();

    expect($variant->gallery_paths)->toHaveCount(1)
        ->and($variant->gallery_paths[0])->toBe($secondImage);

    Storage::disk('public')->assertMissing($firstImage);
    Storage::disk('public')->assertExists($secondImage);
});
