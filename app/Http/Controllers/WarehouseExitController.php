<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\WarehouseExit;
use App\Models\WarehouseExitItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseExitController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', WarehouseExit::class);

        $exits = WarehouseExit::query()
            ->with('user:id,name')
            ->latest('exit_date')
            ->paginate(15)
            ->through(fn (WarehouseExit $exit): array => [
                'id' => $exit->id,
                'exit_code' => $exit->exit_code,
                'exit_date' => $exit->exit_date,
                'user_name' => $exit->user?->name,
            ]);

        return Inertia::render('Exits/Index', [
            'exits' => $exits,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', WarehouseExit::class);

        return Inertia::render('Exits/Create', [
            'products' => $this->productsForForms(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WarehouseExit::class);

        $validated = $request->validate([
            'exit_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $exit = DB::transaction(function () use ($validated, $request): WarehouseExit {
            $variantIds = collect($validated['items'])
                ->pluck('variant_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            $variants = ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $exit = WarehouseExit::create([
                'exit_code' => 'SAL-'.now()->format('YmdHis'),
                'exit_date' => $validated['exit_date'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);

            foreach ($validated['items'] as $index => $item) {
                $variant = $variants->get((int) $item['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        "items.{$index}.variant_id" => 'La variante seleccionada no es válida.',
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $stockBefore = $variant->current_stock;

                if ($quantity > $stockBefore) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => 'No hay stock suficiente para la variante seleccionada.',
                    ]);
                }

                $stockAfter = $stockBefore - $quantity;

                WarehouseExitItem::create([
                    'warehouse_exit_id' => $exit->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);

                $variant->update([
                    'current_stock' => $stockAfter,
                ]);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'salida',
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_id' => $exit->id,
                    'reference_type' => 'warehouse_exit',
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => $request->user()->id,
                    'movement_date' => $validated['exit_date'],
                ]);
            }

            return $exit;
        });

        return redirect()->route('exits.show', $exit)->with('success', 'Salida registrada correctamente.');
    }

    public function edit(WarehouseExit $exit): Response
    {
        $this->authorize('update', $exit);

        $exit->load(['items.variant.product:id,name']);

        return Inertia::render('Exits/Edit', [
            'products' => $this->productsForForms(),
            'exit' => [
                'id' => $exit->id,
                'exit_date' => $exit->exit_date,
                'notes' => $exit->notes,
                'items' => $exit->items->map(fn (WarehouseExitItem $item): array => [
                    'product_id' => $item->variant?->product_id,
                    'variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                ])->all(),
            ],
        ]);
    }

    public function update(Request $request, WarehouseExit $exit): RedirectResponse
    {
        $this->authorize('update', $exit);

        $validated = $request->validate([
            'exit_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated, $request, $exit): void {
            $exit->load('items');

            $previousItems = $exit->items->map(fn (WarehouseExitItem $item): array => [
                'variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ]);

            $newItems = collect($validated['items'])->map(fn (array $item): array => [
                'variant_id' => (int) $item['variant_id'],
                'quantity' => (int) $item['quantity'],
            ]);

            $variantIds = $previousItems->pluck('variant_id')
                ->merge($newItems->pluck('variant_id'))
                ->unique()
                ->values()
                ->all();

            $variants = ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($previousItems as $index => $previousItem) {
                $variant = $variants->get((int) $previousItem['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        "items.{$index}.variant_id" => 'La variante seleccionada no es válida.',
                    ]);
                }

                $variant->update([
                    'current_stock' => $variant->current_stock + (int) $previousItem['quantity'],
                ]);
            }

            $exit->update([
                'exit_date' => $validated['exit_date'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);

            WarehouseExitItem::query()->where('warehouse_exit_id', $exit->id)->delete();
            StockMovement::query()
                ->where('reference_type', 'warehouse_exit')
                ->where('reference_id', $exit->id)
                ->delete();

            foreach ($newItems as $index => $item) {
                $variant = $variants->get((int) $item['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        "items.{$index}.variant_id" => 'La variante seleccionada no es válida.',
                    ]);
                }

                $stockBefore = $variant->current_stock;

                if ((int) $item['quantity'] > $stockBefore) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => 'No hay stock suficiente para la variante seleccionada.',
                    ]);
                }

                $stockAfter = $stockBefore - (int) $item['quantity'];

                WarehouseExitItem::create([
                    'warehouse_exit_id' => $exit->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => (int) $item['quantity'],
                ]);

                $variant->update([
                    'current_stock' => $stockAfter,
                ]);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'salida',
                    'quantity' => (int) $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_id' => $exit->id,
                    'reference_type' => 'warehouse_exit',
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => $request->user()->id,
                    'movement_date' => $validated['exit_date'],
                ]);
            }
        });

        return redirect()->route('exits.show', $exit)->with('success', 'Salida actualizada correctamente.');
    }

    public function show(WarehouseExit $exit): Response
    {
        $this->authorize('view', $exit);

        $exit->load(['user:id,name', 'items.variant.product:id,name']);

        return Inertia::render('Exits/Show', [
            'exit' => [
                'id' => $exit->id,
                'exit_code' => $exit->exit_code,
                'exit_date' => $exit->exit_date,
                'notes' => $exit->notes,
                'user_name' => $exit->user?->name,
                'items' => $exit->items->map(fn (WarehouseExitItem $item): array => [
                    'id' => $item->id,
                    'product_name' => $item->variant?->product?->name,
                    'variant_name' => $item->variant?->variant_name,
                    'quantity' => $item->quantity,
                ])->all(),
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