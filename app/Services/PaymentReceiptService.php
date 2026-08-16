<?php

namespace App\Services;

use App\Models\PaymentReceipt;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\BankTransaction;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\BankAccount;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentReceiptService
{
    protected $accountingSettings;

    public function __construct()
    {
        $this->accountingSettings = AccountingSetting::getSettings();
    }

    /**
     * Create payment receipt with accounting entries
     */
    public function createPaymentReceipt(array $data): PaymentReceipt
    {
        return DB::transaction(function () use ($data) {
            // Generate receipt number if not provided
            if (!isset($data['receipt_number'])) {
                $data['receipt_number'] = PaymentReceipt::generateReceiptNumber();
            }

            // Create the payment receipt record
            $receipt = PaymentReceipt::create($data);

            // If receipt is created as paid, completed, verified, deposited, or received, mark as deposited
            if (in_array($receipt->status, ['paid', 'completed', 'verified', 'deposited', 'received'])) {
                $this->markReceiptAsDeposited($receipt, $receipt->created_by);
            }

            return $receipt;
        });
    }

    /**
     * Update payment receipt and handle accounting changes
     */
    public function updatePaymentReceipt(PaymentReceipt $receipt, array $data): PaymentReceipt
    {
        return DB::transaction(function () use ($receipt, $data) {
            $oldStatus = $receipt->status;
            $receipt->update($data);

            // If status changed to paid, completed, verified, deposited, or received, mark as deposited
            if ($oldStatus !== $receipt->status && in_array($receipt->status, ['paid', 'completed', 'verified', 'deposited', 'received'])) {
                $this->markReceiptAsDeposited($receipt, auth()->id() ?? $receipt->created_by);
            }

            // If receipt was cancelled, reverse accounting entries
            if ($oldStatus !== 'cancelled' && $receipt->status === 'cancelled') {
                $this->reverseAccountingEntries($receipt);
            }

            return $receipt;
        });
    }

    /**
     * Verify payment receipt and create accounting entries
     */
    public function verifyPaymentReceipt(PaymentReceipt $receipt, int $userId, string $notes = null): PaymentReceipt
    {
        return DB::transaction(function () use ($receipt, $userId, $notes) {
            $receipt->verify($userId, $notes);
            $this->createAccountingEntries($receipt);
            return $receipt;
        });
    }

    /**
     * Mark payment receipt as deposited and create bank transaction
     */
    public function markReceiptAsDeposited(PaymentReceipt $receipt, int $userId): PaymentReceipt
    {
        return DB::transaction(function () use ($receipt, $userId) {
            // Create or update journal entry
            $journalEntry = $this->createOrUpdateJournalEntry($receipt);

            // Create bank transaction and sync bank account balance
            $bankTransaction = $this->createBankTransaction($receipt, $journalEntry->id);
            
            // Sync Customer Wallet Balance for unallocated customer payment or advance
            $this->syncCustomerWalletOnDeposit($receipt);

            // Mark receipt as deposited
            $receipt->markAsDeposited($userId, $journalEntry->id, $bankTransaction->id);
            
            return $receipt;
        });
    }

    /**
     * Create accounting entries for payment receipt
     */
    protected function createAccountingEntries(PaymentReceipt $receipt): void
    {
        if ($receipt->journal_entry_id) {
            return; // Already has accounting entries
        }

        $journalEntry = $this->createOrUpdateJournalEntry($receipt);
        $receipt->update(['journal_entry_id' => $journalEntry->id]);
    }

    /**
     * Create or update journal entry for payment receipt
     */
    protected function createOrUpdateJournalEntry(PaymentReceipt $receipt): JournalEntry
    {
        if ($receipt->journal_entry_id) {
            return $receipt->journalEntry;
        }

        return DB::transaction(function () use ($receipt) {
            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => $receipt->receipt_date,
                'reference' => $receipt->receipt_number,
                'description' => $this->getJournalEntryDescription($receipt),
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $receipt->amount,
                'total_credit' => $receipt->amount,
                'created_by' => $receipt->created_by,
                'posted_by' => $receipt->deposited_by ?? $receipt->created_by,
                'posted_at' => now(),
            ]);

            // Create journal entry lines based on receipt type
            $this->createJournalEntryLines($journalEntry, $receipt);

            return $journalEntry;
        });
    }

    /**
     * Create journal entry lines based on receipt type
     */
    protected function createJournalEntryLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        switch ($receipt->receipt_type) {
            case 'customer_payment':
                $this->createCustomerPaymentLines($journalEntry, $receipt);
                break;
            case 'customer_advance':
                $this->createCustomerAdvanceLines($journalEntry, $receipt);
                break;
            case 'supplier_refund':
            case 'supplier_rebate':
                $this->createSupplierRefundLines($journalEntry, $receipt);
                break;
            case 'interest_income':
                $this->createInterestIncomeLines($journalEntry, $receipt);
                break;
            case 'rental_income':
                $this->createRentalIncomeLines($journalEntry, $receipt);
                break;
            case 'commission_income':
                $this->createCommissionIncomeLines($journalEntry, $receipt);
                break;
            case 'asset_sale':
                $this->createAssetSaleLines($journalEntry, $receipt);
                break;
            case 'bank_transfer_in':
                $this->createBankTransferLines($journalEntry, $receipt);
                break;
            case 'cash_deposit':
                $this->createCashDepositLines($journalEntry, $receipt);
                break;
            default:
                $this->createMiscellaneousIncomeLines($journalEntry, $receipt);
                break;
        }
    }

    /**
     * Create customer payment journal lines
     */
    protected function createCustomerPaymentLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Payment received from customer: {$receipt->payer_name}",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
            'partner_type' => $receipt->payer_type ? 'App\\Models\\Customer' : null,
            'partner_id' => $receipt->payer_id,
        ]);

        // Credit: Accounts Receivable
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->accountingSettings->sales_invoice_receivable_account_id,
            'description' => "Payment from customer: {$receipt->payer_name}",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
            'partner_type' => $receipt->payer_type ? 'App\\Models\\Customer' : null,
            'partner_id' => $receipt->payer_id,
        ]);
    }

    /**
     * Create customer advance payment journal lines
     */
    protected function createCustomerAdvanceLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Advance payment from customer: {$receipt->payer_name}",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
            'partner_type' => 'App\\Models\\Customer',
            'partner_id' => $receipt->payer_id,
        ]);

        // Credit: Customer Advances (Liability)
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getCustomerAdvancesAccount()->id,
            'description' => "Advance from customer: {$receipt->payer_name}",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
            'partner_type' => 'App\\Models\\Customer',
            'partner_id' => $receipt->payer_id,
        ]);
    }

    /**
     * Create supplier refund journal lines
     */
    protected function createSupplierRefundLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Refund from supplier: {$receipt->payer_name}",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
            'partner_type' => 'App\\Models\\Supplier',
            'partner_id' => $receipt->payer_id,
        ]);

        // Credit: Purchase Returns or Accounts Payable
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->accountingSettings->purchase_invoice_payable_account_id,
            'description' => "Refund from supplier: {$receipt->payer_name}",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
            'partner_type' => 'App\\Models\\Supplier',
            'partner_id' => $receipt->payer_id,
        ]);
    }

    /**
     * Create interest income journal lines
     */
    protected function createInterestIncomeLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Interest income received",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Interest Income
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getInterestIncomeAccount()->id,
            'description' => "Interest income earned",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    /**
     * Create rental income journal lines
     */
    protected function createRentalIncomeLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Rental income received",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Rental Income
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getRentalIncomeAccount()->id,
            'description' => "Rental income earned",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    /**
     * Create commission income journal lines
     */
    protected function createCommissionIncomeLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Commission income received",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Commission Income
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getCommissionIncomeAccount()->id,
            'description' => "Commission income earned",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    /**
     * Create asset sale journal lines
     */
    protected function createAssetSaleLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Proceeds from asset sale",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Gain on Asset Sale (or Loss if negative)
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getAssetSaleAccount()->id,
            'description' => "Gain from asset sale",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    /**
     * Create bank transfer journal lines
     */
    protected function createBankTransferLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Destination Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Bank transfer received",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Source Bank Account (would need to be specified)
        // For now, use a general transfer account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getBankTransferAccount()->id,
            'description' => "Bank transfer sent",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    /**
     * Create cash deposit journal lines
     */
    protected function createCashDepositLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => "Cash deposited to bank",
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Cash Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getCashAccount()->id,
            'description' => "Cash withdrawn for deposit",
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    /**
     * Create miscellaneous income journal lines
     */
    protected function createMiscellaneousIncomeLines(JournalEntry $journalEntry, PaymentReceipt $receipt): void
    {
        // Debit: Bank Account
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->bankAccount->chart_account_id,
            'description' => $receipt->description,
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Credit: Miscellaneous Income
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $this->getMiscellaneousIncomeAccount()->id,
            'description' => $receipt->description,
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    /**
     * Create bank transaction for payment receipt
     */
    protected function createBankTransaction(PaymentReceipt $receipt, ?int $journalEntryId = null): BankTransaction
    {
        $bankAccount = $receipt->bankAccount;
        if (!$bankAccount) {
            throw new \Exception('No valid bank account associated with this payment receipt.');
        }

        $currentBal = (float)$bankAccount->current_balance;
        $amount = (float)$receipt->amount;
        $newBalance = round($currentBal + $amount, 2);
        $companyId = $receipt->company_id ?? session('active_company_id') ?? auth()->user()?->company_id ?? \App\Models\Company::first()?->id ?? 1;
        $description = "Payment In from Customer: " . ($receipt->payer_name ?: 'Customer');

        $transaction = BankTransaction::create([
            'company_id' => $companyId,
            'bank_account_id' => $bankAccount->id,
            'journal_entry_id' => $journalEntryId,
            'transaction_date' => $receipt->receipt_date,
            'reference_number' => $receipt->transaction_reference ?: $receipt->receipt_number,
            'description' => $description,
            'transaction_type' => 'credit',
            'amount' => $amount,
            'running_balance' => $newBalance,
            'status' => 'cleared',
            'partner_type' => $receipt->payer_type ? $this->getPartnerTypeClass($receipt->payer_type) : null,
            'partner_id' => $receipt->payer_id,
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
                'type' => 'income',
                'paid_at' => $receipt->receipt_date,
                'payment_method' => ucfirst($receipt->payment_method ?? 'cash'),
                'account_id' => $bankAccount->id,
                'amount' => $amount,
                'description' => $description,
                'customer_id' => (strtolower((string)$receipt->payer_type) === 'customer') ? $receipt->payer_id : null,
                'number' => $receipt->receipt_number,
                'reference' => $receipt->transaction_reference ?: $receipt->receipt_number,
            ]);
        } catch (\Throwable $e) {
            // Ignore if Transaction model write is optional
        }

        return $transaction;
    }

    /**
     * Reverse accounting entries for cancelled receipt
     */
    protected function reverseAccountingEntries(PaymentReceipt $receipt): void
    {
        if (!$receipt->journal_entry_id) {
            return;
        }

        $originalEntry = $receipt->journalEntry;

        // Create reversal journal entry
        $reversalEntry = JournalEntry::create([
            'entry_number' => $this->generateJournalEntryNumber(),
            'entry_date' => now()->toDateString(),
            'reference' => $receipt->receipt_number . '-REV',
            'description' => "Reversal of cancelled receipt: {$originalEntry->description}",
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
            if ($account) {
                $account->updateCurrentBalance();
            }
        }

        // Revert Customer Wallet Balance if credited on deposit
        $this->syncCustomerWalletOnReversal($receipt);
    }

    /**
     * Credit customer's wallet balance and allocate to pending sales for customer payment receipts on deposit.
     */
    protected function syncCustomerWalletOnDeposit(PaymentReceipt $receipt): void
    {
        if (in_array($receipt->receipt_type, ['customer_payment', 'customer_advance']) && $receipt->payer_id) {
            $customer = Customer::find($receipt->payer_id);
            if ($customer) {
                $allocatedAmount = 0;
                $finalAllocations = [];
                
                // If user provided specific invoice allocations from the frontend
                if (!empty($receipt->invoice_allocations) && is_array($receipt->invoice_allocations)) {
                    foreach ($receipt->invoice_allocations as $alloc) {
                        $amt = (float) ($alloc['amount'] ?? 0);
                        $saleId = $alloc['invoice_id'] ?? $alloc['sale_id'] ?? null;
                        
                        if ($saleId && $amt > 0) {
                            $sale = Sale::find($saleId);
                            if ($sale) {
                                $due = max(0, (float)$sale->total_amount - (float)$sale->paid_amount);
                                $deduct = min($amt, $due);
                                if ($deduct > 0) {
                                    $sale->paid_amount = (float)$sale->paid_amount + $deduct;
                                    if ($sale->paid_amount >= $sale->total_amount) {
                                        $sale->status = 'completed';
                                    }
                                    $sale->save();
                                    $allocatedAmount += $deduct;
                                    
                                    $finalAllocations[] = [
                                        'sale_id' => $sale->id,
                                        'amount' => $deduct
                                    ];
                                }
                            }
                        }
                    }
                }
                
                $remainingToAllocate = max(0, (float) $receipt->amount - $allocatedAmount);
                
                // If there's still money remaining, auto-allocate to oldest open sales
                if ($remainingToAllocate > 0) {
                    $openSales = Sale::where('customer_id', $customer->id)
                        ->where('status', 'pending')
                        ->orderBy('sale_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
                        
                    foreach ($openSales as $sale) {
                        if ($remainingToAllocate <= 0) break;
                        
                        $due = max(0, (float)$sale->total_amount - (float)$sale->paid_amount);
                        if ($due <= 0) continue;
                        
                        $deduct = min($remainingToAllocate, $due);
                        
                        $sale->paid_amount = (float)$sale->paid_amount + $deduct;
                        if ($sale->paid_amount >= $sale->total_amount) {
                            $sale->status = 'completed';
                        }
                        $sale->save();
                        
                        $remainingToAllocate -= $deduct;
                        $allocatedAmount += $deduct;
                        
                        $finalAllocations[] = [
                            'sale_id' => $sale->id,
                            'amount' => $deduct
                        ];
                    }
                }
                
                // Update receipt with actual applied allocations so we can reverse them later
                if (!empty($finalAllocations)) {
                    $receipt->invoice_allocations = $finalAllocations;
                    $receipt->saveQuietly();
                }

                // Any remaining excess payment goes to wallet balance (advance)
                if ($remainingToAllocate > 0) {
                    $customer->creditWallet($remainingToAllocate);
                }
            }
        }
    }

    /**
     * Debit/revert customer's wallet balance when payment receipt is cancelled or deleted.
     */
    protected function syncCustomerWalletOnReversal(PaymentReceipt $receipt): void
    {
        if (in_array($receipt->receipt_type, ['customer_payment', 'customer_advance']) && $receipt->payer_id) {
            $customer = Customer::find($receipt->payer_id);
            if ($customer) {
                $allocatedAmount = 0;
                
                // Revert actual allocations applied to sales
                if (!empty($receipt->invoice_allocations) && is_array($receipt->invoice_allocations)) {
                    foreach ($receipt->invoice_allocations as $alloc) {
                        $amt = (float) ($alloc['amount'] ?? 0);
                        $saleId = $alloc['sale_id'] ?? $alloc['invoice_id'] ?? null;
                        
                        if ($saleId && $amt > 0) {
                            $sale = Sale::find($saleId);
                            if ($sale) {
                                $sale->paid_amount = max(0, (float)$sale->paid_amount - $amt);
                                if ($sale->paid_amount < $sale->total_amount && $sale->status === 'completed') {
                                    $sale->status = 'pending';
                                }
                                $sale->save();
                                $allocatedAmount += $amt;
                            }
                        }
                    }
                }
                
                $unallocated = max(0, (float) $receipt->amount - $allocatedAmount);
                if ($unallocated > 0) {
                    $customer->debitWallet($unallocated);
                }
            }
        }
    }

    /**
     * Get journal entry description based on receipt type
     */
    protected function getJournalEntryDescription(PaymentReceipt $receipt): string
    {
        $descriptions = [
            'customer_payment' => "Payment received from customer: {$receipt->payer_name}",
            'customer_advance' => "Advance payment from customer: {$receipt->payer_name}",
            'supplier_refund' => "Refund from supplier: {$receipt->payer_name}",
            'supplier_rebate' => "Rebate from supplier: {$receipt->payer_name}",
            'interest_income' => "Interest income received",
            'rental_income' => "Rental income received",
            'commission_income' => "Commission income received",
            'asset_sale' => "Proceeds from asset sale",
            'bank_transfer_in' => "Bank transfer received",
            'cash_deposit' => "Cash deposited to bank",
            'miscellaneous_income' => "Miscellaneous income: {$receipt->description}",
            'other_receipt' => "Other receipt: {$receipt->description}",
        ];

        return $descriptions[$receipt->receipt_type] ?? "Receipt: {$receipt->description}";
    }

    /**
     * Get bank transaction description
     */
    protected function getBankTransactionDescription(PaymentReceipt $receipt): string
    {
        return $this->getJournalEntryDescription($receipt);
    }

    /**
     * Get partner type class name
     */
    protected function getPartnerTypeClass(?string $payerType): ?string
    {
        if (!$payerType) return null;
        $classes = [
            'customer' => 'App\\Models\\Customer',
            'supplier' => 'App\\Models\\Supplier',
        ];

        return $classes[$payerType] ?? null;
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
            ->where('entry_number', 'like', $prefix . '%')
                               ->whereMonth('created_at', $month)
                               ->orderBy('id', 'desc')
                               ->first();

        $sequence = $lastEntry ? (int) substr($lastEntry->entry_number, -4) + 1 : 1;

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    // Account helper methods
    protected function getCustomerAdvancesAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '2120'],
            [
                'account_name' => 'Customer Advances',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Advance payments received from customers',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    protected function getInterestIncomeAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '4100'],
            [
                'account_name' => 'Interest Income',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Interest earned on bank deposits and investments',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    protected function getRentalIncomeAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '4200'],
            [
                'account_name' => 'Rental Income',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Income from property rentals',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    protected function getCommissionIncomeAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '4300'],
            [
                'account_name' => 'Commission Income',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Commission income from sales and services',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    protected function getAssetSaleAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '4400'],
            [
                'account_name' => 'Gain on Asset Sale',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Gains from sale of fixed assets',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    protected function getBankTransferAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '1020'],
            [
                'account_name' => 'Bank Transfer Clearing',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Temporary account for bank transfers',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    protected function getCashAccount(): Account
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

    protected function getMiscellaneousIncomeAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '4999'],
            [
                'account_name' => 'Miscellaneous Income',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Other miscellaneous income',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }
}
