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
        // Debit: Accounts Payable
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->accountingSettings->purchase_invoice_payable_account_id,
            'description' => "Payment to supplier: {$payment->payee_name}",
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'partner_type' => $payment->payee_type ? 'App\\Models\\Supplier' : null,
            'partner_id' => $payment->payee_id,
        ]);

        // Credit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payment->bankAccount->chart_account_id,
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

        return BankTransaction::create([
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
    }

    /**
     * Reverse accounting entries for cancelled payment
     */
    protected function reverseAccountingEntries(Payment $payment): void
    {
        if (!$payment->journal_entry_id) {
            return;
        }

        $originalEntry = $payment->journalEntry;

        // Create reversal journal entry
        $reversalEntry = JournalEntry::create([
            'entry_number' => $this->generateJournalEntryNumber(),
            'entry_date' => now()->toDateString(),
            'reference' => $payment->payment_number . '-REV',
            'description' => "Reversal of cancelled payment: {$originalEntry->description}",
            'entry_type' => 'adjustment',
            'status' => 'posted',
            'total_debit' => $originalEntry->total_debit,
            'total_credit' => $originalEntry->total_credit,
            'created_by' => auth()->id() ?? 1,
            'posted_by' => auth()->id() ?? 1,
            'posted_at' => now(),
        ]);

        // Create reversal lines (swap debit and credit)
        foreach ($originalEntry->journalEntryLines as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $reversalEntry->id,
                'account_id' => $line->account_id,
                'description' => "Reversal: {$line->description}",
                'debit_amount' => $line->credit_amount,
                'credit_amount' => $line->debit_amount,
                'partner_type' => $line->partner_type,
                'partner_id' => $line->partner_id,
            ]);
        }

        // Update account balances
        foreach ($reversalEntry->journalEntryLines as $line) {
            $account = Account::find($line->account_id);
            $account->updateCurrentBalance();
        }
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
    protected function getPartnerTypeClass(string $payeeType): string
    {
        $classes = [
            'supplier' => 'App\\Models\\Supplier',
            'employee' => 'App\\Models\\Employee',
            'customer' => 'App\\Models\\Customer',
        ];

        return $classes[$payeeType] ?? null;
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

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
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
