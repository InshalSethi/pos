<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SupplierLedgerController extends Controller
{
    /**
     * Get supplier ledger report
     */
    public function getLedger(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Get opening balance
        $openingBalance = $this->calculateOpeningBalance($supplier, $startDate);

        // Get all transactions for the period
        $transactions = $this->getSupplierTransactions($supplier, $startDate, $endDate);

        // Calculate running balance
        $runningBalance = $openingBalance;
        $processedTransactions = [];

        foreach ($transactions as $transaction) {
            $runningBalance += $transaction['credit'] - $transaction['debit'];
            $transaction['running_balance'] = $runningBalance;
            $processedTransactions[] = $transaction;
        }

        $closingBalance = $runningBalance;

        return response()->json([
            'supplier' => $supplier->only(['id', 'name', 'company_name', 'email', 'phone']),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'transactions' => $processedTransactions,
            'summary' => [
                'total_debits' => collect($processedTransactions)->sum('debit'),
                'total_credits' => collect($processedTransactions)->sum('credit'),
                'net_movement' => $closingBalance - $openingBalance,
                'transaction_count' => count($processedTransactions)
            ]
        ]);
    }

    /**
     * Get supplier aging report
     */
    public function getAgingReport(Request $request, Supplier $supplier): JsonResponse
    {
        $asOfDate = $request->get('as_of_date', now());

        // Get unpaid purchase orders
        $unpaidOrders = PurchaseOrder::where('supplier_id', $supplier->id)
                                   ->where('status', 'pending')
                                   ->where('order_date', '<=', $asOfDate)
                                   ->get();

        $aging = [
            'current' => 0,      // 0-30 days
            'days_31_60' => 0,   // 31-60 days
            'days_61_90' => 0,   // 61-90 days
            'over_90' => 0       // Over 90 days
        ];

        $details = [];

        foreach ($unpaidOrders as $order) {
            $daysOverdue = Carbon::parse($asOfDate)->diffInDays($order->order_date);
            $outstandingAmount = $order->total_amount;

            if ($daysOverdue <= 30) {
                $aging['current'] += $outstandingAmount;
                $category = 'Current (0-30 days)';
            } elseif ($daysOverdue <= 60) {
                $aging['days_31_60'] += $outstandingAmount;
                $category = '31-60 days';
            } elseif ($daysOverdue <= 90) {
                $aging['days_61_90'] += $outstandingAmount;
                $category = '61-90 days';
            } else {
                $aging['over_90'] += $outstandingAmount;
                $category = 'Over 90 days';
            }

            $details[] = [
                'purchase_order_id' => $order->id,
                'po_number' => $order->po_number,
                'order_date' => $order->order_date->format('Y-m-d'),
                'expected_delivery_date' => $order->expected_delivery_date?->format('Y-m-d'),
                'total_amount' => $order->total_amount,
                'outstanding_amount' => $outstandingAmount,
                'days_overdue' => $daysOverdue,
                'aging_category' => $category
            ];
        }

        $totalOutstanding = array_sum($aging);

        return response()->json([
            'supplier' => $supplier->only(['id', 'name', 'company_name', 'email', 'phone']),
            'as_of_date' => $asOfDate,
            'aging_summary' => $aging,
            'total_outstanding' => $totalOutstanding,
            'details' => $details
        ]);
    }

    /**
     * Get supplier statement
     */
    public function getStatement(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Get opening balance
        $openingBalance = $this->calculateOpeningBalance($supplier, $startDate);

        // Get purchase orders for the period
        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
                                     ->whereBetween('order_date', [$startDate, $endDate])
                                     ->with(['user', 'purchaseOrderItems.product'])
                                     ->orderBy('order_date')
                                     ->get();

        // Get payments for the period
        $payments = $this->getSupplierPayments($supplier, $startDate, $endDate);

        // Combine and sort transactions
        $transactions = [];

        foreach ($purchaseOrders as $order) {
            $transactions[] = [
                'date' => $order->order_date->format('Y-m-d'),
                'type' => 'Purchase Order',
                'reference' => $order->po_number,
                'description' => "Purchase Order - {$order->purchaseOrderItems->count()} items",
                'debit' => 0,
                'credit' => $order->total_amount,
                'details' => $order
            ];
        }

        foreach ($payments as $payment) {
            $transactions[] = [
                'date' => $payment['date'],
                'type' => 'Payment',
                'reference' => $payment['reference'],
                'description' => $payment['description'],
                'debit' => $payment['amount'],
                'credit' => 0,
                'details' => $payment
            ];
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        // Calculate running balance
        $runningBalance = $openingBalance;
        foreach ($transactions as &$transaction) {
            $runningBalance += $transaction['credit'] - $transaction['debit'];
            $transaction['balance'] = $runningBalance;
        }

        return response()->json([
            'supplier' => $supplier->only(['id', 'name', 'company_name', 'email', 'phone', 'full_address']),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'transactions' => $transactions,
            'summary' => [
                'total_purchases' => collect($transactions)->where('type', 'Purchase Order')->sum('credit'),
                'total_payments' => collect($transactions)->where('type', 'Payment')->sum('debit'),
                'net_change' => $runningBalance - $openingBalance
            ]
        ]);
    }

    /**
     * Get supplier transaction summary
     */
    public function getTransactionSummary(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now()->endOfYear());

        $summary = [
            'total_purchases' => PurchaseOrder::where('supplier_id', $supplier->id)
                                            ->whereBetween('order_date', [$startDate, $endDate])
                                            ->sum('total_amount'),
            'purchase_orders_count' => PurchaseOrder::where('supplier_id', $supplier->id)
                                                  ->whereBetween('order_date', [$startDate, $endDate])
                                                  ->count(),
            'average_order_value' => PurchaseOrder::where('supplier_id', $supplier->id)
                                                 ->whereBetween('order_date', [$startDate, $endDate])
                                                 ->avg('total_amount'),
            'largest_order' => PurchaseOrder::where('supplier_id', $supplier->id)
                                          ->whereBetween('order_date', [$startDate, $endDate])
                                          ->max('total_amount'),
            'last_order_date' => PurchaseOrder::where('supplier_id', $supplier->id)
                                            ->whereBetween('order_date', [$startDate, $endDate])
                                            ->max('order_date'),
            'outstanding_balance' => $supplier->getOutstandingBalance(),
            'credit_limit' => $supplier->credit_limit,
            'available_credit' => $supplier->getAvailableCredit(),
            'credit_utilization' => $supplier->getCreditUtilization(),
            'payment_terms_days' => $supplier->payment_terms_days,
            'is_payment_overdue' => $supplier->isPaymentOverdue()
        ];

        // Monthly breakdown
        $monthlyBreakdown = PurchaseOrder::where('supplier_id', $supplier->id)
                                       ->whereBetween('order_date', [$startDate, $endDate])
                                       ->selectRaw('YEAR(order_date) as year, MONTH(order_date) as month, 
                                                  SUM(total_amount) as total_purchases, 
                                                  COUNT(*) as orders_count')
                                       ->groupBy('year', 'month')
                                       ->orderBy('year')
                                       ->orderBy('month')
                                       ->get();

        return response()->json([
            'supplier' => $supplier->only(['id', 'name', 'company_name', 'email', 'phone']),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => $summary,
            'monthly_breakdown' => $monthlyBreakdown
        ]);
    }

    /**
     * Calculate opening balance for a supplier as of a specific date
     */
    private function calculateOpeningBalance(Supplier $supplier, $asOfDate): float
    {
        // Calculate total purchases before the start date
        $totalPurchases = PurchaseOrder::where('supplier_id', $supplier->id)
                                     ->where('order_date', '<', $asOfDate)
                                     ->sum('total_amount');

        // For suppliers, we owe them money (credit balance)
        // This would typically come from a payments table
        // For now, we'll assume all orders are unpaid
        return $totalPurchases;
    }

    /**
     * Get supplier transactions for a period
     */
    private function getSupplierTransactions(Supplier $supplier, $startDate, $endDate): array
    {
        $transactions = [];

        // Get purchase orders
        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
                                     ->whereBetween('order_date', [$startDate, $endDate])
                                     ->get();

        foreach ($purchaseOrders as $order) {
            $transactions[] = [
                'date' => $order->order_date->format('Y-m-d'),
                'type' => 'Purchase Order',
                'reference' => $order->po_number,
                'description' => "Purchase Order #{$order->po_number}",
                'debit' => 0,
                'credit' => $order->total_amount,
                'source_id' => $order->id,
                'source_type' => 'purchase_order'
            ];
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $transactions;
    }

    /**
     * Get supplier payments for a period
     */
    private function getSupplierPayments(Supplier $supplier, $startDate, $endDate): array
    {
        // This would typically come from a payments table
        // For now, we'll return an empty array as we don't have payment tracking yet
        return [];
    }
}
