<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
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
     * Generate Profit & Loss Statement (Income Statement)
     */
    public function profitLoss(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Fetch all revenue and expense accounts with posted lines in period
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->with(['journalEntryLines' => function ($query) use ($startDateTime, $endDateTime) {
                $query->whereHas('journalEntry', function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDateTime, $endDateTime]);
                });
            }])
            ->get();

        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->with(['journalEntryLines' => function ($query) use ($startDateTime, $endDateTime) {
                $query->whereHas('journalEntry', function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDateTime, $endDateTime]);
                });
            }])
            ->get();

        $grossRevenue = [];
        $totalGrossRevenue = 0;

        $discountsReturns = [];
        $totalDiscountsReturns = 0;

        $otherIncome = [];
        $totalOtherIncome = 0;

        foreach ($revenueAccounts as $account) {
            $creditSum = $account->journalEntryLines->sum('credit_amount');
            $debitSum = $account->journalEntryLines->sum('debit_amount');
            $net = $creditSum - $debitSum;

            if ($net == 0) continue;

            $item = [
                'account_id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_subtype' => $account->account_subtype,
                'amount' => abs($net)
            ];

            if ($account->account_subtype === 'other_revenue' || in_array($account->account_code, ['4900', '4910', '4920', '4930', '4940', '4990'])) {
                $otherIncome[] = $item;
                $totalOtherIncome += $net;
            } elseif ($net < 0 || in_array($account->account_code, ['4050', '4060'])) {
                // Contra Revenue (Discounts / Returns)
                $discountsReturns[] = $item;
                $totalDiscountsReturns += abs($net);
            } else {
                $grossRevenue[] = $item;
                $totalGrossRevenue += $net;
            }
        }

        $netRevenue = $totalGrossRevenue - $totalDiscountsReturns;

        $cogs = [];
        $totalCogs = 0;

        $operatingExpenses = [];
        $totalOperatingExpenses = 0;

        $otherExpenses = [];
        $totalOtherExpenses = 0;

        foreach ($expenseAccounts as $account) {
            $debitSum = $account->journalEntryLines->sum('debit_amount');
            $creditSum = $account->journalEntryLines->sum('credit_amount');
            $net = $debitSum - $creditSum;

            if ($net == 0) continue;

            $item = [
                'account_id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_subtype' => $account->account_subtype,
                'amount' => $net
            ];

            if ($account->account_subtype === 'cost_of_goods_sold' || $account->account_subtype === 'cost_of_sales' || str_starts_with($account->account_code, '5')) {
                $cogs[] = $item;
                $totalCogs += $net;
            } elseif ($account->account_subtype === 'other_expense' || str_starts_with($account->account_code, '7')) {
                $otherExpenses[] = $item;
                $totalOtherExpenses += $net;
            } else {
                $operatingExpenses[] = $item;
                $totalOperatingExpenses += $net;
            }
        }

        $grossProfit = $netRevenue - $totalCogs;
        $grossProfitMargin = $netRevenue != 0 ? round(($grossProfit / $netRevenue) * 100, 2) : 0;

        $operatingProfit = $grossProfit - $totalOperatingExpenses;
        $operatingProfitMargin = $netRevenue != 0 ? round(($operatingProfit / $netRevenue) * 100, 2) : 0;

        $netNonOperating = $totalOtherIncome - $totalOtherExpenses;
        $netIncome = $operatingProfit + $netNonOperating;
        $netIncomeMargin = $netRevenue != 0 ? round(($netIncome / $netRevenue) * 100, 2) : 0;

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'gross_revenue' => $grossRevenue,
            'total_gross_revenue' => round($totalGrossRevenue, 2),
            'discounts_returns' => $discountsReturns,
            'total_discounts_returns' => round($totalDiscountsReturns, 2),
            'net_revenue' => round($netRevenue, 2),
            'cogs' => $cogs,
            'total_cogs' => round($totalCogs, 2),
            'gross_profit' => round($grossProfit, 2),
            'gross_profit_margin' => $grossProfitMargin,
            'operating_expenses' => $operatingExpenses,
            'total_operating_expenses' => round($totalOperatingExpenses, 2),
            'operating_profit' => round($operatingProfit, 2),
            'operating_profit_margin' => $operatingProfitMargin,
            'other_income' => $otherIncome,
            'total_other_income' => round($totalOtherIncome, 2),
            'other_expenses' => $otherExpenses,
            'total_other_expenses' => round($totalOtherExpenses, 2),
            'net_non_operating' => round($netNonOperating, 2),
            'net_income' => round($netIncome, 2),
            'net_income_margin' => $netIncomeMargin
        ]);
    }

    /**
     * Generate Balance Sheet (Statement of Financial Position)
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $asOfDate = $request->get('as_of_date', Carbon::now()->toDateString());
        $asOfDateTime = Carbon::parse($asOfDate)->endOfDay();

        // 1. Current Assets (subtypes: current_asset, cash_and_bank or code 10xx)
        $currentAssets = $this->getAccountBalancesForTypes(['asset'], ['current_asset', 'cash_and_bank'], $asOfDateTime, ['10']);
        // 2. Fixed Assets (subtypes: fixed_asset or code 15xx)
        $fixedAssets = $this->getAccountBalancesForTypes(['asset'], ['fixed_asset'], $asOfDateTime, ['15']);
        // 3. Other Assets
        $otherAssets = $this->getAccountBalancesForTypes(['asset'], ['other_asset'], $asOfDateTime, ['18', '19']);

        // 4. Current Liabilities (subtypes: current_liability or code 20xx)
        $currentLiabilities = $this->getAccountBalancesForTypes(['liability'], ['current_liability'], $asOfDateTime, ['20']);
        // 5. Long-term Liabilities (subtypes: long_term_liability or code 25xx)
        $longTermLiabilities = $this->getAccountBalancesForTypes(['liability'], ['long_term_liability'], $asOfDateTime, ['25']);

        // 6. Equity Accounts (subtypes: equity, owner_equity or code 30xx)
        $equityAccounts = $this->getAccountBalancesForTypes(['equity'], ['equity', 'owner_equity'], $asOfDateTime, ['30']);

        $totalCurrentAssets = collect($currentAssets)->sum('amount');
        $totalFixedAssets = collect($fixedAssets)->sum('amount');
        $totalOtherAssets = collect($otherAssets)->sum('amount');
        $totalAssets = $totalCurrentAssets + $totalFixedAssets + $totalOtherAssets;

        $totalCurrentLiabilities = collect($currentLiabilities)->sum('amount');
        $totalLongTermLiabilities = collect($longTermLiabilities)->sum('amount');
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;

        $baseEquity = collect($equityAccounts)->sum('amount');

        // Calculate Current Period Net Income up to $asOfDate
        $currentPeriodNetIncome = $this->getNetIncomeUpToDate($asOfDateTime);

        $totalEquity = $baseEquity + $currentPeriodNetIncome;
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;

        $isBalanced = abs($totalAssets - $totalLiabilitiesEquity) < 0.01;
        $variance = round($totalAssets - $totalLiabilitiesEquity, 2);

        return response()->json([
            'as_of_date' => $asOfDate,
            'current_assets' => $currentAssets,
            'fixed_assets' => $fixedAssets,
            'other_assets' => $otherAssets,
            'total_current_assets' => round($totalCurrentAssets, 2),
            'total_fixed_assets' => round($totalFixedAssets, 2),
            'total_other_assets' => round($totalOtherAssets, 2),
            'total_assets' => round($totalAssets, 2),

            'current_liabilities' => $currentLiabilities,
            'long_term_liabilities' => $longTermLiabilities,
            'total_current_liabilities' => round($totalCurrentLiabilities, 2),
            'total_long_term_liabilities' => round($totalLongTermLiabilities, 2),
            'total_liabilities' => round($totalLiabilities, 2),

            'equity_accounts' => $equityAccounts,
            'base_equity' => round($baseEquity, 2),
            'current_period_net_income' => round($currentPeriodNetIncome, 2),
            'total_equity' => round($totalEquity, 2),
            'total_liabilities_equity' => round($totalLiabilitiesEquity, 2),

            'is_balanced' => $isBalanced,
            'variance' => $variance
        ]);
    }

    /**
     * Generate Trial Balance
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $asOfDate = $request->get('as_of_date', Carbon::now()->toDateString());
        $asOfDateTime = Carbon::parse($asOfDate)->endOfDay();

        $accounts = ChartOfAccount::with(['journalEntryLines' => function ($query) use ($asOfDateTime) {
            $query->whereHas('journalEntry', function ($q) use ($asOfDateTime) {
                $q->where('status', 'posted')
                  ->where('entry_date', '<=', $asOfDateTime);
            });
        }])
        ->orderBy('account_code')
        ->get();

        $trialBalanceData = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $totalDebitsForAccount = $account->journalEntryLines->sum('debit_amount');
            $totalCreditsForAccount = $account->journalEntryLines->sum('credit_amount');

            $debitBalance = 0;
            $creditBalance = 0;

            if ($account->isDebitAccount()) {
                $balance = $account->opening_balance + $totalDebitsForAccount - $totalCreditsForAccount;
                if ($balance >= 0) {
                    $debitBalance = $balance;
                } else {
                    $creditBalance = abs($balance);
                }
            } else {
                $balance = $account->opening_balance + $totalCreditsForAccount - $totalDebitsForAccount;
                if ($balance >= 0) {
                    $creditBalance = $balance;
                } else {
                    $debitBalance = abs($balance);
                }
            }

            if (round($debitBalance, 2) != 0 || round($creditBalance, 2) != 0) {
                $trialBalanceData[] = [
                    'account_id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_type' => $account->account_type,
                    'account_subtype' => $account->account_subtype,
                    'debit_balance' => round($debitBalance, 2),
                    'credit_balance' => round($creditBalance, 2)
                ];

                $totalDebits += $debitBalance;
                $totalCredits += $creditBalance;
            }
        }

        $totalDebits = round($totalDebits, 2);
        $totalCredits = round($totalCredits, 2);
        $isBalanced = abs($totalDebits - $totalCredits) < 0.01;
        $variance = round($totalDebits - $totalCredits, 2);

        return response()->json([
            'as_of_date' => $asOfDate,
            'accounts' => $trialBalanceData,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => $isBalanced,
            'variance' => $variance
        ]);
    }

    /**
     * Generate Cash Flow Statement (IAS 7 Indirect Method)
     */
    public function cashFlow(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();
        $priorStartDateTime = Carbon::parse($startDate)->subDay()->endOfDay();

        // 1. Net Income for period
        $netIncomeData = json_decode($this->profitLoss($request)->getContent(), true);
        $netIncome = $netIncomeData['net_income'] ?? 0;

        // 2. Non-cash adjustments (Depreciation & Amortization)
        $depreciationAccounts = ChartOfAccount::where(function ($q) {
            $q->where('account_name', 'like', '%depreciation%')
              ->orWhere('account_code', 'like', '6100%')
              ->orWhere('account_code', 'like', '6110%');
        })->get();

        $depreciation = 0;
        foreach ($depreciationAccounts as $account) {
            $lines = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('status', 'posted')->whereBetween('entry_date', [$startDateTime, $endDateTime]);
                })->get();
            $depreciation += ($lines->sum('debit_amount') - $lines->sum('credit_amount'));
        }

        // 3. Working Capital Adjustments (Changes in AR, Inventory, AP, Accruals)
        $arStart = $this->getAccountGroupBalance(['1030'], $priorStartDateTime);
        $arEnd = $this->getAccountGroupBalance(['1030'], $endDateTime);
        $changeInAR = -($arEnd - $arStart); // Increase in AR is outflow

        $invStart = $this->getAccountGroupBalance(['1040', '1041', '1042'], $priorStartDateTime);
        $invEnd = $this->getAccountGroupBalance(['1040', '1041', '1042'], $endDateTime);
        $changeInInventory = -($invEnd - $invStart); // Increase in Inventory is outflow

        $apStart = $this->getAccountGroupBalance(['2010'], $priorStartDateTime);
        $apEnd = $this->getAccountGroupBalance(['2010'], $endDateTime);
        $changeInAP = ($apEnd - $apStart); // Increase in AP is inflow

        $accrualStart = $this->getAccountGroupBalance(['2020', '2025', '2030', '2035'], $priorStartDateTime);
        $accrualEnd = $this->getAccountGroupBalance(['2020', '2025', '2030', '2035'], $endDateTime);
        $changeInAccruals = ($accrualEnd - $accrualStart);

        $operatingActivities = [
            ['description' => 'Net Income / (Loss)', 'amount' => round($netIncome, 2)],
            ['description' => 'Depreciation & Amortization (Non-Cash)', 'amount' => round($depreciation, 2)],
            ['description' => 'Change in Accounts Receivable', 'amount' => round($changeInAR, 2)],
            ['description' => 'Change in Inventory', 'amount' => round($changeInInventory, 2)],
            ['description' => 'Change in Accounts Payable', 'amount' => round($changeInAP, 2)],
            ['description' => 'Change in Taxes & Accrued Liabilities', 'amount' => round($changeInAccruals, 2)],
        ];

        $netOperatingCash = collect($operatingActivities)->sum('amount');

        // 4. Investing Activities (Purchase/Sale of Fixed Assets)
        $faStart = $this->getAccountGroupBalance(['1510', '1520', '1530', '1540', '1550', '1560', '1590'], $priorStartDateTime);
        $faEnd = $this->getAccountGroupBalance(['1510', '1520', '1530', '1540', '1550', '1560', '1590'], $endDateTime);
        $capex = -($faEnd - $faStart);

        $investingActivities = [
            ['description' => 'Capital Expenditures / Equipment Purchases', 'amount' => round($capex, 2)],
        ];

        $netInvestingCash = collect($investingActivities)->sum('amount');

        // 5. Financing Activities (Capital, Drawings, Loans)
        $capStart = $this->getAccountGroupBalance(['3010'], $priorStartDateTime);
        $capEnd = $this->getAccountGroupBalance(['3010'], $endDateTime);
        $ownerContributions = ($capEnd - $capStart);

        $drawStart = $this->getAccountGroupBalance(['3040'], $priorStartDateTime);
        $drawEnd = $this->getAccountGroupBalance(['3040'], $endDateTime);
        $ownerDrawings = -($drawEnd - $drawStart);

        $debtStart = $this->getAccountGroupBalance(['2050', '2510', '2520', '2530'], $priorStartDateTime);
        $debtEnd = $this->getAccountGroupBalance(['2050', '2510', '2520', '2530'], $endDateTime);
        $changeInLoans = ($debtEnd - $debtStart);

        $financingActivities = [
            ['description' => 'Owner Capital Contributions', 'amount' => round($ownerContributions, 2)],
            ['description' => 'Owner Drawings & Distributions', 'amount' => round($ownerDrawings, 2)],
            ['description' => 'Net Change in Borrowings & Loans', 'amount' => round($changeInLoans, 2)],
        ];

        $netFinancingCash = collect($financingActivities)->sum('amount');

        $netChangeInCash = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        // Cash & Cash Equivalents (subtypes: cash_and_bank or code 1010, 1011, 1020, 1021, 1022)
        $cashAccounts = ChartOfAccount::where(function ($q) {
            $q->where('account_subtype', 'cash_and_bank')
              ->orWhereIn('account_code', ['1010', '1011', '1020', '1021', '1022']);
        })->get();

        $beginningCash = $cashAccounts->sum(function ($account) use ($priorStartDateTime) {
            return $account->calculateBalance($priorStartDateTime);
        });

        $endingCash = $cashAccounts->sum(function ($account) use ($endDateTime) {
            return $account->calculateBalance($endDateTime);
        });

        // Ensure exact cash balance reconciliation
        $calculatedEndingCash = $beginningCash + $netChangeInCash;
        $reconciliationVariance = round($endingCash - $calculatedEndingCash, 2);

        if ($reconciliationVariance != 0) {
            $operatingActivities[] = [
                'description' => 'Operating Cash Reconciliation Adjustment',
                'amount' => $reconciliationVariance
            ];
            $netOperatingCash += $reconciliationVariance;
            $netChangeInCash = $netOperatingCash + $netInvestingCash + $netFinancingCash;
        }

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'net_operating_cash' => round($netOperatingCash, 2),
            'net_investing_cash' => round($netInvestingCash, 2),
            'net_financing_cash' => round($netFinancingCash, 2),
            'net_change_in_cash' => round($netChangeInCash, 2),
            'beginning_cash' => round($beginningCash, 2),
            'ending_cash' => round($endingCash, 2)
        ]);
    }

    /**
     * Helper to calculate balance for account types and subtypes as of a given date
     */
    private function getAccountBalancesForTypes(array $types, array $subtypes, $asOfDateTime, array $codePrefixes = []): array
    {
        $accounts = ChartOfAccount::where(function ($q) use ($types, $subtypes, $codePrefixes) {
            $q->whereIn('account_type', $types)
              ->where(function ($q2) use ($subtypes, $codePrefixes) {
                  $q2->whereIn('account_subtype', $subtypes);
                  foreach ($codePrefixes as $prefix) {
                      $q2->orWhere('account_code', 'like', $prefix . '%');
                  }
              });
        })
        ->orderBy('account_code')
        ->get();

        $balances = [];
        foreach ($accounts as $account) {
            $balance = $account->calculateBalance($asOfDateTime);
            if (round($balance, 2) != 0) {
                $balances[] = [
                    'account_id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_type' => $account->account_type,
                    'account_subtype' => $account->account_subtype,
                    'amount' => round($balance, 2)
                ];
            }
        }

        return $balances;
    }

    /**
     * Helper to calculate Net Income earned from start of time up to $asOfDateTime
     */
    private function getNetIncomeUpToDate($asOfDateTime): float
    {
        $revenue = ChartOfAccount::where('account_type', 'revenue')
            ->get()
            ->sum(function ($account) use ($asOfDateTime) {
                $lines = $account->journalEntryLines()
                    ->whereHas('journalEntry', function ($q) use ($asOfDateTime) {
                        $q->where('status', 'posted')
                          ->where('entry_date', '<=', $asOfDateTime);
                    })->get();
                return $lines->sum('credit_amount') - $lines->sum('debit_amount');
            });

        $expenses = ChartOfAccount::where('account_type', 'expense')
            ->get()
            ->sum(function ($account) use ($asOfDateTime) {
                $lines = $account->journalEntryLines()
                    ->whereHas('journalEntry', function ($q) use ($asOfDateTime) {
                        $q->where('status', 'posted')
                          ->where('entry_date', '<=', $asOfDateTime);
                    })->get();
                return $lines->sum('debit_amount') - $lines->sum('credit_amount');
            });

        return $revenue - $expenses;
    }

    /**
     * Helper to calculate total balance of specific account codes as of a given date
     */
    private function getAccountGroupBalance(array $codes, $asOfDateTime): float
    {
        $accounts = ChartOfAccount::whereIn('account_code', $codes)->get();
        return $accounts->sum(function ($account) use ($asOfDateTime) {
            return $account->calculateBalance($asOfDateTime);
        });
    }

    /**
     * Export any report (Financial or Operational) as PDF
     */
    public function exportPdf(Request $request)
    {
        $reportType = $request->get('type', 'profit-loss');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $asOfDate = $request->get('as_of_date', Carbon::now()->toDateString());

        $titles = [
            'profit-loss' => 'Profit & Loss Statement (Income Statement)',
            'balance-sheet' => 'Balance Sheet (Statement of Financial Position)',
            'trial-balance' => 'General Ledger Trial Balance',
            'cash-flow' => 'Cash Flow Statement (IAS 7 Indirect Method)',
            'sales-summary' => 'Sales & Revenue Summary',
            'monthly-revenue' => 'Monthly Revenue Analysis',
            'top-products' => 'Top Selling Products Report',
            'customer-analysis' => 'Customer Sales & Credit Analysis',
            'inventory-summary' => 'Inventory Stock Summary',
            'low-stock' => 'Low Stock & Reorder Alert',
            'inventory-valuation' => 'Inventory Valuation Report',
            'stock-movement' => 'Stock Movement History',
        ];

        $title = $titles[$reportType] ?? 'Financial & Operational Report';

        $data = [];
        if ($reportType === 'profit-loss') {
            $data = json_decode($this->profitLoss($request)->getContent(), true);
            $periodText = "{$startDate} to {$endDate}";
        } elseif ($reportType === 'balance-sheet') {
            $data = json_decode($this->balanceSheet($request)->getContent(), true);
            $periodText = "As of {$asOfDate}";
        } elseif ($reportType === 'trial-balance') {
            $data = json_decode($this->trialBalance($request)->getContent(), true);
            $periodText = "As of {$asOfDate}";
        } elseif ($reportType === 'cash-flow') {
            $data = json_decode($this->cashFlow($request)->getContent(), true);
            $periodText = "{$startDate} to {$endDate}";
        } else {
            $opController = app(\App\Http\Controllers\Api\ReportController::class);
            if ($reportType === 'sales-summary') {
                $data = json_decode($opController->salesSummary($request)->getContent(), true);
            } elseif ($reportType === 'monthly-revenue') {
                $data = json_decode($opController->monthlyRevenue($request)->getContent(), true);
            } elseif ($reportType === 'top-products') {
                $data = json_decode($opController->topSellingProducts($request)->getContent(), true);
            } elseif ($reportType === 'customer-analysis') {
                $data = json_decode($opController->customerSalesAnalysis($request)->getContent(), true);
            } elseif ($reportType === 'inventory-summary') {
                $data = json_decode($opController->inventoryReport($request)->getContent(), true);
            } elseif ($reportType === 'low-stock') {
                $data = json_decode($opController->lowStockAlert($request)->getContent(), true);
            } elseif ($reportType === 'inventory-valuation') {
                $data = json_decode($opController->inventoryValuation($request)->getContent(), true);
            } elseif ($reportType === 'stock-movement') {
                $data = json_decode($opController->stockMovementHistory($request)->getContent(), true);
            }
            $periodText = "{$startDate} to {$endDate}";
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.financial_report', [
            'companyName' => config('app.name', 'POS & ERP System'),
            'title' => $title,
            'reportType' => $reportType,
            'periodText' => $periodText,
            'generatedAt' => Carbon::now()->format('M d, Y g:i A'),
            'data' => $data,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $fileName = str_replace('-', '_', $reportType) . '_report_' . now()->format('Y_m_d') . '.pdf';

        return $pdf->download($fileName);
    }
}
