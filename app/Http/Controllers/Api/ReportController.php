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
    /**
     * Daily & Period Sales Summary Report
     */
    public function salesSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $sales = Sale::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(paid_amount) as total_paid,
                SUM(total_amount - paid_amount) as total_due,
                SUM(discount_amount) as total_discount,
                SUM(tax_amount) as total_tax,
                AVG(total_amount) as average_sale
            ')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $summary = [
            'total_sales' => (int) $sales->sum('total_sales'),
            'total_revenue' => (float) round($sales->sum('total_revenue'), 2),
            'total_paid' => (float) round($sales->sum('total_paid'), 2),
            'total_due' => (float) round($sales->sum('total_due'), 2),
            'total_discount' => (float) round($sales->sum('total_discount'), 2),
            'total_tax' => (float) round($sales->sum('total_tax'), 2),
            'average_sale' => (float) round($sales->count() > 0 ? $sales->avg('average_sale') : 0, 2),
            'daily_breakdown' => $sales
        ];

        return response()->json($summary);
    }

    /**
     * Monthly Revenue Analysis Report
     */
    public function monthlyRevenue(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        $revenue = Sale::whereYear('created_at', $year)
            ->selectRaw('
                MONTH(created_at) as month,
                MONTHNAME(created_at) as month_name,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(paid_amount) as total_paid,
                SUM(total_amount - paid_amount) as total_due,
                AVG(total_amount) as average_sale
            ')
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get();

        $totalRevenue = (float) round($revenue->sum('total_revenue'), 2);
        $totalPaid = (float) round($revenue->sum('total_paid'), 2);
        $totalDue = (float) round($revenue->sum('total_due'), 2);
        $totalSalesCount = (int) $revenue->sum('total_sales');

        return response()->json([
            'year' => (int) $year,
            'total_revenue' => $totalRevenue,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
            'total_sales' => $totalSalesCount,
            'monthly_breakdown' => $revenue
        ]);
    }

    /**
     * Top Selling Products & Velocity Report
     */
    public function topSellingProducts(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $limit = (int) $request->get('limit', 25);

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $products = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.created_at', [$startDateTime, $endDateTime])
            ->selectRaw('
                products.id,
                products.name as product_name,
                products.sku,
                COALESCE(categories.name, "Uncategorized") as category_name,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.quantity * sale_items.unit_price) as total_revenue,
                SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as total_cost,
                COUNT(DISTINCT sales.id) as times_sold
            ')
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->total_quantity = (float) $item->total_quantity;
                $item->total_revenue = (float) round($item->total_revenue, 2);
                $item->total_cost = (float) round($item->total_cost, 2);
                $item->net_profit = (float) round($item->total_revenue - $item->total_cost, 2);
                $item->profit_margin_percent = $item->total_revenue > 0
                    ? (float) round(($item->net_profit / $item->total_revenue) * 100, 1)
                    : 0;
                return $item;
            });

        $totalUnits = $products->sum('total_quantity');
        $totalRevenue = $products->sum('total_revenue');
        $totalProfit = $products->sum('net_profit');

        return response()->json([
            'total_units_sold' => $totalUnits,
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'products' => $products
        ]);
    }

    /**
     * Customer Sales & Credit Analysis Report
     */
    public function customerSalesAnalysis(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $limit = (int) $request->get('limit', 25);

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $customers = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.created_at', [$startDateTime, $endDateTime])
            ->selectRaw('
                customers.id,
                CONCAT("CUST-", customers.id) as customer_code,
                customers.name as customer_name,
                customers.email,
                customers.phone,
                COUNT(sales.id) as total_purchases,
                SUM(sales.total_amount) as total_spent,
                SUM(sales.paid_amount) as total_paid,
                SUM(sales.total_amount - sales.paid_amount) as total_due,
                AVG(sales.total_amount) as average_purchase,
                MAX(sales.created_at) as last_purchase
            ')
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.phone')
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($c) {
                $c->total_spent = (float) round($c->total_spent, 2);
                $c->total_paid = (float) round($c->total_paid, 2);
                $c->total_due = (float) round($c->total_due, 2);
                $c->average_purchase = (float) round($c->average_purchase, 2);
                return $c;
            });

        return response()->json([
            'active_customers' => $customers->count(),
            'total_spent' => (float) round($customers->sum('total_spent'), 2),
            'total_paid' => (float) round($customers->sum('total_paid'), 2),
            'total_due' => (float) round($customers->sum('total_due'), 2),
            'customers' => $customers
        ]);
    }

    /**
     * Inventory Stock Level Report (Supports Product Variations)
     */
    public function inventoryReport(Request $request)
    {
        $lowStockThreshold = (int) $request->get('low_stock_threshold', 10);

        $products = Product::with(['category', 'variations'])->get();
        $itemsList = [];

        foreach ($products as $p) {
            $categoryName = $p->category->name ?? 'Uncategorized';

            if ($p->has_variations && $p->variations->count() > 0) {
                foreach ($p->variations as $v) {
                    $qty = (float) ($v->stock_qty ?? 0);
                    $cost = (float) ($v->cost_price ?? $p->cost_price ?? 0);
                    $retail = (float) ($v->retail_price ?? $v->wholesale_price ?? $p->selling_price ?? 0);
                    $minLevel = (float) ($v->min_stock_alert ?? $p->min_stock_level ?? $lowStockThreshold);

                    if ($qty <= 0) {
                        $status = 'Out of Stock';
                    } elseif ($qty <= $minLevel) {
                        $status = 'Low Stock';
                    } else {
                        $status = 'In Stock';
                    }

                    $varNameStr = $v->variation_name_string ?: $v->sku;
                    $itemsList[] = [
                        'id' => $p->id . '_var_' . $v->id,
                        'sku' => $v->sku ?: $p->sku,
                        'product_name' => $p->name . " ({$varNameStr})",
                        'category_name' => $categoryName,
                        'stock_quantity' => $qty,
                        'min_stock_level' => $minLevel,
                        'cost_price' => $cost,
                        'selling_price' => $retail,
                        'stock_status' => $status,
                        'inventory_cost_value' => round($qty * $cost, 2),
                        'inventory_retail_value' => round($qty * $retail, 2),
                        'potential_margin' => round(($qty * $retail) - ($qty * $cost), 2),
                    ];
                }
            } else {
                $qty = (float) ($p->stock_quantity ?? 0);
                $cost = (float) ($p->cost_price ?? 0);
                $retail = (float) ($p->selling_price ?? 0);
                $minLevel = (float) ($p->min_stock_level ?? $lowStockThreshold);

                if ($qty <= 0) {
                    $status = 'Out of Stock';
                } elseif ($qty <= $minLevel) {
                    $status = 'Low Stock';
                } else {
                    $status = 'In Stock';
                }

                $itemsList[] = [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'product_name' => $p->name,
                    'category_name' => $categoryName,
                    'stock_quantity' => $qty,
                    'min_stock_level' => $minLevel,
                    'cost_price' => $cost,
                    'selling_price' => $retail,
                    'stock_status' => $status,
                    'inventory_cost_value' => round($qty * $cost, 2),
                    'inventory_retail_value' => round($qty * $retail, 2),
                    'potential_margin' => round(($qty * $retail) - ($qty * $cost), 2),
                ];
            }
        }

        $inventory = collect($itemsList);

        $summary = [
            'total_products' => $inventory->count(),
            'total_units' => $inventory->sum('stock_quantity'),
            'in_stock_items' => $inventory->where('stock_status', 'In Stock')->count(),
            'low_stock_items' => $inventory->where('stock_status', 'Low Stock')->count(),
            'out_of_stock_items' => $inventory->where('stock_status', 'Out of Stock')->count(),
            'total_inventory_cost_value' => round($inventory->sum('inventory_cost_value'), 2),
            'total_inventory_retail_value' => round($inventory->sum('inventory_retail_value'), 2),
            'potential_gross_profit' => round($inventory->sum('potential_margin'), 2),
            'products' => $inventory->values()->all()
        ];

        return response()->json($summary);
    }

    /**
     * Low Stock Alert & Reorder Planning Report (Supports Product Variations)
     */
    public function lowStockAlert(Request $request)
    {
        $threshold = (int) $request->get('threshold', 10);

        $products = Product::with(['category', 'variations'])->get();
        $lowStockList = [];

        foreach ($products as $p) {
            $categoryName = $p->category->name ?? 'Uncategorized';

            if ($p->has_variations && $p->variations->count() > 0) {
                foreach ($p->variations as $v) {
                    $qty = (float) ($v->stock_qty ?? 0);
                    $min = (float) ($v->min_stock_alert ?? $p->min_stock_level ?? $threshold);
                    if ($qty <= $min || $qty <= $threshold) {
                        $deficit = max(0, $min - $qty + 10);
                        $cost = (float) ($v->cost_price ?? $p->cost_price ?? 0);
                        $varNameStr = $v->variation_name_string ?: $v->sku;

                        $lowStockList[] = [
                            'id' => $p->id . '_var_' . $v->id,
                            'sku' => $v->sku ?: $p->sku,
                            'product_name' => $p->name . " ({$varNameStr})",
                            'category_name' => $categoryName,
                            'stock_quantity' => $qty,
                            'min_stock_level' => $min,
                            'deficit_quantity' => $deficit,
                            'unit_cost' => $cost,
                            'reorder_cost' => round($deficit * $cost, 2),
                        ];
                    }
                }
            } else {
                $qty = (float) ($p->stock_quantity ?? 0);
                $min = (float) ($p->min_stock_level ?? $threshold);
                if ($qty <= $min || $qty <= $threshold) {
                    $deficit = max(0, $min - $qty + 10);
                    $cost = (float) ($p->cost_price ?? 0);

                    $lowStockList[] = [
                        'id' => $p->id,
                        'sku' => $p->sku,
                        'product_name' => $p->name,
                        'category_name' => $categoryName,
                        'stock_quantity' => $qty,
                        'min_stock_level' => $min,
                        'deficit_quantity' => $deficit,
                        'unit_cost' => $cost,
                        'reorder_cost' => round($deficit * $cost, 2),
                    ];
                }
            }
        }

        $lowStockProducts = collect($lowStockList)->sortBy('stock_quantity')->values();

        return response()->json([
            'alert_count' => $lowStockProducts->count(),
            'total_deficit_units' => $lowStockProducts->sum('deficit_quantity'),
            'total_reorder_cost' => round($lowStockProducts->sum('reorder_cost'), 2),
            'products' => $lowStockProducts
        ]);
    }

    /**
     * Inventory Valuation Report (Supports Product Variations)
     */
    public function inventoryValuation(Request $request)
    {
        $products = Product::with(['category', 'variations'])->get();
        $categoriesData = [];

        foreach ($products as $p) {
            $catName = $p->category->name ?? 'Uncategorized';

            if (!isset($categoriesData[$catName])) {
                $categoriesData[$catName] = [
                    'category_name' => $catName,
                    'product_count' => 0,
                    'total_quantity' => 0,
                    'cost_value' => 0.0,
                    'retail_value' => 0.0,
                ];
            }

            if ($p->has_variations && $p->variations->count() > 0) {
                foreach ($p->variations as $v) {
                    $qty = (float) ($v->stock_qty ?? 0);
                    $cost = (float) ($v->cost_price ?? $p->cost_price ?? 0);
                    $retail = (float) ($v->retail_price ?? $v->wholesale_price ?? $p->selling_price ?? 0);

                    $categoriesData[$catName]['product_count'] += 1;
                    $categoriesData[$catName]['total_quantity'] += $qty;
                    $categoriesData[$catName]['cost_value'] += round($qty * $cost, 2);
                    $categoriesData[$catName]['retail_value'] += round($qty * $retail, 2);
                }
            } else {
                $qty = (float) ($p->stock_quantity ?? 0);
                $cost = (float) ($p->cost_price ?? 0);
                $retail = (float) ($p->selling_price ?? 0);

                $categoriesData[$catName]['product_count'] += 1;
                $categoriesData[$catName]['total_quantity'] += $qty;
                $categoriesData[$catName]['cost_value'] += round($qty * $cost, 2);
                $categoriesData[$catName]['retail_value'] += round($qty * $retail, 2);
            }
        }

        $valuation = collect($categoriesData)->values()->map(function ($c) {
            $c['cost_value'] = (float) round($c['cost_value'], 2);
            $c['retail_value'] = (float) round($c['retail_value'], 2);
            $c['potential_profit'] = (float) round($c['retail_value'] - $c['cost_value'], 2);
            $c['margin_percent'] = $c['retail_value'] > 0
                ? (float) round(($c['potential_profit'] / $c['retail_value']) * 100, 1)
                : 0;
            return $c;
        });

        $totalCostValue = round($valuation->sum('cost_value'), 2);
        $totalRetailValue = round($valuation->sum('retail_value'), 2);
        $potentialProfit = round($totalRetailValue - $totalCostValue, 2);
        $overallMargin = $totalRetailValue > 0 ? round(($potentialProfit / $totalRetailValue) * 100, 1) : 0;

        return response()->json([
            'total_categories' => $valuation->count(),
            'total_cost_value' => $totalCostValue,
            'total_retail_value' => $totalRetailValue,
            'potential_profit' => $potentialProfit,
            'overall_margin_percent' => $overallMargin,
            'categories' => $valuation
        ]);
    }

    /**
     * Stock Movement Audit Trail Report
     */
    public function stockMovementHistory(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $productId = $request->get('product_id');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$startDateTime, $endDateTime])
            ->select([
                'sales.created_at',
                'sales.sale_number as reference_no',
                'products.name as product_name',
                'products.sku',
                'sale_items.quantity',
                'sale_items.unit_price',
                DB::raw('"Customer Sale" as movement_type')
            ]);

        if ($productId) {
            $query->where('products.id', $productId);
        }

        $movements = $query->orderBy('sales.created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($m) {
                $m->total_value = round($m->quantity * $m->unit_price, 2);
                return $m;
            });

        return response()->json([
            'total_movements' => $movements->count(),
            'total_units_moved' => $movements->sum('quantity'),
            'total_value_moved' => round($movements->sum('total_value'), 2),
            'movements' => $movements
        ]);
    }

    /**
     * Inactive Customer Report
     * Shows customers who have no sale invoices in the selected date range.
     * Optionally filters to only show those with pending payments (due amount > 0).
     */
    public function inactiveCustomers(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $pendingPaymentsOnly = filter_var($request->get('pending_payments_only', false), FILTER_VALIDATE_BOOLEAN);
        $search = $request->get('search');
        $statusFilter = $request->get('status');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Customers with no sales within start_date and end_date
        $query = Customer::whereDoesntHave('sales', function ($q) use ($startDateTime, $endDateTime) {
            $q->where(function ($sub) use ($startDateTime, $endDateTime) {
                $sub->whereBetween('sale_date', [$startDateTime, $endDateTime])
                    ->orWhere(function ($sub2) use ($startDateTime, $endDateTime) {
                        $sub2->whereNull('sale_date')
                            ->whereBetween('created_at', [$startDateTime, $endDateTime]);
                    });
            })
            ->where('is_refund', false)
            ->whereNotIn('status', ['void', 'voided', 'cancelled']);
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $customers = $query->with(['sales' => function ($q) {
            $q->where('is_refund', false)
              ->whereNotIn('status', ['void', 'voided', 'cancelled'])
              ->orderBy('sale_date', 'desc');
        }])->get()->map(function ($customer) {
            $lastSale = $customer->sales->first();
            $lastSaleDate = $lastSale ? ($lastSale->sale_date ? $lastSale->sale_date->format('Y-m-d') : $lastSale->created_at->format('Y-m-d')) : null;
            $lastSaleAmount = $lastSale ? (float) round($lastSale->total_amount, 2) : 0.0;

            // Calculate total outstanding due balance
            $dueAmount = (float) round($customer->sales->sum(function ($s) {
                return max(0, (float) $s->total_amount - (float) $s->paid_amount);
            }), 2);

            $totalSpent = (float) round($customer->sales->sum('total_amount'), 2);

            return [
                'id' => $customer->id,
                'customer_code' => 'CUST-' . $customer->id,
                'customer_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? $customer->mobile,
                'is_active' => (bool) $customer->is_active,
                'last_sale_date' => $lastSaleDate,
                'last_sale_amount' => $lastSaleAmount,
                'total_spent' => $totalSpent,
                'due_amount' => $dueAmount,
                'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d') : null,
            ];
        });

        if ($pendingPaymentsOnly) {
            $customers = $customers->filter(function ($c) {
                return $c['due_amount'] > 0;
            });
        }

        $customers = $customers->values();

        $totalInactive = $customers->count();
        $totalPendingPayments = (float) round($customers->sum('due_amount'), 2);
        $customersWithPending = $customers->where('due_amount', '>', 0)->count();
        $systemInactiveCount = $customers->where('is_active', false)->count();

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'pending_payments_only' => $pendingPaymentsOnly,
            'total_inactive_customers' => $totalInactive,
            'total_pending_payments' => $totalPendingPayments,
            'customers_with_pending' => $customersWithPending,
            'system_inactive_count' => $systemInactiveCount,
            'customers' => $customers
        ]);
    }

    /**
     * Business Assets & Inventory Valuation Breakdown Report
     */
    public function businessValuation(Request $request)
    {
        $products = Product::with(['category', 'variations'])->get();

        $rawMaterialsValue = 0;
        $finishedGoodsValue = 0;
        $standardProductsValue = 0;

        foreach ($products as $p) {
            if ($p->has_variations && $p->variations->count() > 0) {
                foreach ($p->variations as $v) {
                    $cost = (float) ($v->cost_price ?? $p->cost_price ?? 0);
                    $qty = (float) ($v->stock_qty ?? 0);
                    $val = $cost * $qty;

                    if ($p->item_type === 'raw_material') {
                        $rawMaterialsValue += $val;
                    } elseif ($p->item_type === 'finished_good') {
                        $finishedGoodsValue += $val;
                    } else {
                        $standardProductsValue += $val;
                    }
                }
            } else {
                $cost = (float) ($p->cost_price ?? 0);
                $qty = (float) ($p->stock_quantity ?? 0);
                $val = $cost * $qty;

                if ($p->item_type === 'raw_material') {
                    $rawMaterialsValue += $val;
                } elseif ($p->item_type === 'finished_good') {
                    $finishedGoodsValue += $val;
                } else {
                    $standardProductsValue += $val;
                }
            }
        }

        $assetSummary = json_decode(app(\App\Http\Controllers\Api\AssetController::class)->summary()->getContent(), true);
        $totalFixedAssetsValue = $assetSummary['total_current_valuation'] ?? 0;
        $totalFixedAssetsCost = $assetSummary['total_purchase_cost'] ?? 0;

        $totalInventoryValue = $rawMaterialsValue + $finishedGoodsValue + $standardProductsValue;
        $totalBusinessAssets = $totalInventoryValue + $totalFixedAssetsValue;

        return response()->json([
            'raw_materials_valuation' => round($rawMaterialsValue, 2),
            'finished_goods_valuation' => round($finishedGoodsValue, 2),
            'standard_inventory_valuation' => round($standardProductsValue, 2),
            'total_inventory_valuation' => round($totalInventoryValue, 2),
            'fixed_assets_cost' => round($totalFixedAssetsCost, 2),
            'fixed_assets_current_valuation' => round($totalFixedAssetsValue, 2),
            'total_business_assets_valuation' => round($totalBusinessAssets, 2),
        ]);
    }
}
