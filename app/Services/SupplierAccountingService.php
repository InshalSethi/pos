<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplierAccountingService
{
    /**
     * Create journal entry for purchase order
     */
    public function createPurchaseOrderJournalEntry(PurchaseOrder $purchaseOrder): JournalEntry
    {
        return DB::transaction(function () use ($purchaseOrder) {
            // Get accounts
            $inventoryAccount = $this->getInventoryAccount();
            $accountsPayableAccount = $this->getAccountsPayableAccount();
            $taxPayableAccount = $this->getTaxPayableAccount();

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => $purchaseOrder->order_date,
                'reference' => $purchaseOrder->po_number,
                'description' => "Purchase from {$purchaseOrder->supplier->display_name}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $purchaseOrder->total_amount,
                'total_credit' => $purchaseOrder->total_amount,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Debit: Inventory
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'description' => 'Inventory purchase',
                'debit_amount' => $purchaseOrder->subtotal,
                'credit_amount' => 0,
                'partner_type' => 'App\Models\Supplier',
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // Debit: Tax (if any)
            if ($purchaseOrder->tax_amount > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxPayableAccount->id,
                    'description' => 'Purchase tax',
                    'debit_amount' => $purchaseOrder->tax_amount,
                    'credit_amount' => 0,
                    'partner_type' => 'App\Models\Supplier',
                    'partner_id' => $purchaseOrder->supplier_id,
                ]);
            }

            // Credit: Accounts Payable
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $accountsPayableAccount->id,
                'description' => 'Accounts payable',
                'debit_amount' => 0,
                'credit_amount' => $purchaseOrder->total_amount,
                'partner_type' => 'App\Models\Supplier',
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // Post the journal entry
            $this->postJournalEntry($journalEntry);

            return $journalEntry;
        });
    }

    /**
     * Create journal entry for supplier payment
     */
    public function createSupplierPaymentJournalEntry(
        Supplier $supplier, 
        float $paymentAmount, 
        string $reference,
        string $paymentMethod = 'bank_transfer'
    ): JournalEntry {
        return DB::transaction(function () use ($supplier, $paymentAmount, $reference, $paymentMethod) {
            // Get accounts
            $accountsPayableAccount = $this->getAccountsPayableAccount();
            $cashAccount = $this->getCashAccount();
            $bankAccount = $this->getBankAccount();

            $paymentAccount = $paymentMethod === 'cash' ? $cashAccount : $bankAccount;

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => now(),
                'reference' => $reference,
                'description' => "Payment to {$supplier->display_name}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $paymentAmount,
                'total_credit' => $paymentAmount,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Debit: Accounts Payable
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $accountsPayableAccount->id,
                'description' => 'Payment to supplier',
                'debit_amount' => $paymentAmount,
                'credit_amount' => 0,
                'partner_type' => 'App\Models\Supplier',
                'partner_id' => $supplier->id,
            ]);

            // Credit: Cash/Bank
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $paymentAccount->id,
                'description' => 'Payment made',
                'debit_amount' => 0,
                'credit_amount' => $paymentAmount,
                'partner_type' => 'App\Models\Supplier',
                'partner_id' => $supplier->id,
            ]);

            // Post the journal entry
            $this->postJournalEntry($journalEntry);

            return $journalEntry;
        });
    }

    /**
     * Get supplier balance from journal entries
     */
    public function getSupplierBalance(Supplier $supplier, $asOfDate = null): float
    {
        $query = JournalEntryLine::where('partner_type', 'App\Models\Supplier')
                                ->where('partner_id', $supplier->id)
                                ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                                    $q->where('status', 'posted');
                                    if ($asOfDate) {
                                        $q->where('entry_date', '<=', $asOfDate);
                                    }
                                });

        $totalDebits = $query->sum('debit_amount');
        $totalCredits = $query->sum('credit_amount');

        // For suppliers, credits increase the balance (what we owe them)
        return $totalCredits - $totalDebits;
    }

    /**
     * Get supplier transaction history
     */
    public function getSupplierTransactionHistory(Supplier $supplier, $startDate = null, $endDate = null): array
    {
        $query = JournalEntryLine::where('partner_type', 'App\Models\Supplier')
                                ->where('partner_id', $supplier->id)
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
            $runningBalance += $line->credit_amount - $line->debit_amount;
            
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
     * Get accounts payable aging for supplier
     */
    public function getSupplierAging(Supplier $supplier, $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();
        
        // Get unpaid purchase orders
        $unpaidOrders = PurchaseOrder::where('supplier_id', $supplier->id)
                                   ->where('status', 'pending')
                                   ->where('order_date', '<=', $asOfDate)
                                   ->get();

        $aging = [
            'current' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'over_90' => 0
        ];

        foreach ($unpaidOrders as $order) {
            $daysOverdue = Carbon::parse($asOfDate)->diffInDays($order->order_date);
            $outstandingAmount = $order->total_amount;

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
     * Get or create inventory account
     */
    private function getInventoryAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '1300'],
            [
                'account_name' => 'Inventory',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Inventory assets',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get or create accounts payable account
     */
    private function getAccountsPayableAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '2000'],
            [
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Supplier accounts payable',
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
     * Get or create bank account
     */
    private function getBankAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '1010'],
            [
                'account_name' => 'Bank Account',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Bank checking account',
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
