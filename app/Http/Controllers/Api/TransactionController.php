<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:accounting.view')->only(['index']);
    }

    /**
     * Display a listing of bank account transactions
     */
    public function index(Request $request): JsonResponse
    {
        $query = JournalEntryLine::with(['journalEntry', 'account'])
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted');
            })
            ->whereHas('account', function ($q) {
                $q->where('account_type', 'asset')
                  ->whereIn('account_name', ['Cash', 'Bank Account', 'Petty Cash']);
            });

        // Filter by account
        if ($request->has('account_id') && $request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('entry_date', '>=', $request->date_from);
            });
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('entry_date', '<=', $request->date_to);
            });
        }

        // Filter by transaction type
        if ($request->has('transaction_type') && $request->transaction_type) {
            if ($request->transaction_type === 'debit') {
                $query->where('debit_amount', '>', 0);
            } elseif ($request->transaction_type === 'credit') {
                $query->where('credit_amount', '>', 0);
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('journalEntry', function ($subQ) use ($searchTerm) {
                    $subQ->where('reference', 'like', "%{$searchTerm}%")
                         ->orWhere('description', 'like', "%{$searchTerm}%");
                })
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->orWhereHas('account', function ($subQ) use ($searchTerm) {
                    $subQ->where('account_name', 'like', "%{$searchTerm}%")
                         ->orWhere('account_code', 'like', "%{$searchTerm}%");
                });
            });
        }

        // Sorting
        $sortField = $request->get('sort_field', 'entry_date');
        $sortOrder = $request->get('sort_order', 'desc');

        // Join with journal_entries for sorting and selection
        $query->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
              ->select('journal_entry_lines.*');

        // Apply sorting based on the field
        switch ($sortField) {
            case 'id':
                $query->orderBy('journal_entry_lines.id', $sortOrder);
                break;
            case 'entry_date':
                $query->orderBy('journal_entries.entry_date', $sortOrder)
                      ->orderBy('journal_entries.id', $sortOrder);
                break;
            case 'reference':
                $query->orderBy('journal_entries.reference', $sortOrder);
                break;
            case 'account_name':
                $query->join('chart_of_accounts as accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
                      ->orderBy('accounts.account_name', $sortOrder);
                break;
            case 'debit_amount':
                $query->orderBy('journal_entry_lines.debit_amount', $sortOrder);
                break;
            case 'credit_amount':
                $query->orderBy('journal_entry_lines.credit_amount', $sortOrder);
                break;
            default:
                $query->orderBy('journal_entries.entry_date', 'desc')
                      ->orderBy('journal_entries.id', 'desc');
        }

        // Handle export request (get all records) or paginate
        if ($request->get('export') === 'true' || $request->get('per_page') === 'all') {
            $transactions = $query->get();

            // For export, return simplified structure with running balance
            $runningBalance = 0;
            $processedTransactions = [];

            foreach ($transactions as $transaction) {
                if ($transaction->debit_amount > 0) {
                    $runningBalance -= $transaction->debit_amount;
                } else {
                    $runningBalance += $transaction->credit_amount;
                }

                $processedTransactions[] = [
                    'id' => $transaction->id,
                    'entry_date' => $transaction->journalEntry->entry_date,
                    'reference' => $transaction->journalEntry->reference,
                    'description' => $transaction->description ?: $transaction->journalEntry->description,
                    'account_name' => $transaction->account->account_name,
                    'debit_amount' => $transaction->debit_amount,
                    'credit_amount' => $transaction->credit_amount,
                    'running_balance' => $runningBalance
                ];
            }

            return response()->json([
                'data' => $processedTransactions
            ]);
        }

        $perPage = $request->get('per_page', 50);
        $transactions = $query->paginate($perPage);

        // Calculate running balance for each transaction
        $runningBalance = 0;
        $accountId = $request->account_id;
        
        if ($accountId) {
            // Get the account to determine balance calculation method
            $account = Account::find($accountId);
            
            // Get all transactions up to the first transaction in current page
            $firstTransaction = $transactions->items()[0] ?? null;
            if ($firstTransaction) {
                $previousTransactions = JournalEntryLine::where('account_id', $accountId)
                    ->whereHas('journalEntry', function ($q) use ($firstTransaction) {
                        $q->where('status', 'posted')
                          ->where(function ($subQ) use ($firstTransaction) {
                              $subQ->where('entry_date', '<', $firstTransaction->journalEntry->entry_date)
                                   ->orWhere(function ($dateQ) use ($firstTransaction) {
                                       $dateQ->where('entry_date', '=', $firstTransaction->journalEntry->entry_date)
                                            ->where('id', '<', $firstTransaction->journal_entry_id);
                                   });
                          });
                    })
                    ->get();

                // Calculate starting balance
                $runningBalance = $account->opening_balance ?? 0;
                foreach ($previousTransactions as $prevTrans) {
                    if (in_array($account->account_type, ['asset', 'expense'])) {
                        $runningBalance += $prevTrans->debit_amount - $prevTrans->credit_amount;
                    } else {
                        $runningBalance += $prevTrans->credit_amount - $prevTrans->debit_amount;
                    }
                }
            }
        }

        // Add running balance to each transaction
        $transactionsWithBalance = $transactions->getCollection()->map(function ($transaction) use (&$runningBalance, $accountId) {
            if ($accountId) {
                $account = $transaction->account;
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $runningBalance += $transaction->debit_amount - $transaction->credit_amount;
                } else {
                    $runningBalance += $transaction->credit_amount - $transaction->debit_amount;
                }
            }

            return [
                'id' => $transaction->id,
                'entry_date' => $transaction->journalEntry->entry_date,
                'reference' => $transaction->journalEntry->reference,
                'description' => $transaction->description,
                'account_name' => $transaction->account->account_name,
                'account_code' => $transaction->account->account_code,
                'debit_amount' => (float) $transaction->debit_amount,
                'credit_amount' => (float) $transaction->credit_amount,
                'running_balance' => $accountId ? (float) $runningBalance : null,
                'entry_type' => $transaction->journalEntry->entry_type,
                'entry_number' => $transaction->journalEntry->entry_number,
            ];
        });

        $transactions->setCollection($transactionsWithBalance);

        return response()->json($transactions);
    }

    /**
     * Get transaction summary for a specific account
     */
    public function summary(Request $request): JsonResponse
    {
        $accountId = $request->get('account_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if (!$accountId) {
            return response()->json([
                'message' => 'Account ID is required'
            ], 422);
        }

        $account = Account::find($accountId);
        if (!$account) {
            return response()->json([
                'message' => 'Account not found'
            ], 404);
        }

        $query = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'posted');
                if ($dateFrom) {
                    $q->whereDate('entry_date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate('entry_date', '<=', $dateTo);
                }
            });

        $totalDebits = $query->sum('debit_amount');
        $totalCredits = $query->sum('credit_amount');
        $transactionCount = $query->count();

        // Calculate net change
        $netChange = 0;
        if (in_array($account->account_type, ['asset', 'expense'])) {
            $netChange = $totalDebits - $totalCredits;
        } else {
            $netChange = $totalCredits - $totalDebits;
        }

        return response()->json([
            'account' => [
                'id' => $account->id,
                'name' => $account->account_name,
                'code' => $account->account_code,
                'type' => $account->account_type,
                'current_balance' => (float) $account->current_balance,
            ],
            'summary' => [
                'total_debits' => (float) $totalDebits,
                'total_credits' => (float) $totalCredits,
                'net_change' => (float) $netChange,
                'transaction_count' => $transactionCount,
            ],
            'period' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        ]);
    }

    public function exportPDF(Request $request)
    {
        // Build the same query as index method
        $query = JournalEntryLine::with(['journalEntry', 'account'])
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted');
            })
            ->whereHas('account', function ($q) {
                $q->where('account_type', 'asset')
                  ->whereIn('account_name', ['Cash', 'Bank Account', 'Petty Cash']);
            });

        // Apply filters
        if ($request->has('account_id') && $request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('entry_date', '>=', $request->date_from);
            });
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('entry_date', '<=', $request->date_to);
            });
        }

        if ($request->has('transaction_type') && $request->transaction_type) {
            if ($request->transaction_type === 'debit') {
                $query->where('debit_amount', '>', 0);
            } elseif ($request->transaction_type === 'credit') {
                $query->where('credit_amount', '>', 0);
            }
        }

        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('journalEntry', function ($subQ) use ($searchTerm) {
                    $subQ->where('reference', 'like', "%{$searchTerm}%")
                         ->orWhere('description', 'like', "%{$searchTerm}%");
                })
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->orWhereHas('account', function ($subQ) use ($searchTerm) {
                    $subQ->where('account_name', 'like', "%{$searchTerm}%")
                         ->orWhere('account_code', 'like', "%{$searchTerm}%");
                });
            });
        }

        // Order by date
        $query->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
              ->orderBy('journal_entries.entry_date', 'desc')
              ->orderBy('journal_entries.id', 'desc')
              ->select('journal_entry_lines.*');

        // Get all transactions
        $transactions = $query->get();

        // Calculate running balance and format data
        $runningBalance = 0;
        $processedTransactions = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->debit_amount > 0) {
                $runningBalance -= $transaction->debit_amount;
                $totalDebits += $transaction->debit_amount;
            } else {
                $runningBalance += $transaction->credit_amount;
                $totalCredits += $transaction->credit_amount;
            }

            $processedTransactions[] = [
                'id' => $transaction->id,
                'entry_date' => $transaction->journalEntry->entry_date,
                'reference' => $transaction->journalEntry->reference,
                'description' => $transaction->description ?: $transaction->journalEntry->description,
                'account_name' => $transaction->account->account_name,
                'debit_amount' => $transaction->debit_amount,
                'credit_amount' => $transaction->credit_amount,
                'running_balance' => $runningBalance
            ];
        }

        // Prepare filter information for display
        $filters = [];
        if ($request->account_id) {
            $account = Account::find($request->account_id);
            $filters['account_name'] = $account ? $account->account_name : '';
        }
        $filters['date_from'] = $request->date_from;
        $filters['date_to'] = $request->date_to;
        $filters['transaction_type'] = $request->transaction_type;
        $filters['search'] = $request->search;

        // Summary data
        $summary = [
            'total_count' => count($processedTransactions),
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.transactions', [
            'transactions' => $processedTransactions,
            'filters' => $filters,
            'summary' => $summary
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'transactions_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
