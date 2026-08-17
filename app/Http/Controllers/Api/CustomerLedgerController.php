<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\PaymentReceipt;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerLedgerController extends Controller
{
    /**
     * Export customer ledger as PDF
     */
    public function exportPDF(Request $request, Customer $customer)
    {
        $startDate = $request->input('start_date') ?? $request->input('date_from');
        $endDate = $request->input('end_date') ?? $request->input('date_to');

        if (!$startDate || $startDate === 'undefined' || $startDate === 'null') {
            $startDate = null;
        }
        if (!$endDate || $endDate === 'undefined' || $endDate === 'null') {
            $endDate = null;
        }

        $openingBalance = $startDate ? $this->calculateOpeningBalance($customer, $startDate) : 0.00;
        $transactions = $this->getCustomerTransactions($customer, $startDate, $endDate);

        $runningBalance = $openingBalance;
        $processedTransactions = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($transactions as $transaction) {
            $totalDebits += $transaction['debit'];
            $totalCredits += $transaction['credit'];
            $runningBalance += $transaction['debit'] - $transaction['credit'];
            $transaction['running_balance'] = $runningBalance;
            $processedTransactions[] = $transaction;
        }

        $closingBalance = $runningBalance;

        // Get Sale Invoices
        $salesQuery = Sale::where('customer_id', $customer->id)
            ->where('is_refund', false)
            ->where('sale_number', 'not like', 'RETURN-%')
            ->with(['salesman', 'user']);

        if ($startDate) {
            $salesQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->whereDate('sale_date', '<=', $endDate);
        }
        $sales = $salesQuery->orderBy('sale_date', 'desc')->get();

        $paymentPending = $sales->sum(function ($s) {
            return max(0, (float)$s->total_amount - (float)$s->paid_amount);
        });

        // Get Sale Returns
        $returnsQuery = Sale::where('customer_id', $customer->id)
            ->where(function ($q) {
                $q->where('is_refund', true)
                  ->orWhere('sale_number', 'like', 'RETURN-%');
            });

        if ($startDate) {
            $returnsQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $returnsQuery->whereDate('sale_date', '<=', $endDate);
        }
        $returns = $returnsQuery->orderBy('sale_date', 'desc')->get();

        $pdf = Pdf::loadView('pdf.customer_ledger', [
            'customer' => $customer,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'paymentPending' => $paymentPending,
            'paymentReceived' => $totalCredits,
            'sales' => $sales,
            'returns' => $returns,
            'transactions' => $processedTransactions,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits
        ]);

        $pdf->setPaper('a4', 'portrait');
        $fileName = 'customer_ledger_' . Str::slug($customer->name ?: 'customer') . '_' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }
    /**
     * Get customer ledger report
     */
    public function getLedger(Request $request, Customer $customer): JsonResponse
    {
        $startDate = $request->input('start_date') ?? $request->input('date_from');
        $endDate = $request->input('end_date') ?? $request->input('date_to');

        if (!$startDate || $startDate === 'undefined' || $startDate === 'null') {
            $startDate = null;
        }
        if (!$endDate || $endDate === 'undefined' || $endDate === 'null') {
            $endDate = null;
        }

        // Get opening balance (0 if showing all-time data)
        $openingBalance = $startDate ? $this->calculateOpeningBalance($customer, $startDate) : 0.00;

        // Get all transactions for the period (or all time if no dates selected)
        $transactions = $this->getCustomerTransactions($customer, $startDate, $endDate);

        // Calculate running balance
        $runningBalance = $openingBalance;
        $processedTransactions = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($transactions as $transaction) {
            $totalDebits += $transaction['debit'];
            $totalCredits += $transaction['credit'];
            $runningBalance += $transaction['debit'] - $transaction['credit'];
            $transaction['running_balance'] = $runningBalance;
            $processedTransactions[] = $transaction;
        }

        $closingBalance = $runningBalance;

        // Calculate Payment Pending for this customer
        $periodSalesQuery = Sale::where('customer_id', $customer->id)
            ->where('is_refund', false)
            ->where('sale_number', 'not like', 'RETURN-%');

        if ($startDate) {
            $periodSalesQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $periodSalesQuery->whereDate('sale_date', '<=', $endDate);
        }

        $periodSales = $periodSalesQuery->get();

        $paymentPending = $periodSales->sum(function ($s) {
            return max(0, (float)$s->total_amount - (float)$s->paid_amount);
        });

        $paymentReceived = $totalCredits;

        return response()->json([
            'customer' => $customer->only([
                'id', 'name', 'email', 'phone', 'type', 'city', 'state', 'country',
                'address', 'credit_limit', 'wallet_balance', 'due_amount', 'is_active', 'profile_image'
            ]),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'opening_balance' => (float)$openingBalance,
            'closing_balance' => (float)$closingBalance,
            'payment_pending' => (float)$paymentPending,
            'payment_received' => (float)$paymentReceived,
            'balance' => (float)$closingBalance,
            'transactions' => $processedTransactions,
            'summary' => [
                'total_debits' => (float)$totalDebits,
                'total_credits' => (float)$totalCredits,
                'net_movement' => (float)($closingBalance - $openingBalance),
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
        // Total Sale Invoices before start date (Debits)
        $priorSales = Sale::where('customer_id', $customer->id)
                         ->whereDate('sale_date', '<', $asOfDate)
                         ->where('is_refund', false)
                         ->where('sale_number', 'not like', 'RETURN-%')
                         ->sum('total_amount');

        // Total Sale Returns before start date (Credits)
        $priorReturns = Sale::where('customer_id', $customer->id)
                           ->whereDate('sale_date', '<', $asOfDate)
                           ->where(function ($q) {
                               $q->where('is_refund', true)
                                 ->orWhere('sale_number', 'like', 'RETURN-%');
                           })
                           ->sum('total_amount');

        // Income Banking Transactions before start date (Credits)
        $priorTxIncome = Transaction::where('customer_id', $customer->id)
                                    ->whereDate('paid_at', '<', $asOfDate)
                                    ->where('type', 'income')
                                    ->sum('amount');

        // Expense Banking Transactions before start date (Debits)
        $priorTxExpense = Transaction::where('customer_id', $customer->id)
                                     ->whereDate('paid_at', '<', $asOfDate)
                                     ->where('type', 'expense')
                                     ->sum('amount');

        // Payment Receipts before start date not in transactions table (Credits)
        $trackedTxNumbers = Transaction::where('customer_id', $customer->id)
                                       ->whereNotNull('number')
                                       ->pluck('number')
                                       ->toArray();

        $priorReceipts = PaymentReceipt::where('payer_id', $customer->id)
            ->where(function ($q) {
                $q->whereNull('payer_type')
                  ->orWhere('payer_type', '')
                  ->orWhere('payer_type', 'customer')
                  ->orWhere('payer_type', 'App\\Models\\Customer');
            })
            ->whereDate('receipt_date', '<', $asOfDate)
            ->whereNotIn('receipt_number', $trackedTxNumbers)
            ->sum('amount');

        return (float) ($priorSales + $priorTxExpense) - ($priorReturns + $priorTxIncome + $priorReceipts);
    }

    /**
     * Get customer transactions for a period (Banking and Payment Transactions only)
     */
    private function getCustomerTransactions(Customer $customer, $startDate, $endDate): array
    {
        $transactions = [];

        // 1. Get registered banking transactions from `transactions` table (Banking -> Transactions module)
        $bankingQuery = Transaction::where('customer_id', $customer->id);
        if ($startDate) {
            $bankingQuery->whereDate('paid_at', '>=', $startDate);
        }
        if ($endDate) {
            $bankingQuery->whereDate('paid_at', '<=', $endDate);
        }
        $bankingTxs = $bankingQuery->get();

        $trackedTxNumbers = [];

        foreach ($bankingTxs as $tx) {
            $formattedDate = $tx->paid_at ? Carbon::parse($tx->paid_at)->format('Y-m-d') : date('Y-m-d');
            $ref = $tx->reference ?: $tx->number;
            if ($ref) {
                $trackedTxNumbers[] = $ref;
            }

            $isIncome = ($tx->type === 'income');

            $transactions[] = [
                'date' => $formattedDate,
                'type' => $isIncome ? 'Payment Received' : 'Payment Out',
                'reference' => $ref ?: "TX-{$tx->id}",
                'description' => $tx->description ?: ($isIncome ? "Customer Payment ({$ref})" : "Transaction Out ({$ref})"),
                'debit' => $isIncome ? 0 : (float) $tx->amount,
                'credit' => $isIncome ? (float) $tx->amount : 0,
                'source_id' => $tx->id,
                'source_type' => 'transaction',
                'created_at' => $tx->created_at ? $tx->created_at->toDateTimeString() : $formattedDate
            ];
        }

        // 2. Get any Payment Receipts for this customer not already recorded in `transactions` table
        $receiptQuery = PaymentReceipt::where('payer_id', $customer->id)
            ->where(function ($q) {
                $q->whereNull('payer_type')
                  ->orWhere('payer_type', '')
                  ->orWhere('payer_type', 'customer')
                  ->orWhere('payer_type', 'App\\Models\\Customer');
            });
        if ($startDate) {
            $receiptQuery->whereDate('receipt_date', '>=', $startDate);
        }
        if ($endDate) {
            $receiptQuery->whereDate('receipt_date', '<=', $endDate);
        }
        $paymentReceipts = $receiptQuery->get();

        foreach ($paymentReceipts as $pr) {
            if ($pr->receipt_number && in_array($pr->receipt_number, $trackedTxNumbers)) {
                continue; // Already processed via Transaction model
            }

            $formattedDate = $pr->receipt_date ? Carbon::parse($pr->receipt_date)->format('Y-m-d') : date('Y-m-d');

            $transactions[] = [
                'date' => $formattedDate,
                'type' => 'Payment Receipt',
                'reference' => $pr->receipt_number,
                'description' => $pr->description ?: "Customer Payment Receipt #{$pr->receipt_number}",
                'debit' => 0,
                'credit' => (float) $pr->amount,
                'source_id' => $pr->id,
                'source_type' => 'payment_receipt',
                'created_at' => $pr->created_at ? $pr->created_at->toDateTimeString() : $formattedDate
            ];
        }

        // Sort chronologically by date and created_at
        usort($transactions, function ($a, $b) {
            $dateCmp = strcmp($a['date'], $b['date']);
            if ($dateCmp !== 0) return $dateCmp;
            return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
        });

        return $transactions;
    }

    /**
     * Get customer payments for a period
     */
    private function getCustomerPayments(Customer $customer, $startDate, $endDate): array
    {
        $payments = [];

        // 1. From Banking Transactions module (`transactions` table)
        $bankingTxs = Transaction::where('customer_id', $customer->id)
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->where('type', 'income')
            ->get();

        foreach ($bankingTxs as $tx) {
            $payments[] = [
                'date' => $tx->paid_at ? Carbon::parse($tx->paid_at)->format('Y-m-d') : date('Y-m-d'),
                'reference' => $tx->reference ?: $tx->number ?: "TX-{$tx->id}",
                'description' => $tx->description ?: "Payment from customer",
                'amount' => (float) $tx->amount,
                'method' => $tx->payment_method ?? 'Bank'
            ];
        }

        // 2. From Payment Receipts module
        $trackedTxNumbers = Transaction::where('customer_id', $customer->id)
            ->whereNotNull('number')
            ->pluck('number')
            ->toArray();

        $receipts = PaymentReceipt::where(function ($q) use ($customer) {
                $q->where('payer_id', $customer->id)
                  ->orWhere(function ($sub) use ($customer) {
                      $sub->where('payer_type', 'App\\Models\\Customer')
                          ->where('payer_id', $customer->id);
                  });
            })
            ->whereDate('receipt_date', '>=', $startDate)
            ->whereDate('receipt_date', '<=', $endDate)
            ->get();

        foreach ($receipts as $pr) {
            if ($pr->receipt_number && in_array($pr->receipt_number, $trackedTxNumbers)) {
                continue;
            }
            $payments[] = [
                'date' => $pr->receipt_date ? Carbon::parse($pr->receipt_date)->format('Y-m-d') : date('Y-m-d'),
                'reference' => $pr->receipt_number,
                'description' => $pr->description ?: "Payment receipt",
                'amount' => (float) $pr->amount,
                'method' => $pr->payment_method ?? 'Cash'
            ];
        }

        return $payments;
    }
}
