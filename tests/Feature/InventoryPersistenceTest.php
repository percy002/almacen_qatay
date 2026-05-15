<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseExit;

test('storing an entry updates stock and records movements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela drill',
        'internal_code' => 'TD-001',
        'description' => 'Producto de prueba',
        'min_stock' => 5,
        'status' => 'activo',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Azul / L',
        'sku' => 'TD-001-AZL',
        'current_stock' => 10,
    ]);

    $response = $this->actingAs($admin)->post(route('entries.store'), [
        'entry_date' => now()->toDateString(),
        'notes' => 'Ingreso de prueba',
        'items' => [
            ['variant_id' => $variant->id, 'quantity' => 4],
        ],
    ]);

    $response->assertRedirect();

    expect(WarehouseEntry::count())->toBe(1);
    expect($variant->fresh()->current_stock)->toBe(14);

    $movement = StockMovement::query()->sole();

    expect($movement->type)->toBe('entrada')
        ->and($movement->stock_before)->toBe(10)
        ->and($movement->stock_after)->toBe(14)
        ->and($movement->reference_type)->toBe('warehouse_entry');
});

test('storing an exit updates stock and records movements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela lycra',
        'internal_code' => 'TL-001',
        'description' => 'Producto de prueba',
        'min_stock' => 3,
        'status' => 'activo',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Negro / M',
        'sku' => 'TL-001-NGM',
        'current_stock' => 9,
    ]);

    $response = $this->actingAs($admin)->post(route('exits.store'), [
        'exit_date' => now()->toDateString(),
        'notes' => 'Salida de prueba',
        'items' => [
            ['variant_id' => $variant->id, 'quantity' => 2],
        ],
    ]);

    $response->assertRedirect();

    expect(WarehouseExit::count())->toBe(1);
    expect($variant->fresh()->current_stock)->toBe(7);

    $movement = StockMovement::query()->sole();

    expect($movement->type)->toBe('salida')
        ->and($movement->stock_before)->toBe(9)
        ->and($movement->stock_after)->toBe(7)
        ->and($movement->reference_type)->toBe('warehouse_exit');
});

test('storing an exit with insufficient stock is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela denim',
        'internal_code' => 'TDN-001',
        'description' => 'Producto de prueba',
        'min_stock' => 2,
        'status' => 'activo',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Índigo / S',
        'sku' => 'TDN-001-INS',
        'current_stock' => 1,
    ]);

    $response = $this->actingAs($admin)
        ->from(route('exits.create'))
        ->post(route('exits.store'), [
            'exit_date' => now()->toDateString(),
            'notes' => 'Salida inválida',
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 4],
            ],
        ]);

    $response->assertRedirect(route('exits.create'));
    $response->assertSessionHasErrors(['items.0.quantity']);

    expect(WarehouseExit::count())->toBe(0);
    expect(StockMovement::count())->toBe(0);
    expect($variant->fresh()->current_stock)->toBe(1);
});

test('storing an adjustment updates stock and records movements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela popelina',
        'internal_code' => 'TP-001',
        'description' => 'Producto de prueba',
        'min_stock' => 4,
        'status' => 'activo',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Blanco / XL',
        'sku' => 'TP-001-BLXL',
        'current_stock' => 12,
    ]);

    $response = $this->actingAs($admin)->post(route('adjustments.store'), [
        'adjustment_date' => now()->toDateString(),
        'adjustment_type' => 'decremento',
        'quantity' => 3,
        'reason' => 'Corrección por conteo físico',
        'product_variant_id' => $variant->id,
    ]);

    $response->assertRedirect();

    $adjustment = StockAdjustment::query()->sole();
    $movement = StockMovement::query()->sole();

    expect($variant->fresh()->current_stock)->toBe(9)
        ->and($adjustment->stock_before)->toBe(12)
        ->and($adjustment->stock_after)->toBe(9)
        ->and($movement->type)->toBe('ajuste')
        ->and($movement->reference_type)->toBe('stock_adjustment');
});

