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
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierLedgerController extends Controller
{
    /**
     * Get supplier ledger overall report and stats
     */
    public function getLedger(Request $request, Supplier $supplier): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Opening balance calculation
        $openingBalance = $startDate ? $this->calculateOpeningBalance($supplier, $startDate) : 0.0;

        // Transactions logic
        $transactions = $this->getSupplierTransactions($supplier, $startDate, $endDate);

        $runningBalance = $openingBalance;
        $processedTransactions = [];
        foreach ($transactions as $tx) {
            $runningBalance += ($tx['credit'] - $tx['debit']);
            $tx['running_balance'] = $runningBalance;
            $processedTransactions[] = $tx;
        }

        $closingBalance = $runningBalance;

        // Purchase Orders
        $poQuery = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereNotIn('status', ['cancelled']);
        if ($startDate) {
            $poQuery->whereDate('order_date', '>=', $startDate);
        }
        if ($endDate) {
            $poQuery->whereDate('order_date', '<=', $endDate);
        }
        $purchaseOrders = $poQuery->orderBy('order_date', 'desc')->get();

        $paymentPending = $purchaseOrders->sum(function ($po) {
            $total = (float) ($po->total_amount ?? $po->grand_total ?? 0);
            $paid = (float) ($po->amount_paid ?? $po->paid_amount ?? 0);
            return max(0, $total - $paid);
        });

        $paymentMade = collect($processedTransactions)->sum('debit');

        return response()->json([
            'supplier' => $supplier->only(['id', 'name', 'company_name', 'email', 'phone', 'city', 'state', 'country', 'credit_limit', 'advance_balance', 'due_amount', 'tax_number', 'full_address']),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'stats' => [
                'payment_pending' => $paymentPending,
                'payment_made' => $paymentMade,
                'balance' => $closingBalance
            ],
            'summary' => [
                'total_debits' => collect($processedTransactions)->sum('debit'),
                'total_credits' => collect($processedTransactions)->sum('credit'),
                'net_movement' => $closingBalance - $openingBalance,
                'transaction_count' => count($processedTransactions)
            ]
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

        $allTx = $this->getSupplierTransactions($supplier, $startDate, $endDate);

        if ($search) {
            $allTx = array_values(array_filter($allTx, function ($t) use ($search) {
                $s = strtolower($search);
                return str_contains(strtolower($t['reference'] ?? ''), $s) ||
                       str_contains(strtolower($t['description'] ?? ''), $s) ||
                       str_contains(strtolower($t['type'] ?? ''), $s);
            }));
        }

        $openingBalance = $startDate ? $this->calculateOpeningBalance($supplier, $startDate) : 0.0;
        $runningBalance = $openingBalance;
        foreach ($allTx as &$t) {
            $runningBalance += ($t['credit'] - $t['debit']);
            $t['running_balance'] = $runningBalance;
        }
        unset($t);

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
     * Export Supplier General Ledger PDF
     */
    public function exportPDF(Request $request, Supplier $supplier)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $openingBalance = $startDate ? $this->calculateOpeningBalance($supplier, $startDate) : 0.0;
        $transactions = $this->getSupplierTransactions($supplier, $startDate, $endDate);

        $runningBalance = $openingBalance;
        $processedTransactions = [];
        foreach ($transactions as $tx) {
            $runningBalance += ($tx['credit'] - $tx['debit']);
            $tx['running_balance'] = $runningBalance;
            $processedTransactions[] = $tx;
        }
        $closingBalance = $runningBalance;

        // Purchase Orders
        $poQuery = PurchaseOrder::where('supplier_id', $supplier->id)
            ->with(['user']);
        if ($startDate) {
            $poQuery->whereDate('order_date', '>=', $startDate);
        }
        if ($endDate) {
            $poQuery->whereDate('order_date', '<=', $endDate);
        }
        $purchaseOrders = $poQuery->orderBy('order_date', 'desc')->get();

        $paymentPending = $purchaseOrders->sum(function ($po) {
            $total = (float) ($po->total_amount ?? $po->grand_total ?? 0);
            $paid = (float) ($po->amount_paid ?? $po->paid_amount ?? 0);
            return max(0, $total - $paid);
        });
        $paymentMade = collect($processedTransactions)->sum('debit');

        // Purchase Returns
        $retQuery = PurchaseReturn::where('supplier_id', $supplier->id)
            ->with(['purchaseOrder']);
        if ($startDate) {
            $retQuery->whereDate('return_date', '>=', $startDate);
        }
        if ($endDate) {
            $retQuery->whereDate('return_date', '<=', $endDate);
        }
        $purchaseReturns = $retQuery->orderBy('return_date', 'desc')->get();

        $pdf = Pdf::loadView('pdf.supplier_ledger', [
            'supplier' => $supplier,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'paymentPending' => $paymentPending,
            'paymentMade' => $paymentMade,
            'purchaseOrders' => $purchaseOrders,
            'returns' => $purchaseReturns,
            'transactions' => $processedTransactions
        ]);

        $name = strtolower(preg_replace('/[^a-z0-9]/i', '_', $supplier->name ?: 'supplier'));
        $dateStr = now()->format('Y-m-d');
        return $pdf->download("supplier_ledger_{$name}_{$dateStr}.pdf");
    }

    /**
     * Calculate opening balance for a supplier as of a specific date
     */
    private function calculateOpeningBalance(Supplier $supplier, $asOfDate): float
    {
        $priorPurchases = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereDate('order_date', '<', $asOfDate)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        $priorReturns = PurchaseReturn::where('supplier_id', $supplier->id)
            ->whereDate('return_date', '<', $asOfDate)
            ->whereNotIn('status', ['cancelled'])
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

        $priorPayments = Payment::where(function ($q) use ($supplier) {
                $q->where('payee_id', $supplier->id)
                  ->orWhere('payee_type', 'App\\Models\\Supplier');
            })
            ->whereDate('payment_date', '<', $asOfDate)
            ->whereNotIn('payment_number', $trackedTxNumbers)
            ->sum('amount');

        return (float) ($priorPurchases - $priorReturns - $priorTxExpense - $priorPayments + $priorTxIncome);
    }

    /**
     * Get supplier transactions for a period
     */
    private function getSupplierTransactions(Supplier $supplier, $startDate, $endDate): array
    {
        $transactions = [];

        // 1. Banking Transactions
        $bankingQuery = Transaction::where('vendor_id', $supplier->id);
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

            $isExpense = ($tx->type === 'expense');

            $transactions[] = [
                'date' => $formattedDate,
                'type' => $isExpense ? 'Payment Out' : 'Refund Received',
                'reference' => $ref ?: "TX-{$tx->id}",
                'description' => $tx->description ?: ($isExpense ? "Supplier Payment ({$ref})" : "Refund Received ({$ref})"),
                'debit' => $isExpense ? (float) $tx->amount : 0,
                'credit' => $isExpense ? 0 : (float) $tx->amount,
                'source_id' => $tx->id,
                'source_type' => 'transaction',
                'created_at' => $tx->created_at ? $tx->created_at->toDateTimeString() : $formattedDate
            ];
        }

        // 2. Payments (Payment Out)
        $paymentQuery = Payment::where(function ($q) use ($supplier) {
            $q->where('payee_id', $supplier->id)
              ->orWhere(function ($sub) use ($supplier) {
                  $sub->where('payee_type', 'App\\Models\\Supplier')
                      ->where('payee_id', $supplier->id);
              });
        });
        if ($startDate) {
            $paymentQuery->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $paymentQuery->whereDate('payment_date', '<=', $endDate);
        }
        $payments = $paymentQuery->get();

        foreach ($payments as $pm) {
            if ($pm->payment_number && in_array($pm->payment_number, $trackedTxNumbers)) {
                continue;
            }

            $formattedDate = $pm->payment_date ? Carbon::parse($pm->payment_date)->format('Y-m-d') : date('Y-m-d');

            $transactions[] = [
                'date' => $formattedDate,
                'type' => 'Supplier Payment',
                'reference' => $pm->payment_number,
                'description' => $pm->description ?: "Supplier Payment #{$pm->payment_number}",
                'debit' => (float) $pm->amount,
                'credit' => 0,
                'source_id' => $pm->id,
                'source_type' => 'payment',
                'created_at' => $pm->created_at ? $pm->created_at->toDateTimeString() : $formattedDate
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
}
