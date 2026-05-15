<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', StockAdjustment::class);

        $adjustments = StockAdjustment::query()
            ->with(['variant:id,variant_name', 'user:id,name'])
            ->latest('adjustment_date')
            ->paginate(15)
            ->through(fn (StockAdjustment $adjustment): array => [
                'id' => $adjustment->id,
                'adjustment_code' => $adjustment->adjustment_code,
                'adjustment_date' => $adjustment->adjustment_date,
                'variant_name' => $adjustment->variant?->variant_name,
                'adjustment_type' => $adjustment->adjustment_type,
                'quantity' => $adjustment->quantity,
                'user_name' => $adjustment->user?->name,
            ]);

        return Inertia::render('Adjustments/Index', [
            'adjustments' => $adjustments,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StockAdjustment::class);

        return Inertia::render('Adjustments/Create', [
            'products' => $this->productsForForms(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockAdjustment::class);

        $validated = $request->validate([
            'adjustment_date' => ['required', 'date'],
            'adjustment_type' => ['required', Rule::in(['incremento', 'decremento'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:10'],
            'product_variant_id' => ['required', Rule::exists('product_variants', 'id')],
        ]);

        $adjustment = DB::transaction(function () use ($validated, $request): StockAdjustment {
            $variant = ProductVariant::query()
                ->whereKey($validated['product_variant_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $variant->current_stock;
            $stockAfter = $validated['adjustment_type'] === 'decremento'
                ? $stockBefore - $validated['quantity']
                : $stockBefore + $validated['quantity'];

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'No puede aplicar un ajuste que deje el stock en negativo.',
                ]);
            }

            $adjustment = StockAdjustment::create([
                'adjustment_code' => 'AJU-'.now()->format('YmdHis'),
                'product_variant_id' => $variant->id,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'],
                'adjustment_date' => $validated['adjustment_date'],
                'user_id' => $request->user()->id,
            ]);

            $variant->update(['current_stock' => $stockAfter]);

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'type' => 'ajuste',
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_id' => $adjustment->id,
                'reference_type' => 'stock_adjustment',
                'notes' => $validated['reason'],
                'user_id' => $request->user()->id,
                'movement_date' => $validated['adjustment_date'],
            ]);

            return $adjustment;
        });

        return redirect()->route('adjustments.show', $adjustment)->with('success', 'Ajuste registrado correctamente.');
    }

    public function edit(StockAdjustment $adjustment): Response
    {
        $this->authorize('update', $adjustment);

        $adjustment->load('variant.product:id,name');

        return Inertia::render('Adjustments/Edit', [
            'products' => $this->productsForForms(),
            'adjustment' => [
                'id' => $adjustment->id,
                'adjustment_date' => $adjustment->adjustment_date,
                'adjustment_type' => $adjustment->adjustment_type,
                'quantity' => $adjustment->quantity,
                'reason' => $adjustment->reason,
                'product_id' => $adjustment->variant?->product_id,
                'product_variant_id' => $adjustment->product_variant_id,
            ],
        ]);
    }

    public function update(Request $request, StockAdjustment $adjustment): RedirectResponse
    {
        $this->authorize('update', $adjustment);

        $validated = $request->validate([
            'adjustment_date' => ['required', 'date'],
            'adjustment_type' => ['required', Rule::in(['incremento', 'decremento'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:10'],
            'product_variant_id' => ['required', Rule::exists('product_variants', 'id')],
        ]);

        DB::transaction(function () use ($validated, $request, $adjustment): void {
            $variantIds = collect([
                (int) $adjustment->product_variant_id,
                (int) $validated['product_variant_id'],
            ])->unique()->values()->all();

            $variants = ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $originalVariant = $variants->get((int) $adjustment->product_variant_id);
            $newVariant = $variants->get((int) $validated['product_variant_id']);

            if (! $originalVariant || ! $newVariant) {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'La variante seleccionada no es válida.',
                ]);
            }

            $stockAfterRevert = $adjustment->adjustment_type === 'decremento'
                ? $originalVariant->current_stock + $adjustment->quantity
                : $originalVariant->current_stock - $adjustment->quantity;

            if ($stockAfterRevert < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'No es posible editar este ajuste porque el stock actual no permite revertir el ajuste original.',
                ]);
            }

            $originalVariant->update(['current_stock' => $stockAfterRevert]);

            $stockBefore = $newVariant->current_stock;
            $stockAfter = $validated['adjustment_type'] === 'decremento'
                ? $stockBefore - (int) $validated['quantity']
                : $stockBefore + (int) $validated['quantity'];

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'No puede aplicar un ajuste que deje el stock en negativo.',
                ]);
            }

            $adjustment->update([
                'product_variant_id' => $newVariant->id,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity' => (int) $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'],
                'adjustment_date' => $validated['adjustment_date'],
                'user_id' => $request->user()->id,
            ]);

            $newVariant->update(['current_stock' => $stockAfter]);

            StockMovement::query()
                ->where('reference_type', 'stock_adjustment')
                ->where('reference_id', $adjustment->id)
                ->delete();

            StockMovement::create([
                'product_variant_id' => $newVariant->id,
                'type' => 'ajuste',
                'quantity' => (int) $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_id' => $adjustment->id,
                'reference_type' => 'stock_adjustment',
                'notes' => $validated['reason'],
                'user_id' => $request->user()->id,
                'movement_date' => $validated['adjustment_date'],
            ]);
        });

        return redirect()->route('adjustments.show', $adjustment)->with('success', 'Ajuste actualizado correctamente.');
    }

    public function show(StockAdjustment $adjustment): Response
    {
        $this->authorize('view', $adjustment);

        $adjustment->load(['variant:id,variant_name', 'user:id,name']);

        return Inertia::render('Adjustments/Show', [
            'adjustment' => [
                'id' => $adjustment->id,
                'adjustment_code' => $adjustment->adjustment_code,
                'adjustment_date' => $adjustment->adjustment_date,
                'variant_name' => $adjustment->variant?->variant_name,
                'adjustment_type' => $adjustment->adjustment_type,
                'quantity' => $adjustment->quantity,
                'stock_before' => $adjustment->stock_before,
                'stock_after' => $adjustment->stock_after,
                'reason' => $adjustment->reason,
                'user_name' => $adjustment->user?->name,
            ],
        ]);
    }

    private function productsForForms()
    {
        return Product::query()
            ->with(['variants:id,product_id,variant_name,sku,current_stock'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}