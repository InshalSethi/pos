<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoubleEntryAccountingService
{
    protected $accountingSettings;

    public function __construct()
    {
        $this->accountingSettings = AccountingSetting::getSettings();
    }

    /**
     * Create journal entry for sales invoice
     */
    public function createSalesInvoiceEntry(Sale $sale): ?JournalEntry
    {
        if (!$this->accountingSettings->sales_invoice_revenue_account_id || 
            !$this->accountingSettings->sales_invoice_receivable_account_id) {
            return null;
        }

        return DB::transaction(function () use ($sale) {
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateEntryNumber('SI'),
                'entry_date' => $sale->sale_date,
                'reference' => "Sales Invoice #{$sale->sale_number}",
                'description' => "Sale to " . ($sale->customer->name ?? 'Walk-in Customer'),
                'entry_type' => 'sales_invoice',
                'status' => 'posted',
                'total_debit' => $sale->total_amount,
                'total_credit' => $sale->total_amount,
                'created_by' => $sale->user_id,
                'posted_by' => $sale->user_id,
                'posted_at' => now(),
            ]);

            // Debit: Accounts Receivable (or Cash if paid)
            $receivableAccount = $sale->payment_method === 'cash' 
                ? $this->accountingSettings->cash_account_id 
                : $this->accountingSettings->sales_invoice_receivable_account_id;

            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $receivableAccount,
                'description' => "Sales Invoice #{$sale->sale_number}",
                'debit_amount' => $sale->total_amount,
                'credit_amount' => 0,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // Credit: Sales Revenue
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->accountingSettings->sales_invoice_revenue_account_id,
                'description' => "Sales Revenue - Invoice #{$sale->sale_number}",
                'debit_amount' => 0,
                'credit_amount' => $sale->subtotal,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // Credit: Tax Account (if tax exists)
            if ($sale->tax_amount > 0 && $this->accountingSettings->sales_invoice_tax_account_id) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $this->accountingSettings->sales_invoice_tax_account_id,
                    'description' => "Sales Tax - Invoice #{$sale->sale_number}",
                    'debit_amount' => 0,
                    'credit_amount' => $sale->tax_amount,
                    'partner_type' => Customer::class,
                    'partner_id' => $sale->customer_id,
                ]);
            }

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        });
    }

    /**
     * Create journal entry for sales return
     */
    public function createSalesReturnEntry(Sale $saleReturn): ?JournalEntry
    {
        if (!$this->accountingSettings->sales_return_revenue_account_id || 
            !$this->accountingSettings->sales_return_receivable_account_id) {
            return null;
        }

        return DB::transaction(function () use ($saleReturn) {
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateEntryNumber('SR'),
                'entry_date' => $saleReturn->sale_date,
                'reference' => "Sales Return #{$saleReturn->sale_number}",
                'description' => "Return from " . ($saleReturn->customer->name ?? 'Walk-in Customer'),
                'entry_type' => 'sales_return',
                'status' => 'posted',
                'total_debit' => $saleReturn->total_amount,
                'total_credit' => $saleReturn->total_amount,
                'created_by' => $saleReturn->user_id,
                'posted_by' => $saleReturn->user_id,
                'posted_at' => now(),
            ]);

            // Debit: Sales Returns (contra-revenue account)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->accountingSettings->sales_return_revenue_account_id,
                'description' => "Sales Return #{$saleReturn->sale_number}",
                'debit_amount' => $saleReturn->subtotal,
                'credit_amount' => 0,
                'partner_type' => Customer::class,
                'partner_id' => $saleReturn->customer_id,
            ]);

            // Debit: Tax Account (if tax exists)
            if ($saleReturn->tax_amount > 0 && $this->accountingSettings->sales_return_tax_account_id) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $this->accountingSettings->sales_return_tax_account_id,
                    'description' => "Sales Tax Return #{$saleReturn->sale_number}",
                    'debit_amount' => $saleReturn->tax_amount,
                    'credit_amount' => 0,
                    'partner_type' => Customer::class,
                    'partner_id' => $saleReturn->customer_id,
                ]);
            }

            // Credit: Accounts Receivable (or Cash if refunded)
            $receivableAccount = $saleReturn->payment_method === 'cash' 
                ? $this->accountingSettings->cash_account_id 
                : $this->accountingSettings->sales_return_receivable_account_id;

            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $receivableAccount,
                'description' => "Sales Return Refund #{$saleReturn->sale_number}",
                'debit_amount' => 0,
                'credit_amount' => $saleReturn->total_amount,
                'partner_type' => Customer::class,
                'partner_id' => $saleReturn->customer_id,
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        });
    }

    /**
     * Create journal entry for purchase invoice
     */
    public function createPurchaseInvoiceEntry(PurchaseOrder $purchaseOrder): ?JournalEntry
    {
        if (!$this->accountingSettings->purchase_invoice_expense_account_id || 
            !$this->accountingSettings->purchase_invoice_payable_account_id) {
            return null;
        }

        return DB::transaction(function () use ($purchaseOrder) {
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateEntryNumber('PI'),
                'entry_date' => $purchaseOrder->order_date,
                'reference' => "Purchase Order #{$purchaseOrder->po_number}",
                'description' => "Purchase from {$purchaseOrder->supplier->name}",
                'entry_type' => 'purchase_invoice',
                'status' => 'posted',
                'total_debit' => $purchaseOrder->total_amount,
                'total_credit' => $purchaseOrder->total_amount,
                'created_by' => $purchaseOrder->user_id,
                'posted_by' => $purchaseOrder->user_id,
                'posted_at' => now(),
            ]);

            // Debit: Expense/Inventory Account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->accountingSettings->purchase_invoice_expense_account_id,
                'description' => "Purchase #{$purchaseOrder->po_number}",
                'debit_amount' => $purchaseOrder->subtotal,
                'credit_amount' => 0,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // Debit: Tax Account (if tax exists)
            if ($purchaseOrder->tax_amount > 0 && $this->accountingSettings->purchase_invoice_tax_account_id) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $this->accountingSettings->purchase_invoice_tax_account_id,
                    'description' => "Purchase Tax #{$purchaseOrder->po_number}",
                    'debit_amount' => $purchaseOrder->tax_amount,
                    'credit_amount' => 0,
                    'partner_type' => Supplier::class,
                    'partner_id' => $purchaseOrder->supplier_id,
                ]);
            }

            // Credit: Accounts Payable
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->accountingSettings->purchase_invoice_payable_account_id,
                'description' => "Purchase Payable #{$purchaseOrder->po_number}",
                'debit_amount' => 0,
                'credit_amount' => $purchaseOrder->total_amount,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        });
    }

    /**
     * Create journal entry for expense
     */
    public function createExpenseEntry(Expense $expense): ?JournalEntry
    {
        if (!$this->accountingSettings->expense_default_account_id) {
            return null;
        }

        return DB::transaction(function () use ($expense) {
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateEntryNumber('EX'),
                'entry_date' => $expense->expense_date,
                'reference' => "Expense #{$expense->expense_number}",
                'description' => $expense->title,
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $expense->amount,
                'total_credit' => $expense->amount,
                'created_by' => $expense->user_id,
                'posted_by' => $expense->user_id,
                'posted_at' => now(),
            ]);

            // Debit: Expense Account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->accountingSettings->expense_default_account_id,
                'description' => $expense->title,
                'debit_amount' => $expense->amount,
                'credit_amount' => 0,
            ]);

            // Credit: Cash or Accounts Payable
            $creditAccount = $expense->payment_method === 'cash' 
                ? $this->accountingSettings->cash_account_id 
                : $this->accountingSettings->expense_payable_account_id;

            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccount,
                'description' => "Payment for {$expense->title}",
                'debit_amount' => 0,
                'credit_amount' => $expense->amount,
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        });
    }

    /**
     * Create journal entry for purchase return
     */
    public function createPurchaseReturnEntry(PurchaseReturn $purchaseReturn): ?JournalEntry
    {
        if (!$this->accountingSettings->purchase_return_expense_account_id ||
            !$this->accountingSettings->purchase_return_payable_account_id) {
            return null;
        }

        return DB::transaction(function () use ($purchaseReturn) {
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateEntryNumber('PRR'),
                'entry_date' => $purchaseReturn->return_date,
                'reference' => "Purchase Return #{$purchaseReturn->return_number}",
                'description' => "Return to {$purchaseReturn->supplier->name}",
                'entry_type' => 'purchase_return',
                'status' => 'posted',
                'total_debit' => $purchaseReturn->total_amount,
                'total_credit' => $purchaseReturn->total_amount,
                'created_by' => $purchaseReturn->user_id,
                'posted_by' => $purchaseReturn->user_id,
                'posted_at' => now(),
            ]);

            // Debit: Accounts Payable (reducing liability)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->accountingSettings->purchase_return_payable_account_id,
                'description' => "Purchase Return Payable #{$purchaseReturn->return_number}",
                'debit_amount' => $purchaseReturn->total_amount,
                'credit_amount' => 0,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseReturn->supplier_id,
            ]);

            // Credit: Purchase Returns (reducing expense)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->accountingSettings->purchase_return_expense_account_id,
                'description' => "Purchase Return #{$purchaseReturn->return_number}",
                'debit_amount' => 0,
                'credit_amount' => $purchaseReturn->total_amount,
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        });
    }

    /**
     * Generate journal entry number
     */
    private function generateEntryNumber(string $prefix): string
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $lastEntry = JournalEntry::where('entry_number', 'like', "{$prefix}{$year}{$month}%")
                                ->orderBy('id', 'desc')
                                ->first();

        $sequence = $lastEntry ? (int) substr($lastEntry->entry_number, -4) + 1 : 1;

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    /**
     * Update account balances
     */
    private function updateAccountBalances(JournalEntry $journalEntry): void
    {
        foreach ($journalEntry->journalEntryLines as $line) {
            if ($line->account) {
                $line->account->updateCurrentBalance();
            }
        }
    }
}
