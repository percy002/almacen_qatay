<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\WarehouseEntry;
use App\Models\WarehouseEntryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseEntryController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', WarehouseEntry::class);

        $entries = WarehouseEntry::query()
            ->with('user:id,name')
            ->latest('entry_date')
            ->paginate(15)
            ->through(fn (WarehouseEntry $entry): array => [
                'id' => $entry->id,
                'entry_code' => $entry->entry_code,
                'entry_date' => $entry->entry_date,
                'user_name' => $entry->user?->name,
            ]);

        return Inertia::render('Entries/Index', [
            'entries' => $entries,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', WarehouseEntry::class);

        return Inertia::render('Entries/Create', [
            'products' => $this->productsForForms(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WarehouseEntry::class);

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $entry = DB::transaction(function () use ($validated, $request): WarehouseEntry {
            $variantIds = collect($validated['items'])
                ->pluck('variant_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            $variants = ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $entry = WarehouseEntry::create([
                'entry_code' => 'ENT-'.now()->format('YmdHis'),
                'entry_date' => $validated['entry_date'],
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
                $stockAfter = $stockBefore + $quantity;

                WarehouseEntryItem::create([
                    'warehouse_entry_id' => $entry->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);

                $variant->update([
                    'current_stock' => $stockAfter,
                ]);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'entrada',
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_id' => $entry->id,
                    'reference_type' => 'warehouse_entry',
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => $request->user()->id,
                    'movement_date' => $validated['entry_date'],
                ]);
            }

            return $entry;
        });

        return redirect()->route('entries.show', $entry)->with('success', 'Entrada registrada correctamente.');
    }

    public function edit(WarehouseEntry $entry): Response
    {
        $this->authorize('update', $entry);

        $entry->load(['items.variant.product:id,name']);

        return Inertia::render('Entries/Edit', [
            'products' => $this->productsForForms(),
            'entry' => [
                'id' => $entry->id,
                'entry_date' => $entry->entry_date,
                'notes' => $entry->notes,
                'items' => $entry->items->map(fn (WarehouseEntryItem $item): array => [
                    'product_id' => $item->variant?->product_id,
                    'variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                ])->all(),
            ],
        ]);
    }

    public function update(Request $request, WarehouseEntry $entry): RedirectResponse
    {
        $this->authorize('update', $entry);

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated, $request, $entry): void {
            $entry->load('items');

            $previousItems = $entry->items->map(fn (WarehouseEntryItem $item): array => [
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

                $stockAfterRevert = $variant->current_stock - (int) $previousItem['quantity'];

                if ($stockAfterRevert < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'No es posible editar esta entrada porque ya se consumió parte del stock generado originalmente.',
                    ]);
                }

                $variant->update([
                    'current_stock' => $stockAfterRevert,
                ]);
            }

            $entry->update([
                'entry_date' => $validated['entry_date'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);

            WarehouseEntryItem::query()->where('warehouse_entry_id', $entry->id)->delete();
            StockMovement::query()
                ->where('reference_type', 'warehouse_entry')
                ->where('reference_id', $entry->id)
                ->delete();

            foreach ($newItems as $index => $item) {
                $variant = $variants->get((int) $item['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        "items.{$index}.variant_id" => 'La variante seleccionada no es válida.',
                    ]);
                }

                $stockBefore = $variant->current_stock;
                $stockAfter = $stockBefore + (int) $item['quantity'];

                WarehouseEntryItem::create([
                    'warehouse_entry_id' => $entry->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => (int) $item['quantity'],
                ]);

                $variant->update([
                    'current_stock' => $stockAfter,
                ]);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'entrada',
                    'quantity' => (int) $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_id' => $entry->id,
                    'reference_type' => 'warehouse_entry',
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => $request->user()->id,
                    'movement_date' => $validated['entry_date'],
                ]);
            }
        });

        return redirect()->route('entries.show', $entry)->with('success', 'Entrada actualizada correctamente.');
    }

    public function show(WarehouseEntry $entry): Response
    {
        $this->authorize('view', $entry);

        $entry->load(['user:id,name', 'items.variant.product:id,name']);

        return Inertia::render('Entries/Show', [
            'entry' => [
                'id' => $entry->id,
                'entry_code' => $entry->entry_code,
                'entry_date' => $entry->entry_date,
                'notes' => $entry->notes,
                'user_name' => $entry->user?->name,
                'items' => $entry->items->map(fn (WarehouseEntryItem $item): array => [
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