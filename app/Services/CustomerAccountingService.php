<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerAccountingService
{
    /**
     * Create journal entry for customer sale
     */
    public function createSaleJournalEntry(Sale $sale): JournalEntry
    {
        return DB::transaction(function () use ($sale) {
            // Get accounts
            $accountsReceivableAccount = $this->getAccountsReceivableAccount();
            $salesRevenueAccount = $this->getSalesRevenueAccount();
            $cashAccount = $this->getCashAccount();
            $taxPayableAccount = $this->getTaxPayableAccount();

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => $sale->sale_date,
                'reference' => $sale->sale_number,
                'description' => "Sale to {$sale->customer->name ?? 'Walk-in Customer'}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $sale->total_amount,
                'total_credit' => $sale->total_amount,
                'created_by' => auth()->id() ?? 1,
            ]);

            // If customer sale (on credit)
            if ($sale->customer_id && $sale->paid_amount < $sale->total_amount) {
                $creditAmount = $sale->total_amount - $sale->paid_amount;
                
                // Debit: Accounts Receivable
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $accountsReceivableAccount->id,
                    'description' => 'Accounts receivable',
                    'debit_amount' => $creditAmount,
                    'credit_amount' => 0,
                    'partner_type' => 'App\Models\Customer',
                    'partner_id' => $sale->customer_id,
                ]);
            }

            // If cash payment
            if ($sale->paid_amount > 0) {
                // Debit: Cash
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $cashAccount->id,
                    'description' => 'Cash received',
                    'debit_amount' => $sale->paid_amount,
                    'credit_amount' => 0,
                    'partner_type' => 'App\Models\Customer',
                    'partner_id' => $sale->customer_id,
                ]);
            }

            // Credit: Sales Revenue
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $salesRevenueAccount->id,
                'description' => 'Sales revenue',
                'debit_amount' => 0,
                'credit_amount' => $sale->subtotal,
                'partner_type' => 'App\Models\Customer',
                'partner_id' => $sale->customer_id,
            ]);

            // Credit: Tax Payable (if any)
            if ($sale->tax_amount > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxPayableAccount->id,
                    'description' => 'Sales tax',
                    'debit_amount' => 0,
                    'credit_amount' => $sale->tax_amount,
                    'partner_type' => 'App\Models\Customer',
                    'partner_id' => $sale->customer_id,
                ]);
            }

            // Post the journal entry
            $this->postJournalEntry($journalEntry);

            return $journalEntry;
        });
    }

    /**
     * Create journal entry for customer payment
     */
    public function createPaymentJournalEntry(Sale $sale, float $paymentAmount, string $paymentMethod = 'cash'): JournalEntry
    {
        return DB::transaction(function () use ($sale, $paymentAmount, $paymentMethod) {
            // Get accounts
            $accountsReceivableAccount = $this->getAccountsReceivableAccount();
            $cashAccount = $this->getCashAccount();

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => now(),
                'reference' => $sale->sale_number . '-PAY',
                'description' => "Payment from {$sale->customer->name} for Sale #{$sale->sale_number}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $paymentAmount,
                'total_credit' => $paymentAmount,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Debit: Cash
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashAccount->id,
                'description' => 'Payment received',
                'debit_amount' => $paymentAmount,
                'credit_amount' => 0,
                'partner_type' => 'App\Models\Customer',
                'partner_id' => $sale->customer_id,
            ]);

            // Credit: Accounts Receivable
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $accountsReceivableAccount->id,
                'description' => 'Payment against receivable',
                'debit_amount' => 0,
                'credit_amount' => $paymentAmount,
                'partner_type' => 'App\Models\Customer',
                'partner_id' => $sale->customer_id,
            ]);

            // Post the journal entry
            $this->postJournalEntry($journalEntry);

            return $journalEntry;
        });
    }

    /**
     * Get customer balance from journal entries
     */
    public function getCustomerBalance(Customer $customer, $asOfDate = null): float
    {
        $query = JournalEntryLine::where('partner_type', 'App\Models\Customer')
                                ->where('partner_id', $customer->id)
                                ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                                    $q->where('status', 'posted');
                                    if ($asOfDate) {
                                        $q->where('entry_date', '<=', $asOfDate);
                                    }
                                });

        $totalDebits = $query->sum('debit_amount');
        $totalCredits = $query->sum('credit_amount');

        // For customers, debits increase the balance (what they owe us)
        return $totalDebits - $totalCredits;
    }

    /**
     * Get customer transaction history
     */
    public function getCustomerTransactionHistory(Customer $customer, $startDate = null, $endDate = null): array
    {
        $query = JournalEntryLine::where('partner_type', 'App\Models\Customer')
                                ->where('partner_id', $customer->id)
                                ->with(['journalEntry', 'account'])
                                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                                    $q->where('status', 'posted');
                                    if ($startDate) {
                                        $q->where('entry_date', '>=', $startDate);
                                    }
                                    if ($endDate) {
                                        $q->where('entry_date', '<=', $endDate);
                                    }
                                })
                                ->orderBy('created_at');

        $lines = $query->get();
        $transactions = [];
        $runningBalance = 0;

        foreach ($lines as $line) {
            $runningBalance += $line->debit_amount - $line->credit_amount;
            
            $transactions[] = [
                'date' => $line->journalEntry->entry_date->format('Y-m-d'),
                'reference' => $line->journalEntry->reference,
                'description' => $line->description,
                'account' => $line->account->account_name,
                'debit' => $line->debit_amount,
                'credit' => $line->credit_amount,
                'balance' => $runningBalance,
                'journal_entry_id' => $line->journal_entry_id
            ];
        }

        return $transactions;
    }

    /**
     * Get accounts receivable aging for customer
     */
    public function getCustomerAging(Customer $customer, $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();
        
        // Get unpaid sales
        $unpaidSales = Sale::where('customer_id', $customer->id)
                          ->where('status', 'pending')
                          ->where('sale_date', '<=', $asOfDate)
                          ->get();

        $aging = [
            'current' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'over_90' => 0
        ];

        foreach ($unpaidSales as $sale) {
            $daysOverdue = Carbon::parse($asOfDate)->diffInDays($sale->sale_date);
            $outstandingAmount = $sale->total_amount - $sale->paid_amount;

            if ($daysOverdue <= 30) {
                $aging['current'] += $outstandingAmount;
            } elseif ($daysOverdue <= 60) {
                $aging['days_31_60'] += $outstandingAmount;
            } elseif ($daysOverdue <= 90) {
                $aging['days_61_90'] += $outstandingAmount;
            } else {
                $aging['over_90'] += $outstandingAmount;
            }
        }

        return $aging;
    }

    /**
     * Get or create accounts receivable account
     */
    private function getAccountsReceivableAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '1200'],
            [
                'account_name' => 'Accounts Receivable',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Customer accounts receivable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get or create sales revenue account
     */
    private function getSalesRevenueAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '4000'],
            [
                'account_name' => 'Sales Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Revenue from sales',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get or create cash account
     */
    private function getCashAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '1000'],
            [
                'account_name' => 'Cash',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Cash on hand',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get or create tax payable account
     */
    private function getTaxPayableAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '2100'],
            [
                'account_name' => 'Sales Tax Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Sales tax collected',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Generate journal entry number
     */
    private function generateJournalEntryNumber(): string
    {
        $prefix = 'JE';
        $year = Carbon::now()->year;

        $lastEntry = JournalEntry::whereYear('created_at', $year)
                                ->orderBy('id', 'desc')
                                ->first();

        $sequence = $lastEntry ? (int) substr($lastEntry->entry_number, -6) + 1 : 1;

        return sprintf('%s%s%06d', $prefix, $year, $sequence);
    }

    /**
     * Post journal entry and update account balances
     */
    private function postJournalEntry(JournalEntry $journalEntry): void
    {
        $journalEntry->update([
            'status' => 'posted',
            'posted_by' => auth()->id() ?? 1,
            'posted_at' => now(),
        ]);

        // Update account balances
        foreach ($journalEntry->journalEntryLines as $line) {
            $account = Account::find($line->account_id);
            $account->updateCurrentBalance();
        }
    }
}
