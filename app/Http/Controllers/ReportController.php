<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\WarehouseEntry;
use App\Models\WarehouseExit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }

    public function stock(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $stocksQuery = ProductVariant::query()
            ->with('product:id,name,min_stock')
            ->when(
                ! empty($filters['q']),
                function (Builder $query) use ($filters): void {
                    $search = trim((string) $filters['q']);

                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('variant_name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhereHas('product', fn (Builder $productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
                    });
                }
            )
            ->orderBy('sku')
            ->orderBy('id');

        $stocks = $stocksQuery
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'product_name' => $variant->product?->name,
                'variant_name' => $variant->variant_name,
                'sku' => $variant->sku,
                'current_stock' => $variant->current_stock,
                'min_stock' => $variant->min_stock,
                'status' => $variant->current_stock <= ($variant->min_stock ?? 0) ? 'Bajo mínimo' : 'Disponible',
            ]);

        return Inertia::render('Reports/Stock', [
            'stocks' => $stocks,
            'filters' => [
                'q' => $filters['q'] ?? null,
            ],
        ]);
    }

    public function movements(Request $request): Response
    {
        $filters = $this->validatedReportFilters($request);

        $movementsQuery = StockMovement::query()
            ->with(['variant.product:id,name', 'user:id,name'])
            ->when(! empty($filters['from']), fn (Builder $query) => $query->whereDate('movement_date', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn (Builder $query) => $query->whereDate('movement_date', '<=', $filters['to']))
            ->when(
                ! empty($filters['q']),
                function (Builder $query) use ($filters): void {
                    $search = trim((string) $filters['q']);

                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('type', 'like', "%{$search}%")
                            ->orWhere('reference_type', 'like', "%{$search}%")
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('variant', fn (Builder $variantQuery) => $variantQuery->where('variant_name', 'like', "%{$search}%"))
                            ->orWhereHas('variant.product', fn (Builder $productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
                    });
                }
            )
            ->latest('movement_date')
            ->latest('id');

        $movements = $movementsQuery
            ->paginate(15)
            ->withQueryString()
            ->through(fn (StockMovement $movement): array => [
                'id' => $movement->id,
                'date' => $movement->movement_date,
                'type' => $movement->type,
                'product_name' => $movement->variant?->product?->name,
                'variant_name' => $movement->variant?->variant_name,
                'quantity' => $movement->quantity,
                'user_name' => $movement->user?->name,
                'reference' => $movement->reference_type.' #'.$movement->reference_id,
            ]);

        return Inertia::render('Reports/Movements', [
            'movements' => $movements,
            'filters' => [
                'from' => $filters['from'] ?? null,
                'to' => $filters['to'] ?? null,
                'q' => $filters['q'] ?? null,
            ],
        ]);
    }

    public function entries(Request $request): Response
    {
        $filters = $this->validatedReportFilters($request);

        $entriesQuery = WarehouseEntry::query()
            ->with(['user:id,name', 'items'])
            ->when(! empty($filters['from']), fn (Builder $query) => $query->whereDate('entry_date', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn (Builder $query) => $query->whereDate('entry_date', '<=', $filters['to']))
            ->when(
                ! empty($filters['q']),
                function (Builder $query) use ($filters): void {
                    $search = trim((string) $filters['q']);

                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('entry_code', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%")
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                    });
                }
            )
            ->latest('entry_date')
            ->latest('id');

        $entries = $entriesQuery
            ->paginate(15)
            ->withQueryString()
            ->through(fn (WarehouseEntry $entry): array => [
                'id' => $entry->id,
                'entry_code' => $entry->entry_code,
                'entry_date' => $entry->entry_date,
                'supplier_name' => 'Interno',
                'total_items' => $entry->items->count(),
                'user_name' => $entry->user?->name,
            ]);

        return Inertia::render('Reports/Entries', [
            'entries' => $entries,
            'filters' => [
                'from' => $filters['from'] ?? null,
                'to' => $filters['to'] ?? null,
                'q' => $filters['q'] ?? null,
            ],
        ]);
    }

    public function exits(Request $request): Response
    {
        $filters = $this->validatedReportFilters($request);

        $exitsQuery = WarehouseExit::query()
            ->with(['user:id,name', 'items'])
            ->when(! empty($filters['from']), fn (Builder $query) => $query->whereDate('exit_date', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn (Builder $query) => $query->whereDate('exit_date', '<=', $filters['to']))
            ->when(
                ! empty($filters['q']),
                function (Builder $query) use ($filters): void {
                    $search = trim((string) $filters['q']);

                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('exit_code', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%")
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                    });
                }
            )
            ->latest('exit_date')
            ->latest('id');

        $exits = $exitsQuery
            ->paginate(15)
            ->withQueryString()
            ->through(fn (WarehouseExit $exit): array => [
                'id' => $exit->id,
                'exit_code' => $exit->exit_code,
                'exit_date' => $exit->exit_date,
                'destination' => 'Despacho',
                'total_items' => $exit->items->count(),
                'user_name' => $exit->user?->name,
            ]);

        return Inertia::render('Reports/Exits', [
            'exits' => $exits,
            'filters' => [
                'from' => $filters['from'] ?? null,
                'to' => $filters['to'] ?? null,
                'q' => $filters['q'] ?? null,
            ],
        ]);
    }

    public function adjustments(Request $request): Response
    {
        $filters = $this->validatedReportFilters($request);

        $adjustmentsQuery = StockAdjustment::query()
            ->with(['variant:id,variant_name', 'user:id,name'])
            ->when(! empty($filters['from']), fn (Builder $query) => $query->whereDate('adjustment_date', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn (Builder $query) => $query->whereDate('adjustment_date', '<=', $filters['to']))
            ->when(
                ! empty($filters['q']),
                function (Builder $query) use ($filters): void {
                    $search = trim((string) $filters['q']);

                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('adjustment_code', 'like', "%{$search}%")
                            ->orWhere('adjustment_type', 'like', "%{$search}%")
                            ->orWhere('reason', 'like', "%{$search}%")
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('variant', fn (Builder $variantQuery) => $variantQuery->where('variant_name', 'like', "%{$search}%"));
                    });
                }
            )
            ->latest('adjustment_date')
            ->latest('id');

        $adjustments = $adjustmentsQuery
            ->paginate(15)
            ->withQueryString()
            ->through(fn (StockAdjustment $adjustment): array => [
                'id' => $adjustment->id,
                'adjustment_code' => $adjustment->adjustment_code,
                'adjustment_date' => $adjustment->adjustment_date,
                'variant_name' => $adjustment->variant?->variant_name,
                'adjustment_type' => $adjustment->adjustment_type,
                'quantity' => $adjustment->quantity,
                'user_name' => $adjustment->user?->name,
            ]);

        return Inertia::render('Reports/Adjustments', [
            'adjustments' => $adjustments,
            'filters' => [
                'from' => $filters['from'] ?? null,
                'to' => $filters['to'] ?? null,
                'q' => $filters['q'] ?? null,
            ],
        ]);
    }

    private function validatedReportFilters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
