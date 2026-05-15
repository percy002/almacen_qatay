<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseEntry;
use Inertia\Testing\AssertableInertia as Assert;

test('entries report applies date range and search filters', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    WarehouseEntry::create([
        'entry_code' => 'ENT-FILTER-OK',
        'entry_date' => '2026-05-10',
        'notes' => 'Ingreso principal para filtro',
        'user_id' => $admin->id,
    ]);

    WarehouseEntry::create([
        'entry_code' => 'ENT-FILTER-NO',
        'entry_date' => '2026-04-01',
        'notes' => 'Registro fuera de rango',
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('reports.entries', [
            'from' => '2026-05-01',
            'to' => '2026-05-31',
            'q' => 'FILTER-OK',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Entries')
            ->where('filters.from', '2026-05-01')
            ->where('filters.to', '2026-05-31')
            ->where('filters.q', 'FILTER-OK')
            ->has('entries.data', 1)
            ->where('entries.data.0.entry_code', 'ENT-FILTER-OK')
        );
});

    test('movements report applies search filter', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $product = Product::create([
        'name' => 'Tela exportable',
        'internal_code' => 'TEX-001',
        'description' => 'Producto para prueba de reporte',
        'min_stock' => 2,
        'status' => 'activo',
    ]);

    $matchingVariant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Filtro Azul',
        'sku' => 'TEX-AZ',
        'current_stock' => 10,
    ]);

    $otherVariant = ProductVariant::create([
        'product_id' => $product->id,
        'variant_name' => 'Filtro Rojo',
        'sku' => 'TEX-RJ',
        'current_stock' => 10,
    ]);

    StockMovement::create([
        'product_variant_id' => $matchingVariant->id,
        'type' => 'entrada',
        'quantity' => 4,
        'stock_before' => 6,
        'stock_after' => 10,
        'reference_id' => 1,
        'reference_type' => 'warehouse_entry',
        'notes' => 'Movimiento visible',
        'user_id' => $admin->id,
        'movement_date' => '2026-05-11',
    ]);

    StockMovement::create([
        'product_variant_id' => $otherVariant->id,
        'type' => 'entrada',
        'quantity' => 2,
        'stock_before' => 8,
        'stock_after' => 10,
        'reference_id' => 2,
        'reference_type' => 'warehouse_entry',
        'notes' => 'Movimiento oculto',
        'user_id' => $admin->id,
        'movement_date' => '2026-05-11',
    ]);

    $this->actingAs($admin)
        ->get(route('reports.movements', [
            'q' => 'Azul',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Movements')
            ->where('filters.q', 'Azul')
            ->has('movements.data', 1)
            ->where('movements.data.0.variant_name', 'Filtro Azul')
        );
});
