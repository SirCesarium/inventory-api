<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * List products
     */
    public function index(): JsonResponse
    {
        $products = Product::with('category')
            ->latest()
            ->paginate($this->perPage());

        return response()->json($products, 200);
    }

    /**
     * Create product
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('manage-products');

        $fields = $request->validate([
            'sku' => 'required|string|max:255|unique:products',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($fields);

        return response()->json($product->load('category'), 201);
    }

    /**
     * Get product
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return response()->json($product, 200);
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        Gate::authorize('manage-products');

        $fields = $request->validate([
            'sku' => 'string|max:255|unique:products,sku,'.$product->id,
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
            'category_id' => 'exists:categories,id',
        ]);

        $product->fill($fields)->save();

        return response()->json($product->load('category'), 200);
    }

    /**
     * Delete product
     */
    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('manage-products');

        Product::destroy($product->id);

        return response()->json(['message' => 'Product deleted.'], 200);
    }
}