test('updating an entry recalculates stock and replaces movements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela viscosa',
        'internal_code' => 'TV-001',
        'description' => 'Producto de prueba',
        'min_stock' => 5,
        'status' => 'activo',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Verde / M',
        'sku' => 'TV-001-VDM',
        'current_stock' => 10,
    ]);

    $this->actingAs($admin)->post(route('entries.store'), [
        'entry_date' => now()->toDateString(),
        'notes' => 'Ingreso original',
        'items' => [
            ['variant_id' => $variant->id, 'quantity' => 4],
        ],
    ])->assertRedirect();

    $entry = WarehouseEntry::query()->sole();

    $response = $this->actingAs($admin)->put(route('entries.update', $entry), [
        'entry_date' => now()->addDay()->toDateString(),
        'notes' => 'Ingreso editado',
        'items' => [
            ['variant_id' => $variant->id, 'quantity' => 2],
        ],
    ]);

    $response->assertRedirect(route('entries.show', $entry));

    $entry->refresh();
    $movement = StockMovement::query()->where('reference_type', 'warehouse_entry')->where('reference_id', $entry->id)->sole();

    expect($variant->fresh()->current_stock)->toBe(12)
        ->and($entry->notes)->toBe('Ingreso editado')
        ->and($movement->stock_before)->toBe(10)
        ->and($movement->stock_after)->toBe(12)
        ->and($movement->quantity)->toBe(2)
        ->and(StockMovement::query()->where('reference_type', 'warehouse_entry')->where('reference_id', $entry->id)->count())->toBe(1);
});

test('updating an exit recalculates stock and replaces movements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela microfibra',
        'internal_code' => 'TM-001',
        'description' => 'Producto de prueba',
        'min_stock' => 5,
        'status' => 'activo',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Rojo / L',
        'sku' => 'TM-001-RJL',
        'current_stock' => 10,
    ]);

    $this->actingAs($admin)->post(route('exits.store'), [
        'exit_date' => now()->toDateString(),
        'notes' => 'Salida original',
        'items' => [
            ['variant_id' => $variant->id, 'quantity' => 3],
        ],
    ])->assertRedirect();

    $exit = WarehouseExit::query()->sole();

    $response = $this->actingAs($admin)->put(route('exits.update', $exit), [
        'exit_date' => now()->addDay()->toDateString(),
        'notes' => 'Salida editada',
        'items' => [
            ['variant_id' => $variant->id, 'quantity' => 1],
        ],
    ]);

    $response->assertRedirect(route('exits.show', $exit));

    $exit->refresh();
    $movement = StockMovement::query()->where('reference_type', 'warehouse_exit')->where('reference_id', $exit->id)->sole();

    expect($variant->fresh()->current_stock)->toBe(9)
        ->and($exit->notes)->toBe('Salida editada')
        ->and($movement->stock_before)->toBe(10)
        ->and($movement->stock_after)->toBe(9)
        ->and($movement->quantity)->toBe(1)
        ->and(StockMovement::query()->where('reference_type', 'warehouse_exit')->where('reference_id', $exit->id)->count())->toBe(1);
});

test('updating an adjustment recalculates stock and replaces movements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Tela gabardina',
        'internal_code' => 'TG-001',
        'description' => 'Producto de prueba',
        'min_stock' => 5,
        'status' => 'activo',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Beige / S',
        'sku' => 'TG-001-BGS',
        'current_stock' => 12,
    ]);

    $this->actingAs($admin)->post(route('adjustments.store'), [
        'adjustment_date' => now()->toDateString(),
        'adjustment_type' => 'decremento',
        'quantity' => 2,
        'reason' => 'Ajuste inicial por control',
        'product_variant_id' => $variant->id,
    ])->assertRedirect();

    $adjustment = StockAdjustment::query()->sole();

    $response = $this->actingAs($admin)->put(route('adjustments.update', $adjustment), [
        'adjustment_date' => now()->addDay()->toDateString(),
        'adjustment_type' => 'incremento',
        'quantity' => 5,
        'reason' => 'Ajuste editado por recuento físico',
        'product_variant_id' => $variant->id,
    ]);

    $response->assertRedirect(route('adjustments.show', $adjustment));

    $adjustment->refresh();
    $movement = StockMovement::query()->where('reference_type', 'stock_adjustment')->where('reference_id', $adjustment->id)->sole();

    expect($variant->fresh()->current_stock)->toBe(17)
        ->and($adjustment->adjustment_type)->toBe('incremento')
        ->and($adjustment->stock_before)->toBe(12)
        ->and($adjustment->stock_after)->toBe(17)
        ->and($movement->stock_before)->toBe(12)
        ->and($movement->stock_after)->toBe(17)
        ->and($movement->quantity)->toBe(5)
        ->and(StockMovement::query()->where('reference_type', 'stock_adjustment')->where('reference_id', $adjustment->id)->count())->toBe(1);
});