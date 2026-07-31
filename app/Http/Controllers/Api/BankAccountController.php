<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show', 'transactions', 'reconciliationSummary', 'transfersList']);
        $this->middleware('permission:accounting.create')->only(['store', 'transfer']);
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
        $companyId = auth()->user()->current_company_id;

        $rawType = $request->input('account_type', 'checking');
        if ($rawType === 'bank') {
            $rawType = 'checking';
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bank_accounts', 'account_number')->where('company_id', $companyId),
            ],
            'account_type' => 'required|string',
            'chart_account_id' => 'nullable|exists:chart_of_accounts,id',
            'routing_number' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'opening_balance' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'bank_phone' => 'nullable|string|max:50',
            'bank_address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['account_type'] = $rawType;
        if (empty($data['bank_name'])) {
            $data['bank_name'] = $data['account_name'];
        }

        if (empty($data['currency'])) {
            $company = auth()->user()->currentCompany ?? \App\Models\Company::find($companyId);
            $data['currency'] = $company?->base_currency ?? $company?->currency_code ?? 'PKR';
        }

        try {
            return DB::transaction(function () use ($data, $request, $companyId) {
                if (!empty($data['is_default'])) {
                    BankAccount::where('company_id', $companyId)->update(['is_default' => false]);
                }

                // Auto-create or update Chart of Account to link to 1020 Bank Account
                $parentAccount = Account::where('company_id', $companyId)
                    ->where(function ($q) {
                        $q->where('account_code', '1020')
                          ->orWhere('account_name', 'like', '%Bank Account%');
                    })
                    ->where('account_type', 'asset')
                    ->first();

                if (empty($data['chart_account_id'])) {
                    $maxCode = Account::where('account_type', 'asset')
                        ->where('company_id', $companyId)
                        ->max('account_code');
                    $newCode = $maxCode && is_numeric($maxCode) ? (string)((int)$maxCode + 10) : '1050';

                    $isCreditCard = ($data['account_type'] === 'credit_card');

                    $chartAccount = Account::create([
                        'account_code' => $newCode,
                        'account_name' => $data['account_name'] . ' (' . $data['bank_name'] . ')',
                        'account_type' => $isCreditCard ? 'liability' : 'asset',
                        'account_subtype' => $isCreditCard ? 'current_liability' : 'current_asset',
                        'description' => 'Bank Account for ' . $data['account_name'],
                        'opening_balance' => $data['opening_balance'] ?? 0,
                        'current_balance' => $data['opening_balance'] ?? 0,
                        'is_active' => true,
                        'is_system_account' => false,
                        'parent_account_id' => $isCreditCard ? null : $parentAccount?->id,
                    ]);
                    $data['chart_account_id'] = $chartAccount->id;
                } else {
                    $chartAccount = Account::find($request->chart_account_id);
                    if ($chartAccount && !in_array($chartAccount->account_type, ['asset', 'liability'])) {
                        return response()->json([
                            'message' => 'Bank accounts must be linked to asset or liability accounts'
                        ], 422);
                    }

                    $isCreditCard = ($data['account_type'] === 'credit_card');
                    if ($chartAccount && !$isCreditCard && $parentAccount && $chartAccount->id !== $parentAccount->id) {
                        $chartAccount->update([
                            'parent_account_id' => $parentAccount->id,
                            'account_type' => 'asset',
                            'account_subtype' => $chartAccount->account_subtype ?: 'current_asset',
                        ]);
                    }
                }

                $bankAccount = BankAccount::create($data);
                $this->updateAccountBalance($bankAccount->id);

                return response()->json([
                    'message' => 'Bank account created successfully',
                    'bank_account' => $bankAccount->load('chartAccount')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create bank account: ' . $e->getMessage()
            ], 500);
        }
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
        $companyId = auth()->user()->current_company_id;

        $rawType = $request->input('account_type', $bankAccount->account_type);
        if ($rawType === 'bank') {
            $rawType = 'checking';
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bank_accounts', 'account_number')->ignore($bankAccount->id)->where('company_id', $companyId),
            ],
            'account_type' => 'required|string',
            'chart_account_id' => 'nullable|exists:chart_of_accounts,id',
            'routing_number' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'opening_balance' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'bank_phone' => 'nullable|string|max:50',
            'bank_address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['account_type'] = $rawType;
        if (empty($data['bank_name'])) {
            $data['bank_name'] = $data['account_name'];
        }

        if (!empty($data['is_default'])) {
            BankAccount::where('company_id', $companyId)->where('id', '!=', $bankAccount->id)->update(['is_default' => false]);
        }

        if (!empty($data['chart_account_id'])) {
            $chartAccount = Account::find($data['chart_account_id']);
            if ($chartAccount && !in_array($chartAccount->account_type, ['asset', 'liability'])) {
                return response()->json([
                    'message' => 'Bank accounts must be linked to asset or liability accounts'
                ], 422);
            }
        }

        $bankAccount->update($data);
        $this->updateAccountBalance($bankAccount->id);

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
        $query = $bankAccount->bankTransactions()->with(['journalEntry']);

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

    /**
     * List all inter-account transfers with proper from/to bank names
     */
    public function transfersList(Request $request): JsonResponse
    {
        // Get all transfer bank transactions (those with a journal_entry_id and description containing "Transfer")
        $transactions = BankTransaction::with('bankAccount')
            ->whereNotNull('journal_entry_id')
            ->where('description', 'like', '%Transfer%')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Group by journal_entry_id to pair credit/debit sides
        $grouped = $transactions->groupBy('journal_entry_id');

        $transfers = [];
        foreach ($grouped as $journalEntryId => $pair) {
            $creditTx = $pair->firstWhere('transaction_type', 'credit'); // from (money out)
            $debitTx = $pair->firstWhere('transaction_type', 'debit');   // to (money in)

            if (!$creditTx || !$debitTx) continue;

            $transfers[] = [
                'id' => $creditTx->id,
                'journal_entry_id' => $journalEntryId,
                'transaction_date' => $creditTx->transaction_date?->format('Y-m-d'),
                'from_account_name' => $creditTx->bankAccount->account_name ?? 'Unknown',
                'to_account_name' => $debitTx->bankAccount->account_name ?? 'Unknown',
                'reference_number' => $creditTx->reference_number,
                'description' => $creditTx->description,
                'amount' => $creditTx->amount,
            ];
        }

        return response()->json($transfers);
    }

    /**
     * Transfer funds between bank accounts
     */
    public function transfer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from_bank_account_id' => 'required|exists:bank_accounts,id|different:to_bank_account_id',
            'to_bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $fromAccount = BankAccount::with('chartAccount')->findOrFail($request->from_bank_account_id);
                $toAccount = BankAccount::with('chartAccount')->findOrFail($request->to_bank_account_id);
                $amount = (float) $request->amount;
                $date = $request->transfer_date;
                $ref = $request->reference_number ?: ('TRF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT));
                $desc = $request->description ?: "Transfer from {$fromAccount->account_name} to {$toAccount->account_name}";

                // 1. Create COA Journal Entry
                $journalEntry = JournalEntry::create([
                    'entry_number' => 'JE-TRF-' . date('Ymd') . '-' . mt_rand(100, 999),
                    'entry_date' => $date,
                    'reference' => $ref,
                    'description' => $desc,
                    'entry_type' => 'automatic',
                    'status' => 'posted',
                    'total_debit' => $amount,
                    'total_credit' => $amount,
                    'created_by' => auth()->id(),
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                // Debit Destination Account (COA)
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $toAccount->chart_account_id,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'description' => "Transfer in from {$fromAccount->account_name}",
                ]);

                // Credit Source Account (COA)
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $fromAccount->chart_account_id,
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'description' => "Transfer out to {$toAccount->account_name}",
                ]);

                // 2. Log Bank Transactions
                $fromLastTx = BankTransaction::where('bank_account_id', $fromAccount->id)->latest('id')->first();
                $fromPrevBal = $fromLastTx ? $fromLastTx->running_balance : $fromAccount->opening_balance;
                $fromNewBal = $fromPrevBal - $amount;
                BankTransaction::create([
                    'bank_account_id' => $fromAccount->id,
                    'transaction_date' => $date,
                    'reference_number' => $ref,
                    'description' => $desc,
                    'transaction_type' => 'credit',
                    'amount' => $amount,
                    'running_balance' => $fromNewBal,
                    'status' => 'cleared',
                    'journal_entry_id' => $journalEntry->id,
                ]);

                $toLastTx = BankTransaction::where('bank_account_id', $toAccount->id)->latest('id')->first();
                $toPrevBal = $toLastTx ? $toLastTx->running_balance : $toAccount->opening_balance;
                $toNewBal = $toPrevBal + $amount;
                BankTransaction::create([
                    'bank_account_id' => $toAccount->id,
                    'transaction_date' => $date,
                    'reference_number' => $ref,
                    'description' => $desc,
                    'transaction_type' => 'debit',
                    'amount' => $amount,
                    'running_balance' => $toNewBal,
                    'status' => 'cleared',
                    'journal_entry_id' => $journalEntry->id,
                ]);

                // 3. Update Bank Accounts Table
                $fromAccount->decrement('current_balance', $amount);
                $toAccount->increment('current_balance', $amount);

                // 4. Update Linked Chart of Accounts Records
                if ($fromAccount->chart_account_id) {
                    DB::table('chart_of_accounts')
                        ->where('id', $fromAccount->chart_account_id)
                        ->decrement('current_balance', $amount);
                }

                if ($toAccount->chart_account_id) {
                    DB::table('chart_of_accounts')
                        ->where('id', $toAccount->chart_account_id)
                        ->increment('current_balance', $amount);
                }

                return response()->json([
                    'message' => 'Transfer completed successfully',
                    'journal_entry' => $journalEntry
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Standard Balance Sync Helper Function:
     * Syncs current_balance on both bank_accounts and chart_of_accounts
     */
    public function updateAccountBalance($bankAccountId, $newBalance = null): void
    {
        DB::transaction(function () use ($bankAccountId, $newBalance) {
            $bankAccount = BankAccount::find($bankAccountId);
            if (!$bankAccount) return;

            if ($newBalance === null) {
                $newBalance = $bankAccount->calculateBalance();
            }

            // 1. Update Bank Account table
            $bankAccount->update(['current_balance' => $newBalance]);

            // 2. Sync linked Chart of Account record
            Account::where('company_id', $bankAccount->company_id)
                ->where(function ($query) use ($bankAccount) {
                    if ($bankAccount->chart_account_id) {
                        $query->where('id', $bankAccount->chart_account_id);
                    }
                    if ($bankAccount->bank_name) {
                        $query->orWhere('account_name', 'LIKE', "%{$bankAccount->bank_name}%");
                    }
                    if ($bankAccount->account_name) {
                        $query->orWhere('account_name', 'LIKE', "%{$bankAccount->account_name}%");
                    }
                })
                ->update(['current_balance' => $newBalance]);
        });
    }
}
