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
    Route::get('tablero', function () {
        $stockSnapshot = ProductVariant::query()
            ->with('product:id,name')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select('product_variants.*')
            ->orderByRaw('CASE WHEN product_variants.current_stock <= product_variants.min_stock THEN 0 ELSE 1 END ASC')
            ->orderByRaw('(SELECT COALESCE(SUM(quantity), 0) FROM warehouse_exit_items WHERE product_variant_id = product_variants.id) DESC')
            ->limit(12)
            ->get()
            ->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'product_name' => $variant->product?->name,
                'variant_name' => $variant->variant_name,
                'sku' => $variant->sku,
                'current_stock' => $variant->current_stock,
                'min_stock' => $variant->min_stock,
                'status' => $variant->current_stock <= ($variant->min_stock ?? 0) ? 'Bajo mínimo' : 'Disponible',
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
    Route::get('productos', [ProductController::class, 'index'])->name('products.index');
    Route::get('productos/crear', [ProductController::class, 'create'])->middleware('role:admin')->name('products.create');
    Route::post('productos', [ProductController::class, 'store'])->middleware('role:admin')->name('products.store');
    Route::get('productos/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('productos/{product}/editar', [ProductController::class, 'edit'])->middleware('role:admin')->name('products.edit');
    Route::put('productos/{product}', [ProductController::class, 'update'])->middleware('role:admin')->name('products.update');
    Route::delete('productos/{product}', [ProductController::class, 'destroy'])->middleware('role:admin')->name('products.destroy');

    // Variantes
    Route::post('productos/{product}/variantes', [ProductVariantController::class, 'store'])->middleware('role:admin')->name('products.variants.store');
    Route::put('variantes/{variant}', [ProductVariantController::class, 'update'])->middleware('role:admin')->name('variants.update');
    Route::delete('variantes/{variant}', [ProductVariantController::class, 'destroy'])->middleware('role:admin')->name('variants.destroy');

    // Usuarios
    Route::resource('usuarios', UserController::class);

    // Reportes
    Route::get('reportes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reportes/inventario', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reportes/movimientos', [ReportController::class, 'movements'])->name('reports.movements');
    Route::get('reportes/entradas', [ReportController::class, 'entries'])->name('reports.entries');
    Route::get('reportes/salidas', [ReportController::class, 'exits'])->name('reports.exits');
    Route::get('reportes/ajustes', [ReportController::class, 'adjustments'])->name('reports.adjustments');

    // Movimientos
    Route::get('movimientos', [MovementController::class, 'index'])->name('movements.index');
    Route::get('movimientos/{movement}', [MovementController::class, 'show'])->name('movements.show');

    // Entradas
    Route::get('entradas', [WarehouseEntryController::class, 'index'])->name('entries.index');
    Route::get('entradas/crear', [WarehouseEntryController::class, 'create'])->name('entries.create');
    Route::post('entradas', [WarehouseEntryController::class, 'store'])->name('entries.store');
    Route::get('entradas/{entry}/editar', [WarehouseEntryController::class, 'edit'])->name('entries.edit');
    Route::put('entradas/{entry}', [WarehouseEntryController::class, 'update'])->name('entries.update');
    Route::get('entradas/{entry}', [WarehouseEntryController::class, 'show'])->name('entries.show');

    // Salidas
    Route::get('salidas', [WarehouseExitController::class, 'index'])->name('exits.index');
    Route::get('salidas/crear', [WarehouseExitController::class, 'create'])->name('exits.create');
    Route::post('salidas', [WarehouseExitController::class, 'store'])->name('exits.store');
    Route::get('salidas/{exit}/editar', [WarehouseExitController::class, 'edit'])->name('exits.edit');
    Route::put('salidas/{exit}', [WarehouseExitController::class, 'update'])->name('exits.update');
    Route::get('salidas/{exit}', [WarehouseExitController::class, 'show'])->name('exits.show');

    // Ajustes
    Route::get('ajustes', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
    Route::get('ajustes/crear', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
    Route::post('ajustes', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
    Route::get('ajustes/{adjustment}/editar', [StockAdjustmentController::class, 'edit'])->name('adjustments.edit');
    Route::put('ajustes/{adjustment}', [StockAdjustmentController::class, 'update'])->name('adjustments.update');
    Route::get('ajustes/{adjustment}', [StockAdjustmentController::class, 'show'])->name('adjustments.show');
});

require __DIR__.'/settings.php';
