<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MovementController extends Controller
{
    public function index(Request $request): Response
    {
        $movements = StockMovement::query()
            ->with(['variant.product:id,name', 'user:id,name'])
            ->latest('movement_date')
            ->paginate(15)
            ->through(fn (StockMovement $movement): array => [
                'id' => $movement->id,
                'date' => $movement->movement_date,
                'type' => $movement->type,
                'product' => $movement->variant?->product?->name,
                'variant' => $movement->variant?->variant_name,
                'quantity' => $movement->quantity,
                'user' => $movement->user?->name,
            ]);

        return Inertia::render('Movements/Index', [
            'movements' => $movements,
        ]);
    }

    public function show(StockMovement $movement): Response
    {
        $movement->load(['variant.product:id,name', 'user:id,name']);

        return Inertia::render('Movements/Show', [
            'movement' => [
                'id' => $movement->id,
                'date' => $movement->movement_date,
                'type' => $movement->type,
                'product' => $movement->variant?->product?->name,
                'variant' => $movement->variant?->variant_name,
                'quantity' => $movement->quantity,
                'user' => $movement->user?->name,
                'reference' => $movement->reference_type.' #'.$movement->reference_id,
            ],
        ]);
    }
}