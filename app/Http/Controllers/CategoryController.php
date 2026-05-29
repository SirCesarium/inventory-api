<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * List categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->latest()
            ->paginate($this->perPage());

        return response()->json($categories, 200);
    }

    /**
     * Create category
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('categories.create');

        $fields = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($fields);

        return response()->json($category, 201);
    }

    /**
     * Get category
     */
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');

        return response()->json($category, 200);
    }

    /**
     * Update category
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('categories.update');

        $fields = $request->validate([
            'name' => 'string|max:255|unique:categories,name,'.$category->id,
            'description' => 'nullable|string',
        ]);

        $category->fill($fields)->save();

        return response()->json($category, 200);
    }

    /**
     * Delete category
     */
    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('categories.delete');

        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Category has associated products.',
            ], 409);
        }

        Category::destroy($category->id);

        return response()->json(['message' => 'Category deleted.'], 200);
    }

    /**
     * Permanently delete category
     */
    public function forceDestroy(Category $category): JsonResponse
    {
        return $this->performForceDestroy($category, 'categories.delete');
    }
}
