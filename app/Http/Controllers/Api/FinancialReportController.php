<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Sale;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.financial');
    }

    /**
     * Generate Profit & Loss Statement
     */
    public function profitLoss(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        // Get revenue accounts
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->with(['journalEntryLines' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                });
            }])
            ->get();

        // Get expense accounts
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->with(['journalEntryLines' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                });
            }])
            ->get();

        $revenue = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $amount = $account->journalEntryLines->sum('credit_amount') - $account->journalEntryLines->sum('debit_amount');
            if ($amount != 0) {
                $revenue[] = [
                    'account_name' => $account->account_name,
                    'account_code' => $account->account_code,
                    'amount' => $amount
                ];
                $totalRevenue += $amount;
            }
        }

        $expenses = [];
        $totalExpenses = 0;

        foreach ($expenseAccounts as $account) {
            $amount = $account->journalEntryLines->sum('debit_amount') - $account->journalEntryLines->sum('credit_amount');
            if ($amount != 0) {
                $expenses[] = [
                    'account_name' => $account->account_name,
                    'account_code' => $account->account_code,
                    'amount' => $amount
                ];
                $totalExpenses += $amount;
            }
        }

        $netIncome = $totalRevenue - $totalExpenses;

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome
        ]);
    }

    /**
     * Generate Balance Sheet
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $asOfDate = $request->get('as_of_date', Carbon::now());

        // Get asset accounts
        $currentAssets = $this->getAccountBalances('asset', 'current_asset', $asOfDate);
        $fixedAssets = $this->getAccountBalances('asset', 'fixed_asset', $asOfDate);
        $otherAssets = $this->getAccountBalances('asset', 'other_asset', $asOfDate);

        // Get liability accounts
        $currentLiabilities = $this->getAccountBalances('liability', 'current_liability', $asOfDate);
        $longTermLiabilities = $this->getAccountBalances('liability', 'long_term_liability', $asOfDate);

        // Get equity accounts
        $equity = $this->getAccountBalances('equity', 'equity', $asOfDate);

        $totalCurrentAssets = collect($currentAssets)->sum('amount');
        $totalFixedAssets = collect($fixedAssets)->sum('amount');
        $totalOtherAssets = collect($otherAssets)->sum('amount');
        $totalAssets = $totalCurrentAssets + $totalFixedAssets + $totalOtherAssets;

        $totalCurrentLiabilities = collect($currentLiabilities)->sum('amount');
        $totalLongTermLiabilities = collect($longTermLiabilities)->sum('amount');
        $totalEquity = collect($equity)->sum('amount');
        $totalLiabilitiesEquity = $totalCurrentLiabilities + $totalLongTermLiabilities + $totalEquity;

        return response()->json([
            'as_of_date' => $asOfDate,
            'current_assets' => $currentAssets,
            'fixed_assets' => $fixedAssets,
            'other_assets' => $otherAssets,
            'total_current_assets' => $totalCurrentAssets,
            'total_fixed_assets' => $totalFixedAssets,
            'total_other_assets' => $totalOtherAssets,
            'total_assets' => $totalAssets,
            'current_liabilities' => $currentLiabilities,
            'long_term_liabilities' => $longTermLiabilities,
            'total_current_liabilities' => $totalCurrentLiabilities,
            'total_long_term_liabilities' => $totalLongTermLiabilities,
            'equity' => $equity,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilitiesEquity
        ]);
    }

    /**
     * Generate Trial Balance
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $asOfDate = $request->get('as_of_date', Carbon::now());

        $accounts = ChartOfAccount::with(['journalEntryLines' => function ($query) use ($asOfDate) {
            $query->whereHas('journalEntry', function ($q) use ($asOfDate) {
                $q->where('status', 'posted')
                  ->where('entry_date', '<=', $asOfDate);
            });
        }])->get();

        $trialBalanceData = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $totalDebitsForAccount = $account->journalEntryLines->sum('debit_amount');
            $totalCreditsForAccount = $account->journalEntryLines->sum('credit_amount');
            
            $debitBalance = 0;
            $creditBalance = 0;

            // Calculate balance based on account type
            if (in_array($account->account_type, ['asset', 'expense'])) {
                $balance = $account->opening_balance + $totalDebitsForAccount - $totalCreditsForAccount;
                if ($balance > 0) {
                    $debitBalance = $balance;
                } else {
                    $creditBalance = abs($balance);
                }
            } else {
                $balance = $account->opening_balance + $totalCreditsForAccount - $totalDebitsForAccount;
                if ($balance > 0) {
                    $creditBalance = $balance;
                } else {
                    $debitBalance = abs($balance);
                }
            }

            if ($debitBalance != 0 || $creditBalance != 0) {
                $trialBalanceData[] = [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_type' => $account->account_type,
                    'debit_balance' => $debitBalance,
                    'credit_balance' => $creditBalance
                ];

                $totalDebits += $debitBalance;
                $totalCredits += $creditBalance;
            }
        }

        return response()->json([
            'as_of_date' => $asOfDate,
            'accounts' => $trialBalanceData,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
        ]);
    }

    /**
     * Generate Cash Flow Statement
     */
    public function cashFlow(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        // Get cash accounts
        $cashAccounts = ChartOfAccount::where('account_name', 'like', '%cash%')
            ->orWhere('account_name', 'like', '%bank%')
            ->get();

        $beginningCash = $cashAccounts->sum(function ($account) use ($startDate) {
            return $account->calculateBalance($startDate);
        });

        $endingCash = $cashAccounts->sum(function ($account) use ($endDate) {
            return $account->calculateBalance($endDate);
        });

        // Operating Activities (simplified)
        $operatingActivities = [
            ['description' => 'Net Income', 'amount' => $this->getNetIncome($startDate, $endDate)],
            ['description' => 'Depreciation', 'amount' => 0], // Would need to calculate from depreciation accounts
        ];

        // Investing Activities (simplified)
        $investingActivities = [
            ['description' => 'Equipment Purchases', 'amount' => 0], // Would calculate from fixed asset purchases
        ];

        // Financing Activities (simplified)
        $financingActivities = [
            ['description' => 'Owner Contributions', 'amount' => 0], // Would calculate from equity changes
        ];

        $netOperatingCash = collect($operatingActivities)->sum('amount');
        $netInvestingCash = collect($investingActivities)->sum('amount');
        $netFinancingCash = collect($financingActivities)->sum('amount');
        $netChangeInCash = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'net_operating_cash' => $netOperatingCash,
            'net_investing_cash' => $netInvestingCash,
            'net_financing_cash' => $netFinancingCash,
            'net_change_in_cash' => $netChangeInCash,
            'beginning_cash' => $beginningCash,
            'ending_cash' => $endingCash
        ]);
    }

    /**
     * Helper method to get account balances by type and subtype
     */
    private function getAccountBalances($type, $subtype, $asOfDate)
    {
        $accounts = ChartOfAccount::where('account_type', $type)
            ->where('account_subtype', $subtype)
            ->with(['journalEntryLines' => function ($query) use ($asOfDate) {
                $query->whereHas('journalEntry', function ($q) use ($asOfDate) {
                    $q->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                });
            }])
            ->get();

        $balances = [];
        foreach ($accounts as $account) {
            $balance = $account->calculateBalance($asOfDate);
            if ($balance != 0) {
                $balances[] = [
                    'account_name' => $account->account_name,
                    'account_code' => $account->account_code,
                    'amount' => abs($balance)
                ];
            }
        }

        return $balances;
    }

    /**
     * Helper method to get net income for a period
     */
    private function getNetIncome($startDate, $endDate)
    {
        $revenue = ChartOfAccount::where('account_type', 'revenue')
            ->get()
            ->sum(function ($account) use ($startDate, $endDate) {
                return $account->journalEntryLines()
                    ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                        $q->where('status', 'posted')
                          ->whereBetween('entry_date', [$startDate, $endDate]);
                    })
                    ->sum('credit_amount') - 
                    $account->journalEntryLines()
                    ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                        $q->where('status', 'posted')
                          ->whereBetween('entry_date', [$startDate, $endDate]);
                    })
                    ->sum('debit_amount');
            });

        $expenses = ChartOfAccount::where('account_type', 'expense')
            ->get()
            ->sum(function ($account) use ($startDate, $endDate) {
                return $account->journalEntryLines()
                    ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                        $q->where('status', 'posted')
                          ->whereBetween('entry_date', [$startDate, $endDate]);
                    })
                    ->sum('debit_amount') - 
                    $account->journalEntryLines()
                    ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                        $q->where('status', 'posted')
                          ->whereBetween('entry_date', [$startDate, $endDate]);
                    })
                    ->sum('credit_amount');
            });

        return $revenue - $expenses;
    }
}
