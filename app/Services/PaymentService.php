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

            // If payment is created as paid or completed, mark as paid. If approved, create accounting entries
            if (in_array($payment->status, ['paid', 'completed'])) {
                $this->markPaymentAsPaid($payment, $payment->created_by);
            } elseif ($payment->status === 'approved') {
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

            // If status changed to paid/completed, process full payment; if approved, create accounting entries
            if ($oldStatus !== $payment->status) {
                if (in_array($payment->status, ['paid', 'completed'])) {
                    $this->markPaymentAsPaid($payment, auth()->id() ?? $payment->created_by);
                } elseif ($payment->status === 'approved') {
                    $this->createAccountingEntries($payment);
                }
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
            // Create or update journal entry
            $journalEntry = $this->createOrUpdateJournalEntry($payment);

            // Create bank transaction and sync bank account balance
            $bankTransaction = $this->createBankTransaction($payment, $journalEntry->id);
            
            // Mark payment as paid
            $payment->markAsPaid($userId, $journalEntry->id, $bankTransaction->id);
            
            // Process supplier dues & advance allocation
            $this->processSupplierPaymentAllocation($payment);

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
            $companyId = $payment->company_id ?: (auth()->user()?->current_company_id ?? 1);
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateJournalEntryNumber($companyId),
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
    protected function createBankTransaction(Payment $payment, ?int $journalEntryId = null): BankTransaction
    {
        $bankAccount = $payment->bankAccount;
        if (!$bankAccount) {
            throw new \Exception('No valid bank account associated with this payment.');
        }

        $currentBal = (float)$bankAccount->current_balance;
        $amount = (float)$payment->amount;

        if ($currentBal < $amount) {
            throw new \Exception("Insufficient balance in account '{$bankAccount->account_name}'. Available balance: $" . number_format($currentBal, 2) . ", required: $" . number_format($amount, 2));
        }

        $newBalance = round($currentBal - $amount, 2);
        $companyId = $payment->company_id ?? session('active_company_id') ?? auth()->user()?->company_id ?? \App\Models\Company::first()?->id ?? 1;
        $description = $this->getBankTransactionDescription($payment);

        $transaction = BankTransaction::create([
            'company_id' => $companyId,
            'bank_account_id' => $bankAccount->id,
            'journal_entry_id' => $journalEntryId,
            'transaction_date' => $payment->payment_date,
            'reference_number' => $payment->reference_number ?: $payment->payment_number,
            'description' => $description,
            'transaction_type' => 'debit',
            'amount' => $amount,
            'running_balance' => $newBalance,
            'status' => 'cleared',
            'partner_type' => $payment->payee_type ? $this->getPartnerTypeClass($payment->payee_type) : null,
            'partner_id' => $payment->payee_id,
        ]);

        // Sync bank account current_balance and COA balance
        if ($bankAccount->chartAccount) {
            $bankAccount->chartAccount->updateCurrentBalance();
        } else {
            $bankAccount->update(['current_balance' => $newBalance]);
        }

        // Also create a record in `transactions` table so it shows up in Banking -> Transactions list
        try {
            \App\Models\Transaction::create([
                'company_id' => $companyId,
                'type' => 'expense',
                'paid_at' => $payment->payment_date,
                'payment_method' => ucfirst($payment->payment_method ?? 'cash'),
                'account_id' => $bankAccount->id,
                'amount' => $amount,
                'description' => $description,
                'vendor_id' => ($payment->payee_type === 'supplier' || strtolower((string)$payment->payee_type) === 'supplier') ? $payment->payee_id : null,
                'customer_id' => ($payment->payee_type === 'customer' || strtolower((string)$payment->payee_type) === 'customer') ? $payment->payee_id : null,
                'number' => $payment->payment_number,
                'reference' => $payment->reference_number ?: $payment->payment_number,
            ]);
        } catch (\Throwable $e) {
            // Ignore if Transaction model/table write is optional
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

            // 0. Reverse supplier payment allocation (PO dues & advance balance)
            $this->reverseSupplierPaymentAllocation($payment);

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
                    $bAccount->increment('current_balance', (float) $payment->amount);
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
        $payeeName = $payment->payee_name;
        if (empty($payeeName) && $payment->payee_id && strtolower((string)$payment->payee_type) === 'supplier') {
            $supplier = Supplier::find($payment->payee_id);
            if ($supplier) {
                $payeeName = $supplier->name;
            }
        }

        $descriptions = [
            'supplier_payment' => "Payment Out to Supplier: " . ($payeeName ?: 'Supplier'),
            'expense_payment' => "Payment for expense: {$payment->description}",
            'salary_payment' => "Salary payment to: {$payment->payee_name}",
            'sale_return_payment' => "Refund for sale return: {$payment->description}",
            'purchase_invoice_payment' => "Payment for purchase invoice: {$payment->description}",
            'other_payment' => "Other payment: {$payment->description}",
        ];

        return $descriptions[$payment->payment_type] ?? "Payment Out: {$payment->description}";
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
     * Generate journal entry number cleanly with company scope and withoutGlobalScopes
     */
    protected function generateJournalEntryNumber(?int $companyId = null): string
    {
        $companyId = $companyId ?? (auth()->user()?->current_company_id ?? 1);
        $prefix = 'JE';
        $year = now()->year;
        $month = now()->format('m');
        $datePrefix = "{$year}{$month}";

        $query = JournalEntry::withoutGlobalScopes()
            ->withTrashed()
            ->where('company_id', $companyId)
            ->where('entry_number', 'like', "{$prefix}{$datePrefix}%")
            ->orderBy('id', 'desc');

        if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $lastEntry = $query->first();

        $sequence = 1;
        if ($lastEntry && strlen($lastEntry->entry_number) >= 4) {
            $last4 = substr($lastEntry->entry_number, -4);
            if (is_numeric($last4)) {
                $sequence = (int) $last4 + 1;
            }
        }

        $entryNumber = sprintf('%s%s%04d', $prefix, $datePrefix, $sequence);

        while (JournalEntry::withoutGlobalScopes()->withTrashed()->where('company_id', $companyId)->where('entry_number', $entryNumber)->exists()) {
            $sequence++;
            $entryNumber = sprintf('%s%s%04d', $prefix, $datePrefix, $sequence);
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

    /**
     * Process supplier payment allocation across open PO dues and advance balance
     */
    public function processSupplierPaymentAllocation(Payment $payment): void
    {
        // Only process for supplier payments
        $payeeType = strtolower($payment->payee_type ?? '');
        $isSupplierPayment = $payment->payment_type === 'supplier_payment'
            || $payeeType === 'supplier'
            || $payment->payee_type === Supplier::class;

        if (!$isSupplierPayment) {
            return;
        }

        // Find supplier
        $supplier = null;
        if ($payment->payee_id) {
            $supplier = Supplier::find($payment->payee_id);
        }
        if (!$supplier && $payment->payee_name) {
            $companyId = $payment->company_id ?? 1;
            $supplier = Supplier::where('company_id', $companyId)
                ->where(function ($q) use ($payment) {
                    $q->where('name', $payment->payee_name)
                      ->orWhere('company_name', $payment->payee_name);
                })->first();
        }

        if (!$supplier) {
            return;
        }

        // Check if allocation already processed
        $additionalData = $payment->additional_data ?? [];
        if (!empty($additionalData['supplier_allocation'])) {
            return; // Already allocated
        }

        $paidAmount = (float) $payment->amount;
        if ($paidAmount <= 0) {
            return;
        }

        // Query open POs for this supplier with due_amount > 0, ordered by order_date asc
        $openOrders = \App\Models\PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('status', '!=', 'cancelled')
            ->where('due_amount', '>', 0)
            ->orderBy('order_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // If payment is explicitly linked to a Purchase Order, prioritize it
        if (in_array($payment->reference_type, [\App\Models\PurchaseOrder::class, 'App\\Models\\PurchaseOrder', 'PurchaseOrder']) && $payment->reference_id) {
            $targetedPoIndex = $openOrders->search(fn($po) => $po->id == $payment->reference_id);
            if ($targetedPoIndex !== false) {
                $targetedPo = $openOrders->pull($targetedPoIndex);
                $openOrders->prepend($targetedPo);
            }
        }

        $totalDue = (float) $openOrders->sum('due_amount');
        $allocatedOrders = [];
        $appliedDue = 0;
        $appliedAdvance = 0;

        if ($totalDue > 0) {
            // Case A: Supplier has open bills / due amount
            $remainingToAllocate = $paidAmount;

            foreach ($openOrders as $po) {
                if ($remainingToAllocate <= 0) break;

                $poDue = (float) $po->due_amount;
                $deduct = min($remainingToAllocate, $poDue);

                $po->due_amount = max(0, $poDue - $deduct);
                $po->amount_paid = (float) $po->amount_paid + $deduct;
                $po->save();

                $allocatedOrders[] = [
                    'po_id' => $po->id,
                    'po_number' => $po->po_number,
                    'allocated_amount' => $deduct,
                ];

                $appliedDue += $deduct;
                $remainingToAllocate -= $deduct;
            }

            // Overpayment excess goes to advance balance
            if ($remainingToAllocate > 0) {
                $appliedAdvance = $remainingToAllocate;
                $supplier->creditAdvance($appliedAdvance);
            }
        } else {
            // Case B: Supplier has zero due / no pending bills -> Full amount to advance
            $appliedAdvance = $paidAmount;
            $supplier->creditAdvance($appliedAdvance);
        }

        // Store allocation metadata in additional_data
        $additionalData['supplier_allocation'] = [
            'supplier_id' => $supplier->id,
            'paid_amount' => $paidAmount,
            'applied_due' => $appliedDue,
            'applied_advance' => $appliedAdvance,
            'allocated_orders' => $allocatedOrders,
            'allocated_at' => now()->toIso8601String(),
        ];

        $payment->update(['additional_data' => $additionalData]);
    }

    /**
     * Reverse supplier payment allocation (restore PO dues and subtract advance)
     */
    public function reverseSupplierPaymentAllocation(Payment $payment): void
    {
        $additionalData = $payment->additional_data ?? [];
        $allocation = $additionalData['supplier_allocation'] ?? null;

        if (!$allocation) {
            return;
        }

        $supplierId = $allocation['supplier_id'] ?? $payment->payee_id;
        $supplier = $supplierId ? Supplier::find($supplierId) : null;

        // 1. Reverse Advance Balance
        $appliedAdvance = (float) ($allocation['applied_advance'] ?? 0);
        if ($appliedAdvance > 0 && $supplier) {
            $supplier->debitAdvance($appliedAdvance);
        }

        // 2. Reverse Allocated PO Dues
        $allocatedOrders = $allocation['allocated_orders'] ?? [];
        foreach ($allocatedOrders as $alloc) {
            $poId = $alloc['po_id'] ?? null;
            $allocAmount = (float) ($alloc['allocated_amount'] ?? 0);

            if ($poId && $allocAmount > 0) {
                $po = \App\Models\PurchaseOrder::find($poId);
                if ($po) {
                    $po->due_amount = (float) $po->due_amount + $allocAmount;
                    $po->amount_paid = max(0, (float) $po->amount_paid - $allocAmount);
                    $po->save();
                }
            }
        }

        // Clear supplier allocation record from additional_data
        unset($additionalData['supplier_allocation']);
        $payment->update(['additional_data' => $additionalData]);
    }
}
