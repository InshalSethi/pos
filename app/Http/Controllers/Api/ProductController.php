<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
        $query = Product::with('category');

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

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
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
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode',
            'unit_of_measure' => 'nullable|string',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());
        $product->load('category');

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('category', 'saleItems.sale');

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'unit_of_measure' => 'nullable|string',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());
        $product->load('category');

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
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
                    'category_id' => !empty($data[3]) ? (int)$data[3] : null,
                    'selling_price' => !empty($data[4]) ? (float)$data[4] : 0,
                    'cost_price' => !empty($data[5]) ? (float)$data[5] : 0,
                    'stock_quantity' => !empty($data[6]) ? (int)$data[6] : 0,
                    'min_stock_level' => !empty($data[7]) ? (int)$data[7] : 0,
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
}
