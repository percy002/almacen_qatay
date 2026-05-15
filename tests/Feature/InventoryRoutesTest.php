<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseExit;

test('admin can access essential inventory routes', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $product = Product::create([
        'name' => 'Tela base',
        'internal_code' => 'TELA-001',
        'description' => 'Producto de prueba',
        'min_stock' => 5,
        'status' => 'activo',
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Rojo / M',
        'sku' => 'TELA-001-RM',
        'current_stock' => 20,
    ]);

    $entry = WarehouseEntry::create([
        'entry_code' => 'ENT-0001',
        'entry_date' => now()->toDateString(),
        'user_id' => $admin->id,
    ]);

    $exit = WarehouseExit::create([
        'exit_code' => 'SAL-0001',
        'exit_date' => now()->toDateString(),
        'user_id' => $admin->id,
    ]);

    $adjustment = StockAdjustment::create([
        'adjustment_code' => 'AJU-0001',
        'product_variant_id' => $variant->id,
        'adjustment_type' => 'incremento',
        'quantity' => 2,
        'stock_before' => 20,
        'stock_after' => 22,
        'reason' => 'Ajuste inicial de prueba',
        'adjustment_date' => now()->toDateString(),
        'user_id' => $admin->id,
    ]);

    $movement = StockMovement::create([
        'product_variant_id' => $variant->id,
        'type' => 'ajuste',
        'quantity' => 2,
        'stock_before' => 20,
        'stock_after' => 22,
        'reference_id' => $adjustment->id,
        'reference_type' => 'stock_adjustment',
        'notes' => 'Movimiento de prueba',
        'user_id' => $admin->id,
        'movement_date' => now()->toDateString(),
    ]);

    $this->actingAs($admin);

    $this->get(route('users.index'))->assertOk();
    $this->get(route('users.show', $admin))->assertOk();
    $this->get(route('users.edit', $admin))->assertOk();
    $this->get(route('reports.index'))->assertOk();
    $this->get(route('reports.stock'))->assertOk();
    $this->get(route('reports.movements'))->assertOk();
    $this->get(route('reports.entries'))->assertOk();
    $this->get(route('reports.exits'))->assertOk();
    $this->get(route('reports.adjustments'))->assertOk();
    $this->get(route('movements.index'))->assertOk();
    $this->get(route('movements.show', $movement))->assertOk();
    $this->get(route('entries.index'))->assertOk();
    $this->get(route('entries.create'))->assertOk();
    $this->get(route('entries.show', $entry))->assertOk();
    $this->get(route('exits.index'))->assertOk();
    $this->get(route('exits.create'))->assertOk();
    $this->get(route('exits.show', $exit))->assertOk();
    $this->get(route('adjustments.index'))->assertOk();
    $this->get(route('adjustments.create'))->assertOk();
    $this->get(route('adjustments.show', $adjustment))->assertOk();
});