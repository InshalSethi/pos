<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Transaction;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\AccountingSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics with real operational data
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $dateFromStr = $request->input('date_from');
        $dateToStr = $request->input('date_to');
        $chartPeriod = $request->input('chart_period', '6_months');

        $fromDate = ($dateFromStr !== null && $dateFromStr !== '') ? Carbon::parse($dateFromStr)->startOfDay() : null;
        $toDate = ($dateToStr !== null && $dateToStr !== '') ? Carbon::parse($dateToStr)->endOfDay() : null;

        $statistics = [
            'sales' => $this->getRealSalesStatistics($fromDate, $toDate),
            'returns' => $this->getRealReturnsStatistics($fromDate, $toDate),
            'purchases' => $this->getRealPurchasesStatistics($fromDate, $toDate),
            'purchase_returns' => $this->getRealPurchaseReturnsStatistics($fromDate, $toDate),
            'expenses' => $this->getRealExpensesStatistics($fromDate, $toDate),
            'payments' => $this->getRealPaymentStatistics($fromDate, $toDate),
            'low_stock' => $this->getLowStockStatistics(),
            'sales_trend' => $this->getSalesTrend($fromDate, $toDate),
            'sales_purchases_chart' => $this->getSalesPurchasesChartData($fromDate, $toDate, $chartPeriod),
            'financial_distribution' => $this->getFinancialDistribution($fromDate, $toDate),
            'recent_invoices' => $this->getRecentInvoices($fromDate, $toDate),
            'stock_history' => $this->getStockHistory($fromDate, $toDate),
            'payment_trends' => $this->getPaymentTrends($fromDate, $toDate),
            'stock_alerts' => $this->getStockAlerts(),
            'expense_categories' => $this->getExpenseCategories($fromDate, $toDate),
            'recent_transactions' => $this->getRecentTransactions($fromDate, $toDate),
            'accounting_summary' => $this->getAccountingSummary($fromDate, $toDate),
            'inventory_valuation' => $this->getInventoryValuation(),
            'product_intelligence' => $this->getProductIntelligence($fromDate, $toDate),
            'expiry_alerts' => $this->getExpiryAlerts(),
        ];

        return response()->json($statistics);
    }

    /**
     * Helper to format product brand and category tree details
     */
    private function formatProductDetails(?Product $product): array
    {
        if (!$product) {
            return [
                'name' => 'Unknown Product',
                'sku' => '',
                'brand_name' => 'N/A',
                'main_category' => 'Uncategorized',
                'sub_category' => null,
                'child_category' => null,
                'category_path' => 'Uncategorized',
            ];
        }

        $brandName = $product->brand->name ?? null;

        $categories = [];
        $cat = $product->category;
        while ($cat) {
            array_unshift($categories, $cat->name);
            $cat = $cat->parent;
        }

        $mainCategory = $categories[0] ?? 'Uncategorized';
        $subCategory = $categories[1] ?? null;
        $childCategory = $categories[2] ?? null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'brand_name' => $brandName,
            'main_category' => $mainCategory,
            'sub_category' => $subCategory,
            'child_category' => $childCategory,
            'category_path' => count($categories) > 0 ? implode(' > ', $categories) : 'Uncategorized',
        ];
    }

    /**
     * Real Sales Statistics
     */
    private function getRealSalesStatistics(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $query = Sale::where('is_refund', false)
            ->whereNotIn('status', ['cancelled', 'void']);

        if ($fromDate && $toDate) {
            $query->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('sale_date', [$fromDate, $toDate])
                  ->orWhereBetween('created_at', [$fromDate, $toDate]);
            });
        }

        $totalAmount = (float) $query->sum('total_amount');
        $count = $query->count();
        $averageSale = $count > 0 ? $totalAmount / $count : 0;

        return [
            'total_amount' => $totalAmount,
            'count' => $count,
            'average_sale' => $averageSale
        ];
    }

    /**
     * Real Sale Returns Statistics
     */
    private function getRealReturnsStatistics(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $query = Sale::where('is_refund', true)
            ->whereNotIn('status', ['cancelled', 'void']);

        if ($fromDate && $toDate) {
            $query->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('sale_date', [$fromDate, $toDate])
                  ->orWhereBetween('created_at', [$fromDate, $toDate]);
            });
        }

        $totalAmount = (float) $query->sum('total_amount');
        $count = $query->count();

        return [
            'total_amount' => $totalAmount,
            'count' => $count
        ];
    }

    /**
     * Real Purchase Orders Statistics
     */
    private function getRealPurchasesStatistics(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $query = PurchaseOrder::whereNotIn('status', ['cancelled']);

        if ($fromDate && $toDate) {
            $query->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('order_date', [$fromDate, $toDate])
                  ->orWhereBetween('created_at', [$fromDate, $toDate]);
            });
        }

        $totalAmount = (float) $query->sum('total_amount');
        $count = $query->count();

        return [
            'total_amount' => $totalAmount,
            'count' => $count
        ];
    }

    /**
     * Real Purchase Returns Statistics
     */
    private function getRealPurchaseReturnsStatistics(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $query = PurchaseReturn::whereNotIn('status', ['cancelled']);

        if ($fromDate && $toDate) {
            $query->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('return_date', [$fromDate, $toDate])
                  ->orWhereBetween('created_at', [$fromDate, $toDate]);
            });
        }

        $totalAmount = (float) $query->sum('total_amount');
        $count = $query->count();

        return [
            'total_amount' => $totalAmount,
            'count' => $count
        ];
    }

    /**
     * Real Expenses Statistics
     */
    private function getRealExpensesStatistics(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $query = Expense::whereIn('status', ['approved', 'paid']);

        if ($fromDate && $toDate) {
            $query->whereBetween('expense_date', [$fromDate, $toDate]);
        }

        $totalAmount = (float) $query->sum('amount');
        $count = $query->count();

        return [
            'total_amount' => $totalAmount,
            'count' => $count
        ];
    }

    /**
     * Real Payment Statistics (Payments In & Payments Out)
     */
    private function getRealPaymentStatistics(?Carbon $fromDate, ?Carbon $toDate): array
    {
        // 1. Payment In (Receipts OR Sales Paid + Standalone Income Transactions)
        $receiptsQuery = PaymentReceipt::whereIn('receipt_type', ['payment_in', 'customer_payment', 'sales_payment'])
            ->where('status', '!=', 'cancelled');
        if ($fromDate && $toDate) {
            $receiptsQuery->whereBetween('receipt_date', [$fromDate, $toDate]);
        }
        $receiptsIn = (float) $receiptsQuery->sum('amount');

        $salesPaidQuery = Sale::where('is_refund', false)->whereNotIn('status', ['cancelled', 'void']);
        if ($fromDate && $toDate) {
            $salesPaidQuery->whereBetween('sale_date', [$fromDate, $toDate]);
        }
        $salesPaid = (float) $salesPaidQuery->sum('paid_amount');

        // Standalone Income Transactions (exclude transactions generated from payment receipts)
        $txIncomeQuery = Transaction::whereIn('type', ['income', 'payment_in', 'credit'])
            ->where(function ($q) {
                $q->whereNull('number')
                  ->orWhere(function ($sub) {
                      $sub->where('number', 'not like', 'RCP%')
                          ->where('reference', 'not like', 'RCP%');
                  });
            });
        if ($fromDate && $toDate) {
            $txIncomeQuery->whereBetween('paid_at', [$fromDate, $toDate]);
        }
        $txIncomeStandalone = (float) $txIncomeQuery->sum('amount');

        $paymentReceivedTotal = max($receiptsIn, $salesPaid) + $txIncomeStandalone;

        // 2. Payment Out (Payments / Expenses + PO Amount Paid)
        $expensesQuery = Expense::whereIn('status', ['approved', 'paid']);
        if ($fromDate && $toDate) {
            $expensesQuery->whereBetween('expense_date', [$fromDate, $toDate]);
        }
        $expensesTotal = (float) $expensesQuery->sum('amount');

        $poPaidQuery = PurchaseOrder::whereNotIn('status', ['cancelled']);
        if ($fromDate && $toDate) {
            $poPaidQuery->whereBetween('order_date', [$fromDate, $toDate]);
        }
        $poPaidTotal = (float) $poPaidQuery->sum('amount_paid');

        $paymentsOutQuery = Payment::where('status', '!=', 'cancelled');
        if ($fromDate && $toDate) {
            $paymentsOutQuery->whereBetween('payment_date', [$fromDate, $toDate]);
        }
        $paymentsOutTotal = (float) $paymentsOutQuery->sum('amount');

        $paymentSentTotal = max($expensesTotal + $poPaidTotal, $paymentsOutTotal + $expensesTotal);

        // 3. Transactions counts
        $totalTxnsCount = PaymentReceipt::where('status', '!=', 'cancelled')->count()
            + Payment::where('status', '!=', 'cancelled')->count()
            + Expense::whereIn('status', ['approved', 'paid'])->count();

        // 4. Pending payments (Receivables & Payables with balance due)
        $pendingSales = Sale::where('is_refund', false)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->whereRaw('(total_amount - COALESCE(paid_amount, 0)) > 0.01');
        if ($fromDate && $toDate) {
            $pendingSales->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        $pendingPOs = PurchaseOrder::whereNotIn('status', ['cancelled'])
            ->where(function ($q) {
                $q->where('due_amount', '>', 0)
                  ->orWhereRaw('(total_amount - COALESCE(amount_paid, 0)) > 0.01');
            });
        if ($fromDate && $toDate) {
            $pendingPOs->whereBetween('order_date', [$fromDate, $toDate]);
        }

        $pendingSalesCount = $pendingSales->count();
        $pendingPOsCount = $pendingPOs->count();

        $salesDue = (float) $pendingSales->get()->sum(function ($s) {
            return max(0, (float) $s->total_amount - (float) $s->paid_amount);
        });
        $posDue = (float) $pendingPOs->get()->sum(function ($p) {
            return $p->due_amount > 0 ? (float) $p->due_amount : max(0, (float) $p->total_amount - (float) $p->amount_paid);
        });

        $pendingSalesDue = round($salesDue, 2);
        $pendingPOsDue = round($posDue, 2);
        $pendingCount = $pendingSalesCount + $pendingPOsCount;
        $pendingAmount = round($salesDue + $posDue, 2);

        return [
            'total_payments' => $totalTxnsCount,
            'total_amount' => $paymentReceivedTotal + $paymentSentTotal,
            'pending_payments' => $pendingCount,
            'pending_amount' => $pendingAmount,
            'pending_receivables_count' => $pendingSalesCount,
            'pending_receivables_amount' => $pendingSalesDue,
            'pending_payables_count' => $pendingPOsCount,
            'pending_payables_amount' => $pendingPOsDue,
            'payment_sent' => [
                'total_amount' => $paymentSentTotal,
                'change_percentage' => 0
            ],
            'payment_received' => [
                'total_amount' => $paymentReceivedTotal,
                'change_percentage' => 0
            ],
        ];
    }

    /**
     * Get financial distribution for circle chart
     */
    private function getFinancialDistribution(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $sales = $this->getRealSalesStatistics($fromDate, $toDate)['total_amount'];
        $saleReturns = $this->getRealReturnsStatistics($fromDate, $toDate)['total_amount'];
        $purchases = $this->getRealPurchasesStatistics($fromDate, $toDate)['total_amount'];
        $purchaseReturns = $this->getRealPurchaseReturnsStatistics($fromDate, $toDate)['total_amount'];

        return [
            ['name' => 'Real Sales', 'value' => $sales, 'color' => '#10b981'],
            ['name' => 'Sale Returns', 'value' => $saleReturns, 'color' => '#f43f5e'],
            ['name' => 'Purchase Orders', 'value' => $purchases, 'color' => '#3b82f6'],
            ['name' => 'Purchase Returns', 'value' => $purchaseReturns, 'color' => '#f59e0b'],
        ];
    }

    /**
     * Get low stock statistics
     */
    private function getLowStockStatistics(): array
    {
        $lowStockCount = Product::active()
            ->where('track_inventory', true)
            ->whereRaw('stock_quantity <= COALESCE(min_stock_level, 0)')
            ->count();

        return [
            'count' => $lowStockCount
        ];
    }

    /**
     * Get sales trend (last 7 days or date range breakdown)
     */
    private function getSalesTrend(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $startDate = $fromDate ?? now()->subDays(6)->startOfDay();
        $endDate = $toDate ?? now()->endOfDay();

        $trend = [];
        $days = min(30, max(1, $startDate->diffInDays($endDate) + 1));

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);

            $dailySales = Sale::whereDate('sale_date', $date)
                ->where('is_refund', false)
                ->whereNotIn('status', ['cancelled', 'void'])
                ->sum('total_amount');

            $trend[] = [
                'date' => $date->toDateString(),
                'amount' => (float) $dailySales
            ];
        }

        return $trend;
    }

    /**
     * Get real sales and purchases chart data without mock sales targets
     */
    private function getSalesPurchasesChartData(?Carbon $fromDate, ?Carbon $toDate, string $period = '6_months'): array
    {
        if (!$fromDate || !$toDate) {
            if ($period === '7_days') {
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                $groupBy = 'day';
            } elseif ($period === '1_month') {
                $startDate = now()->subDays(29)->startOfDay();
                $endDate = now()->endOfDay();
                $groupBy = 'day';
            } elseif ($period === '3_months') {
                $startDate = now()->subMonths(3)->startOfMonth();
                $endDate = now()->endOfMonth();
                $groupBy = 'month';
            } elseif ($period === '1_year') {
                $startDate = now()->subYear()->startOfMonth();
                $endDate = now()->endOfMonth();
                $groupBy = 'month';
            } else {
                // 6_months (default)
                $startDate = now()->subMonths(5)->startOfMonth();
                $endDate = now()->endOfMonth();
                $groupBy = 'month';
            }
        } else {
            $startDate = $fromDate;
            $endDate = $toDate;
            $daysDiff = $startDate->diffInDays($endDate);
            $groupBy = $daysDiff > 45 ? 'month' : 'day';
        }

        $chartData = [];

        if ($groupBy === 'day') {
            $days = min(60, max(1, $startDate->diffInDays($endDate) + 1));
            for ($i = 0; $i < $days; $i++) {
                $currentDate = $startDate->copy()->addDays($i);

                $sales = Sale::where('is_refund', false)
                    ->whereNotIn('status', ['cancelled', 'void'])
                    ->where(function ($q) use ($currentDate) {
                        $q->whereDate('sale_date', $currentDate)
                          ->orWhere(function ($sub) use ($currentDate) {
                              $sub->whereNull('sale_date')->whereDate('created_at', $currentDate);
                          });
                    })
                    ->sum('total_amount');

                $purchases = PurchaseOrder::whereNotIn('status', ['cancelled'])
                    ->where(function ($q) use ($currentDate) {
                        $q->whereDate('order_date', $currentDate)
                          ->orWhere(function ($sub) use ($currentDate) {
                              $sub->whereNull('order_date')->whereDate('created_at', $currentDate);
                          });
                    })
                    ->sum('total_amount');

                $chartData[] = [
                    'date' => $currentDate->format('M d'),
                    'sales' => (float) $sales,
                    'purchases' => (float) $purchases,
                ];
            }
        } else {
            $currentMonth = $startDate->copy()->startOfMonth();
            $endMonth = $endDate->copy()->startOfMonth();

            while ($currentMonth->lte($endMonth)) {
                $monthStart = $currentMonth->copy()->startOfMonth();
                $monthEnd = $currentMonth->copy()->endOfMonth();

                $sales = Sale::where('is_refund', false)
                    ->whereNotIn('status', ['cancelled', 'void'])
                    ->where(function ($q) use ($monthStart, $monthEnd) {
                        $q->whereBetween('sale_date', [$monthStart, $monthEnd])
                          ->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                              $sub->whereNull('sale_date')->whereBetween('created_at', [$monthStart, $monthEnd]);
                          });
                    })
                    ->sum('total_amount');

                $purchases = PurchaseOrder::whereNotIn('status', ['cancelled'])
                    ->where(function ($q) use ($monthStart, $monthEnd) {
                        $q->whereBetween('order_date', [$monthStart, $monthEnd])
                          ->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                              $sub->whereNull('order_date')->whereBetween('created_at', [$monthStart, $monthEnd]);
                          });
                    })
                    ->sum('total_amount');

                $chartData[] = [
                    'date' => $currentMonth->format('M Y'),
                    'sales' => (float) $sales,
                    'purchases' => (float) $purchases,
                ];

                $currentMonth->addMonth();
            }
        }

        return $chartData;
    }

    /**
     * Get recent invoices (combine Sales and Purchase Orders)
     */
    private function getRecentInvoices(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $salesQuery = Sale::with('customer')
            ->whereNotIn('status', ['cancelled', 'void'])
            ->orderBy('sale_date', 'desc')
            ->limit(10);

        if ($fromDate && $toDate) {
            $salesQuery->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        $invoices = $salesQuery->get()->map(function ($sale) {
            return [
                'invoice_id' => $sale->sale_number ?? ('INV' . str_pad($sale->id, 6, '0', STR_PAD_LEFT)),
                'customer' => $sale->customer->name ?? $sale->customer_phone ?? 'Walk-in Customer',
                'sales_date' => $sale->sale_date ? $sale->sale_date->toDateString() : $sale->created_at->toDateString(),
                'paid_amount' => (float) $sale->total_amount,
                'sales_status' => $sale->is_refund ? 'Returned' : 'Delivered',
                'status_color' => $sale->is_refund ? 'red' : 'green'
            ];
        });

        return $invoices->toArray();
    }

    /**
     * Get stock history
     */
    private function getStockHistory(?Carbon $fromDate, ?Carbon $toDate): array
    {
        // 1. Total Sales Items
        $salesItemsQuery = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
            $q->where('is_refund', false)->whereNotIn('status', ['cancelled', 'void']);
            if ($fromDate && $toDate) {
                $q->whereBetween('sale_date', [$fromDate, $toDate]);
            }
        });
        $totalSalesItems = (int) $salesItemsQuery->sum('quantity');

        // 2. Total Purchase Items
        $purchaseItemsQuery = PurchaseOrderItem::whereHas('purchaseOrder', function ($q) use ($fromDate, $toDate) {
            $q->whereNotIn('status', ['cancelled']);
            if ($fromDate && $toDate) {
                $q->whereBetween('order_date', [$fromDate, $toDate]);
            }
        });
        $totalPurchaseItems = (int) $purchaseItemsQuery->sum('quantity_ordered');

        // 3. Total Sale Return Items
        $saleReturnItemsQuery = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
            $q->where('is_refund', true)->whereNotIn('status', ['cancelled', 'void']);
            if ($fromDate && $toDate) {
                $q->whereBetween('sale_date', [$fromDate, $toDate]);
            }
        });
        $totalSaleReturnItems = (int) $saleReturnItemsQuery->sum('quantity');

        // 4. Total Purchase Return Items
        $purchaseReturnItemsQuery = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($fromDate, $toDate) {
            $q->whereNotIn('status', ['cancelled']);
            if ($fromDate && $toDate) {
                $q->whereBetween('return_date', [$fromDate, $toDate]);
            }
        });
        $totalPurchaseReturnItems = (int) $purchaseReturnItemsQuery->sum('quantity');

        return [
            'total_sales_items' => [
                'count' => $totalSalesItems,
                'change_percentage' => 0
            ],
            'total_purchase_items' => [
                'count' => $totalPurchaseItems,
                'change_percentage' => 0
            ],
            'total_return_items' => [
                'count' => $totalSaleReturnItems + $totalPurchaseReturnItems,
                'change_percentage' => 0
            ]
        ];
    }

    /**
     * Get payment trends
     */
    private function getPaymentTrends(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $trends = [];
        $startDate = $fromDate ?? now()->subDays(14)->startOfDay();
        $endDate = $toDate ?? now()->endOfDay();

        $days = min(30, max(1, $startDate->diffInDays($endDate) + 1));

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i);

            $paymentSent = Expense::whereDate('expense_date', $currentDate)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount')
                + PurchaseOrder::whereDate('order_date', $currentDate)
                ->whereNotIn('status', ['cancelled'])
                ->sum('amount_paid');

            $paymentReceived = Sale::whereDate('sale_date', $currentDate)
                ->where('is_refund', false)
                ->whereNotIn('status', ['cancelled', 'void'])
                ->sum('paid_amount');

            $trends[] = [
                'date' => $currentDate->format('j M'),
                'payment_sent' => (float) $paymentSent,
                'payment_received' => (float) $paymentReceived
            ];
        }

        return $trends;
    }

    /**
     * Get stock alerts with product brand and category tree details
     */
    private function getStockAlerts(): array
    {
        $lowStockProducts = Product::active()
            ->with(['brand', 'category.parent.parent'])
            ->where('track_inventory', true)
            ->whereRaw('stock_quantity <= COALESCE(min_stock_level, 0)')
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $details = $this->formatProductDetails($product);
                $details['quantity'] = (int) $product->stock_quantity;
                $details['minimum_level'] = (int) ($product->min_stock_level ?? 5);
                return $details;
            });

        return $lowStockProducts->toArray();
    }

    /**
     * Get expense categories
     */
    private function getExpenseCategories(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $query = Expense::with('category')
            ->whereIn('status', ['approved', 'paid'])
            ->select('category_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category_id');

        if ($fromDate && $toDate) {
            $query->whereBetween('expense_date', [$fromDate, $toDate]);
        }

        $categories = $query->get()->map(function ($expense) {
            return [
                'name' => $expense->category->name ?? 'Uncategorized',
                'amount' => (float) $expense->total_amount
            ];
        });

        return $categories->toArray();
    }

    /**
     * Get recent transactions
     */
    private function getRecentTransactions(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $querySales = Sale::whereNotIn('status', ['cancelled', 'void'])
            ->orderBy('sale_date', 'desc')
            ->limit(5);

        if ($fromDate && $toDate) {
            $querySales->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        $sales = $querySales->get()->map(function ($sale) {
            return [
                'id' => $sale->id,
                'type' => $sale->is_refund ? 'return' : 'sale',
                'description' => $sale->is_refund ? "Return #{$sale->sale_number}" : "Sale #{$sale->sale_number}",
                'amount' => (float) $sale->total_amount,
                'created_at' => $sale->sale_date ? $sale->sale_date->toDateTimeString() : $sale->created_at->toDateTimeString()
            ];
        });

        $queryPOs = PurchaseOrder::whereNotIn('status', ['cancelled'])
            ->orderBy('order_date', 'desc')
            ->limit(3);

        if ($fromDate && $toDate) {
            $queryPOs->whereBetween('order_date', [$fromDate, $toDate]);
        }

        $purchases = $queryPOs->get()->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'type' => 'purchase',
                'description' => "Purchase Order #{$purchase->po_number}",
                'amount' => (float) $purchase->total_amount,
                'created_at' => $purchase->order_date ? $purchase->order_date->toDateTimeString() : $purchase->created_at->toDateTimeString()
            ];
        });

        return $sales->concat($purchases)
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get accounting summary
     */
    private function getAccountingSummary(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $totalDebits = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
            $q->where('status', 'posted');
            if ($fromDate && $toDate) {
                $q->whereBetween('entry_date', [$fromDate, $toDate]);
            }
        })->sum('debit_amount');

        $totalCredits = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
            $q->where('status', 'posted');
            if ($fromDate && $toDate) {
                $q->whereBetween('entry_date', [$fromDate, $toDate]);
            }
        })->sum('credit_amount');

        $entriesQuery = JournalEntry::where('status', 'posted');
        if ($fromDate && $toDate) {
            $entriesQuery->whereBetween('entry_date', [$fromDate, $toDate]);
        }

        return [
            'total_debits' => (float) $totalDebits,
            'total_credits' => (float) $totalCredits,
            'total_entries' => $entriesQuery->count(),
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
        ];
    }

    /**
     * Get smart inventory valuation (Supports Product Variations)
     */
    private function getInventoryValuation(): array
    {
        $products = Product::active()->with('variations')->get();
        $cost = 0.0;
        $retail = 0.0;

        foreach ($products as $p) {
            if ($p->has_variations && $p->variations->count() > 0) {
                foreach ($p->variations as $v) {
                    $qty = (float) ($v->stock_qty ?? 0);
                    $c = (float) ($v->cost_price ?? $p->cost_price ?? 0);
                    $r = (float) ($v->retail_price ?? $v->wholesale_price ?? $p->selling_price ?? 0);

                    $cost += round($qty * $c, 2);
                    $retail += round($qty * $r, 2);
                }
            } else {
                $qty = (float) ($p->stock_quantity ?? 0);
                $c = (float) ($p->cost_price ?? 0);
                $r = (float) ($p->selling_price ?? 0);

                $cost += round($qty * $c, 2);
                $retail += round($qty * $r, 2);
            }
        }

        $cost = round($cost, 2);
        $retail = round($retail, 2);
        $potentialProfit = round($retail - $cost, 2);

        return [
            'total_cost_value' => $cost,
            'total_retail_value' => $retail,
            'potential_profit' => $potentialProfit
        ];
    }

    /**
     * Get product intelligence (Fast/Slow moving with brand and category details)
     */
    private function getProductIntelligence(?Carbon $fromDate, ?Carbon $toDate): array
    {
        $fastMovingQuery = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
                $q->whereNotIn('status', ['cancelled', 'void'])->where('is_refund', false);
                if ($fromDate && $toDate) {
                    $q->whereBetween('sale_date', [$fromDate, $toDate]);
                }
            })
            ->with(['product.brand', 'product.category.parent.parent'])
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5);

        $fastMoving = $fastMovingQuery->get()->map(function ($item) {
            $details = $this->formatProductDetails($item->product);
            $details['total_sold'] = (int) $item->total_sold;
            return $details;
        })->values();

        $slowMoving = Product::active()
            ->with(['brand', 'category.parent.parent'])
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $details = $this->formatProductDetails($product);
                $details['stock_quantity'] = (int) $product->stock_quantity;
                return $details;
            })->values();

        return [
            'fast_moving' => $fastMoving,
            'slow_moving' => $slowMoving
        ];
    }

    /**
     * Get expiry alerts with product brand and category tree details
     */
    private function getExpiryAlerts(): array
    {
        $expiringSoon = Product::active()
            ->with(['brand', 'category.parent.parent'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $daysToExpiry = now()->diffInDays(Carbon::parse($product->expiry_date), false);
                $details = $this->formatProductDetails($product);
                $details['expiry_date'] = Carbon::parse($product->expiry_date)->toDateString();
                $details['days_to_expire'] = $daysToExpiry;
                $details['status'] = $daysToExpiry < 0 ? 'Expired' : ($daysToExpiry <= 7 ? 'Critical' : 'Warning');
                return $details;
            });

        return [
            'count' => $expiringSoon->count(),
            'items' => $expiringSoon
        ];
    }
}
