<?php

use App\Http\Controllers\MovementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseEntryController;
use App\Http\Controllers\WarehouseExitController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // Productos
    Route::get('products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [\App\Http\Controllers\ProductController::class, 'create'])->middleware('role:admin')->name('products.create');
    Route::post('products', [\App\Http\Controllers\ProductController::class, 'store'])->middleware('role:admin')->name('products.store');
    Route::get('products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->middleware('role:admin')->name('products.edit');
    Route::put('products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->middleware('role:admin')->name('products.update');
    Route::delete('products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->middleware('role:admin')->name('products.destroy');

    // Variantes
    Route::post('products/{product}/variants', [\App\Http\Controllers\ProductVariantController::class, 'store'])->middleware('role:admin')->name('products.variants.store');
    Route::put('variants/{variant}', [\App\Http\Controllers\ProductVariantController::class, 'update'])->middleware('role:admin')->name('variants.update');
    Route::delete('variants/{variant}', [\App\Http\Controllers\ProductVariantController::class, 'destroy'])->middleware('role:admin')->name('variants.destroy');

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
