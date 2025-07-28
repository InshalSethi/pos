<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:expenses.view')->only(['index', 'show']);
        $this->middleware('permission:expenses.create')->only(['store']);
        $this->middleware('permission:expenses.edit')->only(['update']);
        $this->middleware('permission:expenses.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExpenseCategory::with(['parent', 'children']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by parent category
        if ($request->has('parent_id')) {
            if ($request->get('parent_id') === 'null') {
                $query->whereNull('parent_category_id');
            } else {
                $query->where('parent_category_id', $request->get('parent_id'));
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->has('per_page')) {
            $categories = $query->paginate($request->get('per_page', 15));
        } else {
            $categories = $query->get();
        }

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:expense_categories,code',
            'parent_category_id' => 'nullable|exists:expense_categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = ExpenseCategory::create($request->all());
        $category->load(['parent', 'children']);

        return response()->json([
            'message' => 'Expense category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->load(['parent', 'children', 'expenses']);

        return response()->json($expenseCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:expense_categories,code,' . $expenseCategory->id,
            'parent_category_id' => 'nullable|exists:expense_categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent circular reference
        if ($request->has('parent_category_id') && $request->parent_category_id) {
            $parentCategory = ExpenseCategory::find($request->parent_category_id);
            if ($parentCategory && $this->wouldCreateCircularReference($expenseCategory, $parentCategory)) {
                return response()->json([
                    'message' => 'Cannot set parent category as it would create a circular reference'
                ], 422);
            }
        }

        $expenseCategory->update($request->all());
        $expenseCategory->load(['parent', 'children']);

        return response()->json([
            'message' => 'Expense category updated successfully',
            'category' => $expenseCategory
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        // Check if category has expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category that has expenses associated with it'
            ], 422);
        }

        // Check if category has children
        if ($expenseCategory->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category that has subcategories'
            ], 422);
        }

        $expenseCategory->delete();

        return response()->json([
            'message' => 'Expense category deleted successfully'
        ]);
    }

    /**
     * Get category tree structure
     */
    public function tree(): JsonResponse
    {
        $categories = ExpenseCategory::with(['children' => function ($query) {
            $query->active()->orderBy('name');
        }])
        ->whereNull('parent_category_id')
        ->active()
        ->orderBy('name')
        ->get();

        return response()->json($categories);
    }

    /**
     * Check if setting a parent would create circular reference
     */
    private function wouldCreateCircularReference(ExpenseCategory $category, ExpenseCategory $potentialParent): bool
    {
        if ($category->id === $potentialParent->id) {
            return true;
        }

        $children = $category->getAllChildren();
        return $children->contains('id', $potentialParent->id);
    }
}
