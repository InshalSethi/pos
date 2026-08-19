<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierLedgerController extends Controller
{
    /**
     * Export supplier general ledger as PDF (IAS 1 & Accounts Payable Standard)
     */
    public function exportPDF(Request $request, Supplier $supplier)
    {
        $startDate = $request->input('start_date') ?? $request->input('date_from');
        $endDate = $request->input('end_date') ?? $request->input('date_to');

        if (!$startDate || $startDate === 'undefined' || $startDate === 'null') {
            $startDate = null;
        }
        if (!$endDate || $endDate === 'undefined' || $endDate === 'null') {
            $endDate = null;
        }

        $ledgerData = $this->buildUnifiedLedger($supplier, $startDate, $endDate);

        $pdf = Pdf::loadView('pdf.supplier_ledger', [
            'supplier' => $supplier,
            'accountCode' => 'AP-' . str_pad($supplier->id, 5, '0', STR_PAD_LEFT),
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
        $name = Str::slug($supplier->name ?: 'supplier');
        $fileName = "supplier_general_ledger_{$name}_" . now()->format('Y-m-d') . ".pdf";
        return $pdf->download($fileName);
    }

    /**
     * Get supplier general ledger API (IAS 1 Accounts Payable Standard)
     */
    public function getLedger(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->input('start_date') ?? $request->input('date_from');
        $endDate = $request->input('end_date') ?? $request->input('date_to');

        if (!$startDate || $startDate === 'undefined' || $startDate === 'null') {
            $startDate = null;
        }
        if (!$endDate || $endDate === 'undefined' || $endDate === 'null') {
            $endDate = null;
        }

        $ledgerData = $this->buildUnifiedLedger($supplier, $startDate, $endDate);

        $accountCode = 'AP-' . str_pad($supplier->id, 5, '0', STR_PAD_LEFT);

        return response()->json([
            'supplier' => array_merge($supplier->only([
                'id', 'name', 'company_name', 'email', 'phone', 'city', 'state', 'country',
                'address', 'credit_limit', 'advance_balance', 'due_amount', 'tax_number'
            ]), [
                'account_code' => $accountCode,
                'account_title' => 'Accounts Payable - ' . $supplier->name,
                'account_type' => 'Liability (Creditors / Payable)'
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
            'net_payable_due' => max(0, $ledgerData['closing_balance']),
            'balance' => $ledgerData['closing_balance'],
            'transactions' => $ledgerData['transactions'],
            'summary' => $ledgerData['summary']
        ]);
    }

    /**
     * Get paginated Purchase Orders for Supplier
     */
    public function getPurchaseOrders(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $sortField = $request->get('sort_by', 'order_date');
        $sortDirection = $request->get('sort_dir', 'desc');

        $query = PurchaseOrder::where('supplier_id', $supplier->id)
            ->with(['user']);

        if ($startDate) {
            $query->whereDate('order_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('order_date', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['po_number', 'order_date', 'status', 'total_amount', 'amount_paid', 'due_amount'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'order_date';
        }

        $orders = $query->orderBy($sortField, $sortDirection)->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Get paginated Purchase Returns for Supplier
     */
    public function getPurchaseReturns(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $sortField = $request->get('sort_by', 'return_date');
        $sortDirection = $request->get('sort_dir', 'desc');

        $query = PurchaseReturn::where('supplier_id', $supplier->id)
            ->with(['purchaseOrder']);

        if ($startDate) {
            $query->whereDate('return_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('return_date', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['return_number', 'return_date', 'status', 'total_amount'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'return_date';
        }

        $returns = $query->orderBy($sortField, $sortDirection)->paginate($perPage);

        return response()->json($returns);
    }

    /**
     * Get paginated Transactions for Supplier
     */
    public function getTransactions(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $ledgerData = $this->buildUnifiedLedger($supplier, $startDate, $endDate);
        $allTx = $ledgerData['transactions'];

        if ($search) {
            $allTx = array_values(array_filter($allTx, function ($t) use ($search) {
                $s = strtolower($search);
                return str_contains(strtolower($t['reference'] ?? ''), $s) ||
                       str_contains(strtolower($t['description'] ?? ''), $s) ||
                       str_contains(strtolower($t['particulars'] ?? ''), $s) ||
                       str_contains(strtolower($t['type'] ?? ''), $s);
            }));
        }

        $total = count($allTx);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($allTx, $offset, $perPage);

        return response()->json([
            'current_page' => $page,
            'data' => $items,
            'from' => $total > 0 ? $offset + 1 : null,
            'last_page' => (int) ceil($total / $perPage) ?: 1,
            'per_page' => $perPage,
            'to' => min($offset + $perPage, $total),
            'total' => $total
        ]);
    }

    /**
     * Build unified chronological Accounts Payable General Ledger for Supplier
     */
    private function buildUnifiedLedger(Supplier $supplier, ?string $startDate, ?string $endDate): array
    {
        // 1. Calculate Opening Balance (Credit B/F) as of $startDate
        $openingBalance = $startDate ? $this->calculateOpeningBalance($supplier, $startDate) : 0.00;

        $entries = [];

        // 2. Fetch Purchase Orders / Purchase Invoices (Credit = Purchases / Bills)
        $poQuery = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->with(['purchaseOrderItems.product', 'user']);

        if ($startDate) {
            $poQuery->where(function ($q) use ($startDate) {
                $q->whereDate('order_date', '>=', $startDate)
                  ->orWhereDate('created_at', '>=', $startDate);
            });
        }
        if ($endDate) {
            $poQuery->where(function ($q) use ($endDate) {
                $q->whereDate('order_date', '<=', $endDate)
                  ->orWhereDate('created_at', '<=', $endDate);
            });
        }

        $purchaseOrders = $poQuery->get();

        foreach ($purchaseOrders as $po) {
            $entryDate = $po->order_date ? Carbon::parse($po->order_date)->format('Y-m-d') : ($po->created_at ? $po->created_at->format('Y-m-d') : date('Y-m-d'));

            $itemsBreakdown = [];
            if ($po->purchaseOrderItems && $po->purchaseOrderItems->count() > 0) {
                foreach ($po->purchaseOrderItems as $item) {
                    $prodName = $item->product ? $item->product->name : 'Item';
                    $qty = (float)($item->quantity_ordered ?: 1);
                    $price = (float)($item->unit_cost ?: 0);
                    $total = (float)($item->total_cost ?: ($qty * $price));
                    $itemsBreakdown[] = [
                        'name' => $prodName,
                        'qty' => $qty,
                        'price' => $price,
                        'total' => $total
                    ];
                }
            }

            $amount = (float)($po->total_amount ?? $po->grand_total ?? 0);
            $paidAmount = (float)($po->amount_paid ?? $po->paid_amount ?? 0);
            $dueAmount = max(0, $amount - $paidAmount);
            $purchaserName = $po->user ? $po->user->name : 'Admin';

            $statusText = 'Unpaid / Due';
            if ($paidAmount >= $amount && $amount > 0) {
                $statusText = 'Paid';
            } elseif ($paidAmount > 0) {
                $statusText = 'Partially Paid';
            }

            $entries[] = [
                'id' => 'po_' . $po->id,
                'date' => $entryDate,
                'reference' => $po->po_number ?: "PO-{$po->id}",
                'type' => 'Purchase Invoice',
                'description' => $po->notes ?: "Purchase Order #{$po->po_number}",
                'particulars' => "Purchase Invoice #{$po->po_number}",
                'items' => $itemsBreakdown,
                'salesman' => $purchaserName,
                'purchaser' => $purchaserName,
                'status' => $statusText,
                'total_amount' => $amount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'debit' => 0.00,
                'credit' => $amount, // Purchases increase Accounts Payable Liability (Credit)
                'created_at' => $po->created_at ? $po->created_at->toDateTimeString() : $entryDate . ' 00:00:00',
                'source_type' => 'purchase_order',
                'source_id' => $po->id
            ];
        }

        // 3. Fetch Purchase Returns (Debit = Decrease Liability)
        $retQuery = PurchaseReturn::where('supplier_id', $supplier->id)
            ->whereNotIn('status', ['cancelled', 'void']);

        if ($startDate) {
            $retQuery->where(function ($q) use ($startDate) {
                $q->whereDate('return_date', '>=', $startDate)
                  ->orWhereDate('created_at', '>=', $startDate);
            });
        }
        if ($endDate) {
            $retQuery->where(function ($q) use ($endDate) {
                $q->whereDate('return_date', '<=', $endDate)
                  ->orWhereDate('created_at', '<=', $endDate);
            });
        }

        $purchaseReturns = $retQuery->get();

        foreach ($purchaseReturns as $ret) {
            $entryDate = $ret->return_date ? Carbon::parse($ret->return_date)->format('Y-m-d') : ($ret->created_at ? $ret->created_at->format('Y-m-d') : date('Y-m-d'));
            $amount = (float)($ret->total_amount ?? 0);

            $entries[] = [
                'id' => 'ret_' . $ret->id,
                'date' => $entryDate,
                'reference' => $ret->return_number ?: "PR-{$ret->id}",
                'type' => 'Purchase Return',
                'description' => $ret->reason ?: $ret->notes ?: "Purchase Return #{$ret->return_number}",
                'particulars' => "Purchase Return / Debit Note #{$ret->return_number}",
                'items' => [],
                'salesman' => '-',
                'purchaser' => '-',
                'status' => 'Returned & Credited',
                'total_amount' => $amount,
                'paid_amount' => $amount,
                'due_amount' => 0.00,
                'debit' => $amount, // Returns decrease Accounts Payable Liability (Debit)
                'credit' => 0.00,
                'created_at' => $ret->created_at ? $ret->created_at->toDateTimeString() : $entryDate . ' 00:00:00',
                'source_type' => 'purchase_return',
                'source_id' => $ret->id
            ];
        }

        // 4. Fetch Payments Out (`Payment` table where payee is Supplier)
        $paymentQuery = Payment::whereNotIn('status', ['cancelled', 'void', 'failed'])
            ->where(function ($q) use ($supplier) {
                $q->where('payee_id', $supplier->id)
                  ->orWhere(function ($sub) use ($supplier) {
                      $sub->where('payee_type', 'App\\Models\\Supplier')
                          ->where('payee_id', $supplier->id);
                  });
            });

        if ($startDate) {
            $paymentQuery->where(function ($q) use ($startDate) {
                $q->whereDate('payment_date', '>=', $startDate)
                  ->orWhereDate('created_at', '>=', $startDate);
            });
        }
        if ($endDate) {
            $paymentQuery->where(function ($q) use ($endDate) {
                $q->whereDate('payment_date', '<=', $endDate)
                  ->orWhereDate('created_at', '<=', $endDate);
            });
        }

        $payments = $paymentQuery->get();
        $trackedPaymentRefs = [];

        foreach ($payments as $pm) {
            $entryDate = $pm->payment_date ? Carbon::parse($pm->payment_date)->format('Y-m-d') : ($pm->created_at ? $pm->created_at->format('Y-m-d') : date('Y-m-d'));
            $amount = (float)$pm->amount;
            $ref = $pm->payment_number ?: "PM-{$pm->id}";
            $trackedPaymentRefs[] = strtolower(trim($ref));

            $entries[] = [
                'id' => 'pm_' . $pm->id,
                'date' => $entryDate,
                'reference' => $ref,
                'type' => 'Payment Out',
                'description' => $pm->notes ?: $pm->description ?: "Supplier Payment #{$ref}",
                'particulars' => "Supplier Payment (" . ucfirst($pm->payment_method ?: 'cash') . ")",
                'items' => [],
                'salesman' => '-',
                'purchaser' => '-',
                'status' => 'Paid & Posted',
                'total_amount' => $amount,
                'paid_amount' => $amount,
                'due_amount' => 0.00,
                'debit' => $amount, // Payments made decrease Accounts Payable Liability (Debit)
                'credit' => 0.00,
                'created_at' => $pm->created_at ? $pm->created_at->toDateTimeString() : $entryDate . ' 00:00:00',
                'source_type' => 'supplier_payment',
                'source_id' => $pm->id
            ];
        }

        // 5. Fetch Banking Transactions (`transactions` table) with deduplication
        $bankingQuery = Transaction::where('vendor_id', $supplier->id);
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
            $ref = $tx->reference ?: $tx->number;
            if ($ref && in_array(strtolower(trim($ref)), $trackedPaymentRefs)) {
                continue; // Prevent duplicate entries for recorded payments
            }

            $entryDate = $tx->paid_at ? Carbon::parse($tx->paid_at)->format('Y-m-d') : ($tx->created_at ? $tx->created_at->format('Y-m-d') : date('Y-m-d'));
            $amount = (float)$tx->amount;
            $isExpense = ($tx->type === 'expense');

            $entries[] = [
                'id' => 'tx_' . $tx->id,
                'date' => $entryDate,
                'reference' => $ref ?: "TX-{$tx->id}",
                'type' => $isExpense ? 'Payment Out' : 'Vendor Refund',
                'description' => $tx->description ?: ($isExpense ? "Supplier Payment ({$ref})" : "Vendor Refund ({$ref})"),
                'particulars' => $isExpense ? "Supplier Banking Payment ({$ref})" : "Vendor Refund Received ({$ref})",
                'items' => [],
                'salesman' => '-',
                'purchaser' => '-',
                'status' => 'Posted',
                'total_amount' => $amount,
                'paid_amount' => $amount,
                'due_amount' => 0.00,
                'debit' => $isExpense ? $amount : 0.00,
                'credit' => $isExpense ? 0.00 : $amount,
                'created_at' => $tx->created_at ? $tx->created_at->toDateTimeString() : $entryDate . ' 00:00:00',
                'source_type' => 'banking_transaction',
                'source_id' => $tx->id
            ];
        }

        // 6. Sort all entries chronologically by Date (Ascending) and Created At
        usort($entries, function ($a, $b) {
            $dateCmp = strcmp($a['date'], $b['date']);
            if ($dateCmp !== 0) return $dateCmp;
            return strcmp($a['created_at'], $b['created_at']);
        });

        // 7. Calculate Running Accounts Payable Balance
        // Liability Balance: Credit Increases (+), Debit Decreases (-)
        $runningBalance = $openingBalance;
        $totalDebits = 0.00;
        $totalCredits = 0.00;

        foreach ($entries as &$entry) {
            $debit = (float)$entry['debit'];
            $credit = (float)$entry['credit'];

            $totalDebits += $debit;
            $totalCredits += $credit;

            $runningBalance += ($credit - $debit);
            $entry['balance'] = $runningBalance;
            $entry['balance_type'] = $runningBalance >= 0 ? 'Cr' : 'Dr';
        }
        unset($entry);

        $closingBalance = $runningBalance;
        $closingBalanceType = $closingBalance >= 0 ? 'Cr' : 'Dr';

        return [
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'closing_balance_type' => $closingBalanceType,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'transactions' => $entries,
            'summary' => [
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'closing_balance_type' => $closingBalanceType,
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'net_movement' => $closingBalance - $openingBalance,
                'entry_count' => count($entries)
            ]
        ];
    }

    /**
     * Calculate opening balance for a supplier as of a specific date
     */
    private function calculateOpeningBalance(Supplier $supplier, $asOfDate): float
    {
        $priorPurchases = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereDate('order_date', '<', $asOfDate)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->sum('total_amount');

        $priorReturns = PurchaseReturn::where('supplier_id', $supplier->id)
            ->whereDate('return_date', '<', $asOfDate)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->sum('total_amount');

        $priorTxExpense = Transaction::where('vendor_id', $supplier->id)
            ->whereDate('paid_at', '<', $asOfDate)
            ->where('type', 'expense')
            ->sum('amount');

        $priorTxIncome = Transaction::where('vendor_id', $supplier->id)
            ->whereDate('paid_at', '<', $asOfDate)
            ->where('type', 'income')
            ->sum('amount');

        $trackedTxNumbers = Transaction::where('vendor_id', $supplier->id)
            ->whereNotNull('number')
            ->pluck('number')
            ->toArray();

        $priorPayments = Payment::whereNotIn('status', ['cancelled', 'void', 'failed'])
            ->where(function ($q) use ($supplier) {
                $q->where('payee_id', $supplier->id)
                  ->orWhere('payee_type', 'App\\Models\\Supplier');
            })
            ->whereDate('payment_date', '<', $asOfDate)
            ->whereNotIn('payment_number', $trackedTxNumbers)
            ->sum('amount');

        return (float) ($priorPurchases - $priorReturns - $priorTxExpense - $priorPayments + $priorTxIncome);
    }
}
