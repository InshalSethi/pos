<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Temporarily disabled for debugging - permission middleware not registered
        // $this->middleware('permission:products.view')->only(['index', 'show']);
        // $this->middleware('permission:products.create')->only(['store']);
        // $this->middleware('permission:products.edit')->only(['update']);
        // $this->middleware('permission:products.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('parent', 'children')->withCount('products');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by parent categories only
        if ($request->boolean('parent_only')) {
            $query->whereNull('parent_id');
        }

        $categories = $query->orderBy('name')->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create($request->all());
        $category->load('parent', 'children');

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load('parent', 'children', 'products');

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent setting parent to self or descendant
        if ($request->parent_id && $this->wouldCreateCircularReference($category, $request->parent_id)) {
            return response()->json([
                'message' => 'Cannot set parent to self or descendant category'
            ], 422);
        }

        $category->update($request->all());
        $category->load('parent', 'children');

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has products
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with existing products'
            ], 422);
        }

        // Check if category has children
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with subcategories'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Check if setting parent would create circular reference
     */
    private function wouldCreateCircularReference(Category $category, int $parentId): bool
    {
        if ($category->id === $parentId) {
            return true;
        }

        $descendants = $this->getDescendantIds($category);
        return in_array($parentId, $descendants);
    }

    /**
     * Get all descendant category IDs
     */
    private function getDescendantIds(Category $category): array
    {
        $descendants = [];

        foreach ($category->children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getDescendantIds($child));
        }

        return $descendants;
    }
}
