<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\Expense;
use App\Models\Product;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $dateFrom = $request->get('date_from', today()->toDateString());
        $dateTo = $request->get('date_to', today()->toDateString());

        $fromDate = Carbon::parse($dateFrom);
        $toDate = Carbon::parse($dateTo);

        $statistics = [
            'sales' => $this->getAccountingBasedSalesStatistics($fromDate, $toDate),
            'returns' => $this->getAccountingBasedReturnsStatistics($fromDate, $toDate),
            'purchases' => $this->getAccountingBasedPurchasesStatistics($fromDate, $toDate),
            'expenses' => $this->getAccountingBasedExpensesStatistics($fromDate, $toDate),
            'payments' => $this->getPaymentStatistics($fromDate, $toDate),
            'low_stock' => $this->getLowStockStatistics(),
            'sales_trend' => $this->getSalesTrend($fromDate, $toDate),
            'sales_purchases_chart' => $this->getSalesPurchasesChartData($fromDate, $toDate),
            'devices_breakdown' => $this->getDevicesBreakdown(),
            'recent_invoices' => $this->getRecentInvoices($fromDate, $toDate),
            'stock_history' => $this->getStockHistory($fromDate, $toDate),
            'payment_trends' => $this->getPaymentTrends($fromDate, $toDate),
            'stock_alerts' => $this->getStockAlerts(),
            'expense_categories' => $this->getExpenseCategories($fromDate, $toDate),
            'recent_transactions' => $this->getRecentTransactions($fromDate, $toDate),
            'accounting_summary' => $this->getAccountingSummary($fromDate, $toDate)
        ];

        return response()->json($statistics);
    }

    /**
     * Get sales statistics for the selected date range
     */
    private function getSalesStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $sales = Sale::whereBetween('sale_date', [$fromDate, $toDate])
                    ->where('is_refund', false)
                    ->where('status', 'completed');

        return [
            'total_amount' => $sales->sum('total_amount'),
            'count' => $sales->count(),
            'average_sale' => $sales->avg('total_amount') ?: 0
        ];
    }

    /**
     * Get returns statistics for the selected date range
     */
    private function getReturnsStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $returns = Sale::whereBetween('sale_date', [$fromDate, $toDate])
                      ->where('is_refund', true)
                      ->where('status', 'completed');

        return [
            'total_amount' => $returns->sum('total_amount'),
            'count' => $returns->count()
        ];
    }

    /**
     * Get purchases statistics for the selected date range
     */
    private function getPurchasesStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $purchases = PurchaseOrder::whereBetween('order_date', [$fromDate, $toDate])
                                 ->whereIn('status', ['received', 'partially_received']);

        return [
            'total_amount' => $purchases->sum('total_amount'),
            'count' => $purchases->count()
        ];
    }

    /**
     * Get expenses statistics for the selected date range
     */
    private function getExpensesStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $expenses = Expense::whereBetween('expense_date', [$fromDate, $toDate])
                          ->whereIn('status', ['approved', 'paid']);

        return [
            'total_amount' => $expenses->sum('amount'),
            'count' => $expenses->count()
        ];
    }

    /**
     * Get low stock statistics
     */
    private function getLowStockStatistics(): array
    {
        $lowStockCount = Product::where('track_inventory', true)
                               ->where('is_active', true)
                               ->whereRaw('stock_quantity <= low_stock_threshold')
                               ->count();

        return [
            'count' => $lowStockCount
        ];
    }

    /**
     * Get sales trend for the last 7 days
     */
    private function getSalesTrend(Carbon $selectedDate): array
    {
        $trend = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = $selectedDate->copy()->subDays($i);
            
            $dailySales = Sale::whereDate('sale_date', $date)
                             ->where('is_refund', false)
                             ->where('status', 'completed')
                             ->sum('total_amount');

            $trend[] = [
                'date' => $date->toDateString(),
                'amount' => (float) $dailySales
            ];
        }

        return $trend;
    }

    /**
     * Get expense categories for the selected date
     */
    private function getExpenseCategories(Carbon $date): array
    {
        $categories = Expense::with('category')
                            ->whereDate('expense_date', $date)
                            ->whereIn('status', ['approved', 'paid'])
                            ->select('category_id', DB::raw('SUM(amount) as total_amount'))
                            ->groupBy('category_id')
                            ->get()
                            ->map(function ($expense) {
                                return [
                                    'name' => $expense->category->name ?? 'Uncategorized',
                                    'amount' => (float) $expense->total_amount
                                ];
                            });

        return $categories->toArray();
    }

    /**
     * Get recent transactions for the selected date
     */
    private function getRecentTransactions(Carbon $date): array
    {
        $transactions = collect();

        // Get recent sales
        $sales = Sale::whereDate('sale_date', $date)
                    ->where('status', 'completed')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($sale) {
                        return [
                            'id' => $sale->id,
                            'type' => $sale->is_refund ? 'return' : 'sale',
                            'description' => $sale->is_refund 
                                ? "Return #{$sale->sale_number}" 
                                : "Sale #{$sale->sale_number}",
                            'amount' => (float) $sale->total_amount,
                            'created_at' => $sale->created_at
                        ];
                    });

        // Get recent purchases
        $purchases = PurchaseOrder::whereDate('order_date', $date)
                                 ->orderBy('created_at', 'desc')
                                 ->limit(3)
                                 ->get()
                                 ->map(function ($purchase) {
                                     return [
                                         'id' => $purchase->id,
                                         'type' => 'purchase',
                                         'description' => "Purchase #{$purchase->po_number}",
                                         'amount' => (float) $purchase->total_amount,
                                         'created_at' => $purchase->created_at
                                     ];
                                 });

        // Get recent expenses
        $expenses = Expense::whereDate('expense_date', $date)
                          ->whereIn('status', ['approved', 'paid'])
                          ->orderBy('created_at', 'desc')
                          ->limit(3)
                          ->get()
                          ->map(function ($expense) {
                              return [
                                  'id' => $expense->id,
                                  'type' => 'expense',
                                  'description' => $expense->title,
                                  'amount' => (float) $expense->amount,
                                  'created_at' => $expense->created_at
                              ];
                          });

        // Combine and sort by created_at
        $transactions = $sales->concat($purchases)->concat($expenses)
                             ->sortByDesc('created_at')
                             ->take(10)
                             ->values();

        return $transactions->toArray();
    }

    /**
     * Get payment statistics for the selected date range
     */
    private function getPaymentStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        // Get payment statistics from the new Payment model
        $totalPayments = Payment::whereBetween('payment_date', [$fromDate, $toDate])
                               ->where('status', 'paid')
                               ->count();

        $totalAmount = Payment::whereBetween('payment_date', [$fromDate, $toDate])
                             ->where('status', 'paid')
                             ->sum('amount');

        $pendingPayments = Payment::whereBetween('payment_date', [$fromDate, $toDate])
                                 ->where('status', 'pending')
                                 ->count();

        $pendingAmount = Payment::whereBetween('payment_date', [$fromDate, $toDate])
                               ->where('status', 'pending')
                               ->sum('amount');

        // Legacy calculation for backward compatibility
        $paymentSent = Expense::whereBetween('expense_date', [$fromDate, $toDate])
                             ->whereIn('status', ['approved', 'paid'])
                             ->sum('amount');

        $purchasePayments = PurchaseOrder::whereBetween('order_date', [$fromDate, $toDate])
                                        ->whereIn('status', ['received', 'partially_received'])
                                        ->sum('total_amount');

        $totalPaymentSent = $paymentSent + $purchasePayments;

        // Payment Received (sales payments)
        $paymentReceived = Sale::whereBetween('sale_date', [$fromDate, $toDate])
                              ->where('is_refund', false)
                              ->where('status', 'completed')
                              ->sum('total_amount');

        // Calculate previous period for comparison
        $previousFromDate = $fromDate->copy()->subDays($fromDate->diffInDays($toDate) + 1);
        $previousToDate = $fromDate->copy()->subDay();

        $prevPaymentSent = Expense::whereBetween('expense_date', [$previousFromDate, $previousToDate])
                                 ->whereIn('status', ['approved', 'paid'])
                                 ->sum('amount') +
                          PurchaseOrder::whereBetween('order_date', [$previousFromDate, $previousToDate])
                                      ->whereIn('status', ['received', 'partially_received'])
                                      ->sum('total_amount');

        $prevPaymentReceived = Sale::whereBetween('sale_date', [$previousFromDate, $previousToDate])
                                  ->where('is_refund', false)
                                  ->where('status', 'completed')
                                  ->sum('total_amount');

        return [
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
            'pending_payments' => $pendingPayments,
            'pending_amount' => $pendingAmount,
            'payment_sent' => [
                'total_amount' => $totalPaymentSent,
                'previous_amount' => $prevPaymentSent,
                'change_percentage' => $prevPaymentSent > 0 ?
                    (($totalPaymentSent - $prevPaymentSent) / $prevPaymentSent) * 100 : 0
            ],
            'payment_received' => [
                'total_amount' => $paymentReceived,
                'previous_amount' => $prevPaymentReceived,
                'change_percentage' => $prevPaymentReceived > 0 ?
                    (($paymentReceived - $prevPaymentReceived) / $prevPaymentReceived) * 100 : 0
            ],
            'by_type' => Payment::whereBetween('payment_date', [$fromDate, $toDate])
                               ->selectRaw('payment_type, COUNT(*) as count, SUM(amount) as total_amount')
                               ->groupBy('payment_type')
                               ->get(),
            'by_status' => Payment::whereBetween('payment_date', [$fromDate, $toDate])
                                 ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                                 ->groupBy('status')
                                 ->get(),
        ];
    }

    /**
     * Get sales and purchases chart data for the last 7 days
     */
    private function getSalesPurchasesChartData(Carbon $date): array
    {
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $currentDate = $date->copy()->subDays($i);

            $sales = Sale::whereDate('sale_date', $currentDate)
                        ->where('is_refund', false)
                        ->where('status', 'completed')
                        ->sum('total_amount');

            $purchases = PurchaseOrder::whereDate('order_date', $currentDate)
                                    ->whereIn('status', ['received', 'partially_received'])
                                    ->sum('total_amount');

            // Generate a sales target (for demo purposes, 120% of actual sales)
            $salesTarget = $sales * 1.2;

            $chartData[] = [
                'date' => $currentDate->format('M d'),
                'sales' => (float) $sales,
                'purchases' => (float) $purchases,
                'sales_target' => (float) $salesTarget
            ];
        }

        return $chartData;
    }

    /**
     * Get devices breakdown (mock data for demo)
     */
    private function getDevicesBreakdown(): array
    {
        // This would typically come from user agent analysis or device tracking
        // For now, providing mock data that matches the reference image
        return [
            ['name' => 'iOS', 'value' => 40, 'color' => '#FF6B6B'],
            ['name' => 'MacBook', 'value' => 30, 'color' => '#4ECDC4'],
            ['name' => 'Smart TV', 'value' => 12, 'color' => '#45B7D1'],
            ['name' => 'Xbox Series S', 'value' => 10, 'color' => '#96CEB4'],
            ['name' => 'Google Pixel', 'value' => 8, 'color' => '#FFEAA7']
        ];
    }

    /**
     * Get recent invoices
     */
    private function getRecentInvoices(Carbon $date): array
    {
        $invoices = Sale::with('customer')
                       ->whereDate('sale_date', '>=', $date->copy()->subDays(7))
                       ->where('status', 'completed')
                       ->orderBy('sale_date', 'desc')
                       ->limit(10)
                       ->get()
                       ->map(function ($sale) {
                           return [
                               'invoice_id' => 'INV' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                               'customer' => $sale->customer->name ?? 'Walk-in Customer',
                               'sales_date' => $sale->sale_date,
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
    private function getStockHistory(Carbon $date): array
    {
        // Total Sales Items
        $totalSalesItems = Sale::whereDate('sale_date', $date)
                              ->where('status', 'completed')
                              ->where('is_refund', false)
                              ->withCount('items')
                              ->get()
                              ->sum('items_count');

        $prevSalesItems = Sale::whereDate('sale_date', $date->copy()->subDay())
                             ->where('status', 'completed')
                             ->where('is_refund', false)
                             ->withCount('items')
                             ->get()
                             ->sum('items_count');

        // Total Purchase Items
        $totalPurchaseItems = PurchaseOrder::whereDate('order_date', $date)
                                          ->whereIn('status', ['received', 'partially_received'])
                                          ->withCount('items')
                                          ->get()
                                          ->sum('items_count');

        $prevPurchaseItems = PurchaseOrder::whereDate('order_date', $date->copy()->subDay())
                                         ->whereIn('status', ['received', 'partially_received'])
                                         ->withCount('items')
                                         ->get()
                                         ->sum('items_count');

        // Total Return Items
        $totalReturnItems = Sale::whereDate('sale_date', $date)
                               ->where('status', 'completed')
                               ->where('is_refund', true)
                               ->withCount('items')
                               ->get()
                               ->sum('items_count');

        $prevReturnItems = Sale::whereDate('sale_date', $date->copy()->subDay())
                              ->where('status', 'completed')
                              ->where('is_refund', true)
                              ->withCount('items')
                              ->get()
                              ->sum('items_count');

        return [
            'total_sales_items' => [
                'count' => $totalSalesItems,
                'change_percentage' => $prevSalesItems > 0 ?
                    (($totalSalesItems - $prevSalesItems) / $prevSalesItems) * 100 : 0
            ],
            'total_purchase_items' => [
                'count' => $totalPurchaseItems,
                'change_percentage' => $prevPurchaseItems > 0 ?
                    (($totalPurchaseItems - $prevPurchaseItems) / $prevPurchaseItems) * 100 : 0
            ],
            'total_return_items' => [
                'count' => $totalReturnItems,
                'change_percentage' => $prevReturnItems > 0 ?
                    (($totalReturnItems - $prevReturnItems) / $prevReturnItems) * 100 : 0
            ]
        ];
    }

    /**
     * Get payment trends for the last 15 days
     */
    private function getPaymentTrends(Carbon $date): array
    {
        $trends = [];

        for ($i = 14; $i >= 0; $i--) {
            $currentDate = $date->copy()->subDays($i);

            // Payment Sent
            $paymentSent = Expense::whereDate('expense_date', $currentDate)
                                 ->whereIn('status', ['approved', 'paid'])
                                 ->sum('amount') +
                          PurchaseOrder::whereDate('order_date', $currentDate)
                                      ->whereIn('status', ['received', 'partially_received'])
                                      ->sum('total_amount');

            // Payment Received
            $paymentReceived = Sale::whereDate('sale_date', $currentDate)
                                  ->where('is_refund', false)
                                  ->where('status', 'completed')
                                  ->sum('total_amount');

            $trends[] = [
                'date' => $currentDate->format('j'),
                'payment_sent' => (float) $paymentSent,
                'payment_received' => (float) $paymentReceived
            ];
        }

        return $trends;
    }

    /**
     * Get stock alerts for low stock products
     */
    private function getStockAlerts(): array
    {
        $lowStockProducts = Product::where('quantity', '<=', DB::raw('minimum_stock_level'))
                                  ->orWhere('quantity', '<=', 50) // Default threshold
                                  ->orderBy('quantity', 'asc')
                                  ->limit(10)
                                  ->get()
                                  ->map(function ($product) {
                                      return [
                                          'product' => $product->name,
                                          'quantity' => $product->quantity,
                                          'minimum_level' => $product->minimum_stock_level ?? 50
                                      ];
                                  });

        return $lowStockProducts->toArray();
    }

    /**
     * Get accounting-based sales statistics
     */
    private function getAccountingBasedSalesStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $accountingSettings = AccountingSetting::getSettings();

        // Get sales revenue from journal entries
        $salesRevenue = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$fromDate, $toDate])
                  ->where('entry_type', 'sales_invoice');
            })
            ->where('account_id', $accountingSettings->sales_invoice_revenue_account_id)
            ->sum('credit_amount');

        // Count of sales transactions
        $salesCount = JournalEntry::where('status', 'posted')
                                 ->whereBetween('entry_date', [$fromDate, $toDate])
                                 ->where('entry_type', 'sales_invoice')
                                 ->count();

        return [
            'total_amount' => (float) $salesRevenue,
            'count' => $salesCount,
            'average_sale' => $salesCount > 0 ? (float) ($salesRevenue / $salesCount) : 0
        ];
    }

    /**
     * Get accounting-based returns statistics
     */
    private function getAccountingBasedReturnsStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $accountingSettings = AccountingSetting::getSettings();

        // Get returns from journal entries
        $returnsAmount = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$fromDate, $toDate])
                  ->where('entry_type', 'sales_return');
            })
            ->where('account_id', $accountingSettings->sales_return_revenue_account_id)
            ->sum('debit_amount');

        $returnsCount = JournalEntry::where('status', 'posted')
                                  ->whereBetween('entry_date', [$fromDate, $toDate])
                                  ->where('entry_type', 'sales_return')
                                  ->count();

        return [
            'total_amount' => (float) $returnsAmount,
            'count' => $returnsCount
        ];
    }

    /**
     * Get accounting-based purchases statistics
     */
    private function getAccountingBasedPurchasesStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $accountingSettings = AccountingSetting::getSettings();

        // Get purchases from journal entries
        $purchasesAmount = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$fromDate, $toDate])
                  ->where('entry_type', 'purchase_invoice');
            })
            ->where('account_id', $accountingSettings->purchase_invoice_expense_account_id)
            ->sum('debit_amount');

        $purchasesCount = JournalEntry::where('status', 'posted')
                                    ->whereBetween('entry_date', [$fromDate, $toDate])
                                    ->where('entry_type', 'purchase_invoice')
                                    ->count();

        return [
            'total_amount' => (float) $purchasesAmount,
            'count' => $purchasesCount
        ];
    }

    /**
     * Get accounting-based expenses statistics
     */
    private function getAccountingBasedExpensesStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $accountingSettings = AccountingSetting::getSettings();

        // Get expenses from journal entries
        $expensesAmount = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$fromDate, $toDate])
                  ->where('entry_type', 'expense');
            })
            ->where('account_id', $accountingSettings->expense_default_account_id)
            ->sum('debit_amount');

        $expensesCount = JournalEntry::where('status', 'posted')
                                   ->whereBetween('entry_date', [$fromDate, $toDate])
                                   ->where('entry_type', 'expense')
                                   ->count();

        return [
            'total_amount' => (float) $expensesAmount,
            'count' => $expensesCount
        ];
    }

    /**
     * Get accounting summary
     */
    private function getAccountingSummary(Carbon $fromDate, Carbon $toDate): array
    {
        // Get all posted journal entries in the date range
        $totalDebits = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$fromDate, $toDate]);
            })
            ->sum('debit_amount');

        $totalCredits = JournalEntryLine::whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$fromDate, $toDate]);
            })
            ->sum('credit_amount');

        $totalEntries = JournalEntry::where('status', 'posted')
                                  ->whereBetween('entry_date', [$fromDate, $toDate])
                                  ->count();

        return [
            'total_debits' => (float) $totalDebits,
            'total_credits' => (float) $totalCredits,
            'total_entries' => $totalEntries,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
        ];
    }
}
