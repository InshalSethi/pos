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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BankTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show', 'exportPDF']);
        $this->middleware('permission:accounting.create')->only(['store', 'import']);
        $this->middleware('permission:accounting.edit')->only(['update', 'match']);
        $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BankTransaction::with([
            'bankAccount', 
            'journalEntry', 
            'payment:id,bank_transaction_id,payment_type', 
            'paymentReceipt:id,bank_transaction_id,receipt_type'
        ]);

        // Filter by bank account(s) (supports single ID or array/comma-separated IDs)
        $accountIds = $request->input('bank_account_ids') ?? $request->input('bank_account_id');
        if (!empty($accountIds)) {
            if (is_string($accountIds)) {
                $accountIds = explode(',', $accountIds);
            }
            $accountIds = array_filter((array) $accountIds);
            if (!empty($accountIds)) {
                $query->whereIn('bank_account_id', $accountIds);
            }
        }

        // Filter by reconciliation status
        if ($request->has('reconciled')) {
            if ($request->boolean('reconciled')) {
                $query->where('status', 'reconciled');
            } else {
                $query->whereIn('status', ['pending', 'cleared']);
            }
        }

        // Filter by transaction type(s) / categories (supports single type or array/comma-separated types)
        $rawTypes = $request->input('types') ?? $request->input('transaction_types') ?? $request->input('type') ?? $request->input('transaction_type');
        if (!empty($rawTypes)) {
            if (is_string($rawTypes)) {
                $rawTypes = explode(',', $rawTypes);
            }
            $types = array_filter((array) $rawTypes, fn($t) => !empty($t) && $t !== 'all');

            if (!empty($types)) {
                $query->where(function ($parentQuery) use ($types) {
                    foreach ($types as $type) {
                        $parentQuery->orWhere(function ($q) use ($type) {
                            if ($type === 'credit' || $type === 'income' || $type === 'all_income') {
                                $q->where('transaction_type', 'credit');
                            } elseif ($type === 'debit' || $type === 'expense' || $type === 'all_expense') {
                                $q->where('transaction_type', 'debit');
                            } elseif ($type === 'sale_invoice' || $type === 'sale') {
                                $q->where(function ($sub) {
                                    $sub->where('description', 'like', '%sale%')
                                        ->orWhere('description', 'like', '%invoice%')
                                        ->orWhere('reference_number', 'like', 'INV%')
                                        ->orWhere('reference_number', 'like', 'POS%');
                                });
                            } elseif ($type === 'purchase_order' || $type === 'purchase') {
                                $q->where(function ($sub) {
                                    $sub->where('description', 'like', '%purchase%')
                                        ->orWhere('description', 'like', '%bill%')
                                        ->orWhere('reference_number', 'like', 'PO%')
                                        ->orWhere('reference_number', 'like', 'BILL%');
                                });
                            } elseif ($type === 'sale_return') {
                                $q->where(function ($sub) {
                                    $sub->where('description', 'like', '%sale return%')
                                        ->orWhere('description', 'like', '%return%')
                                        ->orWhere('reference_number', 'like', 'SR%')
                                        ->orWhere('reference_number', 'like', 'RET%');
                                });
                            } elseif ($type === 'purchase_return') {
                                $q->where(function ($sub) {
                                    $sub->where('description', 'like', '%purchase return%')
                                        ->orWhere('reference_number', 'like', 'PR%')
                                        ->orWhere('reference_number', 'like', 'DN%');
                                });
                            } else {
                                $cleanType = str_replace(['_', '-'], ' ', $type);
                                $q->where(function ($sub) use ($type, $cleanType) {
                                    $sub->whereHas('paymentReceipt', fn($r) => $r->where('receipt_type', $type))
                                        ->orWhereHas('payment', fn($p) => $p->where('payment_type', $type))
                                        ->orWhere('description', 'like', "%{$type}%")
                                        ->orWhere('description', 'like', "%{$cleanType}%");
                                });
                            }
                        });
                    }
                });
            }
        }

        // Filter by specific amount
        if ($request->filled('amount') && is_numeric($request->amount)) {
            $amountVal = (float) $request->amount;
            $query->where('amount', $amountVal);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Search by description, reference, or bank account name
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('bankAccount', function ($ba) use ($search) {
                      $ba->where('account_name', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $allowedSorts = ['transaction_date', 'reference_number', 'description', 'transaction_type', 'amount', 'running_balance', 'created_at'];
        $sortField = $request->get('sort_by', 'transaction_date');
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'transaction_date';
        }
        $sortDirection = strtolower($request->get('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $transactions = $query->orderBy($sortField, $sortDirection)
                              ->orderBy('id', 'desc')
                              ->paginate($request->get('per_page', 15));

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

            $bankTransaction->load(['bankAccount', 'journalEntry']);

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
        $bankTransaction->load(['bankAccount', 'journalEntry']);

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
            'bank_transaction' => $bankTransaction->load(['bankAccount', 'journalEntry'])
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
            'bank_transaction' => $bankTransaction->load(['bankAccount', 'journalEntry'])
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

    /**
     * Export banking transactions ledger as PDF.
     */
    public function exportPDF(Request $request)
    {
        $query = BankTransaction::with([
            'bankAccount',
            'payment:id,bank_transaction_id,payment_type',
            'paymentReceipt:id,bank_transaction_id,receipt_type'
        ]);

        // Filter by bank account(s)
        $accountIds = $request->input('bank_account_ids') ?? $request->input('bank_account_id');
        if (!empty($accountIds)) {
            if (is_string($accountIds)) {
                $accountIds = explode(',', $accountIds);
            }
            $accountIds = array_filter((array) $accountIds);
            if (!empty($accountIds)) {
                $query->whereIn('bank_account_id', $accountIds);
            }
        }

        // Filter by transaction type(s)
        $rawTypes = $request->input('types') ?? $request->input('transaction_types') ?? $request->input('type');
        if (!empty($rawTypes)) {
            if (is_string($rawTypes)) {
                $rawTypes = explode(',', $rawTypes);
            }
            $types = array_filter((array) $rawTypes, fn($t) => !empty($t) && $t !== 'all');

            if (!empty($types)) {
                $query->where(function ($parentQuery) use ($types) {
                    foreach ($types as $type) {
                        $parentQuery->orWhere(function ($q) use ($type) {
                            if ($type === 'credit' || $type === 'income' || $type === 'all_income') {
                                $q->where('transaction_type', 'credit');
                            } elseif ($type === 'debit' || $type === 'expense' || $type === 'all_expense') {
                                $q->where('transaction_type', 'debit');
                            } elseif ($type === 'sale_invoice' || $type === 'sale') {
                                $q->where(function ($sub) {
                                    $sub->where('description', 'like', '%sale%')
                                        ->orWhere('description', 'like', '%invoice%')
                                        ->orWhere('reference_number', 'like', 'INV%')
                                        ->orWhere('reference_number', 'like', 'POS%');
                                });
                            } elseif ($type === 'purchase_order' || $type === 'purchase') {
                                $q->where(function ($sub) {
                                    $sub->where('description', 'like', '%purchase%')
                                        ->orWhere('description', 'like', '%bill%')
                                        ->orWhere('reference_number', 'like', 'PO%')
                                        ->orWhere('reference_number', 'like', 'BILL%');
                                });
                            } else {
                                $cleanType = str_replace(['_', '-'], ' ', $type);
                                $q->where(function ($sub) use ($type, $cleanType) {
                                    $sub->whereHas('paymentReceipt', fn($r) => $r->where('receipt_type', $type))
                                        ->orWhereHas('payment', fn($p) => $p->where('payment_type', $type))
                                        ->orWhere('description', 'like', "%{$type}%")
                                        ->orWhere('description', 'like', "%{$cleanType}%");
                                });
                            }
                        });
                    }
                });
            }
        }

        // Filter by specific amount
        if ($request->filled('amount') && is_numeric($request->amount)) {
            $query->where('amount', (float) $request->amount);
        }

        // Filter by date range
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $startDate !== 'undefined' && $startDate !== 'null') {
            $query->whereDate('transaction_date', '>=', $startDate);
        } else {
            $startDate = null;
        }

        if ($endDate && $endDate !== 'undefined' && $endDate !== 'null') {
            $query->whereDate('transaction_date', '<=', $endDate);
        } else {
            $endDate = null;
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('bankAccount', function ($ba) use ($search) {
                      $ba->where('account_name', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                              ->orderBy('id', 'desc')
                              ->get();

        // Summary calculations
        $totalInflow = $transactions->filter(fn($tx) => $tx->transaction_type === 'credit' || $tx->transaction_type === 'income')->sum('amount');
        $totalOutflow = $transactions->filter(fn($tx) => $tx->transaction_type === 'debit' || $tx->transaction_type === 'expense')->sum('amount');
        $netBalance = $totalInflow - $totalOutflow;

        // Date range label
        if ($startDate && $endDate) {
            $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y');
        } elseif ($startDate) {
            $dateRangeLabel = 'From ' . Carbon::parse($startDate)->format('M d, Y');
        } elseif ($endDate) {
            $dateRangeLabel = 'Until ' . Carbon::parse($endDate)->format('M d, Y');
        } else {
            $dateRangeLabel = 'All Time';
        }

        // Resolve company name & currency
        $company = \App\Models\Company::find(session('active_company_id') ?? auth()->user()->company_id ?? 1);
        $companyName = $company->company_name ?? config('app.name', 'POS System');
        $currencyCode = $company->base_currency ?? 'PKR';

        $currencySymbols = [
            'PKR' => 'Rs', 'USD' => '$', 'EUR' => '€', 'GBP' => '£',
            'AED' => 'AED', 'SAR' => 'SAR', 'CAD' => 'CA$', 'AUD' => 'A$', 'INR' => '₹',
        ];
        $currencySymbol = $currencySymbols[strtoupper($currencyCode)] ?? $currencyCode;

        // Build active filters labels for PDF display
        $activeFilters = [];
        if ($request->filled('search')) {
            $activeFilters[] = 'Search: "' . $request->input('search') . '"';
        }
        if (!empty($accountIds)) {
            $accountNames = BankAccount::whereIn('id', (array) $accountIds)->pluck('account_name')->toArray();
            $activeFilters[] = 'Accounts: ' . implode(', ', $accountNames);
        }
        if (!empty($types)) {
            $activeFilters[] = 'Types: ' . implode(', ', array_map(fn($t) => ucwords(str_replace('_', ' ', $t)), $types));
        }
        if ($request->filled('amount')) {
            $activeFilters[] = 'Amount: ' . $currencySymbol . ' ' . number_format((float) $request->amount, 2);
        }

        $pdf = Pdf::loadView('pdf.banking_transactions', [
            'transactions' => $transactions,
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
            'netBalance' => $netBalance,
            'dateRangeLabel' => $dateRangeLabel,
            'companyName' => $companyName,
            'currencyCode' => $currencyCode,
            'currencySymbol' => $currencySymbol,
            'activeFilters' => $activeFilters,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $fileName = 'banking-transactions-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }
}
