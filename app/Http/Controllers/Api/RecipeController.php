<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\RecipeIngredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show', 'getByProduct']);
        $this->middleware('permission:products.create')->only(['store']);
        $this->middleware('permission:products.edit')->only(['update']);
        $this->middleware('permission:products.delete')->only(['destroy']);
    }

    /**
     * List all recipes.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $query = ProductRecipe::with([
            'product:id,name,sku,item_type,cost_price,selling_price',
            'variation:id,product_id,combination_key,variation_name_string,cost_price,retail_price',
            'unit:id,name,short_name',
            'ingredients.rawMaterial:id,name,sku,cost_price,unit_of_measure',
            'ingredients.variation:id,product_id,combination_key,variation_name_string,cost_price',
            'ingredients.unit:id,name,short_name'
        ])->where('company_id', $companyId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['name', 'yield_quantity', 'created_at', 'is_active'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->get('per_page', 15);
        $recipes = $query->paginate($perPage);

        // Append calculated total ingredient cost to each recipe item
        $recipes->getCollection()->transform(function ($recipe) {
            $recipe->total_cost = $recipe->calculateTotalCost();
            $recipe->unit_cost = $recipe->yield_quantity > 0 ? round($recipe->total_cost / (float)$recipe->yield_quantity, 2) : 0;
            return $recipe;
        });

        return response()->json($recipes);
    }

    /**
     * Store a new recipe.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'name' => 'required|string|max:255',
            'yield_quantity' => 'required|numeric|min:0.0001',
            'unit_id' => 'nullable|exists:units,id',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.raw_material_id' => 'required|exists:products,id',
            'ingredients.*.raw_material_variation_id' => 'nullable|exists:product_variations,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.0001',
            'ingredients.*.unit_id' => 'nullable|exists:units,id',
            'ingredients.*.waste_percentage' => 'nullable|numeric|min:0|max:100',
            'ingredients.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Deactivate previous recipes if setting this one active
            if ($request->boolean('is_active', true)) {
                ProductRecipe::where('company_id', $companyId)
                    ->where('product_id', $request->product_id)
                    ->where('product_variation_id', $request->product_variation_id)
                    ->update(['is_active' => false]);
            }

            $recipe = ProductRecipe::create([
                'company_id' => $companyId,
                'product_id' => $request->product_id,
                'product_variation_id' => $request->product_variation_id,
                'name' => $request->name,
                'yield_quantity' => $request->yield_quantity,
                'unit_id' => $request->unit_id,
                'instructions' => $request->instructions,
                'is_active' => $request->boolean('is_active', true),
                'notes' => $request->notes,
            ]);

            foreach ($request->ingredients as $ing) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'raw_material_id' => $ing['raw_material_id'],
                    'raw_material_variation_id' => $ing['raw_material_variation_id'] ?? null,
                    'quantity' => $ing['quantity'],
                    'unit_id' => $ing['unit_id'] ?? null,
                    'waste_percentage' => $ing['waste_percentage'] ?? 0.00,
                    'notes' => $ing['notes'] ?? null,
                ]);
            }

            DB::commit();

            $recipe->load([
                'product', 'variation', 'unit', 
                'ingredients.rawMaterial', 'ingredients.variation', 'ingredients.unit'
            ]);
            $recipe->total_cost = $recipe->calculateTotalCost();

            return response()->json([
                'success' => true,
                'message' => 'Recipe created successfully',
                'recipe' => $recipe
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show specified recipe.
     */
    public function show(ProductRecipe $recipe): JsonResponse
    {
        $recipe->load([
            'product', 'variation', 'unit',
            'ingredients.rawMaterial', 'ingredients.variation', 'ingredients.unit'
        ]);
        $recipe->total_cost = $recipe->calculateTotalCost();
        $recipe->unit_cost = $recipe->yield_quantity > 0 ? round($recipe->total_cost / (float)$recipe->yield_quantity, 2) : 0;

        return response()->json($recipe);
    }

    /**
     * Get active recipe by Product ID.
     */
    public function getByProduct($productId): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $recipes = ProductRecipe::with([
            'product', 'variation', 'unit',
            'ingredients.rawMaterial', 'ingredients.variation', 'ingredients.unit'
        ])
        ->where('company_id', $companyId)
        ->where('product_id', $productId)
        ->get()
        ->map(function ($r) {
            $r->total_cost = $r->calculateTotalCost();
            $r->unit_cost = $r->yield_quantity > 0 ? round($r->total_cost / (float)$r->yield_quantity, 2) : 0;
            return $r;
        });

        return response()->json(['recipes' => $recipes]);
    }

    /**
     * Update specified recipe.
     */
    public function update(Request $request, ProductRecipe $recipe): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'name' => 'required|string|max:255',
            'yield_quantity' => 'required|numeric|min:0.0001',
            'unit_id' => 'nullable|exists:units,id',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.raw_material_id' => 'required|exists:products,id',
            'ingredients.*.raw_material_variation_id' => 'nullable|exists:product_variations,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.0001',
            'ingredients.*.unit_id' => 'nullable|exists:units,id',
            'ingredients.*.waste_percentage' => 'nullable|numeric|min:0|max:100',
            'ingredients.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $companyId = auth()->user()->current_company_id;

            if ($request->boolean('is_active') && !$recipe->is_active) {
                ProductRecipe::where('company_id', $companyId)
                    ->where('product_id', $request->product_id)
                    ->where('product_variation_id', $request->product_variation_id)
                    ->where('id', '!=', $recipe->id)
                    ->update(['is_active' => false]);
            }

            $recipe->update([
                'product_id' => $request->product_id,
                'product_variation_id' => $request->product_variation_id,
                'name' => $request->name,
                'yield_quantity' => $request->yield_quantity,
                'unit_id' => $request->unit_id,
                'instructions' => $request->instructions,
                'is_active' => $request->boolean('is_active'),
                'notes' => $request->notes,
            ]);

            // Replace ingredients
            $recipe->ingredients()->delete();
            foreach ($request->ingredients as $ing) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'raw_material_id' => $ing['raw_material_id'],
                    'raw_material_variation_id' => $ing['raw_material_variation_id'] ?? null,
                    'quantity' => $ing['quantity'],
                    'unit_id' => $ing['unit_id'] ?? null,
                    'waste_percentage' => $ing['waste_percentage'] ?? 0.00,
                    'notes' => $ing['notes'] ?? null,
                ]);
            }

            DB::commit();

            $recipe->load([
                'product', 'variation', 'unit',
                'ingredients.rawMaterial', 'ingredients.variation', 'ingredients.unit'
            ]);
            $recipe->total_cost = $recipe->calculateTotalCost();

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully',
                'recipe' => $recipe
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete recipe.
     */
    public function destroy(ProductRecipe $recipe): JsonResponse
    {
        $recipe->delete();
        return response()->json(['success' => true, 'message' => 'Recipe deleted successfully']);
    }
}
