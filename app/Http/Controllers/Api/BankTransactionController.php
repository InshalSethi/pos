<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show']);
        $this->middleware('permission:accounting.create')->only(['store', 'import']);
        $this->middleware('permission:accounting.edit')->only(['update', 'match']);
        $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BankTransaction::with(['bankAccount', 'journalEntryLine.journalEntry']);

        // Filter by bank account
        if ($request->has('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        // Filter by reconciliation status
        if ($request->has('reconciled')) {
            $query->where('is_reconciled', $request->boolean('reconciled'));
        }

        // Filter by transaction type
        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Search by description or reference
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                             ->paginate($request->get('per_page', 20));

        return response()->json($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'create_journal_entry' => 'boolean',
            'contra_account_id' => 'required_if:create_journal_entry,true|exists:chart_of_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate running balance
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            $lastTransaction = BankTransaction::where('bank_account_id', $request->bank_account_id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $currentBalance = $lastTransaction ? $lastTransaction->running_balance : $bankAccount->opening_balance;
            $newBalance = $request->transaction_type === 'credit'
                ? $currentBalance + $request->amount
                : $currentBalance - $request->amount;

            $bankTransaction = BankTransaction::create([
                'bank_account_id' => $request->bank_account_id,
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'amount' => $request->amount,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'running_balance' => $newBalance,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create journal entry if requested
            if ($request->boolean('create_journal_entry')) {
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Generate entry number
                $entryNumber = 'JE-' . date('Ymd') . '-' . str_pad(JournalEntry::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

                $journalEntry = JournalEntry::create([
                    'entry_number' => $entryNumber,
                    'entry_date' => $request->transaction_date,
                    'description' => 'Bank Transaction: ' . $request->description,
                    'entry_type' => 'automatic',
                    'status' => 'posted',
                    'total_debit' => $request->amount,
                    'total_credit' => $request->amount,
                    'created_by' => auth()->id() ?? 1,
                    'posted_by' => auth()->id() ?? 1,
                    'posted_at' => now(),
                ]);

                // Create journal entry lines
                if ($request->transaction_type === 'debit') {
                    // Debit: Contra Account, Credit: Bank Account
                    $debitLine = JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $request->contra_account_id,
                        'description' => $request->description,
                        'debit_amount' => $request->amount,
                        'credit_amount' => 0,
                    ]);

                    $creditLine = JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $bankAccount->chart_account_id,
                        'description' => $request->description,
                        'debit_amount' => 0,
                        'credit_amount' => $request->amount,
                    ]);

                    // Link bank transaction to the bank account line
                    $bankTransaction->update(['journal_entry_line_id' => $creditLine->id]);
                } else {
                    // Credit: Debit Bank Account, Credit: Contra Account
                    $debitLine = JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $bankAccount->chart_account_id,
                        'description' => $request->description,
                        'debit_amount' => $request->amount,
                        'credit_amount' => 0,
                    ]);

                    $creditLine = JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $request->contra_account_id,
                        'description' => $request->description,
                        'debit_amount' => 0,
                        'credit_amount' => $request->amount,
                    ]);

                    // Link bank transaction to the bank account line
                    $bankTransaction->update(['journal_entry_line_id' => $debitLine->id]);
                }

                // Update account balances
                $bankAccount->chartAccount->updateCurrentBalance();
                $contraAccount = \App\Models\Account::find($request->contra_account_id);
                $contraAccount->updateCurrentBalance();
            }

            DB::commit();

            $bankTransaction->load(['bankAccount', 'journalEntryLine.journalEntry']);

            return response()->json([
                'message' => 'Bank transaction created successfully',
                'bank_transaction' => $bankTransaction
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create bank transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BankTransaction $bankTransaction): JsonResponse
    {
        $bankTransaction->load(['bankAccount', 'journalEntryLine.journalEntry']);

        return response()->json($bankTransaction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankTransaction $bankTransaction): JsonResponse
    {
        // Can only edit unreconciled transactions
        if ($bankTransaction->is_reconciled) {
            return response()->json([
                'message' => 'Cannot edit reconciled transactions'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $bankTransaction->update($request->all());

        return response()->json([
            'message' => 'Bank transaction updated successfully',
            'bank_transaction' => $bankTransaction->load(['bankAccount', 'journalEntryLine.journalEntry'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankTransaction $bankTransaction): JsonResponse
    {
        // Can only delete unreconciled transactions
        if ($bankTransaction->is_reconciled) {
            return response()->json([
                'message' => 'Cannot delete reconciled transactions'
            ], 422);
        }

        $bankTransaction->delete();

        return response()->json([
            'message' => 'Bank transaction deleted successfully'
        ]);
    }

    /**
     * Match bank transaction to journal entry line
     */
    public function match(Request $request, BankTransaction $bankTransaction): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'journal_entry_line_id' => 'required|exists:journal_entry_lines,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $bankTransaction->update([
            'journal_entry_line_id' => $request->journal_entry_line_id
        ]);

        return response()->json([
            'message' => 'Bank transaction matched successfully',
            'bank_transaction' => $bankTransaction->load(['bankAccount', 'journalEntryLine.journalEntry'])
        ]);
    }

    /**
     * Import bank transactions from CSV
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transactions' => 'required|array',
            'transactions.*.date' => 'required|date',
            'transactions.*.type' => 'required|in:debit,credit',
            'transactions.*.amount' => 'required|numeric|min:0.01',
            'transactions.*.description' => 'required|string',
            'transactions.*.reference' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $imported = 0;
            $skipped = 0;

            foreach ($request->transactions as $transactionData) {
                // Check for duplicates based on date, amount, and description
                $exists = BankTransaction::where('bank_account_id', $request->bank_account_id)
                                        ->where('transaction_date', $transactionData['date'])
                                        ->where('amount', $transactionData['amount'])
                                        ->where('description', $transactionData['description'])
                                        ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Calculate running balance for import
                $bankAccount = BankAccount::findOrFail($request->bank_account_id);
                $lastTransaction = BankTransaction::where('bank_account_id', $request->bank_account_id)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $currentBalance = $lastTransaction ? $lastTransaction->running_balance : $bankAccount->opening_balance;
                $newBalance = $transactionData['type'] === 'credit'
                    ? $currentBalance + $transactionData['amount']
                    : $currentBalance - $transactionData['amount'];

                BankTransaction::create([
                    'bank_account_id' => $request->bank_account_id,
                    'transaction_date' => $transactionData['date'],
                    'transaction_type' => $transactionData['type'],
                    'amount' => $transactionData['amount'],
                    'description' => $transactionData['description'],
                    'reference_number' => $transactionData['reference'] ?? null,
                    'running_balance' => $newBalance,
                    'status' => 'pending',
                ]);

                $imported++;
            }

            DB::commit();

            return response()->json([
                'message' => 'Bank transactions imported successfully',
                'imported' => $imported,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to import bank transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
