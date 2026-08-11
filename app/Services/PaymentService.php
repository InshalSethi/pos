<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\BankTransaction;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\PayrollRecord;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    protected $accountingSettings;

    public function __construct()
    {
        $this->accountingSettings = AccountingSetting::getSettings();
    }

    /**
     * Create payment with accounting entries
     */
    public function createPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            // Generate payment number if not provided
            if (!isset($data['payment_number'])) {
                $data['payment_number'] = Payment::generatePaymentNumber();
            }

            // Create the payment record
            $payment = Payment::create($data);

            // If payment is created as approved or paid, create accounting entries
            if (in_array($payment->status, ['approved', 'paid'])) {
                $this->createAccountingEntries($payment);
            }

            return $payment;
        });
    }

    /**
     * Update payment and handle accounting changes
     */
    public function updatePayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $oldStatus = $payment->status;
            $payment->update($data);

            // If status changed to approved or paid, create accounting entries
            if ($oldStatus !== $payment->status && in_array($payment->status, ['approved', 'paid'])) {
                $this->createAccountingEntries($payment);
            }

            // If payment was cancelled, reverse accounting entries
            if ($oldStatus !== 'cancelled' && $payment->status === 'cancelled') {
                $this->reverseAccountingEntries($payment);
            }

            return $payment;
        });
    }

    /**
     * Approve payment and create accounting entries
     */
    public function approvePayment(Payment $payment, int $userId, string $notes = null): Payment
    {
        return DB::transaction(function () use ($payment, $userId, $notes) {
            $payment->approve($userId, $notes);
            $this->createAccountingEntries($payment);
            return $payment;
        });
    }

    /**
     * Mark payment as paid and create bank transaction
     */
    public function markPaymentAsPaid(Payment $payment, int $userId): Payment
    {
        return DB::transaction(function () use ($payment, $userId) {
            // Create bank transaction
            $bankTransaction = $this->createBankTransaction($payment);
            
            // Create or update journal entry
            $journalEntry = $this->createOrUpdateJournalEntry($payment);
            
            // Mark payment as paid
            $payment->markAsPaid($userId, $journalEntry->id, $bankTransaction->id);
            
            return $payment;
        });
    }

    /**
     * Create accounting entries for payment
     */
    protected function createAccountingEntries(Payment $payment): void
    {
        if ($payment->journal_entry_id) {
            return; // Already has accounting entries
        }

        $journalEntry = $this->createOrUpdateJournalEntry($payment);
        $payment->update(['journal_entry_id' => $journalEntry->id]);
    }

    /**
     * Create or update journal entry for payment
     */
    protected function createOrUpdateJournalEntry(Payment $payment): JournalEntry
    {
        if ($payment->journal_entry_id) {
            return $payment->journalEntry;
        }

        return DB::transaction(function () use ($payment) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => $payment->payment_date,
                'reference' => $payment->payment_number,
                'description' => $this->getJournalEntryDescription($payment),
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $payment->amount,
                'total_credit' => $payment->amount,
                'created_by' => $payment->created_by,
                'posted_by' => $payment->paid_by ?? $payment->created_by,
                'posted_at' => now(),
            ]);

            // Create journal entry lines based on payment type
            $this->createJournalEntryLines($journalEntry, $payment);

            // Recalculate COA ledger balances for affected accounts
            foreach ($journalEntry->journalEntryLines as $line) {
                if ($line->account_id) {
                    $acc = Account::find($line->account_id);
                    if ($acc) {
                        $acc->updateCurrentBalance();
                    }
                }
            }

            return $journalEntry;
        });
    }

    /**
     * Create journal entry lines based on payment type
     */
    protected function createJournalEntryLines(JournalEntry $journalEntry, Payment $payment): void
    {
        switch ($payment->payment_type) {
            case 'supplier_payment':
                $this->createSupplierPaymentLines($journalEntry, $payment);
                break;
            case 'expense_payment':
                $this->createExpensePaymentLines($journalEntry, $payment);
                break;
            case 'salary_payment':
                $this->createSalaryPaymentLines($journalEntry, $payment);
                break;
            case 'sale_return_payment':
                $this->createSaleReturnPaymentLines($journalEntry, $payment);
                break;
            case 'purchase_invoice_payment':
                $this->createPurchaseInvoicePaymentLines($journalEntry, $payment);
                break;
            default:
                $this->createOtherPaymentLines($journalEntry, $payment);
                break;
        }
    }

    /**
     * Create supplier payment journal lines
     */
    protected function createSupplierPaymentLines(JournalEntry $journalEntry, Payment $payment): void
    {
        $companyId = $payment->company_id ?? auth()->user()?->current_company_id ?? 1;

        $payableAccountId = $this->accountingSettings->purchase_invoice_payable_account_id;
        if (!$payableAccountId) {
            $apAcc = Account::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '20100')
                      ->orWhere('account_code', '2010')
                      ->orWhere('account_name', 'LIKE', '%Accounts Payable%');
                })->first();
            if (!$apAcc) {
                $apAcc = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '20100',
                    'account_name' => 'Accounts Payable',
                    'account_type' => 'liability',
                    'account_subtype' => 'current_liability',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }
            $payableAccountId = $apAcc->id;
        }

        $bankChartAccountId = $payment->bankAccount?->chart_account_id;
        if (!$bankChartAccountId && $payment->bankAccount) {
            $bAccount = $payment->bankAccount;
            $bankChartAccount = Account::where('company_id', $companyId)
                ->where('account_type', 'asset')
                ->where(function ($q) use ($bAccount) {
                    $q->where('account_name', 'LIKE', "%{$bAccount->bank_name}%")
                      ->orWhere('account_name', 'LIKE', "%{$bAccount->account_name}%")
                      ->orWhere('account_code', '1610')
                      ->orWhere('account_code', '1600')
                      ->orWhere('account_code', '1010');
                })->first();
            if (!$bankChartAccount) {
                $bankChartAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1610',
                    'account_name' => $bAccount->account_name,
                    'account_type' => 'asset',
                    'opening_balance' => $bAccount->opening_balance ?? 0,
                    'current_balance' => $bAccount->current_balance ?? 0,
                    'is_active' => true,
                ]);
            }
            $bAccount->update(['chart_account_id' => $bankChartAccount->id]);
            $bankChartAccountId = $bankChartAccount->id;
        }

        // Debit: Accounts Payable
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccountId,
            'description' => "Payment to supplier: {$payment->payee_name}",
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'partner_type' => $payment->payee_type ? 'App\\Models\\Supplier' : null,
            'partner_id' => $payment->payee_id,
        ]);

        // Credit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankChartAccountId,
            'description' => "Payment from {$payment->bankAccount->account_name}",
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
            'partner_type' => $payment->payee_type ? 'App\\Models\\Supplier' : null,
            'partner_id' => $payment->payee_id,
        ]);
    }

    /**
     * Create expense payment journal lines
     */
    protected function createExpensePaymentLines(JournalEntry $journalEntry, Payment $payment): void
    {
        // Debit: Accounts Payable (if expense was previously recorded)
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->accountingSettings->expense_payable_account_id,
            'description' => "Payment for expense: {$payment->description}",
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'partner_type' => $payment->reference_type,
            'partner_id' => $payment->reference_id,
        ]);

        // Credit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payment->bankAccount->chart_account_id,
            'description' => "Payment from {$payment->bankAccount->account_name}",
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
            'partner_type' => $payment->reference_type,
            'partner_id' => $payment->reference_id,
        ]);
    }

    /**
     * Create salary payment journal lines
     */
    protected function createSalaryPaymentLines(JournalEntry $journalEntry, Payment $payment): void
    {
        // Debit: Salary Payable
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getSalaryPayableAccount()->id,
            'description' => "Salary payment to: {$payment->payee_name}",
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'partner_type' => 'App\\Models\\Employee',
            'partner_id' => $payment->payee_id,
        ]);

        // Credit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payment->bankAccount->chart_account_id,
            'description' => "Payment from {$payment->bankAccount->account_name}",
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
            'partner_type' => 'App\\Models\\Employee',
            'partner_id' => $payment->payee_id,
        ]);
    }

    /**
     * Create sale return payment journal lines
     */
    protected function createSaleReturnPaymentLines(JournalEntry $journalEntry, Payment $payment): void
    {
        // Debit: Sales Returns
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->accountingSettings->sales_return_revenue_account_id,
            'description' => "Refund for sale return: {$payment->description}",
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'partner_type' => 'App\\Models\\Customer',
            'partner_id' => $payment->payee_id,
        ]);

        // Credit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payment->bankAccount->chart_account_id,
            'description' => "Refund from {$payment->bankAccount->account_name}",
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
            'partner_type' => 'App\\Models\\Customer',
            'partner_id' => $payment->payee_id,
        ]);
    }

    /**
     * Create purchase invoice payment journal lines
     */
    protected function createPurchaseInvoicePaymentLines(JournalEntry $journalEntry, Payment $payment): void
    {
        $this->createSupplierPaymentLines($journalEntry, $payment);
    }

    /**
     * Create other payment journal lines
     */
    protected function createOtherPaymentLines(JournalEntry $journalEntry, Payment $payment): void
    {
        // Debit: Miscellaneous Expense
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getMiscellaneousExpenseAccount()->id,
            'description' => $payment->description,
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payment->bankAccount->chart_account_id,
            'description' => "Payment from {$payment->bankAccount->account_name}",
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
        ]);
    }

    /**
     * Create bank transaction for payment
     */
    protected function createBankTransaction(Payment $payment): BankTransaction
    {
        $bankAccount = $payment->bankAccount;

        // Get last transaction for running balance
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccount->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $currentBalance = $lastTransaction ? $lastTransaction->running_balance : $bankAccount->opening_balance;
        $newBalance = $currentBalance - $payment->amount; // Debit reduces balance

        $transaction = BankTransaction::create([
            'bank_account_id' => $bankAccount->id,
            'transaction_date' => $payment->payment_date,
            'reference_number' => $payment->reference_number ?: $payment->payment_number,
            'description' => $this->getBankTransactionDescription($payment),
            'transaction_type' => 'debit',
            'amount' => $payment->amount,
            'running_balance' => $newBalance,
            'status' => 'cleared',
            'partner_type' => $payment->payee_type ? $this->getPartnerTypeClass($payment->payee_type) : null,
            'partner_id' => $payment->payee_id,
        ]);

        // Sync bank account current_balance and COA balance
        $bankAccount->update(['current_balance' => $newBalance]);
        if ($bankAccount->chartAccount) {
            $bankAccount->chartAccount->updateCurrentBalance();
        }

        return $transaction;
    }

    /**
     * Cancel payment, reverse bank transaction & balance, and reverse journal entry
     */
    public function cancelPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->status === 'cancelled') {
                return;
            }

            // 1. Reverse accounting journal entry for payment first
            if ($payment->journal_entry_id) {
                $oldEntries = JournalEntry::where('id', $payment->journal_entry_id)->get();
                foreach ($oldEntries as $entry) {
                    $affectedAccountIds = [];
                    foreach ($entry->journalEntryLines as $line) {
                        if ($line->account_id) {
                            $affectedAccountIds[] = $line->account_id;
                        }
                    }
                    JournalEntryLine::where('journal_entry_id', $entry->id)->delete();
                    $entry->delete();
                    foreach (array_unique($affectedAccountIds) as $accId) {
                        $acc = Account::find($accId);
                        if ($acc) {
                            $acc->updateCurrentBalance();
                        }
                    }
                }
            }

            // 2. Reverse bank transaction and restore bank account current balance
            if ($payment->bank_transaction_id) {
                $tx = BankTransaction::find($payment->bank_transaction_id);
                if ($tx) {
                    $bAccount = BankAccount::find($tx->bank_account_id);
                    if ($bAccount) {
                        if ($tx->transaction_type === 'debit') {
                            $bAccount->increment('current_balance', $tx->amount);
                        } elseif ($tx->transaction_type === 'credit') {
                            $bAccount->decrement('current_balance', $tx->amount);
                        }
                        if ($bAccount->chartAccount) {
                            $bAccount->chartAccount->updateCurrentBalance();
                        }
                    }
                    $tx->delete();
                }
            } elseif ($payment->bank_account_id && $payment->status === 'paid') {
                $bAccount = BankAccount::find($payment->bank_account_id);
                if ($bAccount) {
                    $bAccount->increment('current_balance', $payment->amount);
                    if ($bAccount->chartAccount) {
                        $bAccount->chartAccount->updateCurrentBalance();
                    }
                }
            }

            // 3. Update payment status to cancelled
            $payment->update(['status' => 'cancelled']);
        });
    }


    /**
     * Reverse accounting entries for cancelled payment
     */
    protected function reverseAccountingEntries(Payment $payment): void
    {
        $this->cancelPayment($payment);
    }


    /**
     * Get journal entry description based on payment type
     */
    protected function getJournalEntryDescription(Payment $payment): string
    {
        $descriptions = [
            'supplier_payment' => "Payment to supplier: {$payment->payee_name}",
            'expense_payment' => "Payment for expense: {$payment->description}",
            'salary_payment' => "Salary payment to: {$payment->payee_name}",
            'sale_return_payment' => "Refund for sale return: {$payment->description}",
            'purchase_invoice_payment' => "Payment for purchase invoice: {$payment->description}",
            'other_payment' => "Other payment: {$payment->description}",
        ];

        return $descriptions[$payment->payment_type] ?? "Payment: {$payment->description}";
    }

    /**
     * Get bank transaction description
     */
    protected function getBankTransactionDescription(Payment $payment): string
    {
        return $this->getJournalEntryDescription($payment);
    }

    /**
     * Get partner type class name
     */
    protected function getPartnerTypeClass(?string $payeeType): ?string
    {
        if (!$payeeType) return null;
        if (str_contains($payeeType, '\\')) {
            return $payeeType;
        }

        $classes = [
            'supplier' => 'App\\Models\\Supplier',
            'employee' => 'App\\Models\\Employee',
            'customer' => 'App\\Models\\Customer',
        ];

        return $classes[strtolower($payeeType)] ?? $payeeType;
    }

    /**
     * Generate journal entry number
     */
    protected function generateJournalEntryNumber(): string
    {
        $prefix = 'JE';
        $year = now()->year;
        $month = now()->format('m');

        $lastEntry = JournalEntry::whereYear('created_at', $year)
                               ->whereMonth('created_at', $month)
                               ->orderBy('id', 'desc')
                               ->first();

        $sequence = $lastEntry ? (int) substr($lastEntry->entry_number, -4) + 1 : 1;
        $entryNumber = sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);

        while (JournalEntry::where('entry_number', $entryNumber)->exists()) {
            $sequence++;
            $entryNumber = sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
        }

        return $entryNumber;
    }

    /**
     * Get salary payable account
     */
    protected function getSalaryPayableAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '2110'],
            [
                'account_name' => 'Salary Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Outstanding salary payments to employees',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get miscellaneous expense account
     */
    protected function getMiscellaneousExpenseAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '6999'],
            [
                'account_name' => 'Miscellaneous Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Other miscellaneous expenses',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }
}
