<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show', 'transactions']);
        $this->middleware('permission:accounting.create')->only(['store']);
        $this->middleware('permission:accounting.edit')->only(['update', 'reconcile']);
        $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BankAccount::with(['chartAccount']);

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        $bankAccounts = $query->orderBy('account_name')->get();

        // Add current balance to each account
        foreach ($bankAccounts as $account) {
            $account->current_balance = $account->calculateBalance();
        }

        return response()->json($bankAccounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number',
            'account_type' => 'required|in:checking,savings,credit_card,line_of_credit,other',
            'chart_account_id' => 'required|exists:chart_of_accounts,id',
            'routing_number' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate that the chart account is an asset or liability account
        $chartAccount = Account::find($request->chart_account_id);
        if (!in_array($chartAccount->account_type, ['asset', 'liability'])) {
            return response()->json([
                'message' => 'Bank accounts must be linked to asset or liability accounts'
            ], 422);
        }

        $bankAccount = BankAccount::create($request->all());

        return response()->json([
            'message' => 'Bank account created successfully',
            'bank_account' => $bankAccount->load('chartAccount')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->load(['chartAccount', 'bankTransactions' => function ($query) {
            $query->orderBy('transaction_date', 'desc')->limit(10);
        }]);

        $bankAccount->current_balance = $bankAccount->calculateBalance();
        $bankAccount->reconciled_balance = $bankAccount->calculateReconciledBalance();

        return response()->json($bankAccount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number,' . $bankAccount->id,
            'account_type' => 'required|in:checking,savings,credit_card,line_of_credit,other',
            'chart_account_id' => 'required|exists:chart_of_accounts,id',
            'routing_number' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate chart account type
        $chartAccount = Account::find($request->chart_account_id);
        if (!in_array($chartAccount->account_type, ['asset', 'liability'])) {
            return response()->json([
                'message' => 'Bank accounts must be linked to asset or liability accounts'
            ], 422);
        }

        $bankAccount->update($request->all());

        return response()->json([
            'message' => 'Bank account updated successfully',
            'bank_account' => $bankAccount->load('chartAccount')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        // Check if bank account has transactions
        if ($bankAccount->bankTransactions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete bank account with existing transactions'
            ], 422);
        }

        $bankAccount->delete();

        return response()->json([
            'message' => 'Bank account deleted successfully'
        ]);
    }

    /**
     * Get bank account transactions
     */
    public function transactions(Request $request, BankAccount $bankAccount): JsonResponse
    {
        $query = $bankAccount->bankTransactions()->with(['journalEntryLine.journalEntry']);

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Filter by reconciliation status
        if ($request->has('reconciled')) {
            $query->where('is_reconciled', $request->boolean('reconciled'));
        }

        // Search by description or reference
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('check_number', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                             ->paginate($request->get('per_page', 20));

        return response()->json($transactions);
    }

    /**
     * Reconcile bank account
     */
    public function reconcile(Request $request, BankAccount $bankAccount): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'statement_date' => 'required|date',
            'statement_balance' => 'required|numeric',
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:bank_transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Mark selected transactions as reconciled
            BankTransaction::whereIn('id', $request->transaction_ids)
                          ->where('bank_account_id', $bankAccount->id)
                          ->update([
                              'is_reconciled' => true,
                              'reconciled_date' => $request->statement_date
                          ]);

            // Update bank account last reconciliation info
            $bankAccount->update([
                'last_reconciled_date' => $request->statement_date,
                'last_statement_balance' => $request->statement_balance,
            ]);

            return response()->json([
                'message' => 'Bank account reconciled successfully',
                'reconciled_transactions' => count($request->transaction_ids)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reconcile bank account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reconciliation summary
     */
    public function reconciliationSummary(BankAccount $bankAccount): JsonResponse
    {
        $summary = [
            'account_balance' => $bankAccount->calculateBalance(),
            'reconciled_balance' => $bankAccount->calculateReconciledBalance(),
            'unreconciled_transactions' => $bankAccount->bankTransactions()
                                                      ->where('is_reconciled', false)
                                                      ->count(),
            'last_reconciled_date' => $bankAccount->last_reconciled_date,
            'last_statement_balance' => $bankAccount->last_statement_balance,
        ];

        $summary['difference'] = $summary['account_balance'] - $summary['reconciled_balance'];

        return response()->json($summary);
    }
}
