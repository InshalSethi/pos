<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockThresholdService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['store']);
        $this->middleware('permission:products.edit')->only(['update']);
        $this->middleware('permission:products.delete')->only(['destroy']);
        $this->middleware('permission:products.import')->only(['import']);
        $this->middleware('permission:products.export')->only(['export']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'variations' => function($query) {
            $query->select('id', 'product_id', 'combination_key', 'variation_name_string', 'cost_price', 'retail_price', 'wholesale_price', 'tax_rate');
        }])->withCount('variations');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->get('tag'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'selling_price' => 'required_unless:has_variations,true|nullable|numeric|min:0',
            'wholesale_price' => 'required_unless:has_variations,true|nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode',
            'unit_of_measure' => 'nullable|string',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'has_variations' => 'boolean',
            'variations' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $data = $request->all();
            
            $hasVariantsActive = $request->boolean('has_variations') && !empty($request->variations) && count($request->variations) > 0;

            if ($hasVariantsActive) {
                $data['cost_price'] = 0.00;
                $data['selling_price'] = 0.00;
                $data['wholesale_price'] = 0.00;
                $data['tax_rate'] = 0.00;
                $data['stock_quantity'] = collect($request->variations)->sum(function ($row) {
                    return (int) ($row['stock_qty'] ?? 0);
                });
            } else {
                if (empty($data['selling_price'])) $data['selling_price'] = 0;
                if (empty($data['wholesale_price'])) $data['wholesale_price'] = 0;
                if (empty($data['cost_price'])) $data['cost_price'] = 0;
                if (empty($data['tax_rate'])) $data['tax_rate'] = 0;
                $data['has_variations'] = false;
            }

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('product-images', 'public');
                $data['image'] = '/storage/' . $path;
            } else {
                $data['image'] = null;
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

            if ($hasVariantsActive) {
                foreach ($request->variations as $index => $row) {
                    \App\Models\ProductVariation::create([
                        'product_id' => $product->id,
                        'combination_key' => $row['combination_key'] ?? $row['name_string'] ?? strval($index),
                        'variation_name_string' => $row['name_string'] ?? 'Default',
                        'sku' => $row['sku'] ?? 'SKU-' . strtoupper(uniqid()),
                        'barcode' => $row['barcode'] ?? null,
                        'retail_price' => $row['retail_price'] ?? 0,
                        'wholesale_price' => $row['wholesale_price'] ?? 0,
                        'cost_price' => $row['cost_price'] ?? 0,
                        'tax_rate' => $row['tax_rate'] ?? null,
                        'discount_type' => $row['discount_type'] ?? null,
                        'discount_value' => $row['discount_value'] ?? null,
                        'stock_qty' => $row['stock_qty'] ?? 0,
                        'min_stock_alert' => $row['min_stock_alert'] ?? 0,
                        'unit_of_measure' => $row['unit_of_measure'] ?? null,
                        'expiry_date' => $row['expiry_date'] ?? null,
                    ]);
                }
            }

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
        $product->load('category', 'saleItems.sale', 'variations', 'attributes');
        $product->loadCount('variations');

        return response()->json($product);
    }
    
    /**
     * Edit route mapping helper: ensures front-end views read variation attributes count state reactively.
     */
    public function edit($id)
    {
        // Include variations count structure explicitly
        $product = Product::with(['variations', 'attributes'])->withCount('variations')->findOrFail($id);
        return response()->json(['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'selling_price' => 'required_unless:has_variations,true|nullable|numeric|min:0',
            'wholesale_price' => 'required_unless:has_variations,true|nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'unit_of_measure' => 'nullable|string',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'has_variations' => 'boolean',
            'variations' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $data = $request->all();

            $hasVariantsActive = $request->boolean('has_variations') && !empty($request->variations) && count($request->variations) > 0;

            if ($hasVariantsActive) {
                $data['cost_price'] = 0.00;
                $data['selling_price'] = 0.00;
                $data['wholesale_price'] = 0.00;
                $data['tax_rate'] = 0.00;
                $data['stock_quantity'] = collect($request->variations)->sum(function ($row) {
                    return (int) ($row['stock_qty'] ?? 0);
                });
            } else {
                if (empty($data['selling_price'])) $data['selling_price'] = 0;
                if (empty($data['wholesale_price'])) $data['wholesale_price'] = 0;
                if (empty($data['cost_price'])) $data['cost_price'] = 0;
                if (empty($data['tax_rate'])) $data['tax_rate'] = 0;
                $data['has_variations'] = false;
            }

            if ($request->hasFile('image')) {
                if ($product->image) {
                    $oldPath = str_replace('/storage/', '', $product->image);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('image')->store('product-images', 'public');
                $data['image'] = '/storage/' . $path;
            } elseif ($request->input('image') === '') {
                if ($product->image) {
                    $oldPath = str_replace('/storage/', '', $product->image);
                    Storage::disk('public')->delete($oldPath);
                }
                $data['image'] = null;
            } else {
                // Keep the existing image if no new image was uploaded and it was not explicitly cleared
                unset($data['image']);
            }

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

            if ($hasVariantsActive) {
                // Purge old historical iterations for this target to prevent duplicate stacking id errors
                $product->variations()->delete();
                foreach ($request->variations as $index => $row) {
                    \App\Models\ProductVariation::create([
                        'product_id' => $product->id,
                        'combination_key' => $row['combination_key'] ?? $row['name_string'] ?? strval($index),
                        'variation_name_string' => $row['name_string'] ?? 'Default',
                        'sku' => $row['sku'] ?? 'SKU-' . strtoupper(uniqid()),
                        'barcode' => $row['barcode'] ?? null,
                        'retail_price' => $row['retail_price'] ?? 0,
                        'wholesale_price' => $row['wholesale_price'] ?? 0,
                        'cost_price' => $row['cost_price'] ?? 0,
                        'tax_rate' => $row['tax_rate'] ?? null,
                        'discount_type' => $row['discount_type'] ?? null,
                        'discount_value' => $row['discount_value'] ?? null,
                        'stock_qty' => $row['stock_qty'] ?? 0,
                        'min_stock_alert' => $row['min_stock_alert'] ?? 0,
                        'unit_of_measure' => $row['unit_of_measure'] ?? null,
                        'expiry_date' => $row['expiry_date'] ?? null,
                    ]);
                }
            } else {
                $product->variations()->delete();
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
     * Import products from CSV/XLSX file
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'csv') {
                $products = $this->importFromCsv($file);
            } else {
                $products = $this->importFromExcel($file);
            }

            $imported = 0;
            $errors = [];

            foreach ($products as $index => $productData) {
                try {
                    // Validate product data
                    $productValidator = Validator::make($productData, [
                        'name' => 'required|string|max:255',
                        'sku' => 'required|string|max:100|unique:products,sku',
                        'selling_price' => 'required|numeric|min:0',
                        'cost_price' => 'nullable|numeric|min:0',
                        'stock_quantity' => 'nullable|integer|min:0',
                        'category_id' => 'nullable|exists:categories,id',
                    ]);

                    if ($productValidator->fails()) {
                        $errors[] = [
                            'row' => $index + 2, // +2 because index starts at 0 and we skip header
                            'errors' => $productValidator->errors()->all()
                        ];
                        continue;
                    }

                    Product::create($productData);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 2,
                        'errors' => [$e->getMessage()]
                    ];
                }
            }

            return response()->json([
                'message' => "Import completed. {$imported} products imported successfully.",
                'imported' => $imported,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products to CSV
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Product::with(['category']);

        // Apply filters if provided
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_export_' . date('Y-m-d_H-i-s') . '.csv"',
        ];

        return response()->stream(function () use ($products) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Name',
                'SKU',
                'Description',
                'Category',
                'Selling Price',
                'Cost Price',
                'Stock Quantity',
                'Min Stock Level',
                'Unit',
                'Barcode',
                'Status'
            ]);

            // Add product data
            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->name,
                    $product->sku,
                    $product->description,
                    $product->category ? $product->category->name : '',
                    $product->selling_price,
                    $product->cost_price,
                    $product->stock_quantity,
                    $product->min_stock_level,
                    $product->unit,
                    $product->barcode,
                    $product->is_active ? 'Active' : 'Inactive'
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Download sample import template
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'name',
                'sku',
                'description',
                'category_id',
                'selling_price',
                'cost_price',
                'stock_quantity',
                'min_stock_level',
                'unit',
                'barcode'
            ]);

            // Add sample data
            fputcsv($handle, [
                'Sample Product 1',
                'SKU001',
                'This is a sample product description',
                '1',
                '19.99',
                '10.00',
                '100',
                '10',
                'pcs',
                '1234567890123'
            ]);

            fputcsv($handle, [
                'Sample Product 2',
                'SKU002',
                'Another sample product',
                '2',
                '29.99',
                '15.00',
                '50',
                '5',
                'kg',
                '9876543210987'
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import products from CSV file
     */
    private function importFromCsv($file): array
    {
        $products = [];
        $handle = fopen($file->getPathname(), 'r');

        // Skip header row
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 6) { // Minimum required fields
                $products[] = [
                    'name' => $data[0] ?? '',
                    'sku' => $data[1] ?? '',
                    'description' => $data[2] ?? '',
                    'category_id' => !empty($data[3]) ? (int) $data[3] : null,
                    'selling_price' => !empty($data[4]) ? (float) $data[4] : 0,
                    'cost_price' => !empty($data[5]) ? (float) $data[5] : 0,
                    'stock_quantity' => !empty($data[6]) ? (int) $data[6] : 0,
                    'min_stock_level' => !empty($data[7]) ? (int) $data[7] : 0,
                    'unit' => $data[8] ?? 'pcs',
                    'barcode' => $data[9] ?? '',
                    'is_active' => true,
                ];
            }
        }

        fclose($handle);
        return $products;
    }

    /**
     * Import products from Excel file (simplified - would need PhpSpreadsheet for full implementation)
     */
    private function importFromExcel($file): array
    {
        // For now, treat Excel files as CSV
        // In a real implementation, you would use PhpSpreadsheet library
        return $this->importFromCsv($file);
    }

    /**
     * Calculates the Cartesian Product of multi-dimensional attribute values arrays 
     * to auto-generate unique product variations grid entries on the fly.
     */
    public function generateCombinationsMatrix(array $attributeGroups) {
        // Input format example: [ [1, 2], [3, 4] ] -> (IDs of chosen values)
        $result = [[]];
        foreach ($attributeGroups as $property => $values) {
            if (empty($values)) continue;
            $append = [];
            foreach ($result as $productCombo) {
                foreach ($values as $item) {
                    $append[] = array_merge($productCombo, [$property => $item]);
                }
            }
            $result = $append;
        }
        
        // Returns array matrices containing sorted ID combinations keys
        return array_map(function($combo) {
            sort($combo); // Ensure chronological sequence consistency, e.g., always "1-5", never "5-1"
            return [
                'combination_key' => implode('-', $combo),
                'suggested_sku'   => 'SKU-' . strtoupper(uniqid())
            ];
        }, $result);
    }
}
