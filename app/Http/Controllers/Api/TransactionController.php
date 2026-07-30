<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:accounting.view')->only(['index', 'summary', 'exportPDF']);
        $this->middleware('permission:accounting.create')->only(['store']);
    }

    /**
     * Display a listing of transactions
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['bankAccount', 'category', 'customer', 'vendor', 'tax']);

        if ($request->has('account_id') && $request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('transaction_type') && $request->transaction_type) {
            $typeMap = [
                'debit' => 'expense',
                'credit' => 'income',
                'income' => 'income',
                'expense' => 'expense',
            ];
            if (isset($typeMap[$request->transaction_type])) {
                $query->where('type', $typeMap[$request->transaction_type]);
            }
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 50);
        $paginated = $query->orderBy('paid_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        // Map transactions to consistent structure expected by frontend
        $mappedItems = $paginated->getCollection()->map(function ($tx) {
            return [
                'id' => $tx->id,
                'transaction_date' => $tx->paid_at ? $tx->paid_at->format('Y-m-d') : date('Y-m-d'),
                'paid_at' => $tx->paid_at ? $tx->paid_at->format('Y-m-d') : date('Y-m-d'),
                'bank_account_id' => $tx->account_id,
                'bank_account' => $tx->bankAccount ? [
                    'id' => $tx->bankAccount->id,
                    'account_name' => $tx->bankAccount->account_name,
                    'bank_name' => $tx->bankAccount->bank_name,
                    'currency' => $tx->bankAccount->currency,
                ] : null,
                'reference_number' => $tx->reference || $tx->number,
                'number' => $tx->number,
                'description' => $tx->description,
                'transaction_type' => $tx->type === 'income' ? 'credit' : 'debit',
                'type' => $tx->type,
                'amount' => (float) $tx->amount,
                'payment_method' => $tx->payment_method,
                'category' => $tx->category,
                'customer' => $tx->customer,
                'vendor' => $tx->vendor,
                'tax' => $tx->tax,
                'running_balance' => (float) ($tx->bankAccount->opening_balance ?? 0),
            ];
        });

        $paginated->setCollection($mappedItems);

        return response()->json($paginated);
    }

    /**
     * Store a newly created transaction (Income or Expense)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'paid_at' => 'required|date',
            'payment_method' => 'nullable|string',
            'account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'number' => 'required|string|unique:transactions,number',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:chart_of_accounts,id',
            'customer_id' => 'nullable|exists:customers,id',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'reference' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('transaction_attachments', 'public');
            }

            $companyId = session('active_company_id') ?? auth()->user()->company_id ?? 1;

            $transaction = Transaction::create([
                'company_id' => $companyId,
                'type' => $request->type,
                'paid_at' => $request->paid_at,
                'payment_method' => $request->payment_method ?? 'Cash',
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'customer_id' => $request->customer_id,
                'vendor_id' => $request->vendor_id,
                'tax_id' => $request->tax_id,
                'number' => $request->number,
                'reference' => $request->reference,
                'attachment_path' => $attachmentPath,
            ]);

            // Update linked Bank Account balance and record in BankTransaction
            $bankAccount = BankAccount::findOrFail($request->account_id);
            $lastBankTx = BankTransaction::where('bank_account_id', $request->account_id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $currentBal = $lastBankTx ? (float)$lastBankTx->running_balance : (float)$bankAccount->opening_balance;
            $isIncome = $request->type === 'income';
            $txType = $isIncome ? 'credit' : 'debit';
            $newBal = $isIncome ? ($currentBal + (float)$request->amount) : ($currentBal - (float)$request->amount);

            $bankTx = BankTransaction::create([
                'bank_account_id' => $request->account_id,
                'transaction_date' => $request->paid_at,
                'transaction_type' => $txType,
                'amount' => $request->amount,
                'description' => ($isIncome ? 'Income: ' : 'Expense: ') . ($request->description ?? $request->number),
                'reference_number' => $request->reference ?? $request->number,
                'running_balance' => $newBal,
                'status' => 'posted',
                'notes' => "Payment Method: " . ($request->payment_method ?? 'Cash'),
            ]);

            // Update opening_balance or current balance on BankAccount if applicable
            $bankAccount->opening_balance = $newBal;
            $bankAccount->save();

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => $transaction->load(['bankAccount', 'category', 'customer', 'vendor', 'tax'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction summary
     */
    public function summary(Request $request): JsonResponse
    {
        $accountId = $request->get('account_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Transaction::query();

        if ($accountId) {
            $query->where('account_id', $accountId);
        }
        if ($dateFrom) {
            $query->whereDate('paid_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('paid_at', '<=', $dateTo);
        }

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        return response()->json([
            'summary' => [
                'total_income' => (float) $totalIncome,
                'total_expense' => (float) $totalExpense,
                'net_balance' => (float) ($totalIncome - $totalExpense),
                'transaction_count' => $query->count(),
            ]
        ]);
    }

    /**
     * Export PDF of transactions
     */
    public function exportPDF(Request $request)
    {
        $query = Transaction::with(['bankAccount', 'category', 'customer', 'vendor', 'tax']);

        if ($request->has('account_id') && $request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('paid_at', 'desc')->get();

        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        $summary = [
            'total_count' => count($transactions),
            'total_income' => $transactions->where('type', 'income')->sum('amount'),
            'total_expense' => $transactions->where('type', 'expense')->sum('amount'),
        ];

        $pdf = Pdf::loadView('pdf.transactions', [
            'transactions' => $transactions,
            'filters' => $filters,
            'summary' => $summary
        ]);

        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('transactions_' . now()->format('Y-m-d') . '.pdf');
    }
}
