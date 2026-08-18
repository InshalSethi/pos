<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\PaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerLedgerController extends Controller
{
    /**
     * Export customer general ledger as PDF (IAS & QuickBooks Standard Format)
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

        $ledgerData = $this->buildUnifiedLedger($customer, $startDate, $endDate);

        $pdf = Pdf::loadView('pdf.customer_ledger', [
            'customer' => $customer,
            'accountCode' => 'AR-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $ledgerData['opening_balance'],
            'closingBalance' => $ledgerData['closing_balance'],
            'closingBalanceType' => $ledgerData['closing_balance_type'],
            'totalDebits' => $ledgerData['total_debits'],
            'totalCredits' => $ledgerData['total_credits'],
            'transactions' => $ledgerData['transactions'],
            'summary' => $ledgerData['summary']
        ]);

        $pdf->setPaper('a4', 'portrait');
        $fileName = 'general_ledger_' . Str::slug($customer->name ?: 'customer') . '_' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Get customer general ledger API
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

        $ledgerData = $this->buildUnifiedLedger($customer, $startDate, $endDate);

        $accountCode = 'AR-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT);

        return response()->json([
            'customer' => array_merge($customer->only([
                'id', 'name', 'email', 'phone', 'type', 'city', 'state', 'country',
                'address', 'credit_limit', 'wallet_balance', 'due_amount', 'is_active', 'profile_image'
            ]), [
                'account_code' => $accountCode,
                'account_title' => 'Accounts Receivable - ' . $customer->name,
                'account_type' => 'Asset (Debtors / Receivable)'
            ]),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'opening_balance' => $ledgerData['opening_balance'],
            'closing_balance' => $ledgerData['closing_balance'],
            'closing_balance_type' => $ledgerData['closing_balance_type'],
            'total_debits' => $ledgerData['total_debits'],
            'total_credits' => $ledgerData['total_credits'],
            'net_receivable_due' => max(0, $ledgerData['closing_balance']),
            'balance' => $ledgerData['closing_balance'],
            'transactions' => $ledgerData['transactions'],
            'summary' => $ledgerData['summary']
        ]);
    }

    /**
     * Build unified chronological general ledger entries for a customer with strict deduplication
     */
    private function buildUnifiedLedger(Customer $customer, ?string $startDate, ?string $endDate): array
    {
        // 1. Calculate Opening Balance B/F as of $startDate
        $openingBalance = $startDate ? $this->calculateOpeningBalance($customer, $startDate) : 0.00;

        $entries = [];

        // 2. Fetch Sales Invoices (Debits) and Sales Returns (Credits)
        $salesQuery = Sale::where('customer_id', $customer->id)
            ->with(['saleItems.product', 'salesman', 'user']);

        if ($startDate) {
            $salesQuery->where(function ($q) use ($startDate) {
                $q->whereDate('sale_date', '>=', $startDate)
                  ->orWhereDate('created_at', '>=', $startDate);
            });
        }
        if ($endDate) {
            $salesQuery->where(function ($q) use ($endDate) {
                $q->whereDate('sale_date', '<=', $endDate)
                  ->orWhereDate('created_at', '<=', $endDate);
            });
        }

        $sales = $salesQuery->get();

        foreach ($sales as $sale) {
            $isReturn = $sale->is_refund || Str::startsWith($sale->sale_number, 'RETURN-');
            
            $entryDate = $sale->sale_date ? Carbon::parse($sale->sale_date)->format('Y-m-d') : ($sale->created_at ? $sale->created_at->format('Y-m-d') : date('Y-m-d'));

            $itemsBreakdown = [];
            if ($sale->saleItems && $sale->saleItems->count() > 0) {
                foreach ($sale->saleItems as $item) {
                    $prodName = $item->product_name ?: ($item->product ? $item->product->name : 'Item');
                    $itemsBreakdown[] = [
                        'name' => $prodName,
                        'qty' => (float)$item->quantity,
                        'price' => (float)$item->unit_price,
                        'total' => (float)($item->subtotal ?: ($item->quantity * $item->unit_price))
                    ];
                }
            }

            $amount = (float)$sale->total_amount;
            $paidAmount = (float)($sale->paid_amount ?? 0);
            $dueAmount = max(0, $amount - $paidAmount);
            $salesmanName = $sale->salesman ? $sale->salesman->name : ($sale->user ? $sale->user->name : 'Admin');

            $statusText = 'Unpaid / Due';
            if ($paidAmount >= $amount && $amount > 0) {
                $statusText = 'Paid';
            } elseif ($paidAmount > 0) {
                $statusText = 'Partially Paid';
            }

            $entries[] = [
                'id' => 'sale_' . $sale->id,
                'date' => $entryDate,
                'reference' => $sale->sale_number,
                'type' => $isReturn ? 'Sale Return' : 'Sale Invoice',
                'description' => $sale->notes ?: ($isReturn ? "Credit Note / Sale Return #{$sale->sale_number}" : "Sales Invoice #{$sale->sale_number}"),
                'particulars' => $isReturn ? "Sales Return #{$sale->sale_number}" : "Sales Invoice #{$sale->sale_number}",
                'items' => $itemsBreakdown,
                'salesman' => $salesmanName,
                'status' => $isReturn ? 'Returned' : $statusText,
                'total_amount' => $amount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'debit' => $isReturn ? 0.00 : $amount,
                'credit' => $isReturn ? $amount : 0.00,
                'created_at' => $sale->created_at ? $sale->created_at->toDateTimeString() : $entryDate . ' 00:00:00',
                'source_type' => $isReturn ? 'sale_return' : 'sale_invoice',
                'source_id' => $sale->id
            ];
        }

        // 3. Fetch Payment Receipts (Primary source of truth for Payments In)
        $receiptQuery = PaymentReceipt::where('payer_id', $customer->id)
            ->where(function ($q) {
                $q->whereNull('payer_type')
                  ->orWhere('payer_type', '')
                  ->orWhere('payer_type', 'customer')
                  ->orWhere('payer_type', 'App\\Models\\Customer');
            });
        if ($startDate) {
            $receiptQuery->where(function ($q) use ($startDate) {
                $q->whereDate('receipt_date', '>=', $startDate)
                  ->orWhereDate('created_at', '>=', $startDate);
            });
        }
        if ($endDate) {
            $receiptQuery->where(function ($q) use ($endDate) {
                $q->whereDate('receipt_date', '<=', $endDate)
                  ->orWhereDate('created_at', '<=', $endDate);
            });
        }
        $paymentReceipts = $receiptQuery->get();

        $linkedReceiptNumbers = [];
        $linkedTxReferences = [];

        foreach ($paymentReceipts as $pr) {
            $entryDate = $pr->receipt_date ? Carbon::parse($pr->receipt_date)->format('Y-m-d') : ($pr->created_at ? $pr->created_at->format('Y-m-d') : date('Y-m-d'));
            $amount = (float)$pr->amount;

            if ($pr->receipt_number) {
                $linkedReceiptNumbers[] = strtolower(trim($pr->receipt_number));
            }
            if ($pr->transaction_reference) {
                $linkedTxReferences[] = strtolower(trim($pr->transaction_reference));
            }

            $entries[] = [
                'id' => 'pr_' . $pr->id,
                'date' => $entryDate,
                'reference' => $pr->receipt_number ?: "PR-{$pr->id}",
                'type' => 'Payment Received',
                'description' => $pr->description ?: "Payment Receipt #{$pr->receipt_number}",
                'particulars' => $pr->description ?: "Payment Received (" . ucfirst($pr->payment_method ?: 'cash') . ")",
                'items' => [],
                'salesman' => '-',
                'status' => 'Received & Posted',
                'total_amount' => $amount,
                'paid_amount' => $amount,
                'due_amount' => 0.00,
                'debit' => 0.00,
                'credit' => $amount,
                'created_at' => $pr->created_at ? $pr->created_at->toDateTimeString() : $entryDate . ' 00:00:00',
                'source_type' => 'payment_receipt',
                'source_id' => $pr->id
            ];
        }

        // 4. Fetch Banking Transactions (`transactions` table) with STRICT DEDUPLICATION against PaymentReceipts
        $bankingQuery = Transaction::where('customer_id', $customer->id);
        if ($startDate) {
            $bankingQuery->where(function ($q) use ($startDate) {
                $q->whereDate('paid_at', '>=', $startDate)
                  ->orWhereDate('created_at', '>=', $startDate);
            });
        }
        if ($endDate) {
            $bankingQuery->where(function ($q) use ($endDate) {
                $q->whereDate('paid_at', '<=', $endDate)
                  ->orWhereDate('created_at', '<=', $endDate);
            });
        }
        $bankingTxs = $bankingQuery->get();

        foreach ($bankingTxs as $tx) {
            $txNumber = $tx->number ? strtolower(trim($tx->number)) : '';
            $txRef = $tx->reference ? strtolower(trim($tx->reference)) : '';

            // DEDUPLICATION: Skip if this transaction was automatically created by PaymentReceiptService!
            if (
                ($txNumber && (in_array($txNumber, $linkedReceiptNumbers) || in_array($txNumber, $linkedTxReferences))) ||
                ($txRef && (in_array($txRef, $linkedReceiptNumbers) || in_array($txRef, $linkedTxReferences)))
            ) {
                continue; // Skip duplicate mirror entry!
            }

            $entryDate = $tx->paid_at ? Carbon::parse($tx->paid_at)->format('Y-m-d') : ($tx->created_at ? $tx->created_at->format('Y-m-d') : date('Y-m-d'));
            $ref = $tx->reference ?: $tx->number;
            $isIncome = ($tx->type === 'income');
            $amount = (float)$tx->amount;

            $entries[] = [
                'id' => 'tx_' . $tx->id,
                'date' => $entryDate,
                'reference' => $ref ?: "TX-{$tx->id}",
                'type' => $isIncome ? 'Payment Received' : 'Payment Out',
                'description' => $tx->description ?: ($isIncome ? "Customer Payment Received ({$ref})" : "Refund / Payment Out ({$ref})"),
                'particulars' => $tx->description ?: ($isIncome ? "Payment Received via Bank" : "Payment Out"),
                'items' => [],
                'salesman' => '-',
                'status' => 'Received & Cleared',
                'total_amount' => $amount,
                'paid_amount' => $amount,
                'due_amount' => 0.00,
                'debit' => $isIncome ? 0.00 : $amount,
                'credit' => $isIncome ? $amount : 0.00,
                'created_at' => $tx->created_at ? $tx->created_at->toDateTimeString() : $entryDate . ' 00:00:00',
                'source_type' => 'transaction',
                'source_id' => $tx->id
            ];
        }

        // 5. Sort entries chronologically by Date (ASC) and then Created_at (ASC)
        usort($entries, function ($a, $b) {
            $dateCmp = strcmp($a['date'], $b['date']);
            if ($dateCmp !== 0) return $dateCmp;
            return strcmp($a['created_at'], $b['created_at']);
        });

        // 6. Calculate Running Balance & Dr/Cr Indicators
        $runningBalance = (float)$openingBalance;
        $totalDebits = 0.00;
        $totalCredits = 0.00;

        $processedEntries = [];

        foreach ($entries as $entry) {
            $totalDebits += $entry['debit'];
            $totalCredits += $entry['credit'];
            $runningBalance += ($entry['debit'] - $entry['credit']);

            $entry['balance'] = (float)$runningBalance;
            $entry['balance_type'] = $runningBalance >= 0 ? 'Dr' : 'Cr';
            $entry['abs_balance'] = abs($runningBalance);

            $processedEntries[] = $entry;
        }

        $closingBalance = $runningBalance;

        return [
            'opening_balance' => (float)$openingBalance,
            'closing_balance' => (float)$closingBalance,
            'closing_balance_type' => $closingBalance >= 0 ? 'Dr' : 'Cr',
            'total_debits' => (float)$totalDebits,
            'total_credits' => (float)$totalCredits,
            'transactions' => $processedEntries,
            'summary' => [
                'total_sales_billed' => (float)$totalDebits,
                'total_payments_received' => (float)$totalCredits,
                'net_outstanding_due' => max(0, (float)$closingBalance),
                'wallet_advance_credit' => $closingBalance < 0 ? abs((float)$closingBalance) : 0.00,
                'net_movement' => (float)($closingBalance - $openingBalance),
                'transaction_count' => count($processedEntries)
            ]
        ];
    }

    /**
     * Calculate opening balance for a customer as of a specific date (B/F calculation with deduplication)
     */
    private function calculateOpeningBalance(Customer $customer, string $asOfDate): float
    {
        $priorSales = Sale::where('customer_id', $customer->id)
                         ->where(function ($q) use ($asOfDate) {
                             $q->whereDate('sale_date', '<', $asOfDate)
                               ->whereDate('created_at', '<', $asOfDate);
                         })
                         ->where('is_refund', false)
                         ->where('sale_number', 'not like', 'RETURN-%')
                         ->sum('total_amount');

        $priorReturns = Sale::where('customer_id', $customer->id)
                           ->where(function ($q) use ($asOfDate) {
                               $q->whereDate('sale_date', '<', $asOfDate)
                                 ->whereDate('created_at', '<', $asOfDate);
                           })
                           ->where(function ($q) {
                               $q->where('is_refund', true)
                                 ->orWhere('sale_number', 'like', 'RETURN-%');
                           })
                           ->sum('total_amount');

        $priorReceiptsModel = PaymentReceipt::where('payer_id', $customer->id)
            ->where(function ($q) {
                $q->whereNull('payer_type')
                  ->orWhere('payer_type', '')
                  ->orWhere('payer_type', 'customer')
                  ->orWhere('payer_type', 'App\\Models\\Customer');
            })
            ->where(function ($q) use ($asOfDate) {
                $q->whereDate('receipt_date', '<', $asOfDate)
                  ->whereDate('created_at', '<', $asOfDate);
            })->get();

        $priorReceipts = $priorReceiptsModel->sum('amount');
        $linkedNumbers = [];
        foreach ($priorReceiptsModel as $pr) {
            if ($pr->receipt_number) $linkedNumbers[] = strtolower(trim($pr->receipt_number));
            if ($pr->transaction_reference) $linkedNumbers[] = strtolower(trim($pr->transaction_reference));
        }

        $priorBankingTxs = Transaction::where('customer_id', $customer->id)
            ->where(function ($q) use ($asOfDate) {
                $q->whereDate('paid_at', '<', $asOfDate)
                  ->whereDate('created_at', '<', $asOfDate);
            })->get();

        $priorTxIncome = 0.00;
        $priorTxExpense = 0.00;

        foreach ($priorBankingTxs as $tx) {
            $num = $tx->number ? strtolower(trim($tx->number)) : '';
            $ref = $tx->reference ? strtolower(trim($tx->reference)) : '';

            // Skip duplicate mirror entry
            if (($num && in_array($num, $linkedNumbers)) || ($ref && in_array($ref, $linkedNumbers))) {
                continue;
            }

            if ($tx->type === 'income') {
                $priorTxIncome += (float)$tx->amount;
            } else {
                $priorTxExpense += (float)$tx->amount;
            }
        }

        return (float) ($priorSales + $priorTxExpense) - ($priorReturns + $priorTxIncome + $priorReceipts);
    }
}
