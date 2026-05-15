<?php

use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseEntryController;
use App\Http\Controllers\WarehouseExitController;
use App\Models\ProductVariant;
use App\Models\WarehouseEntry;
use App\Models\WarehouseExit;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $stockSnapshot = ProductVariant::query()
            ->with('product:id,name,min_stock')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select('product_variants.*')
            ->orderByRaw('CASE WHEN product_variants.current_stock <= products.min_stock THEN 0 ELSE 1 END ASC')
            ->orderByRaw('(SELECT COALESCE(SUM(quantity), 0) FROM warehouse_exit_items WHERE product_variant_id = product_variants.id) DESC')
            ->limit(12)
            ->get()
            ->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'product_name' => $variant->product?->name,
                'variant_name' => $variant->variant_name,
                'sku' => $variant->sku,
                'current_stock' => $variant->current_stock,
                'min_stock' => $variant->product?->min_stock,
                'status' => $variant->current_stock <= ($variant->product?->min_stock ?? 0) ? 'Bajo mínimo' : 'Disponible',
                'image_url' => $variant->image_url,
            ])
            ->values();

        // Métricas del día
        $today = now('America/Lima')->toDateString();
        $alertCount = $stockSnapshot->where('status', 'Bajo mínimo')->count();
        $entriesCount = WarehouseEntry::whereDate('entry_date', $today)->count();
        $exitsCount = WarehouseExit::whereDate('exit_date', $today)->count();
        $movementsCount = $entriesCount + $exitsCount;

        return Inertia::render('dashboard', [
            'stockSnapshot' => $stockSnapshot,
            'dashboardMetrics' => [
                'alertCount' => $alertCount,
                'entriesCount' => $entriesCount,
                'exitsCount' => $exitsCount,
                'movementsCount' => $movementsCount,
            ],
        ]);
    })->name('dashboard');

    // Productos
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->middleware('role:admin')->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->middleware('role:admin')->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->middleware('role:admin')->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->middleware('role:admin')->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('role:admin')->name('products.destroy');

    // Variantes
    Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->middleware('role:admin')->name('products.variants.store');
    Route::put('variants/{variant}', [ProductVariantController::class, 'update'])->middleware('role:admin')->name('variants.update');
    Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy'])->middleware('role:admin')->name('variants.destroy');

    // Usuarios
    Route::resource('users', UserController::class);

    // Reportes
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/movements', [ReportController::class, 'movements'])->name('reports.movements');
    Route::get('reports/entries', [ReportController::class, 'entries'])->name('reports.entries');
    Route::get('reports/exits', [ReportController::class, 'exits'])->name('reports.exits');
    Route::get('reports/adjustments', [ReportController::class, 'adjustments'])->name('reports.adjustments');

    // Movimientos
    Route::get('movements', [MovementController::class, 'index'])->name('movements.index');
    Route::get('movements/{movement}', [MovementController::class, 'show'])->name('movements.show');

    // Entradas
    Route::get('entries', [WarehouseEntryController::class, 'index'])->name('entries.index');
    Route::get('entries/create', [WarehouseEntryController::class, 'create'])->name('entries.create');
    Route::post('entries', [WarehouseEntryController::class, 'store'])->name('entries.store');
    Route::get('entries/{entry}/edit', [WarehouseEntryController::class, 'edit'])->name('entries.edit');
    Route::put('entries/{entry}', [WarehouseEntryController::class, 'update'])->name('entries.update');
    Route::get('entries/{entry}', [WarehouseEntryController::class, 'show'])->name('entries.show');

    // Salidas
    Route::get('exits', [WarehouseExitController::class, 'index'])->name('exits.index');
    Route::get('exits/create', [WarehouseExitController::class, 'create'])->name('exits.create');
    Route::post('exits', [WarehouseExitController::class, 'store'])->name('exits.store');
    Route::get('exits/{exit}/edit', [WarehouseExitController::class, 'edit'])->name('exits.edit');
    Route::put('exits/{exit}', [WarehouseExitController::class, 'update'])->name('exits.update');
    Route::get('exits/{exit}', [WarehouseExitController::class, 'show'])->name('exits.show');

    // Ajustes
    Route::get('adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
    Route::get('adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
    Route::post('adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
    Route::get('adjustments/{adjustment}/edit', [StockAdjustmentController::class, 'edit'])->name('adjustments.edit');
    Route::put('adjustments/{adjustment}', [StockAdjustmentController::class, 'update'])->name('adjustments.update');
    Route::get('adjustments/{adjustment}', [StockAdjustmentController::class, 'show'])->name('adjustments.show');
});

require __DIR__.'/settings.php';
