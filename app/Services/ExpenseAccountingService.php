<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpenseAccountingService
{
    /**
     * Create journal entry when expense is approved
     */
    public function createExpenseJournalEntry(Expense $expense): JournalEntry
    {
        return DB::transaction(function () use ($expense) {
            // Get the expense account based on category
            $expenseAccount = $this->getExpenseAccount($expense);

            if (!$expenseAccount) {
                throw new \Exception('Expense account not found. Please configure accounting settings.');
            }

            // Get the accounts payable or cash account based on payment status
            $creditAccount = $this->getCreditAccount($expense);

            if (!$creditAccount) {
                throw new \Exception('Credit account not found. Please configure accounting settings.');
            }

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => $expense->expense_date,
                'reference' => $expense->expense_number,
                'description' => "Expense: {$expense->title}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $expense->amount,
                'total_credit' => $expense->amount,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Create debit line (expense account)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $expenseAccount->id,
                'description' => $expense->title,
                'debit_amount' => $expense->amount,
                'credit_amount' => 0,
                'partner_type' => 'App\Models\Expense',
                'partner_id' => $expense->id,
            ]);

            // Create credit line (accounts payable or cash account)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccount->id,
                'description' => $expense->vendor_name ? "Payable to {$expense->vendor_name}" : 'Expense payable',
                'debit_amount' => 0,
                'credit_amount' => $expense->amount,
                'partner_type' => 'App\Models\Expense',
                'partner_id' => $expense->id,
            ]);

            // Auto-post the journal entry
            $this->postJournalEntry($journalEntry);

            // Update expense with journal entry reference
            $expense->update(['journal_entry_id' => $journalEntry->id]);

            return $journalEntry;
        });
    }

    /**
     * Create journal entry when expense is paid
     */
    public function createExpensePaymentJournalEntry(Expense $expense, $bankAccountId = null): ?JournalEntry
    {
        // Only create payment entry if expense was previously approved and has accounts payable
        if (!$expense->journal_entry_id || $expense->status !== 'paid') {
            return null;
        }

        return DB::transaction(function () use ($expense, $bankAccountId) {
            // Get accounts payable account
            $accountsPayableAccount = $this->getAccountsPayableAccount();

            // Get the cash/bank account - use specific bank account if provided
            $cashAccount = $bankAccountId ? $this->getBankChartAccount($bankAccountId) : $this->getCashAccount($expense);

            // Create payment journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => $expense->paid_at ? $expense->paid_at->toDateString() : now()->toDateString(),
                'reference' => $expense->expense_number . '-PAY',
                'description' => "Payment for expense: {$expense->title}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $expense->amount,
                'total_credit' => $expense->amount,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Create debit line (accounts payable - reducing liability)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $accountsPayableAccount->id,
                'description' => "Payment to {$expense->vendor_name}",
                'debit_amount' => $expense->amount,
                'credit_amount' => 0,
                'partner_type' => 'App\Models\Expense',
                'partner_id' => $expense->id,
            ]);

            // Create credit line (cash/bank account)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashAccount->id,
                'description' => "Payment for {$expense->title}",
                'debit_amount' => 0,
                'credit_amount' => $expense->amount,
                'partner_type' => 'App\Models\Expense',
                'partner_id' => $expense->id,
            ]);

            // Auto-post the journal entry
            $this->postJournalEntry($journalEntry);

            return $journalEntry;
        });
    }

    /**
     * Completely remove journal entries and bank transactions for an expense
     */
    public function removeExpenseAccounting(Expense $expense): bool
    {
        return DB::transaction(function () use ($expense) {
            $removed = false;
            $removedItems = [];

            try {
                // Remove bank transactions
                $bankTransactions = BankTransaction::where('reference_number', $expense->expense_number)
                    ->orWhere('reference_number', 'like', $expense->expense_number . '%')
                    ->get();

                foreach ($bankTransactions as $transaction) {
                    $removedItems[] = "Bank Transaction ID: {$transaction->id}";
                    $transaction->delete();
                    $removed = true;
                }

                // Remove journal entries
                if ($expense->journal_entry_id) {
                    $journalEntry = JournalEntry::find($expense->journal_entry_id);
                    if ($journalEntry) {
                        $removedItems[] = "Journal Entry ID: {$journalEntry->id}";
                        $journalEntry->journalEntryLines()->delete();
                        $journalEntry->delete();
                        $removed = true;
                    }
                }

                // Remove any other related journal entries
                $relatedEntries = JournalEntry::where('reference', 'like', $expense->expense_number . '%')->get();
                foreach ($relatedEntries as $entry) {
                    $removedItems[] = "Related Journal Entry ID: {$entry->id}";
                    $entry->journalEntryLines()->delete();
                    $entry->delete();
                    $removed = true;
                }

                // Clear journal entry reference from expense
                if ($removed) {
                    $expense->update(['journal_entry_id' => null]);
                }

                // Log the cleanup for audit purposes
                if (!empty($removedItems)) {
                    Log::info('Expense accounting cleanup completed', [
                        'expense_id' => $expense->id,
                        'expense_number' => $expense->expense_number,
                        'removed_items' => $removedItems
                    ]);
                }

                return $removed;

            } catch (\Exception $e) {
                Log::error('Failed to remove expense accounting', [
                    'expense_id' => $expense->id,
                    'expense_number' => $expense->expense_number,
                    'error' => $e->getMessage(),
                    'partially_removed' => $removedItems
                ]);
                throw $e;
            }
        });
    }

    /**
     * Reverse journal entry when expense is rejected
     */
    public function reverseExpenseJournalEntry(Expense $expense): ?JournalEntry
    {
        if (!$expense->journal_entry_id) {
            return null;
        }

        return DB::transaction(function () use ($expense) {
            $originalEntry = JournalEntry::find($expense->journal_entry_id);
            
            if (!$originalEntry || $originalEntry->status !== 'posted') {
                return null;
            }

            // Create reversal journal entry
            $reversalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => now()->toDateString(),
                'reference' => $expense->expense_number . '-REV',
                'description' => "Reversal of expense: {$expense->title}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $expense->amount,
                'total_credit' => $expense->amount,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Create reversal lines (opposite of original)
            foreach ($originalEntry->journalEntryLines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'description' => "Reversal: {$line->description}",
                    'debit_amount' => $line->credit_amount, // Swap debit and credit
                    'credit_amount' => $line->debit_amount,
                    'partner_type' => 'App\Models\Expense',
                    'partner_id' => $expense->id,
                ]);
            }

            // Auto-post the reversal entry
            $this->postJournalEntry($reversalEntry);

            // Mark original entry as reversed
            $originalEntry->update(['status' => 'reversed']);

            return $reversalEntry;
        });
    }

    /**
     * Get expense account based on category
     */
    private function getExpenseAccount(Expense $expense): Account
    {
        // Try to find account by category code first
        if ($expense->category && $expense->category->code) {
            $account = Account::where('account_code', $expense->category->code)
                            ->where('account_type', 'expense')
                            ->first();
            if ($account) {
                return $account;
            }
        }

        // Fall back to general expense account
        return $this->getOrCreateAccount([
            'account_code' => '6000',
            'account_name' => 'General Expenses',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'description' => 'General operating expenses',
            'is_system_account' => true,
        ]);
    }

    /**
     * Get credit account (accounts payable for unpaid, cash for paid)
     */
    private function getCreditAccount(Expense $expense): Account
    {
        if ($expense->status === 'paid') {
            return $this->getCashAccount($expense);
        }

        return $this->getAccountsPayableAccount();
    }

    /**
     * Get accounts payable account
     */
    private function getAccountsPayableAccount(): Account
    {
        return $this->getOrCreateAccount([
            'account_code' => '2100',
            'account_name' => 'Accounts Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'description' => 'Amounts owed to suppliers and vendors',
            'is_system_account' => true,
        ]);
    }

    /**
     * Get bank account's chart account
     */
    private function getBankChartAccount(int $bankAccountId): Account
    {
        $bankAccount = BankAccount::findOrFail($bankAccountId);
        return $bankAccount->chartAccount;
    }

    /**
     * Get cash account based on payment method
     */
    private function getCashAccount(Expense $expense): Account
    {
        $accountCode = match($expense->payment_method) {
            'bank_transfer' => '1020',
            'credit_card' => '1030',
            'check' => '1020',
            'petty_cash' => '1015',
            default => '1010', // Cash
        };

        $accountName = match($expense->payment_method) {
            'bank_transfer' => 'Bank Account',
            'credit_card' => 'Credit Card',
            'check' => 'Bank Account',
            'petty_cash' => 'Petty Cash',
            default => 'Cash',
        };

        return $this->getOrCreateAccount([
            'account_code' => $accountCode,
            'account_name' => $accountName,
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'description' => "Cash and cash equivalents - {$accountName}",
            'is_system_account' => true,
        ]);
    }

    /**
     * Get or create account
     */
    private function getOrCreateAccount(array $accountData): Account
    {
        return Account::firstOrCreate(
            ['account_code' => $accountData['account_code']],
            $accountData
        );
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

    /**
     * Generate journal entry number
     */
    private function generateJournalEntryNumber(): string
    {
        $prefix = 'JE';
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        
        $lastEntry = JournalEntry::whereYear('created_at', $year)
                                ->whereMonth('created_at', $month)
                                ->orderBy('id', 'desc')
                                ->first();
        
        $sequence = $lastEntry ? (int) substr($lastEntry->entry_number, -4) + 1 : 1;
        
        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }
}
