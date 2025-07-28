<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerLedgerController extends Controller
{
    /**
     * Get customer ledger report
     */
    public function getLedger(Request $request, Customer $customer): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Get opening balance
        $openingBalance = $this->calculateOpeningBalance($customer, $startDate);

        // Get all transactions for the period
        $transactions = $this->getCustomerTransactions($customer, $startDate, $endDate);

        // Calculate running balance
        $runningBalance = $openingBalance;
        $processedTransactions = [];

        foreach ($transactions as $transaction) {
            $runningBalance += $transaction['debit'] - $transaction['credit'];
            $transaction['running_balance'] = $runningBalance;
            $processedTransactions[] = $transaction;
        }

        $closingBalance = $runningBalance;

        return response()->json([
            'customer' => $customer->only(['id', 'name', 'email', 'phone']),
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
     * Get customer aging report
     */
    public function getAgingReport(Request $request, Customer $customer): JsonResponse
    {
        $asOfDate = $request->get('as_of_date', now());

        // Get unpaid sales
        $unpaidSales = Sale::where('customer_id', $customer->id)
                          ->where('status', 'pending')
                          ->where('sale_date', '<=', $asOfDate)
                          ->get();

        $aging = [
            'current' => 0,      // 0-30 days
            'days_31_60' => 0,   // 31-60 days
            'days_61_90' => 0,   // 61-90 days
            'over_90' => 0       // Over 90 days
        ];

        $details = [];

        foreach ($unpaidSales as $sale) {
            $daysOverdue = Carbon::parse($asOfDate)->diffInDays($sale->sale_date);
            $outstandingAmount = $sale->total_amount - $sale->paid_amount;

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
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'sale_date' => $sale->sale_date->format('Y-m-d'),
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'outstanding_amount' => $outstandingAmount,
                'days_overdue' => $daysOverdue,
                'aging_category' => $category
            ];
        }

        $totalOutstanding = array_sum($aging);

        return response()->json([
            'customer' => $customer->only(['id', 'name', 'email', 'phone']),
            'as_of_date' => $asOfDate,
            'aging_summary' => $aging,
            'total_outstanding' => $totalOutstanding,
            'details' => $details
        ]);
    }

    /**
     * Get customer statement
     */
    public function getStatement(Request $request, Customer $customer): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Get opening balance
        $openingBalance = $this->calculateOpeningBalance($customer, $startDate);

        // Get sales for the period
        $sales = Sale::where('customer_id', $customer->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->with(['saleItems.product', 'user'])
                    ->orderBy('sale_date')
                    ->get();

        // Get payments for the period
        $payments = $this->getCustomerPayments($customer, $startDate, $endDate);

        // Combine and sort transactions
        $transactions = [];

        foreach ($sales as $sale) {
            $transactions[] = [
                'date' => $sale->sale_date->format('Y-m-d'),
                'type' => 'Sale',
                'reference' => $sale->sale_number,
                'description' => "Sale - {$sale->saleItems->count()} items",
                'debit' => $sale->total_amount,
                'credit' => 0,
                'details' => $sale
            ];
        }

        foreach ($payments as $payment) {
            $transactions[] = [
                'date' => $payment['date'],
                'type' => 'Payment',
                'reference' => $payment['reference'],
                'description' => $payment['description'],
                'debit' => 0,
                'credit' => $payment['amount'],
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
            $runningBalance += $transaction['debit'] - $transaction['credit'];
            $transaction['balance'] = $runningBalance;
        }

        return response()->json([
            'customer' => $customer->only(['id', 'name', 'email', 'phone', 'full_address']),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'transactions' => $transactions,
            'summary' => [
                'total_sales' => collect($transactions)->where('type', 'Sale')->sum('debit'),
                'total_payments' => collect($transactions)->where('type', 'Payment')->sum('credit'),
                'net_change' => $runningBalance - $openingBalance
            ]
        ]);
    }

    /**
     * Get customer transaction summary
     */
    public function getTransactionSummary(Request $request, Customer $customer): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now()->endOfYear());

        $summary = [
            'total_sales' => Sale::where('customer_id', $customer->id)
                                ->whereBetween('sale_date', [$startDate, $endDate])
                                ->sum('total_amount'),
            'total_payments' => Sale::where('customer_id', $customer->id)
                                  ->whereBetween('sale_date', [$startDate, $endDate])
                                  ->sum('paid_amount'),
            'sales_count' => Sale::where('customer_id', $customer->id)
                               ->whereBetween('sale_date', [$startDate, $endDate])
                               ->count(),
            'average_sale_value' => Sale::where('customer_id', $customer->id)
                                      ->whereBetween('sale_date', [$startDate, $endDate])
                                      ->avg('total_amount'),
            'largest_sale' => Sale::where('customer_id', $customer->id)
                                ->whereBetween('sale_date', [$startDate, $endDate])
                                ->max('total_amount'),
            'last_sale_date' => Sale::where('customer_id', $customer->id)
                                  ->whereBetween('sale_date', [$startDate, $endDate])
                                  ->max('sale_date'),
            'outstanding_balance' => $customer->getOutstandingBalance(),
            'credit_limit' => $customer->credit_limit,
            'available_credit' => $customer->getAvailableCredit(),
            'credit_utilization' => $customer->getCreditUtilization()
        ];

        // Monthly breakdown
        $monthlyBreakdown = Sale::where('customer_id', $customer->id)
                              ->whereBetween('sale_date', [$startDate, $endDate])
                              ->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, 
                                         SUM(total_amount) as total_sales, 
                                         SUM(paid_amount) as total_payments,
                                         COUNT(*) as sales_count')
                              ->groupBy('year', 'month')
                              ->orderBy('year')
                              ->orderBy('month')
                              ->get();

        return response()->json([
            'customer' => $customer->only(['id', 'name', 'email', 'phone']),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => $summary,
            'monthly_breakdown' => $monthlyBreakdown
        ]);
    }

    /**
     * Calculate opening balance for a customer as of a specific date
     */
    private function calculateOpeningBalance(Customer $customer, $asOfDate): float
    {
        // Calculate total sales before the start date
        $totalSales = Sale::where('customer_id', $customer->id)
                         ->where('sale_date', '<', $asOfDate)
                         ->sum('total_amount');

        // Calculate total payments before the start date
        $totalPayments = Sale::where('customer_id', $customer->id)
                            ->where('sale_date', '<', $asOfDate)
                            ->sum('paid_amount');

        return $totalSales - $totalPayments;
    }

    /**
     * Get customer transactions for a period
     */
    private function getCustomerTransactions(Customer $customer, $startDate, $endDate): array
    {
        $transactions = [];

        // Get sales
        $sales = Sale::where('customer_id', $customer->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->get();

        foreach ($sales as $sale) {
            $transactions[] = [
                'date' => $sale->sale_date->format('Y-m-d'),
                'type' => 'Sale',
                'reference' => $sale->sale_number,
                'description' => "Sale - Invoice #{$sale->sale_number}",
                'debit' => $sale->total_amount,
                'credit' => 0,
                'source_id' => $sale->id,
                'source_type' => 'sale'
            ];

            if ($sale->paid_amount > 0) {
                $transactions[] = [
                    'date' => $sale->sale_date->format('Y-m-d'),
                    'type' => 'Payment',
                    'reference' => $sale->sale_number . '-PAY',
                    'description' => "Payment for Sale #{$sale->sale_number}",
                    'debit' => 0,
                    'credit' => $sale->paid_amount,
                    'source_id' => $sale->id,
                    'source_type' => 'payment'
                ];
            }
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $transactions;
    }

    /**
     * Get customer payments for a period
     */
    private function getCustomerPayments(Customer $customer, $startDate, $endDate): array
    {
        // This would typically come from a payments table
        // For now, we'll extract from sales
        $payments = [];

        $paidSales = Sale::where('customer_id', $customer->id)
                        ->whereBetween('sale_date', [$startDate, $endDate])
                        ->where('paid_amount', '>', 0)
                        ->get();

        foreach ($paidSales as $sale) {
            $payments[] = [
                'date' => $sale->sale_date->format('Y-m-d'),
                'reference' => $sale->sale_number . '-PAY',
                'description' => "Payment for Sale #{$sale->sale_number}",
                'amount' => $sale->paid_amount,
                'method' => $sale->payment_method
            ];
        }

        return $payments;
    }
}
