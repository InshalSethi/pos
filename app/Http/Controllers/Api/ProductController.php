<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Services\StockThresholdService;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show', 'fetchDraftsSummary']);
        $this->middleware('permission:products.create')->only(['store']);
        $this->middleware('permission:products.edit')->only(['update', 'toggleStatus']);
        $this->middleware('permission:products.delete')->only(['destroy', 'bulkDestroyDrafts']);
        $this->middleware('permission:products.import')->only(['import']);
        $this->middleware('permission:products.export')->only(['export']);
    }

    /**
     * Toggle the active status of a product.
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        if ($product->status === 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Draft products cannot be toggled from listing. Please edit the product to change its status.'
            ], 422);
        }

        $newActiveState = !$product->is_active;
        $product->update([
            'is_active' => $newActiveState,
            'status' => $newActiveState ? 'active' : 'inactive'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully.',
            'is_active' => $product->is_active,
            'status' => $product->status
        ]);
    }



    /**
     * Fetches all inventory products isolated under draft state criteria.
     */
    public function fetchDraftsSummary(Request $request): JsonResponse
    {
        $drafts = Product::with(['category.parent.parent', 'variations'])
            ->withCount('variations')
            ->where('status', 'draft')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'drafts' => $drafts
        ]);
    }

    /**
     * Processes collection requests to batch delete drafted inventory lines safely.
     */
    public function bulkDestroyDrafts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Delete product variations associated with these draft products first
            \Illuminate\Support\Facades\DB::table('product_variations')
                ->whereIn('product_id', $request->ids)
                ->delete();

            // Delete products
            \Illuminate\Support\Facades\DB::table('products')
                ->whereIn('id', $request->ids)
                ->where('status', 'draft')
                ->delete();

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true, 'message' => 'Selected drafts removed successfully.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with([
            'category.parent.parent',
            'brand',
            'unit',
            'variations' => function ($query) {
                $query->select('id', 'product_id', 'combination_key', 'variation_name_string', 'cost_price', 'retail_price', 'wholesale_price', 'tax_rate', 'sku');
            }
        ])->withCount('variations')->where('status', '!=', 'draft');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->get('brand_id'));
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->get('tag'));
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $val = $request->input('is_active');
            if ($val !== 'all' && $val !== 'both') {
                $query->where('is_active', $request->boolean('is_active'));
            }
        }

        // Filter by on sale
        if ($request->boolean('on_sale')) {
            $query->where('discount_value', '>', 0);
        }

        // Filter by low stock
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        // Validate sort fields
        $allowedSortFields = ['name', 'sku', 'selling_price', 'cost_price', 'stock_quantity', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        foreach (['variations', 'tags', 'taxes', 'attributes', 'warehouses', 'warehouse_ids'] as $key) {
            if (is_string($request->input($key))) {
                $decoded = json_decode($request->input($key), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request->merge([$key => $decoded]);
                }
            }
        }

        if ($request->has('variations') && is_array($request->variations)) {
            $variations = $request->variations;
            foreach ($variations as &$v) {
                if (isset($v['barcode']) && $v['barcode'] === '')
                    $v['barcode'] = null;
                if (isset($v['cost_price']) && $v['cost_price'] === '')
                    $v['cost_price'] = 0;
                if (isset($v['retail_price']) && $v['retail_price'] === '')
                    $v['retail_price'] = 0;
                if (isset($v['wholesale_price']) && $v['wholesale_price'] === '')
                    $v['wholesale_price'] = 0;
                if (isset($v['discount_type']) && $v['discount_type'] === '')
                    $v['discount_type'] = null;
                if (isset($v['discount_value']) && $v['discount_value'] === '')
                    $v['discount_value'] = null;
                if (isset($v['tax_rate']) && $v['tax_rate'] === '')
                    $v['tax_rate'] = null;
                if (isset($v['unit_of_measure']) && $v['unit_of_measure'] === '')
                    $v['unit_of_measure'] = null;
                if (isset($v['expiry_date']) && $v['expiry_date'] === '')
                    $v['expiry_date'] = null;
            }
            unset($v);
            $request->merge(['variations' => $variations]);
        }

        $status = $request->input('status', 'active');
        $companyId = auth()->user()->current_company_id;

        // Accidental Activation Guard & Draft vs Active Rule definition
        if ($status === 'draft') {
            if (!$request->filled('sku')) {
                // Auto-generate a unique draft SKU to satisfy DB constraints
                $request->merge(['sku' => 'DRAFT-' . strtoupper(uniqid())]);
            }
            $skuRule = ['nullable', 'string', Rule::unique('products', 'sku')->where('company_id', $companyId)];
        } else {
            $skuRule = ['required', 'string', 'regex:/^(?!DRAFT-)/i', Rule::unique('products', 'sku')->where('company_id', $companyId)];
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sku' => $skuRule,
            'selling_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $companyId = auth()->user()->current_company_id;
                    return $query->where('company_id', $companyId);
                })
            ],
            'brand_id' => [
                'nullable',
                Rule::exists('brands', 'id')->where(function ($query) {
                    $companyId = auth()->user()->current_company_id;
                    return $query->where('company_id', $companyId);
                })
            ],
            'unit_id' => [
                'nullable',
                Rule::exists('units', 'id')->where(function ($query) {
                    $companyId = auth()->user()->current_company_id;
                    return $query->where('company_id', $companyId);
                })
            ],
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'barcode' => [
                'nullable',
                'string',
                Rule::unique('products', 'barcode')->where('company_id', $companyId)
            ],
            'unit_of_measure' => 'nullable|string',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'status' => 'nullable|string|in:active,inactive,draft',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable',
            'images' => 'nullable',
            'images.*' => 'nullable',
            'category_ids' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'has_variations' => 'boolean',
            'variations' => 'nullable|array',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ], [
            'sku.regex' => 'The SKU cannot be a draft placeholder (starting with DRAFT-) when activating the product.',
        ]);

        $validator->sometimes(['selling_price', 'cost_price'], 'required', function ($input) {
            return ($input->status ?? 'active') !== 'draft' && !$input->has_variations;
        });

        $validator->sometimes('wholesale_price', 'required', function ($input) {
            return ($input->status ?? 'active') !== 'draft' && !$input->has_variations && filter_var($input->enabled_for_wholesale ?? false, FILTER_VALIDATE_BOOLEAN);
        });

        $validator->sometimes('tax_rate', 'required', function ($input) {
            return ($input->status ?? 'active') !== 'draft' && filter_var($input->enabled_for_tax ?? false, FILTER_VALIDATE_BOOLEAN);
        });

        $validator->sometimes('unit_id', 'required', function ($input) {
            return ($input->status ?? 'active') !== 'draft' && filter_var($input->track_inventory ?? true, FILTER_VALIDATE_BOOLEAN);
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $data = $request->all();
            if ($request->has('unit_id') && $request->unit_id) {
                $unit = \App\Models\Unit::find($request->unit_id);
                if ($unit) {
                    $data['unit_of_measure'] = $unit->short_name;
                }
            }

            $hasVariantsActive = $request->boolean('has_variations') && !empty($request->variations) && count($request->variations) > 0;

            if ($hasVariantsActive) {
                $variationsData = $request->variations;
                foreach ($variationsData as $index => &$row) {
                    if (isset($row['taxes']) && is_array($row['taxes'])) {
                        $row['tax_rate'] = \App\Models\Tax::whereIn('id', $row['taxes'])->where('is_active', true)->sum('value');
                    }
                }
                unset($row);
                $request->merge(['variations' => $variationsData]);

                $firstVar = $variationsData[0] ?? null;
                $data['cost_price'] = $firstVar ? ($firstVar['cost_price'] ?? 0.00) : 0.00;
                $data['selling_price'] = $firstVar ? ($firstVar['retail_price'] ?? $firstVar['selling_price'] ?? 0.00) : 0.00;
                $data['wholesale_price'] = $firstVar ? ($firstVar['wholesale_price'] ?? 0.00) : 0.00;
                $data['tax_rate'] = $firstVar ? ($firstVar['tax_rate'] ?? 0.00) : 0.00;
                $data['stock_quantity'] = collect($variationsData)->sum(function ($row) {
                    return (int) ($row['stock_qty'] ?? 0);
                });
            } else {
                if ($request->has('taxes') && is_array($request->taxes)) {
                    $data['tax_rate'] = \App\Models\Tax::whereIn('id', $request->taxes)->where('is_active', true)->sum('value');
                } else {
                    if (empty($data['selling_price']))
                        $data['selling_price'] = 0;
                    if (empty($data['wholesale_price']))
                        $data['wholesale_price'] = 0;
                    if (empty($data['cost_price']))
                        $data['cost_price'] = 0;
                    if (empty($data['tax_rate']))
                        $data['tax_rate'] = 0;
                }
                $data['has_variations'] = false;
            }

            $storedImages = [];
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $idx => $imgItem) {
                    if ($request->hasFile("images.{$idx}")) {
                        $path = $request->file("images.{$idx}")->store('product-images', 'public');
                        $storedImages[] = '/storage/' . $path;
                    } elseif (is_string($imgItem) && !empty($imgItem)) {
                        $storedImages[] = $imgItem;
                    }
                }
            }

            if (count($storedImages) > 0) {
                $data['images'] = $storedImages;
                $data['image'] = $storedImages[0];
            } elseif ($request->hasFile('image')) {
                $path = $request->file('image')->store('product-images', 'public');
                $data['image'] = '/storage/' . $path;
                $data['images'] = ['/storage/' . $path];
            } else {
                $data['image'] = null;
                $data['images'] = [];
            }

            $product = Product::create($data);
            $product->load('category');

            if ($request->has('tags') && is_array($request->tags)) {
                $companyId = auth()->check() ? auth()->user()->current_company_id : null;
                foreach ($request->tags as $tagName) {
                    \App\Models\Tag::firstOrCreate([
                        'company_id' => $companyId,
                        'name' => $tagName
                    ]);
                }
            }

            if ($request->has('attributes') && is_array($request->attributes)) {
                foreach ($request->attributes as $attr) {
                    if (isset($attr['name']) && isset($attr['values'])) {
                        \App\Models\ProductAttribute::create([
                            'product_id' => $product->id,
                            'name' => $attr['name'],
                            'values' => $attr['values']
                        ]);
                    }
                }
            }

            $companyId = auth()->user()->current_company_id ?? $product->company_id;

            if ($hasVariantsActive) {
                $defaultWarehouse = \App\Models\Warehouse::firstOrCreate([
                    'company_id' => $companyId,
                    'is_default' => true,
                ], [
                    'name' => 'Main Warehouse',
                    'is_active' => true,
                ]);

                $whService = new \App\Services\WarehouseInventoryService();

                foreach ($request->variations as $index => $row) {
                    $variation = \App\Models\ProductVariation::create([
                        'product_id' => $product->id,
                        'combination_key' => $row['combination_key'] ?? $row['name_string'] ?? strval($index),
                        'variation_name_string' => $row['name_string'] ?? 'Default',
                        'sku' => $row['sku'] ?? 'SKU-' . strtoupper(uniqid()),
                        'barcode' => $row['barcode'] ?? null,
                        'retail_price' => $row['retail_price'] ?? 0,
                        'wholesale_price' => $row['wholesale_price'] ?? 0,
                        'cost_price' => $row['cost_price'] ?? 0,
                        'tax_rate' => $row['tax_rate'] ?? null,
                        'tags' => $row['tags'] ?? [],
                        'taxes' => $row['taxes'] ?? [],
                        'discount_type' => $row['discount_type'] ?? null,
                        'discount_value' => $row['discount_value'] ?? null,
                        'stock_qty' => $row['stock_qty'] ?? 0,
                        'min_stock_alert' => $row['min_stock_alert'] ?? 0,
                        'unit_of_measure' => $row['unit_of_measure'] ?? null,
                        'expiry_date' => $row['expiry_date'] ?? null,
                    ]);

                    $targetWarehouseIds = isset($row['warehouse_ids']) && is_array($row['warehouse_ids']) && count($row['warehouse_ids']) > 0
                        ? $row['warehouse_ids']
                        : [$defaultWarehouse->id];

                    foreach ($targetWarehouseIds as $whId) {
                        $qty = isset($row['warehouse_stocks'][$whId]) ? (int) $row['warehouse_stocks'][$whId] : 0;
                        $minStock = isset($row['warehouse_min_stocks'][$whId]) ? (int) $row['warehouse_min_stocks'][$whId] : 0;

                        $whService->setStock(
                            $whId,
                            $product->id,
                            $variation->id,
                            $qty,
                            $companyId
                        );

                        \App\Models\Inventory::where('warehouse_id', $whId)
                            ->where('product_id', $product->id)
                            ->where('product_variation_id', $variation->id)
                            ->update(['min_stock_level' => $minStock]);
                    }
                }
            } else {
                if ($product->track_inventory) {
                    $whService = new \App\Services\WarehouseInventoryService();

                    if ($request->has('warehouses') && is_array($request->warehouses) && count($request->warehouses) > 0) {
                        foreach ($request->warehouses as $whAllocation) {
                            $warehouseId = $whAllocation['id'] ?? null;
                            $warehouse = null;
                            if ($warehouseId) {
                                $warehouse = \App\Models\Warehouse::where('company_id', $companyId)->find($warehouseId);
                            }
                            if (!$warehouse) {
                                $warehouse = \App\Models\Warehouse::firstOrCreate([
                                    'company_id' => $companyId,
                                    'name' => $whAllocation['name'] ?? 'Warehouse',
                                ], [
                                    'is_default' => filter_var($whAllocation['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                                    'is_active' => true,
                                ]);
                            }

                            $whService->setStock(
                                $warehouse->id,
                                $product->id,
                                null,
                                (int) ($whAllocation['opening_stock'] ?? 0),
                                $companyId
                            );

                            \App\Models\Inventory::where('warehouse_id', $warehouse->id)
                                ->where('product_id', $product->id)
                                ->whereNull('product_variation_id')
                                ->update(['min_stock_level' => (int) ($whAllocation['reorder_level'] ?? 0)]);
                        }
                    } elseif ($targetWarehouseId = $request->get('warehouse_id')) {
                        $whService->setStock(
                            $targetWarehouseId,
                            $product->id,
                            null,
                            (int) ($request->stock_quantity ?? 0),
                            $companyId
                        );

                        \App\Models\Inventory::where('warehouse_id', $targetWarehouseId)
                            ->where('product_id', $product->id)
                            ->whereNull('product_variation_id')
                            ->update(['min_stock_level' => (int) ($request->min_stock_level ?? 0)]);
                    } else {
                        $defaultWarehouse = \App\Models\Warehouse::firstOrCreate([
                            'company_id' => $companyId,
                            'is_default' => true,
                        ], [
                            'name' => 'Main Warehouse',
                            'is_active' => true,
                        ]);

                        $whService->setStock(
                            $defaultWarehouse->id,
                            $product->id,
                            null,
                            (int) ($request->stock_quantity ?? 0),
                            $companyId
                        );

                        \App\Models\Inventory::where('warehouse_id', $defaultWarehouse->id)
                            ->where('product_id', $product->id)
                            ->whereNull('product_variation_id')
                            ->update(['min_stock_level' => (int) ($request->min_stock_level ?? 0)]);
                    }
                }
            }

            // Perform Double-Entry Posting for initial item creation stock valuation via central service (deduplicated)
            (new \App\Services\DoubleEntryAccountingService())->createOpeningStockEntry($product->fresh());

            \Illuminate\Support\Facades\DB::commit();

            // Evaluate stock thresholds and fire low-stock notifications
            try {
                (new StockThresholdService())->evaluate($product->fresh(['variations']));
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::warning('StockThresholdService failed after store: ' . $th->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed storing product data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('category.parent.parent', 'brand', 'unit', 'saleItems.sale', 'variations', 'attributes');
        $product->loadCount('variations');

        $warehouses = \App\Models\Warehouse::where('company_id', $product->company_id)->get()->map(function ($wh) use ($product) {
            $inventory = \App\Models\Inventory::where('warehouse_id', $wh->id)
                ->where('product_id', $product->id)
                ->whereNull('product_variation_id')
                ->first();
            return [
                'id' => $wh->id,
                'name' => $wh->name,
                'is_default' => $wh->is_default,
                'opening_stock' => $inventory ? $inventory->stock_qty : 0,
                'reorder_level' => $inventory ? $inventory->min_stock_level : 0,
            ];
        });

        $productArray = $product->toArray();
        $productArray['warehouses'] = $warehouses;

        $inventoryWarehouseIds = \App\Models\Inventory::where('product_id', $product->id)
            ->whereNull('product_variation_id')
            ->pluck('warehouse_id')
            ->unique()
            ->values()
            ->toArray();
        $productArray['warehouse_ids'] = $inventoryWarehouseIds;
        $productArray['warehouse_id'] = $inventoryWarehouseIds[0] ?? null;

        $productArray['variations'] = array_map(function ($variation) use ($product) {
            $inventories = \App\Models\Inventory::where('product_id', $product->id)
                ->where('product_variation_id', $variation['id'])
                ->get();

            $variation['warehouse_ids'] = $inventories->pluck('warehouse_id')->toArray();

            $warehouseStocks = [];
            $warehouseMinStocks = [];
            foreach ($inventories as $inv) {
                $warehouseStocks[$inv->warehouse_id] = $inv->stock_qty;
                $warehouseMinStocks[$inv->warehouse_id] = $inv->min_stock_level;
            }
            $variation['warehouse_stocks'] = $warehouseStocks;
            $variation['warehouse_min_stocks'] = $warehouseMinStocks;

            return $variation;
        }, $productArray['variations']);

        return response()->json($productArray);
    }

    /**
     * Edit route mapping helper: ensures front-end views read variation attributes count state reactively.
     */
    public function edit($id)
    {
        // Include variations count structure explicitly
        $product = Product::with(['variations', 'attributes'])->withCount('variations')->findOrFail($id);

        $warehouses = \App\Models\Warehouse::where('company_id', $product->company_id)->get()->map(function ($wh) use ($product) {
            $inventory = \App\Models\Inventory::where('warehouse_id', $wh->id)
                ->where('product_id', $product->id)
                ->whereNull('product_variation_id')
                ->first();
            return [
                'id' => $wh->id,
                'name' => $wh->name,
                'is_default' => $wh->is_default,
                'opening_stock' => $inventory ? $inventory->stock_qty : 0,
                'reorder_level' => $inventory ? $inventory->min_stock_level : 0,
            ];
        });

        $productArray = $product->toArray();
        $productArray['warehouses'] = $warehouses;

        $inventoryWarehouseIds = \App\Models\Inventory::where('product_id', $product->id)
            ->whereNull('product_variation_id')
            ->pluck('warehouse_id')
            ->unique()
            ->values()
            ->toArray();
        $productArray['warehouse_ids'] = $inventoryWarehouseIds;
        $productArray['warehouse_id'] = $inventoryWarehouseIds[0] ?? null;

        $productArray['variations'] = array_map(function ($variation) use ($product) {
            $inventories = \App\Models\Inventory::where('product_id', $product->id)
                ->where('product_variation_id', $variation['id'])
                ->get();

            $variation['warehouse_ids'] = $inventories->pluck('warehouse_id')->toArray();

            $warehouseStocks = [];
            $warehouseMinStocks = [];
            foreach ($inventories as $inv) {
                $warehouseStocks[$inv->warehouse_id] = $inv->stock_qty;
                $warehouseMinStocks[$inv->warehouse_id] = $inv->min_stock_level;
            }
            $variation['warehouse_stocks'] = $warehouseStocks;
            $variation['warehouse_min_stocks'] = $warehouseMinStocks;

            return $variation;
        }, $productArray['variations']);

        return response()->json(['product' => $productArray]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        foreach (['variations', 'tags', 'taxes', 'attributes', 'warehouses', 'warehouse_ids', 'images'] as $key) {
            if (is_string($request->input($key))) {
                $decoded = json_decode($request->input($key), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request->merge([$key => $decoded]);
                }
            }
        }

        if ($request->has('variations') && is_array($request->variations)) {
            $variations = $request->variations;
            foreach ($variations as &$v) {
                if (isset($v['barcode']) && $v['barcode'] === '')
                    $v['barcode'] = null;
                if (isset($v['cost_price']) && $v['cost_price'] === '')
                    $v['cost_price'] = 0;
                if (isset($v['retail_price']) && $v['retail_price'] === '')
                    $v['retail_price'] = 0;
                if (isset($v['wholesale_price']) && $v['wholesale_price'] === '')
                    $v['wholesale_price'] = 0;
                if (isset($v['discount_type']) && $v['discount_type'] === '')
                    $v['discount_type'] = null;
                if (isset($v['discount_value']) && $v['discount_value'] === '')
                    $v['discount_value'] = null;
                if (isset($v['tax_rate']) && $v['tax_rate'] === '')
                    $v['tax_rate'] = null;
                if (isset($v['unit_of_measure']) && $v['unit_of_measure'] === '')
                    $v['unit_of_measure'] = null;
                if (isset($v['expiry_date']) && $v['expiry_date'] === '')
                    $v['expiry_date'] = null;
            }
            unset($v);
            $request->merge(['variations' => $variations]);
        }

        $status = $request->input('status', 'active');
        $companyId = auth()->user()->current_company_id;

        // Accidental Activation Guard & Draft vs Active Rule definition
        if ($status === 'draft') {
            if (!$request->filled('sku') && (!$product->sku || str_starts_with($product->sku, 'DRAFT-'))) {
                // If they update a draft and left SKU blank, preserve or generate one if missing
                $request->merge(['sku' => $product->sku ?: 'DRAFT-' . strtoupper(uniqid())]);
            }
            $skuRule = ['nullable', 'string', Rule::unique('products', 'sku')->ignore($product->id)->where('company_id', $companyId)];
        } else {
            $skuRule = ['required', 'string', 'regex:/^(?!DRAFT-)/i', Rule::unique('products', 'sku')->ignore($product->id)->where('company_id', $companyId)];
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sku' => $skuRule,
            'selling_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $companyId = auth()->user()->current_company_id;
                    return $query->where('company_id', $companyId);
                })
            ],
            'brand_id' => [
                'nullable',
                Rule::exists('brands', 'id')->where(function ($query) {
                    $companyId = auth()->user()->current_company_id;
                    return $query->where('company_id', $companyId);
                })
            ],
            'unit_id' => [
                'nullable',
                Rule::exists('units', 'id')->where(function ($query) {
                    $companyId = auth()->user()->current_company_id;
                    return $query->where('company_id', $companyId);
                })
            ],
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'barcode' => [
                'nullable',
                'string',
                Rule::unique('products', 'barcode')->ignore($product->id)->where('company_id', $companyId)
            ],
            'unit_of_measure' => 'nullable|string',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'status' => 'nullable|string|in:active,inactive,draft',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable',
            'images' => 'nullable',
            'images.*' => 'nullable',
            'category_ids' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'has_variations' => 'boolean',
            'variations' => 'nullable|array',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ], [
            'sku.regex' => 'The SKU cannot be a draft placeholder (starting with DRAFT-) when activating the product.',
        ]);

        $validator->sometimes(['selling_price', 'cost_price'], 'required', function ($input) {
            return ($input->status ?? 'active') !== 'draft' && !$input->has_variations;
        });

        $validator->sometimes('wholesale_price', 'required', function ($input) {
            return ($input->status ?? 'active') !== 'draft' && !$input->has_variations && filter_var($input->enabled_for_wholesale ?? false, FILTER_VALIDATE_BOOLEAN);
        });

        $validator->sometimes('tax_rate', 'required', function ($input) {
            return ($input->status ?? 'active') !== 'draft' && filter_var($input->enabled_for_tax ?? false, FILTER_VALIDATE_BOOLEAN);
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $data = $request->all();
            if ($request->has('unit_id') && $request->unit_id) {
                $unit = \App\Models\Unit::find($request->unit_id);
                if ($unit) {
                    $data['unit_of_measure'] = $unit->short_name;
                }
            }

            $hasVariantsActive = $request->boolean('has_variations') && !empty($request->variations) && count($request->variations) > 0;

            if ($hasVariantsActive) {
                $variationsData = $request->variations;
                foreach ($variationsData as $index => &$row) {
                    if (isset($row['taxes']) && is_array($row['taxes'])) {
                        $row['tax_rate'] = \App\Models\Tax::whereIn('id', $row['taxes'])->where('is_active', true)->sum('value');
                    }
                }
                unset($row);
                $request->merge(['variations' => $variationsData]);

                $firstVar = $variationsData[0] ?? null;
                $data['cost_price'] = $firstVar ? ($firstVar['cost_price'] ?? 0.00) : 0.00;
                $data['selling_price'] = $firstVar ? ($firstVar['retail_price'] ?? $firstVar['selling_price'] ?? 0.00) : 0.00;
                $data['wholesale_price'] = $firstVar ? ($firstVar['wholesale_price'] ?? 0.00) : 0.00;
                $data['tax_rate'] = $firstVar ? ($firstVar['tax_rate'] ?? 0.00) : 0.00;
                $data['stock_quantity'] = collect($variationsData)->sum(function ($row) {
                    return (int) ($row['stock_qty'] ?? 0);
                });
            } else {
                if ($request->has('taxes') && is_array($request->taxes)) {
                    $data['tax_rate'] = \App\Models\Tax::whereIn('id', $request->taxes)->where('is_active', true)->sum('value');
                } else {
                    if (empty($data['selling_price']))
                        $data['selling_price'] = 0;
                    if (empty($data['wholesale_price']))
                        $data['wholesale_price'] = 0;
                    if (empty($data['cost_price']))
                        $data['cost_price'] = 0;
                    if (empty($data['tax_rate']))
                        $data['tax_rate'] = 0;
                }
                $data['has_variations'] = false;
            }

            $storedImages = [];
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $idx => $imgItem) {
                    if ($request->hasFile("images.{$idx}")) {
                        $path = $request->file("images.{$idx}")->store('product-images', 'public');
                        $storedImages[] = '/storage/' . $path;
                    } elseif (is_string($imgItem) && !empty($imgItem) && $imgItem !== 'null' && $imgItem !== 'undefined') {
                        $storedImages[] = $imgItem;
                    }
                }
            } elseif ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('product-images', 'public');
                        $storedImages[] = '/storage/' . $path;
                    }
                }
            }

            // Fallback if image parameter was passed as single string URL
            if (count($storedImages) === 0 && $request->filled('image') && is_string($request->input('image')) && $request->input('image') !== 'null' && $request->input('image') !== 'undefined') {
                $singleImg = $request->input('image');
                $storedImages[] = $singleImg;
            }

            if (count($storedImages) > 0) {
                $data['images'] = $storedImages;
                $data['image'] = $storedImages[0];
            } elseif ($request->hasFile('image')) {
                if ($product->image) {
                    $oldPath = str_replace('/storage/', '', $product->image);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('image')->store('product-images', 'public');
                $data['image'] = '/storage/' . $path;
                $data['images'] = ['/storage/' . $path];
            } elseif ($request->input('image') === '' || ($request->has('images') && is_array($request->images) && count($request->images) === 0)) {
                if ($product->image) {
                    $oldPath = str_replace('/storage/', '', $product->image);
                    Storage::disk('public')->delete($oldPath);
                }
                $data['image'] = null;
                $data['images'] = [];
            } else {
                // Preserve existing product images if no images payload was submitted
                unset($data['image']);
                unset($data['images']);
            }

            $oldStock = (float) ($product->stock_quantity ?? 0);
            $oldCostPrice = floatval($product->cost_price ?? $product->purchase_price ?? 0);
            $product->update($data);
            $product->load('category');

            if ($request->has('tags') && is_array($request->tags)) {
                $companyId = auth()->check() ? auth()->user()->current_company_id : null;
                foreach ($request->tags as $tagName) {
                    \App\Models\Tag::firstOrCreate([
                        'company_id' => $companyId,
                        'name' => $tagName
                    ]);
                }
            }

            if ($request->has('attributes') && is_array($request->attributes)) {
                $product->attributes()->delete();
                foreach ($request->attributes as $attr) {
                    if (isset($attr['name']) && isset($attr['values'])) {
                        \App\Models\ProductAttribute::create([
                            'product_id' => $product->id,
                            'name' => $attr['name'],
                            'values' => $attr['values']
                        ]);
                    }
                }
            } else {
                $product->attributes()->delete();
            }

            $companyId = auth()->user()->current_company_id ?? $product->company_id;

            if ($hasVariantsActive) {
                // Delete old simple product inventory records to prevent double counting
                \App\Models\Inventory::where('product_id', $product->id)
                    ->whereNull('product_variation_id')
                    ->delete();

                \App\Models\Inventory::where('product_id', $product->id)
                    ->whereNotNull('product_variation_id')
                    ->delete();

                $product->variations()->delete();

                $defaultWarehouse = \App\Models\Warehouse::firstOrCreate([
                    'company_id' => $companyId,
                    'is_default' => true,
                ], [
                    'name' => 'Main Warehouse',
                    'is_active' => true,
                ]);

                $whService = new \App\Services\WarehouseInventoryService();

                foreach ($request->variations as $index => $row) {
                    $variation = \App\Models\ProductVariation::create([
                        'product_id' => $product->id,
                        'combination_key' => $row['combination_key'] ?? $row['name_string'] ?? strval($index),
                        'variation_name_string' => $row['name_string'] ?? 'Default',
                        'sku' => $row['sku'] ?? 'SKU-' . strtoupper(uniqid()),
                        'barcode' => $row['barcode'] ?? null,
                        'retail_price' => $row['retail_price'] ?? 0,
                        'wholesale_price' => $row['wholesale_price'] ?? 0,
                        'cost_price' => $row['cost_price'] ?? 0,
                        'tax_rate' => $row['tax_rate'] ?? null,
                        'tags' => $row['tags'] ?? [],
                        'taxes' => $row['taxes'] ?? [],
                        'discount_type' => $row['discount_type'] ?? null,
                        'discount_value' => $row['discount_value'] ?? null,
                        'stock_qty' => $row['stock_qty'] ?? 0,
                        'min_stock_alert' => $row['min_stock_alert'] ?? 0,
                        'unit_of_measure' => $row['unit_of_measure'] ?? null,
                        'expiry_date' => $row['expiry_date'] ?? null,
                    ]);

                    $targetWarehouseIds = isset($row['warehouse_ids']) && is_array($row['warehouse_ids']) && count($row['warehouse_ids']) > 0
                        ? $row['warehouse_ids']
                        : [$defaultWarehouse->id];

                    foreach ($targetWarehouseIds as $whId) {
                        $qty = isset($row['warehouse_stocks'][$whId]) ? (int) $row['warehouse_stocks'][$whId] : 0;
                        $minStock = isset($row['warehouse_min_stocks'][$whId]) ? (int) $row['warehouse_min_stocks'][$whId] : 0;

                        $whService->setStock(
                            $whId,
                            $product->id,
                            $variation->id,
                            $qty,
                            $companyId
                        );

                        \App\Models\Inventory::where('warehouse_id', $whId)
                            ->where('product_id', $product->id)
                            ->where('product_variation_id', $variation->id)
                            ->update(['min_stock_level' => $minStock]);
                    }
                }
            } else {
                $product->variations()->delete();

                \App\Models\Inventory::where('product_id', $product->id)
                    ->whereNotNull('product_variation_id')
                    ->delete();

                if ($product->track_inventory) {
                    $whService = new \App\Services\WarehouseInventoryService();

                    if ($request->has('warehouses') && is_array($request->warehouses) && count($request->warehouses) > 0) {
                        \App\Models\Inventory::where('product_id', $product->id)
                            ->whereNull('product_variation_id')
                            ->delete();

                        foreach ($request->warehouses as $whAllocation) {
                            $warehouseId = $whAllocation['id'] ?? null;
                            $warehouse = null;
                            if ($warehouseId) {
                                $warehouse = \App\Models\Warehouse::where('company_id', $companyId)->find($warehouseId);
                            }
                            if (!$warehouse) {
                                $warehouse = \App\Models\Warehouse::firstOrCreate([
                                    'company_id' => $companyId,
                                    'name' => $whAllocation['name'] ?? 'Warehouse',
                                ], [
                                    'is_default' => filter_var($whAllocation['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                                    'is_active' => true,
                                ]);
                            }

                            $whService->setStock(
                                $warehouse->id,
                                $product->id,
                                null,
                                (int) ($whAllocation['opening_stock'] ?? $whAllocation['stock_qty'] ?? 0),
                                $companyId
                            );

                            \App\Models\Inventory::where('warehouse_id', $warehouse->id)
                                ->where('product_id', $product->id)
                                ->whereNull('product_variation_id')
                                ->update(['min_stock_level' => (int) ($whAllocation['reorder_level'] ?? $whAllocation['min_stock_level'] ?? 0)]);
                        }
                    } elseif ($targetWarehouseId = $request->get('warehouse_id')) {
                        \App\Models\Inventory::where('product_id', $product->id)
                            ->where('warehouse_id', '!=', $targetWarehouseId)
                            ->whereNull('product_variation_id')
                            ->delete();

                        $whService->setStock(
                            $targetWarehouseId,
                            $product->id,
                            null,
                            (int) ($request->stock_quantity ?? 0),
                            $companyId
                        );

                        \App\Models\Inventory::where('warehouse_id', $targetWarehouseId)
                            ->where('product_id', $product->id)
                            ->whereNull('product_variation_id')
                            ->update(['min_stock_level' => (int) ($request->min_stock_level ?? 0)]);
                    } else {
                        $defaultWarehouse = \App\Models\Warehouse::firstOrCreate([
                            'company_id' => $companyId,
                            'is_default' => true,
                        ], [
                            'name' => 'Main Warehouse',
                            'is_active' => true,
                        ]);

                        $whService->setStock(
                            $defaultWarehouse->id,
                            $product->id,
                            null,
                            (int) ($request->stock_quantity ?? 0),
                            $companyId
                        );

                        \App\Models\Inventory::where('warehouse_id', $defaultWarehouse->id)
                            ->where('product_id', $product->id)
                            ->whereNull('product_variation_id')
                            ->update(['min_stock_level' => (int) ($request->min_stock_level ?? 0)]);
                    }
                }
            }

            // Perform Double-Entry Posting for Item Edit valuation delta (COA 1040 Sync)
            $freshProduct = $product->fresh();
            $newStock = (float) ($freshProduct->stock_quantity ?? 0);
            $newCostPrice = floatval(
                $request->input('purchase_price')
                ?? $request->input('cost_price')
                ?? $freshProduct->cost_price
                ?? $freshProduct->purchase_price
                ?? 0
            );

            $oldValuation = round($oldStock * $oldCostPrice, 2);
            $newValuation = round($newStock * $newCostPrice, 2);
            $valuationImpact = round($newValuation - $oldValuation, 2);

            if ($valuationImpact != 0) {
                    $companyId = auth()->user()->current_company_id ?? $freshProduct->company_id;
                    $assetAccount = Account::where('company_id', $companyId)->where('account_code', '1040')->first()
                        ?? Account::where('company_id', $companyId)->where('account_code', '1500')->first()
                        ?? Account::where('company_id', $companyId)->where('account_name', 'LIKE', '%Inventory Asset%')->first();

                    $gainAccount = Account::where('company_id', $companyId)->where('account_code', '5010')->first()
                        ?? Account::where('company_id', $companyId)->where('account_name', 'LIKE', '%Inventory Adjustment Gain%')->first();

                    if ($assetAccount) {
                        $absAmt = abs($valuationImpact);
                        $je = JournalEntry::create([
                            'company_id' => $companyId,
                            'entry_number' => 'JE-ITEM-' . date('YmdHis') . '-' . rand(100, 999),
                            'entry_date' => now(),
                            'reference' => 'ITEM-UPDATE-' . $freshProduct->id,
                            'description' => "Item Direct Stock Update Posting ({$freshProduct->name})",
                            'entry_type' => 'adjustment',
                            'status' => 'posted',
                            'total_debit' => $absAmt,
                            'total_credit' => $absAmt,
                            'created_by' => auth()->id() ?? 1,
                        ]);

                        if ($valuationImpact > 0) {
                            // DEBIT 1040 Asset, CREDIT 5010 Gain
                            JournalEntryLine::create([
                                'journal_entry_id' => $je->id,
                                'account_id' => $assetAccount->id,
                                'debit_amount' => $absAmt,
                                'credit_amount' => 0,
                                'description' => 'Item Direct Stock Increase Asset Posting',
                            ]);
                            if ($gainAccount) {
                                JournalEntryLine::create([
                                    'journal_entry_id' => $je->id,
                                    'account_id' => $gainAccount->id,
                                    'debit_amount' => 0,
                                    'credit_amount' => $absAmt,
                                    'description' => 'Item Direct Stock Increase Gain Posting',
                                ]);
                                $gainAccount->increment('current_balance', $absAmt);
                            }
                            $assetAccount->increment('current_balance', $absAmt);
                        } else {
                            // DEBIT 5010 Gain/Loss, CREDIT 1040 Asset
                            if ($gainAccount) {
                                JournalEntryLine::create([
                                    'journal_entry_id' => $je->id,
                                    'account_id' => $gainAccount->id,
                                    'debit_amount' => $absAmt,
                                    'credit_amount' => 0,
                                    'description' => 'Item Direct Stock Reduction Gain/Loss Posting',
                                ]);
                                $gainAccount->decrement('current_balance', $absAmt);
                            }
                            JournalEntryLine::create([
                                'journal_entry_id' => $je->id,
                                'account_id' => $assetAccount->id,
                                'debit_amount' => 0,
                                'credit_amount' => $absAmt,
                                'description' => 'Item Direct Stock Reduction Asset Posting',
                            ]);
                            $assetAccount->decrement('current_balance', $absAmt);
                        }

                        $assetAccount->updateCurrentBalance();
                        if ($gainAccount) {
                            $gainAccount->updateCurrentBalance();
                        }
                    }
                }

            \Illuminate\Support\Facades\DB::commit();

            // Evaluate stock thresholds and fire low-stock notifications
            try {
                (new StockThresholdService())->evaluate($product->fresh(['variations']));
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::warning('StockThresholdService failed after update: ' . $th->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Update runtime fault: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        // Check if product has sales
        if ($product->saleItems()->exists()) {
            return response()->json([
                'message' => 'Cannot delete product with existing sales'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Calculates the Cartesian Product of multi-dimensional attribute values arrays 
     * to auto-generate unique product variations grid entries on the fly.
     */
    public function generateCombinationsMatrix(array $attributeGroups)
    {
        // Input format example: [ [1, 2], [3, 4] ] -> (IDs of chosen values)
        $result = [[]];
        foreach ($attributeGroups as $property => $values) {
            if (empty($values))
                continue;
            $append = [];
            foreach ($result as $productCombo) {
                foreach ($values as $item) {
                    $append[] = array_merge($productCombo, [$property => $item]);
                }
            }
            $result = $append;
        }

        // Returns array matrices containing sorted ID combinations keys
        return array_map(function ($combo) {
            sort($combo); // Ensure chronological sequence consistency, e.g., always "1-5", never "5-1"
            return [
                'combination_key' => implode('-', $combo),
                'suggested_sku' => 'SKU-' . strtoupper(uniqid())
            ];
        }, $result);
    }

    /**
     * Advanced Item Search Endpoint
     * Accepts: search_term, search, query, sku, brand_id, category_id, subcategory_id, child_category_id, tag_id, tag, tags, min_price, max_price
     */
    public function advancedSearch(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $query = Product::where('company_id', $companyId)
            ->where('status', '!=', 'draft')
            ->where('is_active', true)
            ->with(['category.parent.parent', 'brand', 'unit', 'variations']);

        // Search term (Name, Description, SKU, Barcode)
        $searchTerm = $request->input('search_term') ?? $request->input('search') ?? $request->input('query');
        if ($searchTerm && trim($searchTerm) !== '') {
            $term = trim($searchTerm);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%");
            });
        }

        // SKU filter
        if ($request->filled('sku')) {
            $sku = trim($request->input('sku'));
            $query->where(function ($q) use ($sku) {
                $q->where('sku', 'like', "%{$sku}%")
                    ->orWhereHas('variations', function ($vq) use ($sku) {
                        $vq->where('sku', 'like', "%{$sku}%");
                    });
            });
        }

        // Brand filter
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        // Category hierarchy filter
        $targetCatIds = [];
        if ($request->filled('child_category_id')) {
            $targetCatIds[] = $request->input('child_category_id');
        } elseif ($request->filled('subcategory_id')) {
            $subId = $request->input('subcategory_id');
            $targetCatIds[] = $subId;
            $childIds = \App\Models\Category::where('company_id', $companyId)->where('parent_id', $subId)->pluck('id')->toArray();
            $targetCatIds = array_merge($targetCatIds, $childIds);
        } elseif ($request->filled('category_id')) {
            $mainId = $request->input('category_id');
            $targetCatIds[] = $mainId;
            $subIds = \App\Models\Category::where('company_id', $companyId)->where('parent_id', $mainId)->pluck('id')->toArray();
            $targetCatIds = array_merge($targetCatIds, $subIds);
            if (!empty($subIds)) {
                $childIds = \App\Models\Category::where('company_id', $companyId)->whereIn('parent_id', $subIds)->pluck('id')->toArray();
                $targetCatIds = array_merge($targetCatIds, $childIds);
            }
        } elseif ($request->filled('categories')) {
            $cats = $request->input('categories');
            $targetCatIds = is_array($cats) ? $cats : explode(',', $cats);
        }

        if (!empty($targetCatIds)) {
            $targetCatIds = array_values(array_unique(array_filter(array_map('trim', $targetCatIds))));
            $query->whereIn('category_id', $targetCatIds);
        }

        // Tag filter
        if ($request->filled('tag_id') || $request->filled('tag') || $request->filled('tags')) {
            $rawTags = $request->input('tag_id') ?? $request->input('tag') ?? $request->input('tags');
            $tagsList = is_array($rawTags) ? $rawTags : explode(',', $rawTags);
            $tagsList = array_values(array_filter(array_map('trim', $tagsList)));
            if (!empty($tagsList)) {
                $query->where(function ($q) use ($tagsList) {
                    foreach ($tagsList as $t) {
                        $q->orWhereJsonContains('tags', $t);
                    }
                });
            }
        }

        // Min price
        if ($request->filled('min_price') && is_numeric($request->input('min_price'))) {
            $query->where('selling_price', '>=', (float) $request->input('min_price'));
        }

        // Max price
        if ($request->filled('max_price') && is_numeric($request->input('max_price'))) {
            $query->where('selling_price', '<=', (float) $request->input('max_price'));
        }

        $products = $query->distinct()->groupBy('products.id')->take(100)->get();

        $inventories = \App\Models\Inventory::where('company_id', $companyId)->get();
        $stockMap = [];
        foreach ($inventories as $inv) {
            $key = $inv->product_id . '-' . ($inv->product_variation_id ?? 'null');
            if (!isset($stockMap[$key])) {
                $stockMap[$key] = [];
            }
            $stockMap[$key][$inv->warehouse_id] = $inv->stock_qty;
        }

        $warehouses = \App\Models\Warehouse::where('company_id', $companyId)->where('is_active', true)->get();
        $flatItems = [];

        foreach ($products as $product) {
            $catPath = null;
            if ($product->category) {
                $parts = [];
                if ($product->category->parent && $product->category->parent->parent) {
                    $parts[] = $product->category->parent->parent->name;
                }
                if ($product->category->parent) {
                    $parts[] = $product->category->parent->name;
                }
                $parts[] = $product->category->name;
                $catPath = implode(' - ', $parts);
            }
            $brandName = $product->brand ? $product->brand->name : null;

            if ($product->has_variations && count($product->variations) > 0) {
                foreach ($product->variations as $variation) {
                    $varKey = $product->id . '-' . $variation->id;
                    $warehouseStocks = [];
                    $totalStock = 0;
                    foreach ($warehouses as $wh) {
                        $qty = $stockMap[$varKey][$wh->id] ?? 0;
                        $warehouseStocks[$wh->id] = $qty;
                        $totalStock += $qty;
                    }
                    $flatItems[] = [
                        'key' => 'var-' . $variation->id,
                        'product_id' => $product->id,
                        'product_variation_id' => $variation->id,
                        'name' => $product->name . ' (' . $variation->variation_name_string . ')',
                        'parent_name' => $product->name,
                        'variant_name' => $variation->variation_name_string,
                        'sku' => $variation->sku,
                        'barcode' => $variation->barcode,
                        'description' => $product->description ?? '',
                        'tags' => $product->tags ?? [],
                        'image' => $product->image ?? '/images/product-placeholder.png',
                        'price' => (float) ($variation->retail_price ?? $variation->selling_price ?? $product->selling_price ?? 0),
                        'wholesale_price' => (float) ($variation->wholesale_price ?? $product->wholesale_price ?? 0),
                        'cost_price' => (float) ($variation->cost_price ?? $product->cost_price ?? 0),
                        'tax_rate' => (float) ($variation->tax_rate ?? $product->tax_rate ?? 0),
                        'tax_ids' => $variation->taxes ?? $product->taxes ?? [],
                        'warehouse_stocks' => $warehouseStocks,
                        'total_stock' => $totalStock,
                        'track_inventory' => (bool) $product->track_inventory,
                        'unit' => $product->unit?->short_name ?? $product->unit_of_measure ?? 'pcs',
                        'category' => $product->category?->name ?? 'Uncategorized',
                        'category_id' => $product->category_id,
                        'category_path' => $catPath,
                        'brand_id' => $product->brand_id,
                        'brand_name' => $brandName,
                        'brand' => $brandName,
                    ];
                }
            } else {
                $prodKey = $product->id . '-null';
                $warehouseStocks = [];
                $totalStock = 0;
                foreach ($warehouses as $wh) {
                    $qty = $stockMap[$prodKey][$wh->id] ?? 0;
                    $warehouseStocks[$wh->id] = $qty;
                    $totalStock += $qty;
                }
                $flatItems[] = [
                    'key' => 'prod-' . $product->id,
                    'product_id' => $product->id,
                    'product_variation_id' => null,
                    'name' => $product->name,
                    'parent_name' => $product->name,
                    'variant_name' => null,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'description' => $product->description ?? '',
                    'tags' => $product->tags ?? [],
                    'image' => $product->image ?? '/images/product-placeholder.png',
                    'price' => (float) ($product->selling_price ?? 0),
                    'wholesale_price' => (float) ($product->wholesale_price ?? 0),
                    'cost_price' => (float) ($product->cost_price ?? 0),
                    'tax_rate' => (float) ($product->tax_rate ?? 0),
                    'tax_ids' => $product->taxes ?? [],
                    'warehouse_stocks' => $warehouseStocks,
                    'total_stock' => $totalStock,
                    'track_inventory' => (bool) $product->track_inventory,
                    'unit' => $product->unit?->short_name ?? $product->unit_of_measure ?? 'pcs',
                    'category' => $product->category?->name ?? 'Uncategorized',
                    'category_id' => $product->category_id,
                    'category_path' => $catPath,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $brandName,
                    'brand' => $brandName,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'items' => $flatItems,
            'data' => $flatItems,
        ]);
    }

    /**
     * Download CSV template for importing products.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $columns = [
            'name',
            'sku',
            'barcode',
            'type',
            'service_type',
            'service_detail',
            'short_description',
            'description',
            'category_name',
            'brand_name',
            'supplier_name',
            'warehouse_name',
            'cost_price',
            'selling_price',
            'wholesale_price',
            'stock_quantity',
            'min_stock_level',
            'max_stock_level',
            'unit_of_measure',
            'tax_rate',
            'track_inventory',
            'is_active',
            'batch_number',
            'expiry_date',
            'discount_type',
            'discount_value',
            'tags',
            'has_variations',
            'variation_name',
            'variation_sku',
            'variation_cost_price',
            'variation_selling_price',
            'variation_wholesale_price',
            'variation_stock_quantity'
        ];

        $sample1 = [
            'Sample Product 1',
            'SKU-1001',
            '123456789012',
            'product',
            '',
            '',
            'Sample short desc',
            'Sample full description for item',
            'General',
            'Generic',
            'Sample Supplier',
            'Main Warehouse',
            '50.00',
            '100.00',
            '80.00',
            '100',
            '10',
            '500',
            'pcs',
            '0.00',
            '1',
            '1',
            'BATCH-001',
            '2026-12-31',
            'percentage',
            '0.00',
            'sample,electronics',
            '0',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        $sample2 = [
            'Sample Product 2',
            'SKU-1002',
            '987654321098',
            'product',
            '',
            '',
            'Another sample desc',
            'Detailed item description',
            'General',
            'Generic',
            'Sample Supplier',
            'Main Warehouse',
            '20.00',
            '40.00',
            '35.00',
            '50',
            '5',
            '200',
            'kg',
            '5.00',
            '1',
            '1',
            '',
            '',
            'fixed',
            '0.00',
            'grocery',
            '0',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        $sample3 = [
            'T-Shirt Collection',
            'SKU-1003',
            '555444333221',
            'product',
            '',
            '',
            'Cotton T-Shirt with Variations',
            'High quality apparel item with size/color options',
            'Apparel',
            'Generic',
            'Sample Supplier',
            'Main Warehouse',
            '15.00',
            '30.00',
            '25.00',
            '80',
            '10',
            '300',
            'pcs',
            '0.00',
            '1',
            '1',
            '',
            '',
            'percentage',
            '0.00',
            'apparel,summer',
            '1',
            'Red / Medium',
            'SKU-1003-RED-M',
            '15.00',
            '30.00',
            '25.00',
            '40'
        ];

        $callback = function () use ($columns, $sample1, $sample2, $sample3) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, $sample1);
            fputcsv($file, $sample2);
            fputcsv($file, $sample3);
            fclose($file);
        };

        return response()->streamDownload($callback, 'product_import_template.csv', $headers);
    }

    /**
     * Export products to CSV format.
     */
    public function export(Request $request)
    {
        $filename = 'products_export_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $companyId = auth()->user()->current_company_id;
        $products = Product::with(['category', 'brand', 'supplier', 'variations'])
            ->where('company_id', $companyId)
            ->where('status', '!=', 'draft')
            ->latest()
            ->get();

        $columns = [
            'name',
            'sku',
            'barcode',
            'type',
            'service_type',
            'service_detail',
            'short_description',
            'description',
            'category_name',
            'brand_name',
            'supplier_name',
            'warehouse_name',
            'cost_price',
            'selling_price',
            'wholesale_price',
            'stock_quantity',
            'min_stock_level',
            'max_stock_level',
            'unit_of_measure',
            'tax_rate',
            'track_inventory',
            'is_active',
            'batch_number',
            'expiry_date',
            'discount_type',
            'discount_value',
            'tags',
            'has_variations',
            'variation_name',
            'variation_sku',
            'variation_cost_price',
            'variation_selling_price',
            'variation_wholesale_price',
            'variation_stock_quantity'
        ];

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            foreach ($products as $product) {
                $tags = is_array($product->tags) ? implode(',', $product->tags) : ($product->tags ?? '');

                if ($product->has_variations && $product->variations && $product->variations->count() > 0) {
                    foreach ($product->variations as $var) {
                        fputcsv($file, [
                            $product->name,
                            $product->sku,
                            $product->barcode,
                            $product->type ?? 'product',
                            $product->service_type ?? '',
                            $product->service_detail ?? '',
                            $product->short_description,
                            $product->description,
                            $product->category ? $product->category->name : '',
                            $product->brand ? $product->brand->name : '',
                            $product->supplier ? ($product->supplier->name ?? $product->supplier->company_name ?? '') : '',
                            'Main Warehouse',
                            $product->cost_price,
                            $product->selling_price,
                            $product->wholesale_price,
                            $product->stock_quantity,
                            $product->min_stock_level,
                            $product->max_stock_level,
                            $product->unit_of_measure,
                            $product->tax_rate,
                            $product->track_inventory ? '1' : '0',
                            $product->is_active ? '1' : '0',
                            $product->batch_number,
                            $product->expiry_date ? (is_string($product->expiry_date) ? $product->expiry_date : $product->expiry_date->format('Y-m-d')) : '',
                            $product->discount_type,
                            $product->discount_value,
                            $tags,
                            '1',
                            $var->variation_name_string ?? '',
                            $var->sku ?? '',
                            $var->cost_price ?? '',
                            $var->selling_price ?? $var->retail_price ?? '',
                            $var->wholesale_price ?? '',
                            $var->stock_quantity ?? ''
                        ]);
                    }
                } else {
                    fputcsv($file, [
                        $product->name,
                        $product->sku,
                        $product->barcode,
                        $product->type ?? 'product',
                        $product->service_type ?? '',
                        $product->service_detail ?? '',
                        $product->short_description,
                        $product->description,
                        $product->category ? $product->category->name : '',
                        $product->brand ? $product->brand->name : '',
                        $product->supplier ? ($product->supplier->name ?? $product->supplier->company_name ?? '') : '',
                        'Main Warehouse',
                        $product->cost_price,
                        $product->selling_price,
                        $product->wholesale_price,
                        $product->stock_quantity,
                        $product->min_stock_level,
                        $product->max_stock_level,
                        $product->unit_of_measure,
                        $product->tax_rate,
                        $product->track_inventory ? '1' : '0',
                        $product->is_active ? '1' : '0',
                        $product->batch_number,
                        $product->expiry_date ? (is_string($product->expiry_date) ? $product->expiry_date : $product->expiry_date->format('Y-m-d')) : '',
                        $product->discount_type,
                        $product->discount_value,
                        $tags,
                        '0',
                        '',
                        '',
                        '',
                        '',
                        '',
                        ''
                    ]);
                }
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Parse XLSX file into array of rows without external dependencies.
     */
    private function parseXlsxToRows(string $filePath): array
    {
        $rows = [];
        if (!class_exists('ZipArchive')) {
            return [];
        }
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $sharedStrings = [];
            if (($ssIndex = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $ssXml = $zip->getFromIndex($ssIndex);
                $xml = @simplexml_load_string($ssXml);
                if ($xml && isset($xml->si)) {
                    foreach ($xml->si as $val) {
                        if (isset($val->t)) {
                            $sharedStrings[] = (string) $val->t;
                        } elseif (isset($val->r)) {
                            $t = '';
                            foreach ($val->r as $r) {
                                $t .= (string) $r->t;
                            }
                            $sharedStrings[] = $t;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }

            if (($sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml')) !== false) {
                $sheetXml = $zip->getFromIndex($sheetIndex);
                $xml = @simplexml_load_string($sheetXml);
                if ($xml && isset($xml->sheetData->row)) {
                    foreach ($xml->sheetData->row as $r) {
                        $row = [];
                        foreach ($r->c as $c) {
                            $cellRef = (string) $c['r'];
                            $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                            $colIndex = 0;
                            for ($i = 0; $i < strlen($colLetters); $i++) {
                                $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - 64);
                            }
                            $colIndex--; // 0-indexed

                            $t = (string) $c['t'];
                            $v = (string) $c->v;
                            if ($t === 's' && isset($sharedStrings[(int) $v])) {
                                $val = $sharedStrings[(int) $v];
                            } else {
                                $val = $v;
                            }
                            $row[$colIndex] = $val;
                        }
                        if (!empty($row)) {
                            $maxCol = max(array_keys($row));
                            for ($i = 0; $i <= $maxCol; $i++) {
                                if (!isset($row[$i]))
                                    $row[$i] = '';
                            }
                            ksort($row);
                            $rows[] = array_values($row);
                        }
                    }
                }
            }
            $zip->close();
        }
        return $rows;
    }

    /**
     * Import products from CSV or XLSX file.
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file uploaded.',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        $allRows = [];
        if (in_array($extension, ['xlsx', 'xls'])) {
            $allRows = $this->parseXlsxToRows($filePath);
        }

        // Fallback or CSV processing
        if (empty($allRows)) {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to read the uploaded file.'
                ], 400);
            }

            // Read UTF-8 BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            while (($row = fgetcsv($handle)) !== false) {
                $allRows[] = $row;
            }
            fclose($handle);
        }

        if (empty($allRows)) {
            return response()->json([
                'success' => false,
                'message' => 'The file appears to be empty.'
            ], 400);
        }

        // Header row
        $header = array_shift($allRows);
        if (empty($header)) {
            return response()->json([
                'success' => false,
                'message' => 'Header row is missing in the file.'
            ], 400);
        }

        // Normalize header keys
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', (string) $colName))));
            $headerMap[$normalized] = $index;
        }

        $getValueByAliases = function (array $aliases) use ($headerMap) {
            return function ($row) use ($aliases, $headerMap) {
                foreach ($aliases as $alias) {
                    if (isset($headerMap[$alias]) && isset($row[$headerMap[$alias]])) {
                        $val = trim((string) $row[$headerMap[$alias]]);
                        if ($val !== '')
                            return $val;
                    }
                }
                return null;
            };
        };

        $getName = $getValueByAliases(['name', 'product_name', 'item_name', 'product', 'item', 'title', 'productname', 'itemname', 'item_name_description']);
        $getSku = $getValueByAliases(['sku', 'product_sku', 'item_sku', 'code', 'product_code', 'item_code']);
        $getBarcode = $getValueByAliases(['barcode', 'upc', 'ean', 'barcode_number']);
        $getType = $getValueByAliases(['type', 'item_type']);
        $getServiceType = $getValueByAliases(['service_type']);
        $getServiceDetail = $getValueByAliases(['service_detail']);
        $getShortDesc = $getValueByAliases(['short_description', 'short_desc', 'summary']);
        $getDesc = $getValueByAliases(['description', 'desc', 'details', 'full_description']);
        $getCost = $getValueByAliases(['cost_price', 'cost', 'buy_price', 'purchase_price']);
        $getSelling = $getValueByAliases(['selling_price', 'price', 'unit_price', 'retail_price', 'sale_price', 'sellingprice']);
        $getWholesale = $getValueByAliases(['wholesale_price', 'wholesale', 'wholesale_rate']);
        $getStock = $getValueByAliases(['stock_quantity', 'stock', 'quantity', 'qty', 'opening_stock']);
        $getMinStock = $getValueByAliases(['min_stock_level', 'min_stock', 'minimum_stock']);
        $getMaxStock = $getValueByAliases(['max_stock_level', 'max_stock', 'maximum_stock']);
        $getUnit = $getValueByAliases(['unit_of_measure', 'unit', 'uom', 'unit_name']);
        $getCategory = $getValueByAliases(['category_name', 'category', 'cat', 'category_id']);
        $getBrand = $getValueByAliases(['brand_name', 'brand', 'make', 'brand_id']);
        $getSupplier = $getValueByAliases(['supplier_name', 'supplier', 'vendor', 'supplier_id']);
        $getWarehouse = $getValueByAliases(['warehouse_name', 'warehouse', 'warehouse_id', 'warehouse_title']);
        $getTax = $getValueByAliases(['tax_rate', 'tax', 'vat', 'tax_percentage']);
        $getTrackInv = $getValueByAliases(['track_inventory', 'track_stock']);
        $getIsActive = $getValueByAliases(['is_active', 'active', 'status']);
        $getBatch = $getValueByAliases(['batch_number', 'batch', 'batch_no']);
        $getExpiry = $getValueByAliases(['expiry_date', 'expiry', 'exp_date']);
        $getDiscountType = $getValueByAliases(['discount_type']);
        $getDiscountVal = $getValueByAliases(['discount_value', 'discount']);
        $getTags = $getValueByAliases(['tags', 'tag']);

        // Variation Aliases
        $getHasVariations = $getValueByAliases(['has_variations', 'has_variants', 'is_variant']);
        $getVarName = $getValueByAliases(['variation_name', 'variant_name', 'variation', 'variant']);
        $getVarSku = $getValueByAliases(['variation_sku', 'variant_sku']);
        $getVarCost = $getValueByAliases(['variation_cost_price', 'variant_cost_price', 'variation_cost']);
        $getVarSelling = $getValueByAliases(['variation_selling_price', 'variant_selling_price', 'variation_retail_price', 'variation_price']);
        $getVarWholesale = $getValueByAliases(['variation_wholesale_price', 'variant_wholesale_price', 'variation_wholesale']);
        $getVarStock = $getValueByAliases(['variation_stock_quantity', 'variant_stock_quantity', 'variation_stock', 'variant_stock']);

        $companyId = auth()->user()->current_company_id;
        $imported = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];
        $rowNum = 1;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($allRows as $row) {
                $rowNum++;

                // Skip completely blank rows
                if (empty($row) || (count($row) === 1 && trim((string) $row[0]) === '')) {
                    continue;
                }

                $name = $getName($row);
                if (!$name) {
                    $errors[] = [
                        'row' => $rowNum,
                        'errors' => ['Product name is required.']
                    ];
                    continue;
                }

                // Warehouse resolving
                $warehouseName = $getWarehouse($row) ?? 'Main Warehouse';
                $warehouse = \App\Models\Warehouse::where('company_id', $companyId)
                    ->where(function ($q) use ($warehouseName) {
                        $q->where('name', $warehouseName)->orWhere('id', $warehouseName);
                    })->first();

                if (!$warehouse) {
                    $warehouse = \App\Models\Warehouse::create([
                        'company_id' => $companyId,
                        'name' => is_numeric($warehouseName) ? 'Main Warehouse' : $warehouseName,
                        'is_default' => true,
                        'is_active' => true,
                    ]);
                }

                $categoryName = $getCategory($row);
                $categoryId = null;
                if ($categoryName) {
                    $category = Category::where('company_id', $companyId)
                        ->where(function ($q) use ($categoryName) {
                            $q->where('name', $categoryName)->orWhere('id', $categoryName);
                        })->first();
                    if (!$category && !is_numeric($categoryName)) {
                        $category = Category::create([
                            'company_id' => $companyId,
                            'name' => $categoryName,
                            'slug' => Str::slug($categoryName),
                            'is_active' => true,
                        ]);
                    }
                    if ($category) {
                        $categoryId = $category->id;
                    }
                }

                $brandName = $getBrand($row);
                $brandId = null;
                if ($brandName) {
                    $brand = Brand::where('company_id', $companyId)
                        ->where(function ($q) use ($brandName) {
                            $q->where('name', $brandName)->orWhere('id', $brandName);
                        })->first();
                    if (!$brand && !is_numeric($brandName)) {
                        $brand = Brand::create([
                            'company_id' => $companyId,
                            'name' => $brandName,
                            'slug' => Str::slug($brandName),
                            'is_active' => true,
                        ]);
                    }
                    if ($brand) {
                        $brandId = $brand->id;
                    }
                }

                $supplierName = $getSupplier($row);
                $supplierId = null;
                if ($supplierName) {
                    $supplier = Supplier::where('company_id', $companyId)
                        ->where(function ($q) use ($supplierName) {
                            $q->where('name', $supplierName)
                                ->orWhere('company_name', $supplierName)
                                ->orWhere('id', $supplierName);
                        })->first();
                    if ($supplier) {
                        $supplierId = $supplier->id;
                    }
                }

                $sku = $getSku($row);
                if (!$sku) {
                    $sku = 'SKU-' . strtoupper(Str::random(8));
                }

                $barcode = $getBarcode($row);
                $type = $getType($row) ?? 'product';
                $serviceType = $getServiceType($row);
                $serviceDetail = $getServiceDetail($row);
                $shortDescription = $getShortDesc($row);
                $description = $getDesc($row);
                $costPrice = floatval($getCost($row) ?? 0);
                $sellingPrice = floatval($getSelling($row) ?? 0);
                $wholesalePrice = floatval($getWholesale($row) ?? 0);
                $stockQuantity = intval($getStock($row) ?? 0);
                $minStockLevel = intval($getMinStock($row) ?? 0);
                $maxStockLevel = intval($getMaxStock($row) ?? 0);
                $unitOfMeasure = $getUnit($row) ?? 'pcs';
                $taxRaw = $getTax($row);
                $taxRate = 0;
                if ($taxRaw !== null && $taxRaw !== '') {
                    if (preg_match('/[0-9]+(\.[0-9]+)?/', (string) $taxRaw, $matches)) {
                        $taxRate = floatval($matches[0]);
                    } else {
                        $taxRate = floatval($taxRaw);
                    }
                }
                $productTaxes = [];
                if ($taxRate > 0) {
                    $taxRecord = \App\Models\Tax::where('company_id', $companyId)
                        ->where(function ($q) use ($taxRate, $taxRaw) {
                            $q->where('value', $taxRate)
                                ->orWhere('name', 'LIKE', "%{$taxRate}%")
                                ->orWhere('name', 'LIKE', "%{$taxRaw}%");
                        })->first();

                    if (!$taxRecord) {
                        $taxName = is_string($taxRaw) && trim($taxRaw) !== '' && !is_numeric(trim($taxRaw))
                            ? trim($taxRaw)
                            : "Tax {$taxRate}%";

                        $taxRecord = \App\Models\Tax::create([
                            'company_id' => $companyId,
                            'name' => $taxName,
                            'value' => $taxRate,
                            'type' => 'percentage',
                            'is_active' => true,
                        ]);
                    }
                    $productTaxes = [$taxRecord->id];
                }

                $trackInventoryVal = $getTrackInv($row);
                $trackInventory = $trackInventoryVal !== null ? in_array(strtolower((string) $trackInventoryVal), ['1', 'true', 'yes']) : true;

                $isActiveVal = $getIsActive($row);
                $isActive = $isActiveVal !== null ? in_array(strtolower((string) $isActiveVal), ['1', 'true', 'yes', 'active']) : true;

                $batchNumber = $getBatch($row);
                $expiryDate = $getExpiry($row) ? $getExpiry($row) : null;
                $discountType = $getDiscountType($row) ?? 'percentage';
                $discountValue = floatval($getDiscountVal($row) ?? 0);

                $tagsRaw = $getTags($row);
                $tags = [];
                if ($tagsRaw) {
                    if (is_string($tagsRaw)) {
                        $tags = array_map('trim', explode(',', $tagsRaw));
                    } elseif (is_array($tagsRaw)) {
                        $tags = $tagsRaw;
                    }
                }

                // Automatically save imported tags into the tag module (tags database table)
                foreach ($tags as $tagName) {
                    $cleanTag = trim((string) $tagName);
                    if ($cleanTag !== '') {
                        \App\Models\Tag::firstOrCreate([
                            'company_id' => $companyId,
                            'name' => $cleanTag,
                        ]);
                    }
                }

                $existingProduct = Product::where('company_id', $companyId)->where('sku', $sku)->first();
                if ($existingProduct) {
                    $updatedCount++;
                } else {
                    $createdCount++;
                }

                $product = Product::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'sku' => $sku,
                    ],
                    [
                        'name' => $name,
                        'barcode' => $barcode,
                        'type' => $type,
                        'service_type' => $serviceType,
                        'service_detail' => $serviceDetail,
                        'short_description' => $shortDescription,
                        'description' => $description,
                        'cost_price' => $costPrice,
                        'selling_price' => $sellingPrice,
                        'wholesale_price' => $wholesalePrice,
                        'stock_quantity' => $stockQuantity,
                        'min_stock_level' => $minStockLevel,
                        'max_stock_level' => $maxStockLevel,
                        'unit_of_measure' => $unitOfMeasure,
                        'category_id' => $categoryId,
                        'brand_id' => $brandId,
                        'supplier_id' => $supplierId,
                        'tax_rate' => $taxRate,
                        'taxes' => $productTaxes,
                        'track_inventory' => $trackInventory,
                        'is_active' => $isActive,
                        'status' => $isActive ? 'active' : 'inactive',
                        'batch_number' => $batchNumber,
                        'expiry_date' => $expiryDate,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue,
                        'tags' => $tags,
                    ]
                );

                // Create / update inventory record in specified warehouse
                \App\Models\Inventory::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                        'product_variation_id' => null,
                    ],
                    [
                        'stock_qty' => $stockQuantity,
                        'min_stock_level' => $minStockLevel,
                    ]
                );

                // Process variations if provided
                $hasVariationsVal = $getHasVariations($row);
                $hasVariations = $hasVariationsVal !== null ? in_array(strtolower((string) $hasVariationsVal), ['1', 'true', 'yes']) : false;
                $varName = $getVarName($row);
                $varSku = $getVarSku($row);

                if ($hasVariations || $varName || $varSku) {
                    $vSku = $varSku ?: ($sku . '-' . Str::slug($varName ?: 'V1'));
                    $vCost = floatval($getVarCost($row) ?? $costPrice);
                    $vSelling = floatval($getVarSelling($row) ?? $sellingPrice);
                    $vWholesale = floatval($getVarWholesale($row) ?? $wholesalePrice);
                    $vStock = intval($getVarStock($row) ?? $stockQuantity);

                    $comboKey = Str::slug($varName ?: 'variation');
                    if (empty($comboKey)) {
                        $comboKey = 'var-' . strtolower(Str::random(6));
                    }

                    $variation = \App\Models\ProductVariation::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'sku' => $vSku,
                        ],
                        [
                            'company_id' => $companyId,
                            'combination_key' => $comboKey,
                            'variation_name_string' => $varName ?: 'Standard Variation',
                            'cost_price' => $vCost,
                            'retail_price' => $vSelling,
                            'wholesale_price' => $vWholesale,
                            'stock_qty' => $vStock,
                            'tax_rate' => $taxRate,
                            'taxes' => $productTaxes,
                        ]
                    );

                    \App\Models\Inventory::updateOrCreate(
                        [
                            'company_id' => $companyId,
                            'warehouse_id' => $warehouse->id,
                            'product_id' => $product->id,
                            'product_variation_id' => $variation->id,
                        ],
                        [
                            'stock_qty' => $vStock,
                            'min_stock_level' => $minStockLevel,
                        ]
                    );

                    $totalVarStock = \App\Models\ProductVariation::where('product_id', $product->id)->sum('stock_qty');
                    $product->update([
                        'has_variations' => true,
                        'stock_quantity' => $totalVarStock,
                    ]);
                }

                $imported++;
            }

            \Illuminate\Support\Facades\DB::commit();

            $msg = "Import completed: {$createdCount} new product(s) created";
            if ($updatedCount > 0) {
                $msg .= ", {$updatedCount} existing product(s) updated";
            }
            $msg .= '.';

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'created' => $createdCount,
                'updated' => $updatedCount,
                'errors' => $errors,
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error processing import file: ' . $e->getMessage()
            ], 500);
        }
    }
}
