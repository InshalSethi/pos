<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function salesSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(paid_amount) as total_paid,
                AVG(total_amount) as average_sale
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_sales' => $sales->sum('total_sales'),
            'total_revenue' => $sales->sum('total_revenue'),
            'total_paid' => $sales->sum('total_paid'),
            'average_sale' => $sales->avg('average_sale'),
            'daily_breakdown' => $sales
        ];

        return response()->json($summary);
    }

    public function monthlyRevenue(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        $revenue = Sale::whereYear('created_at', $year)
            ->selectRaw('
                MONTH(created_at) as month,
                MONTHNAME(created_at) as month_name,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(paid_amount) as total_paid
            ')
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get();

        return response()->json($revenue);
    }

    public function topSellingProducts(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $limit = $request->get('limit', 10);

        $products = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->selectRaw('
                products.id,
                products.name,
                products.sku,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.quantity * sale_items.unit_price) as total_revenue,
                COUNT(DISTINCT sales.id) as times_sold
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }

    public function customerSalesAnalysis(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $limit = $request->get('limit', 10);

        $customers = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->selectRaw('
                customers.id,
                customers.name,
                customers.email,
                COUNT(sales.id) as total_purchases,
                SUM(sales.total_amount) as total_spent,
                AVG(sales.total_amount) as average_purchase,
                MAX(sales.created_at) as last_purchase
            ')
            ->groupBy('customers.id', 'customers.name', 'customers.email')
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($customers);
    }

    public function inventoryReport(Request $request)
    {
        $lowStockThreshold = $request->get('low_stock_threshold', 10);

        $inventory = Product::with('category')
            ->selectRaw('
                products.*,
                CASE
                    WHEN stock_quantity <= min_stock_level THEN "Low Stock"
                    WHEN stock_quantity = 0 THEN "Out of Stock"
                    ELSE "In Stock"
                END as stock_status,
                (stock_quantity * cost_price) as inventory_value
            ')
            ->get();

        $summary = [
            'total_products' => $inventory->count(),
            'low_stock_items' => $inventory->where('stock_quantity', '<=', $lowStockThreshold)->count(),
            'out_of_stock_items' => $inventory->where('stock_quantity', 0)->count(),
            'total_inventory_value' => $inventory->sum('inventory_value'),
            'products' => $inventory
        ];

        return response()->json($summary);
    }

    public function lowStockAlert(Request $request)
    {
        $threshold = $request->get('threshold', 10);

        $lowStockProducts = Product::with('category')
            ->where('stock_quantity', '<=', $threshold)
            ->orWhere('stock_quantity', '<=', DB::raw('min_stock_level'))
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return response()->json($lowStockProducts);
    }

    public function inventoryValuation(Request $request)
    {
        $valuation = Product::with('category')
            ->selectRaw('
                categories.name as category_name,
                COUNT(products.id) as product_count,
                SUM(products.stock_quantity) as total_quantity,
                SUM(products.stock_quantity * products.cost_price) as cost_value,
                SUM(products.stock_quantity * products.selling_price) as retail_value
            ')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->get();

        $totalCostValue = $valuation->sum('cost_value');
        $totalRetailValue = $valuation->sum('retail_value');

        return response()->json([
            'total_cost_value' => $totalCostValue,
            'total_retail_value' => $totalRetailValue,
            'potential_profit' => $totalRetailValue - $totalCostValue,
            'categories' => $valuation
        ]);
    }

    public function stockMovementHistory(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $productId = $request->get('product_id');

        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select([
                'sales.created_at',
                'sales.sale_number',
                'products.name as product_name',
                'products.sku',
                'sale_items.quantity',
                'sale_items.unit_price',
                DB::raw('"Sale" as movement_type')
            ]);

        if ($productId) {
            $query->where('products.id', $productId);
        }

        $movements = $query->orderBy('sales.created_at', 'desc')->get();

        return response()->json($movements);
    }
}
