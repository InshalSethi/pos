<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $query = Category::with('parent', 'children', 'tax')->withCount('products');

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
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'tax_id' => [
                'nullable',
                Rule::exists('taxes', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'discount_type' => 'nullable|string|in:percentage,flat,markup_percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = new Category();
        $category->fill($request->all());
        $category->company_id = $companyId;
        $category->save();
        $category->load('parent', 'children', 'tax');

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
        $category->load('parent', 'children', 'tax', 'products');

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id)->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'tax_id' => [
                'nullable',
                Rule::exists('taxes', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'discount_type' => 'nullable|string|in:percentage,flat,markup_percentage',
            'discount_value' => 'nullable|numeric|min:0',
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
        $category->load('parent', 'children', 'tax');

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
     * Apply pricing rules to all products in the category.
     */
    public function applyPricing(Request $request, Category $category): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'action_type' => 'required|string|in:markup_percentage,discount_percentage,set_tax',
            'value' => 'required_if:action_type,markup_percentage,discount_percentage|numeric|min:0',
            'tax_id' => 'required_if:action_type,set_tax|nullable|exists:taxes,id',
            'apply_to_subcategories' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $actionType = $request->input('action_type');
        $value = $request->input('value', 0);
        $taxId = $request->input('tax_id');
        $applyToSubcategories = $request->boolean('apply_to_subcategories');

        // Gather all category IDs (current + children if requested)
        $categoryIds = [$category->id];
        if ($applyToSubcategories) {
            $categoryIds = array_merge($categoryIds, $this->getDescendantIds($category));
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($actionType === 'markup_percentage') {
                $products = Product::whereIn('category_id', $categoryIds)
                    ->where('company_id', $companyId)
                    ->get();

                foreach ($products as $product) {
                    $costPrice = floatval($product->cost_price);
                    $basePrice = $costPrice > 0 ? $costPrice : floatval($product->selling_price);
                    $newSellingPrice = $basePrice * (1 + $value / 100);

                    $product->update([
                        'selling_price' => $newSellingPrice,
                        'markup_percentage' => $value,
                    ]);
                }

                // Update category default settings
                Category::whereIn('id', $categoryIds)
                    ->where('company_id', $companyId)
                    ->update([
                        'discount_type' => 'markup_percentage',
                        'discount_value' => $value
                    ]);

            } elseif ($actionType === 'discount_percentage') {
                Product::whereIn('category_id', $categoryIds)
                    ->where('company_id', $companyId)
                    ->update([
                        'discount_type' => 'percentage',
                        'discount_value' => $value
                    ]);

                // Update category default settings
                Category::whereIn('id', $categoryIds)
                    ->where('company_id', $companyId)
                    ->update([
                        'discount_type' => 'percentage',
                        'discount_value' => $value
                    ]);

            } elseif ($actionType === 'set_tax') {
                $tax = \App\Models\Tax::where('id', $taxId)
                    ->where('company_id', $companyId)
                    ->first();

                if ($tax) {
                    // Update products with tax rate value
                    Product::whereIn('category_id', $categoryIds)
                        ->where('company_id', $companyId)
                        ->update([
                            'tax_rate' => $tax->value
                        ]);

                    // Set category default tax_id
                    Category::whereIn('id', $categoryIds)
                        ->where('company_id', $companyId)
                        ->update([
                            'tax_id' => $tax->id
                        ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Pricing rules applied successfully to products.',
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Failed to apply pricing rules.',
                'error' => $e->getMessage()
            ], 500);
        }
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
