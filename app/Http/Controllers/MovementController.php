<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MovementController extends Controller
{
    /**
     * List stock movements
     */
    public function index(): JsonResponse
    {
        Gate::authorize('movements.read');

        $movements = StockMovement::with('product')
            ->latest()
            ->paginate($this->perPage());

        return response()->json($movements, 200);
    }

    /**
     * Get a stock movement
     */
    public function show(StockMovement $movement): JsonResponse
    {
        Gate::authorize('movements.read');

        $movement->load('product');

        return response()->json($movement, 200);
    }

    /**
     * Register a stock movement for a product
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        Gate::authorize('movements.create');

        $fields = $request->validate([
            'type' => 'required|string|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $before = $product->fresh()->stock;

        $after = match ($fields['type']) {
            'in' => $before + $fields['quantity'],
            'out' => $before - $fields['quantity'],
            'adjustment' => $fields['quantity'],
        };

        if ($fields['type'] === 'out' && $after < 0) {
            return response()->json([
                'message' => 'Insufficient stock.',
                'available' => $before,
            ], 409);
        }

        $movement = DB::transaction(function () use ($product, $fields, $before, $after) {
            return StockMovement::create([
                'product_id' => $product->id,
                'type' => $fields['type'],
                'quantity' => $fields['quantity'],
                'before_quantity' => $before,
                'after_quantity' => $after,
                'reason' => $fields['reason'] ?? null,
            ]);
        });

        $movement->load('product');

        return response()->json($movement, 201);
    }
}
