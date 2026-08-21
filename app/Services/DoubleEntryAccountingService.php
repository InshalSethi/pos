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
use App\Models\BankAccount;
use App\Models\BankTransaction;
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
     * Create journal entry for sales invoice (Delegates to atomic accounting handler)
     */
    public function createSalesInvoiceEntry(Sale $sale): ?JournalEntry
    {
        return $this->processSalesInvoiceAccounting($sale, $sale->payment_details ?? []);
    }

    /**
     * Reverse all accounting entries and balances for an existing sales invoice before updating
     */
    public function reverseSalesInvoiceAccounting(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $companyId = $sale->company_id ?: auth()->user()?->current_company_id;

            // 1. Revert Old Journal Entries & Line Balances
            $oldEntries = JournalEntry::where('company_id', $companyId)
                ->where('source_type', 'sale')
                ->where('source_id', $sale->id)
                ->get();

            $affectedAccountIds = [];

            foreach ($oldEntries as $entry) {
                foreach ($entry->journalEntryLines as $line) {
                    if ($line->account_id) {
                        $affectedAccountIds[] = $line->account_id;
                    }
                }
                JournalEntryLine::where('journal_entry_id', $entry->id)->delete();
                $entry->delete();
            }

            // Recalculate ground-truth balances for affected Chart of Accounts ledgers
            foreach (array_unique($affectedAccountIds) as $accId) {
                $acc = Account::find($accId);
                if ($acc) {
                    $acc->updateCurrentBalance();
                }
            }

            // 2. Revert Old Bank Transactions & Bank Account Balances
            $oldBankTxs = \App\Models\BankTransaction::where('company_id', $companyId)
                ->where('reference_number', $sale->sale_number)
                ->get();

            foreach ($oldBankTxs as $tx) {
                $bAccount = \App\Models\BankAccount::find($tx->bank_account_id);
                if ($bAccount) {
                    if ($tx->transaction_type === 'debit') {
                        $bAccount->decrement('current_balance', $tx->amount);
                    } elseif ($tx->transaction_type === 'credit') {
                        $bAccount->increment('current_balance', $tx->amount);
                    }
                    if ($bAccount->chartAccount) {
                        $bAccount->chartAccount->updateCurrentBalance();
                    }
                }
                $tx->delete();
            }
        });
    }

    /**
     * Reverse all GL journal entries and recalculate account balances for a given source entity.
     */
    public function reverseJournalEntryBySource(string $sourceType, int $sourceId): void
    {
        DB::transaction(function () use ($sourceType, $sourceId) {
            $oldEntries = JournalEntry::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->get();

            $affectedAccountIds = [];

            foreach ($oldEntries as $entry) {
                foreach ($entry->journalEntryLines as $line) {
                    if ($line->account_id) {
                        $affectedAccountIds[] = $line->account_id;
                    }
                }
                JournalEntryLine::where('journal_entry_id', $entry->id)->delete();
                $entry->delete();
            }

            // Recalculate ground-truth balances for affected Chart of Accounts ledgers
            foreach (array_unique($affectedAccountIds) as $accId) {
                $acc = Account::find($accId);
                if ($acc) {
                    $acc->updateCurrentBalance();
                }
            }
        });
    }

    /**
     * Atomic Multi-Payment Double-Entry Accounting Posting for Sales Invoice
     */
    public function processSalesInvoiceAccounting(Sale $sale, array $payments = []): ?JournalEntry
    {
        return DB::transaction(function () use ($sale, $payments) {
            $companyId = $sale->company_id ?: auth()->user()?->current_company_id;
            $grandTotal = (float) $sale->total_amount;
            $subtotal = (float) ($sale->subtotal > 0 ? $sale->subtotal : $sale->total_amount);
            $taxAmount = (float) ($sale->tax_amount > 0 ? $sale->tax_amount : 0);
            $discountAmount = (float) ($sale->discount_amount > 0 ? $sale->discount_amount : 0);

            // 1. Resolve Parent Revenue Account (4000 - Total Sales Revenue)
            $parentRevenueAccount = Account::firstOrCreate([
                'company_id' => $companyId,
                'account_code' => '4000',
            ], [
                'account_name' => 'Total Sales Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);

            // Determine Wholesale vs Retail Revenue Ledger Posting
            $isWholesale = (
                (isset($sale->sale_type) && strtolower($sale->sale_type) === 'wholesale') ||
                !empty($sale->is_wholesale)
            );

            if ($isWholesale) {
                $revenueAccount = Account::firstOrCreate([
                    'company_id' => $companyId,
                    'account_code' => '4020',
                ], [
                    'account_name' => 'Wholesale Sales Revenue',
                    'account_type' => 'revenue',
                    'account_subtype' => 'operating_revenue',
                    'parent_account_id' => $parentRevenueAccount->id,
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            } else {
                $revenueAccount = Account::firstOrCreate([
                    'company_id' => $companyId,
                    'account_code' => '4010',
                ], [
                    'account_name' => 'Retail Sales Revenue',
                    'account_type' => 'revenue',
                    'account_subtype' => 'operating_revenue',
                    'parent_account_id' => $parentRevenueAccount->id,
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }
            $revenueAccountId = $revenueAccount->id;

            // Total journal entry amount = Gross Subtotal + Tax
            $entryTotal = $subtotal + $taxAmount;

            // Create Single Journal Entry for the Sales Invoice
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('SI'),
                'entry_date' => $sale->sale_date ?? now()->toDateString(),
                'reference' => "Sales Invoice #{$sale->sale_number}",
                'description' => "Sales Invoice #{$sale->sale_number} - " . ($sale->customer->name ?? 'Walk-in Customer'),
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $entryTotal,
                'total_credit' => $entryTotal,
                'created_by' => $sale->user_id ?? auth()->id(),
                'posted_by' => $sale->user_id ?? auth()->id(),
                'posted_at' => now(),
                'source_type' => 'sale',
                'source_id' => $sale->id,
            ]);

            // Track affected COA IDs to update current_balance at the end
            $affectedAccountIds = [];

            // Format Payments Array if empty
            if (empty($payments) && $sale->payment_details && is_array($sale->payment_details)) {
                $payments = $sale->payment_details;
            }

            if (empty($payments) && $sale->paid_amount > 0) {
                $payments = [[
                    'type' => ($sale->payment_method === 'bank_transfer' || $sale->payment_method === 'card') ? 'bank' : 'cash',
                    'method' => $sale->payment_method ?? 'cash',
                    'bank_id' => $sale->bank_id ?? null,
                    'amount' => (float) $sale->paid_amount,
                ]];
            }

            $totalPaid = 0;

            // STEP A & B: Process Cash and Bank Payments (DEBIT Cash / Bank COAs)
            if (is_array($payments) && count($payments) > 0) {
                foreach ($payments as $payment) {
                    $type = strtolower($payment['type'] ?? $payment['method'] ?? 'cash');
                    $amount = (float) ($payment['amount'] ?? 0);
                    if ($amount <= 0) continue;

                    if ($type === 'cash') {
                        $totalPaid += $amount;

                        $cashAccount = Account::where('company_id', $companyId)
                            ->where('account_type', 'asset')
                            ->where(function ($q) {
                                $q->where('account_code', '1010')
                                  ->orWhere('account_name', 'LIKE', '%Cash%');
                            })->first();

                        if (!$cashAccount) {
                            $cashAccount = Account::create([
                                'company_id' => $companyId,
                                'account_code' => '1010',
                                'account_name' => 'Cash',
                                'account_type' => 'asset',
                                'opening_balance' => 0,
                                'current_balance' => 0,
                                'is_active' => true,
                                'is_system_account' => true,
                            ]);
                        }

                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $cashAccount->id,
                            'description' => "Cash Payment - Invoice #{$sale->sale_number}",
                            'debit_amount' => $amount,
                            'credit_amount' => 0,
                            'partner_type' => Customer::class,
                            'partner_id' => $sale->customer_id,
                        ]);

                        $affectedAccountIds[] = $cashAccount->id;

                        // Sync Banking Module Default Cash Vault
                        $cashBankAccount = \App\Models\BankAccount::where('company_id', $companyId)
                            ->where(function ($q) use ($cashAccount) {
                                if ($cashAccount) {
                                    $q->where('chart_account_id', $cashAccount->id);
                                }
                                $q->orWhere('account_name', 'LIKE', '%Cash%')
                                  ->orWhere('bank_name', 'LIKE', '%Cash%');
                            })->first();

                        if ($cashBankAccount) {
                            $cashBankAccount->increment('current_balance', $amount);

                            \App\Models\BankTransaction::create([
                                'company_id' => $companyId,
                                'bank_account_id' => $cashBankAccount->id,
                                'transaction_date' => $sale->sale_date ?? now()->toDateString(),
                                'reference_number' => $sale->sale_number,
                                'description' => "Cash Payment for Sales Invoice #{$sale->sale_number}",
                                'transaction_type' => 'debit',
                                'amount' => $amount,
                                'running_balance' => (float)$cashBankAccount->current_balance,
                                'status' => 'cleared',
                            ]);
                        }

                    } elseif ($type === 'bank' || $type === 'card' || $type === 'bank_transfer') {
                        $bankAccountId = isset($payment['bank_id']) ? (int)$payment['bank_id'] : (isset($payment['bank_account_id']) ? (int)$payment['bank_account_id'] : (isset($payment['payment_method_id']) ? (int)$payment['payment_method_id'] : null));
                        $bankAccount = null;
                        if ($bankAccountId) {
                            $bankAccount = \App\Models\BankAccount::find($bankAccountId);
                        }
                        if (!$bankAccount) {
                            $bankAccount = \App\Models\BankAccount::where('company_id', $companyId)
                                ->where(function ($q) {
                                    $q->where('account_name', 'NOT LIKE', '%Cash%')
                                      ->where('bank_name', 'NOT LIKE', '%Cash%');
                                })->first();
                        }

                        if ($bankAccount) {
                            $totalPaid += $amount;

                            $bankAccount->increment('current_balance', $amount);

                            \App\Models\BankTransaction::create([
                                'company_id' => $companyId,
                                'bank_account_id' => $bankAccount->id,
                                'transaction_date' => $sale->sale_date ?? now()->toDateString(),
                                'reference_number' => $sale->sale_number,
                                'description' => "Bank Payment ({$bankAccount->account_name}) - Invoice #{$sale->sale_number}",
                                'transaction_type' => 'debit',
                                'amount' => $amount,
                                'running_balance' => (float)$bankAccount->current_balance,
                                'status' => 'cleared',
                            ]);

                            $bankChartAccount = null;
                            if ($bankAccount->chart_account_id) {
                                $bankChartAccount = Account::where('company_id', $companyId)->find($bankAccount->chart_account_id);
                            }

                            if (!$bankChartAccount) {
                                $bankChartAccount = Account::where('company_id', $companyId)
                                    ->where('account_type', 'asset')
                                    ->where(function ($q) use ($bankAccount) {
                                        $q->where('account_name', 'LIKE', "%{$bankAccount->bank_name}%")
                                          ->orWhere('account_name', 'LIKE', "%{$bankAccount->account_name}%")
                                          ->orWhere('account_code', '1600')
                                          ->orWhere('account_code', '1020');
                                    })->first();
                            }

                            if (!$bankChartAccount) {
                                $bankChartAccount = Account::create([
                                    'company_id' => $companyId,
                                    'account_code' => '1600',
                                    'account_name' => $bankAccount->account_name . ' (' . ($bankAccount->bank_name ?: 'Bank') . ')',
                                    'account_type' => 'asset',
                                    'opening_balance' => $bankAccount->opening_balance ?? 0,
                                    'current_balance' => 0,
                                    'is_active' => true,
                                ]);
                            }

                            if ($bankAccount->chart_account_id !== $bankChartAccount->id) {
                                $bankAccount->update(['chart_account_id' => $bankChartAccount->id]);
                            }

                            JournalEntryLine::create([
                                'journal_entry_id' => $journalEntry->id,
                                'account_id' => $bankChartAccount->id,
                                'description' => "Bank Payment ({$bankAccount->account_name}) - Invoice #{$sale->sale_number}",
                                'debit_amount' => $amount,
                                'credit_amount' => 0,
                                'partner_type' => Customer::class,
                                'partner_id' => $sale->customer_id,
                            ]);

                            $affectedAccountIds[] = $bankChartAccount->id;
                        }
                    }
                }
            }

            // STEP C: Handle Unpaid Portion -> Accounts Receivable (1030) ONLY if remaining due > 0
            $remainingDue = max(0, $grandTotal - $totalPaid);
            if ($remainingDue > 0) {
                $arAccount = Account::where('company_id', $companyId)
                    ->where('account_type', 'asset')
                    ->where(function ($q) {
                        $q->where('account_code', '1030')
                          ->orWhere('account_name', 'LIKE', '%Accounts Receivable%');
                    })->first();

                if (!$arAccount) {
                    $arAccount = Account::create([
                        'company_id' => $companyId,
                        'account_code' => '1030',
                        'account_name' => 'Accounts Receivable',
                        'account_type' => 'asset',
                        'opening_balance' => 0,
                        'current_balance' => 0,
                        'is_active' => true,
                        'is_system_account' => true,
                    ]);
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $arAccount->id,
                    'description' => "Accounts Receivable (Unpaid Balance) - Invoice #{$sale->sale_number}",
                    'debit_amount' => $remainingDue,
                    'credit_amount' => 0,
                    'partner_type' => Customer::class,
                    'partner_id' => $sale->customer_id,
                ]);

                $affectedAccountIds[] = $arAccount->id;
            }

            // STEP D: DEBIT Sales Discounts (4030) if discount > 0
            if ($discountAmount > 0) {
                $discountAccount = Account::where('company_id', $companyId)
                    ->where(function ($q) {
                        $q->where('account_code', '4030')
                          ->orWhere('account_name', 'LIKE', '%Sales Discount%')
                          ->orWhere('account_name', 'LIKE', '%Discount Allowed%')
                          ->orWhere('account_name', 'LIKE', '%Discount%');
                    })->first();

                if (!$discountAccount) {
                    $discountAccount = Account::create([
                        'company_id' => $companyId,
                        'account_code' => '4030',
                        'account_name' => 'Sales Discounts',
                        'account_type' => 'revenue',
                        'account_subtype' => 'sales_discount',
                        'opening_balance' => 0,
                        'current_balance' => 0,
                        'is_active' => true,
                        'is_system_account' => true,
                    ]);
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $discountAccount->id,
                    'description' => "Sales Discount - Invoice #{$sale->sale_number}",
                    'debit_amount' => $discountAmount,
                    'credit_amount' => 0,
                    'partner_type' => Customer::class,
                    'partner_id' => $sale->customer_id,
                ]);

                $affectedAccountIds[] = $discountAccount->id;
            }

            // STEP E: CREDIT Sales Revenue (4010) (Gross Subtotal)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $revenueAccountId,
                'description' => "Sales Revenue - Invoice #{$sale->sale_number}",
                'debit_amount' => 0,
                'credit_amount' => $subtotal,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            $affectedAccountIds[] = $revenueAccountId;

            // STEP F: CREDIT Sales Tax Payable (2020) if tax > 0
            if ($taxAmount > 0) {
                $taxAccountId = $this->accountingSettings->sales_invoice_tax_account_id;
                $taxAccount = null;
                if ($taxAccountId) {
                    $taxAccount = Account::find($taxAccountId);
                }
                if (!$taxAccount) {
                    $taxAccount = Account::where('company_id', $companyId)
                        ->where(function ($q) {
                            $q->where('account_code', '2020')
                              ->orWhere('account_type', 'tax_payable')
                              ->orWhere('account_name', 'LIKE', '%Sales Tax%')
                              ->orWhere('account_name', 'LIKE', '%Tax Payable%');
                        })->first();
                }

                if (!$taxAccount) {
                    $taxAccount = Account::create([
                        'company_id' => $companyId,
                        'account_code' => '2020',
                        'account_name' => 'Sales Tax Payable',
                        'account_type' => 'liability',
                        'account_subtype' => 'current_liability',
                        'opening_balance' => 0,
                        'current_balance' => 0,
                        'is_active' => true,
                        'is_system_account' => true,
                    ]);
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccount->id,
                    'description' => "Sales Tax Payable - Invoice #{$sale->sale_number}",
                    'debit_amount' => 0,
                    'credit_amount' => $taxAmount,
                    'partner_type' => Customer::class,
                    'partner_id' => $sale->customer_id,
                ]);

                $affectedAccountIds[] = $taxAccount->id;
            }

            // Recalculate exact balances for all affected accounts
            foreach (array_unique($affectedAccountIds) as $accId) {
                $acc = Account::find($accId);
                if ($acc) {
                    $acc->updateCurrentBalance();
                }
            }

            // Process COGS / Inventory
            $this->processPerpetualInventoryCOGS($sale);

            return $journalEntry;
        });
    }

    /**
     * Process Perpetual Inventory System COGS & 1040 Inventory deduction for sales invoice
     * 
     * Double-Entry Journal Entry:
     *  Debit:  'Cost of Goods Sold' (Expense Account 5010) (Purchase Price * Quantity)
     *  Credit: '1040 Inventory' (Asset Account 1040)       (Purchase Price * Quantity)
     */
    public function processPerpetualInventoryCOGS(Sale $sale): ?JournalEntry
    {
        $companyId = $sale->company_id ?: auth()->user()?->current_company_id;

        // Load sale items with product and variation relationships
        $sale->unsetRelation('saleItems');
        $sale->load(['saleItems.product', 'saleItems.variation']);

        $totalCogs = 0;
        foreach ($sale->saleItems as $item) {
            // Determine purchase price (cost price) of item from variation or product
            $costPrice = (float) (
                $item->variation?->cost_price 
                ?? $item->product?->cost_price 
                ?? $item->product?->purchase_price 
                ?? 0
            );
            $totalCogs += $costPrice * (float) $item->quantity;
        }

        if ($totalCogs <= 0) {
            return null;
        }

        // Check if COGS entry already posted for this sale to avoid duplicates
        $existingEntry = JournalEntry::where('source_type', 'sale')
            ->where('source_id', $sale->id)
            ->where('reference', "COGS - Invoice #{$sale->sale_number}")
            ->first();

        if ($existingEntry) {
            return $existingEntry;
        }

        return DB::transaction(function () use ($sale, $companyId, $totalCogs) {
            // 1. Resolve '1040 Inventory' Asset Account
            $inventoryAccount = Account::withTrashed()
                ->where('company_id', $companyId)
                ->where('account_code', '1040')
                ->first()
                ?? Account::withTrashed()
                ->where('company_id', $companyId)
                ->where('account_type', 'asset')
                ->where('account_name', 'LIKE', '%Inventory%')
                ->first();

            if ($inventoryAccount && $inventoryAccount->trashed()) {
                $inventoryAccount->restore();
            }

            if (!$inventoryAccount) {
                $inventoryAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1040',
                    'account_name' => 'Inventory',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }

            // 2. Resolve 'Cost of Goods Sold' Expense Account
            $cogsAccount = Account::withTrashed()
                ->where('company_id', $companyId)
                ->where('account_code', '5010')
                ->first()
                ?? Account::withTrashed()
                ->where('company_id', $companyId)
                ->where('account_type', 'expense')
                ->where(function ($q) {
                    $q->where('account_name', 'LIKE', '%Cost of Goods Sold%')
                      ->orWhere('account_name', 'LIKE', '%COGS%');
                })->first();

            if ($cogsAccount && $cogsAccount->trashed()) {
                $cogsAccount->restore();
            }

            if (!$cogsAccount) {
                $cogsAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '5010',
                    'account_name' => 'Cost of Goods Sold',
                    'account_type' => 'expense',
                    'account_subtype' => 'cost_of_goods_sold',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }

            // 3. Create Double-Entry Journal Entry
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('COGS'),
                'entry_date' => $sale->sale_date ?? now()->toDateString(),
                'reference' => "COGS - Invoice #{$sale->sale_number}",
                'description' => "Cost of Goods Sold deduction for Invoice #{$sale->sale_number}",
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $totalCogs,
                'total_credit' => $totalCogs,
                'created_by' => $sale->user_id ?? auth()->id(),
                'posted_by' => $sale->user_id ?? auth()->id(),
                'posted_at' => now(),
                'source_type' => 'sale',
                'source_id' => $sale->id,
            ]);

            // Debit: Cost of Goods Sold (Expense increases)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cogsAccount->id,
                'description' => "COGS Expense - Invoice #{$sale->sale_number}",
                'debit_amount' => $totalCogs,
                'credit_amount' => 0,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // Credit: 1040 Inventory Asset (Asset decreases)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'description' => "Inventory Asset Deduction - Invoice #{$sale->sale_number}",
                'debit_amount' => 0,
                'credit_amount' => $totalCogs,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // 4. Recalculate exact ground-truth balances in chart_of_accounts
            $cogsAccount->updateCurrentBalance();
            $inventoryAccount->updateCurrentBalance();

            return $journalEntry;
        });
    }

    /**
     * Create double-entry journal entry for initial Item/Product Opening Stock.
     * 
     * Double-Entry Rule:
     *  Debit:  '1040 Inventory' Account (Total Opening Value)
     *  Credit: '3010 Owner's Equity' OR 'Opening Balance Equity' Account (Total Opening Value)
     * 
     * Updates current_balance for both ledgers in chart_of_accounts table within DB Transaction.
     */
    public function createOpeningStockEntry($item): ?JournalEntry
    {
        $quantity = (float) ($item->stock_quantity ?? $item->quantity ?? $item->stock_qty ?? 0);
        $purchasePrice = (float) ($item->cost_price ?? $item->purchase_price ?? 0);

        if ($quantity <= 0 || $purchasePrice <= 0) {
            return null;
        }

        $totalOpeningValue = $quantity * $purchasePrice;

        if ($totalOpeningValue <= 0) {
            return null;
        }

        $companyId = $item->company_id ?: (auth()->user()?->current_company_id ?? 1);

        // Prevent duplicate opening stock entry for the same item/product
        $existingEntry = JournalEntry::where('company_id', $companyId)
            ->where('source_type', 'product_opening_stock')
            ->where('source_id', $item->id)
            ->first();

        if ($existingEntry) {
            return $existingEntry;
        }

        return DB::transaction(function () use ($item, $companyId, $quantity, $purchasePrice, $totalOpeningValue) {
            // 1. Resolve Active Leaf / Child '1040 Inventory' Asset Account
            $inventoryAccount = Account::withoutGlobalScopes()->withTrashed()
                ->where('company_id', $companyId)
                ->where('account_code', '1040')
                ->first()
                ?? Account::withoutGlobalScopes()->withTrashed()
                ->where('company_id', $companyId)
                ->where('account_type', 'asset')
                ->where('account_name', 'LIKE', '%Inventory%')
                ->whereDoesntHave('children')
                ->first();

            if ($inventoryAccount && $inventoryAccount->trashed()) {
                $inventoryAccount->restore();
            }

            if (!$inventoryAccount) {
                $inventoryAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1040',
                    'account_name' => 'Inventory on Hand',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }

            // 2. Resolve Active Leaf / Child '3010 Owner's Equity' Account
            $equityAccount = Account::withoutGlobalScopes()->withTrashed()
                ->where('company_id', $companyId)
                ->where('account_code', '3010')
                ->first()
                ?? Account::withoutGlobalScopes()->withTrashed()
                ->where('company_id', $companyId)
                ->where('account_type', 'equity')
                ->where(function ($q) {
                    $q->where('account_name', 'LIKE', '%Opening Balance Equity%')
                      ->orWhere('account_name', 'LIKE', '%Owner%Equity%')
                      ->orWhere('account_name', 'LIKE', '%Owner%Capital%');
                })
                ->whereDoesntHave('children')
                ->first();

            if ($equityAccount && $equityAccount->trashed()) {
                $equityAccount->restore();
            }

            if (!$equityAccount) {
                $equityAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '3010',
                    'account_name' => "Opening Balance Equity",
                    'account_type' => 'equity',
                    'account_subtype' => 'owner_equity',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }

            // 3. Create Double-Entry Journal Entry
            $itemName = $item->name ?? $item->variation_name_string ?? ('Item #' . $item->id);
            $skuStr = !empty($item->sku) ? " (SKU: {$item->sku})" : '';

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('OS', $companyId),
                'entry_date' => now()->toDateString(),
                'reference' => "Opening Stock - {$itemName}{$skuStr}",
                'description' => "Opening Stock valuation for {$itemName} ({$quantity} units @ {$purchasePrice})",
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $totalOpeningValue,
                'total_credit' => $totalOpeningValue,
                'created_by' => auth()->id() ?: 1,
                'posted_by' => auth()->id() ?: 1,
                'posted_at' => now(),
                'source_type' => 'product_opening_stock',
                'source_id' => $item->id,
            ]);

            // Debit: '1040 Inventory' Account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'description' => "Opening Stock Asset - {$itemName}",
                'debit_amount' => $totalOpeningValue,
                'credit_amount' => 0,
            ]);

            // Credit: '3010 Owner's Equity' Account
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $equityAccount->id,
                'description' => "Opening Stock Equity - {$itemName}",
                'debit_amount' => 0,
                'credit_amount' => $totalOpeningValue,
            ]);

            // 4. Recalculate current_balance & propagate parent rollup for both ledgers
            $inventoryAccount->updateCurrentBalance();
            $equityAccount->updateCurrentBalance();

            return $journalEntry;
        });
    }

    /**
     * Sync 1040 Inventory Chart of Account balance with current stock-on-hand valuation.
     */
    public function syncInventoryValuation(?int $companyId = null): void
    {
        $companyId = $companyId ?: auth()->user()?->current_company_id;
        if (!$companyId) {
            return;
        }

        $products = \App\Models\Product::where('company_id', $companyId)->get();
        $currentValuation = 0;
        foreach ($products as $p) {
            $cost = (float) $p->cost_price;
            $qty = (float) $p->stock_quantity;
            $currentValuation += ($cost * $qty);
        }

        $invAcc = Account::withTrashed()
            ->where('company_id', $companyId)
            ->where('account_code', '1040')
            ->first()
            ?? Account::withTrashed()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('account_name', 'LIKE', '%Inventory%');
            })->first();

        if ($invAcc && $invAcc->trashed()) {
            $invAcc->restore();
        }

        if (!$invAcc) {
            $invAcc = Account::create([
                'company_id' => $companyId,
                'account_code' => '1040',
                'account_name' => 'Inventory',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'opening_balance' => $currentValuation,
                'current_balance' => $currentValuation,
                'is_active' => true,
                'is_system_account' => true,
            ]);
            return;
        }

        $totalCredits = (float) $invAcc->journalEntries()->sum('credit_amount');
        $totalDebits = (float) $invAcc->journalEntries()->sum('debit_amount');

        // Calculate opening_balance so that calculateBalance() matches actual stock valuation on hand
        $openingBalanceNeeded = $currentValuation - $totalDebits + $totalCredits;
        $invAcc->opening_balance = max(0, $openingBalanceNeeded);
        $invAcc->current_balance = $invAcc->calculateBalance();
        $invAcc->save();
    }

    /**
     * Create atomic journal entry for sales return
     */
    public function createSalesReturnEntry(Sale $saleReturn): ?JournalEntry
    {
        return DB::transaction(function () use ($saleReturn) {
            $companyId = $saleReturn->company_id ?: auth()->user()?->current_company_id;

            // Resolve Sales Return (Contra-Revenue) Account
            $salesReturnAccount = $this->accountingSettings->sales_return_revenue_account_id
                ?: Account::where('company_id', $companyId)->where(function($q) { 
                    $q->where('account_code', '4020')
                      ->orWhere('account_code', '4060')
                      ->orWhere('account_type', 'sales_return')
                      ->orWhere('account_name', 'like', '%Sales Return%'); 
                })->value('id');

            if (!$salesReturnAccount) {
                $srAcc = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '4020',
                    'account_name' => 'Sales Returns & Allowances',
                    'account_type' => 'revenue',
                    'account_subtype' => 'operating_income',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
                $salesReturnAccount = $srAcc->id;
            }

            // Resolve Tax Account
            $taxAccount = $this->accountingSettings->sales_return_tax_account_id
                ?: Account::where('company_id', $companyId)->where(function($q) { 
                    $q->where('account_code', '2020')
                      ->orWhere('account_type', 'tax_payable')
                      ->orWhere('account_name', 'like', '%Tax%'); 
                })->value('id');

            // Inventory & COGS Accounts for stock reversal
            $inventoryAccount = $this->accountingSettings->inventory_asset_account_id
                ?: Account::where('company_id', $companyId)->where(function($q) { 
                    $q->where('account_code', '1040')
                      ->orWhere('account_type', 'inventory')
                      ->orWhere('account_name', 'like', '%Inventory%'); 
                })->value('id');

            $cogsAccount = $this->accountingSettings->cost_of_goods_sold_account_id
                ?: Account::where('company_id', $companyId)->where(function($q) { 
                    $q->where('account_code', '5000')
                      ->orWhere('account_code', '5010')
                      ->orWhere('account_type', 'cost_of_goods_sold')
                      ->orWhere('account_type', 'cogs')
                      ->orWhere('account_name', 'like', '%Cost of Goods Sold%')
                      ->orWhere('account_name', 'like', '%COGS%'); 
                })->value('id');

            $totalAmount = abs((float)$saleReturn->total_amount);
            $taxAmount = abs((float)$saleReturn->tax_amount);
            if ($taxAmount < 0.001 && $saleReturn->saleItems()->count() > 0) {
                $taxAmount = abs((float)$saleReturn->saleItems()->sum('tax_amount'));
            }
            $subtotalAmount = abs((float)$saleReturn->subtotal);
            if ($subtotalAmount < 0.001 || abs($subtotalAmount - $totalAmount) < 0.001) {
                $subtotalAmount = max(0, $totalAmount - $taxAmount);
            }

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('SR'),
                'entry_date' => $saleReturn->sale_date ?? today()->toDateString(),
                'reference' => "Sales Return #{$saleReturn->sale_number}",
                'description' => "Sales Return #{$saleReturn->sale_number} - " . ($saleReturn->customer->name ?? 'Walk-in Customer'),
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $totalAmount,
                'total_credit' => $totalAmount,
                'created_by' => $saleReturn->user_id ?? auth()->id(),
                'posted_by' => $saleReturn->user_id ?? auth()->id(),
                'posted_at' => now(),
                'source_type' => 'sale_return',
                'source_id' => $saleReturn->id,
            ]);

            // 1. DEBIT: Sales Returns & Allowances (Contra-Revenue)
            $netReturn = ($taxAmount > 0 && $taxAccount) ? $subtotalAmount : $totalAmount;
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $salesReturnAccount,
                'description' => "Sales Return #{$saleReturn->sale_number}",
                'debit_amount' => $netReturn,
                'credit_amount' => 0,
                'partner_type' => Customer::class,
                'partner_id' => $saleReturn->customer_id,
            ]);

            Account::where('id', $salesReturnAccount)->increment('current_balance', $netReturn);

            // 2. DEBIT: Sales Tax Liability (if tax exists)
            if ($taxAmount > 0 && $taxAccount) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccount,
                    'description' => "Sales Tax Refund - Return #{$saleReturn->sale_number}",
                    'debit_amount' => $taxAmount,
                    'credit_amount' => 0,
                    'partner_type' => Customer::class,
                    'partner_id' => $saleReturn->customer_id,
                ]);

                Account::where('id', $taxAccount)->decrement('current_balance', $taxAmount);
            }

            // 3. CREDIT: Cash / Bank Accounts (Decrements balances for refunds)
            $returnPayments = is_array($saleReturn->payment_details) && count($saleReturn->payment_details) > 0
                ? $saleReturn->payment_details
                : [];

            if (empty($returnPayments) && abs((float)$saleReturn->paid_amount) > 0) {
                $returnPayments = [[
                    'type' => ($saleReturn->payment_method === 'bank_transfer' || $saleReturn->payment_method === 'card') ? 'bank' : $saleReturn->payment_method,
                    'method' => $saleReturn->payment_method ?? 'cash',
                    'bank_id' => $saleReturn->bank_id ?? null,
                    'amount' => abs((float)$saleReturn->paid_amount)
                ]];
            }

            $totalRefunded = 0;

            foreach ($returnPayments as $payment) {
                $type = strtolower($payment['type'] ?? $payment['method'] ?? 'cash');
                $amount = abs((float)($payment['amount'] ?? 0));
                if ($amount <= 0) continue;

                if ($type === 'cash') {
                    $totalRefunded += $amount;

                    $cashAccount = Account::where('company_id', $companyId)
                        ->where('account_type', 'asset')
                        ->where(function ($q) {
                            $q->where('account_code', '1010')
                              ->orWhere('account_name', 'LIKE', '%Cash%');
                        })->first();

                    if ($cashAccount) {
                        // CREDIT Cash COA Line (Reduces Cash)
                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $cashAccount->id,
                            'description' => "Cash Refund - Sales Return #{$saleReturn->sale_number}",
                            'debit_amount' => 0,
                            'credit_amount' => $amount,
                            'partner_type' => Customer::class,
                            'partner_id' => $saleReturn->customer_id,
                        ]);

                        $cashBankAccount = \App\Models\BankAccount::where('company_id', $companyId)
                            ->where(function ($q) use ($cashAccount) {
                                if ($cashAccount) $q->where('chart_account_id', $cashAccount->id);
                                $q->orWhere('account_name', 'LIKE', '%Cash%')
                                  ->orWhere('bank_name', 'LIKE', '%Cash%')
                                  ->orWhere('is_default', true);
                            })->first();

                        if ($cashBankAccount) {
                            \App\Models\BankTransaction::create([
                                'company_id' => $companyId,
                                'bank_account_id' => $cashBankAccount->id,
                                'transaction_date' => $saleReturn->sale_date ?? today()->toDateString(),
                                'reference_number' => $saleReturn->sale_number,
                                'description' => "Cash Refund for Sales Return #{$saleReturn->sale_number}",
                                'transaction_type' => 'credit',
                                'amount' => $amount,
                                'running_balance' => (float)$cashBankAccount->current_balance,
                                'status' => 'cleared',
                            ]);
                        }
                    }

                } elseif ($type === 'bank' || $type === 'card' || $type === 'bank_transfer') {
                    $bankAccountId = isset($payment['bank_id']) ? (int)$payment['bank_id'] : (isset($payment['bank_account_id']) ? (int)$payment['bank_account_id'] : (isset($payment['payment_method_id']) ? (int)$payment['payment_method_id'] : null));
                    $bankAccount = null;
                    if ($bankAccountId) {
                        $bankAccount = \App\Models\BankAccount::find($bankAccountId);
                    }
                    if (!$bankAccount) {
                        $bankAccount = \App\Models\BankAccount::where('company_id', $companyId)
                            ->where(function ($q) {
                                $q->where('account_name', 'NOT LIKE', '%Cash%')
                                  ->where('bank_name', 'NOT LIKE', '%Cash%');
                            })->first();
                    }

                    if ($bankAccount) {
                        $totalRefunded += $amount;

                        \App\Models\BankTransaction::create([
                            'company_id' => $companyId,
                            'bank_account_id' => $bankAccount->id,
                            'transaction_date' => $saleReturn->sale_date ?? today()->toDateString(),
                            'reference_number' => $saleReturn->sale_number,
                            'description' => "Bank Refund ({$bankAccount->account_name}) for Sales Return #{$saleReturn->sale_number}",
                            'transaction_type' => 'credit',
                            'amount' => $amount,
                            'running_balance' => (float)$bankAccount->current_balance,
                            'status' => 'cleared',
                        ]);

                        // HARD SYNC: Find or Link Chart of Account record for this Bank
                        $bankChartAccount = null;
                        if ($bankAccount->chart_account_id) {
                            $bankChartAccount = Account::where('company_id', $companyId)->find($bankAccount->chart_account_id);
                        }

                        if (!$bankChartAccount) {
                            $bankChartAccount = Account::where('company_id', $companyId)
                                ->where('account_type', 'asset')
                                ->where(function ($q) use ($bankAccount) {
                                    $q->where('account_name', 'LIKE', "%{$bankAccount->bank_name}%")
                                      ->orWhere('account_name', 'LIKE', "%{$bankAccount->account_name}%")
                                      ->orWhere('account_code', '1600')
                                      ->orWhere('account_code', '1020');
                                })->first();
                        }

                        if (!$bankChartAccount) {
                            $bankChartAccount = Account::create([
                                'company_id' => $companyId,
                                'account_code' => '1600',
                                'account_name' => $bankAccount->account_name . ' (' . ($bankAccount->bank_name ?: 'Bank') . ')',
                                'account_type' => 'asset',
                                'opening_balance' => $bankAccount->opening_balance ?? 0,
                                'current_balance' => 0,
                                'is_active' => true,
                            ]);
                        }

                        if ($bankAccount->chart_account_id !== $bankChartAccount->id) {
                            $bankAccount->update(['chart_account_id' => $bankChartAccount->id]);
                        }

                        // CREDIT Linked Bank COA (Reduces Bank COA Balance)
                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $bankChartAccount->id,
                            'description' => "Bank Refund ({$bankAccount->account_name}) - Sales Return #{$saleReturn->sale_number}",
                            'debit_amount' => 0,
                            'credit_amount' => $amount,
                            'partner_type' => Customer::class,
                            'partner_id' => $saleReturn->customer_id,
                        ]);
                    }
                } elseif ($type === 'store_credit') {
                    $totalRefunded += $amount;

                    $arAccount = Account::where('company_id', $companyId)
                        ->where('account_type', 'asset')
                        ->where(function ($q) {
                            $q->where('account_code', '1030')
                              ->orWhere('account_name', 'LIKE', '%Accounts Receivable%');
                        })->first();

                    if ($arAccount) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $arAccount->id,
                            'description' => "Customer Wallet Store Credit - Sales Return #{$saleReturn->sale_number}",
                            'debit_amount' => 0,
                            'credit_amount' => $amount,
                            'partner_type' => Customer::class,
                            'partner_id' => $saleReturn->customer_id,
                        ]);
                    }
                }
            }

            // 4. Handle unrefunded return balance: Dynamic split between Clearing A/R (1030) and Customer Credit Liability (2010)
            $unrefundedRemainder = max(0, $totalAmount - $totalRefunded);

            if ($unrefundedRemainder > 0) {
                $originalUnpaidDue = 0;
                if ($saleReturn->original_sale_id) {
                    $origSale = Sale::find($saleReturn->original_sale_id);
                    if ($origSale) {
                        $originalUnpaidDue = max(0, (float)$origSale->total_amount - (float)$origSale->paid_amount);
                    }
                }

                // Calculate split amounts
                $arClearanceAmount = 0;
                $storeCreditLiabilityAmount = 0;

                if ($originalUnpaidDue > 0) {
                    $arClearanceAmount = min($unrefundedRemainder, $originalUnpaidDue);
                    $storeCreditLiabilityAmount = max(0, $unrefundedRemainder - $arClearanceAmount);
                } else {
                    $storeCreditLiabilityAmount = $unrefundedRemainder;
                }

                // 4a. Post Accounts Receivable Clearance Line (CREDIT COA 1030)
                if ($arClearanceAmount > 0) {
                    $arAccount = Account::where('company_id', $companyId)
                        ->where(function ($q) {
                            $q->where('account_code', '1030')
                              ->orWhere('account_type', 'receivable')
                              ->orWhere('account_type', 'accounts_receivable')
                              ->orWhere('account_name', 'LIKE', '%Accounts Receivable%');
                        })->first();

                    if (!$arAccount) {
                        $arAccount = Account::create([
                            'company_id' => $companyId,
                            'account_code' => '1030',
                            'account_name' => 'Accounts Receivable',
                            'account_type' => 'asset',
                            'account_subtype' => 'current_asset',
                            'opening_balance' => 0,
                            'current_balance' => 0,
                            'is_active' => true,
                            'is_system_account' => true,
                        ]);
                    }

                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $arAccount->id,
                        'description' => "Accounts Receivable Clearance - Sales Return #{$saleReturn->sale_number}",
                        'debit_amount' => 0,
                        'credit_amount' => $arClearanceAmount,
                        'partner_type' => Customer::class,
                        'partner_id' => $saleReturn->customer_id,
                    ]);

                    Account::where('id', $arAccount->id)->decrement('current_balance', $arClearanceAmount);
                }

                // 4b. Post Customer Credit / Deposit Liability Line (CREDIT COA 2070)
                if ($storeCreditLiabilityAmount > 0) {
                    $creditLiabilityAccount = Account::where('company_id', $companyId)
                        ->where(function ($q) {
                            $q->where('account_name', 'LIKE', '%Customer Credit%')
                              ->orWhere('account_name', 'LIKE', '%Store Credit%')
                              ->orWhere('account_name', 'LIKE', '%Customer Deposit%');
                        })->first();

                    if (!$creditLiabilityAccount) {
                        $creditLiabilityAccount = Account::create([
                            'company_id' => $companyId,
                            'account_code' => '2070',
                            'account_name' => 'Customer Credit / Deposit Liability',
                            'account_type' => 'liability',
                            'account_subtype' => 'current_liability',
                            'opening_balance' => 0,
                            'current_balance' => 0,
                            'is_active' => true,
                            'is_system_account' => true,
                        ]);
                    }

                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $creditLiabilityAccount->id,
                        'description' => "Customer Store Credit Liability - Sales Return #{$saleReturn->sale_number}",
                        'debit_amount' => 0,
                        'credit_amount' => $storeCreditLiabilityAmount,
                        'partner_type' => Customer::class,
                        'partner_id' => $saleReturn->customer_id,
                    ]);

                    Account::where('id', $creditLiabilityAccount->id)->increment('current_balance', $storeCreditLiabilityAmount);
                }
            }

            // 5. Inventory Reversal (DEBIT: Inventory Asset, CREDIT: COGS)
            if ($inventoryAccount && $cogsAccount) {
                $totalReturnCost = 0;
                $saleReturn->loadMissing('saleItems.product');
                foreach ($saleReturn->saleItems as $item) {
                    $qty = abs($item->quantity);
                    $unitCost = $item->product?->purchase_price ?? $item->product?->cost_price ?? 0;
                    $totalReturnCost += ($qty * $unitCost);
                }

                if ($totalReturnCost > 0) {
                    // DEBIT: Inventory Asset (Restoring inventory valuation)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $inventoryAccount,
                        'description' => "Stock Reversal - Sales Return #{$saleReturn->sale_number}",
                        'debit_amount' => $totalReturnCost,
                        'credit_amount' => 0,
                    ]);

                    // CREDIT: COGS (Reducing COGS expense)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $cogsAccount,
                        'description' => "COGS Reversal - Sales Return #{$saleReturn->sale_number}",
                        'debit_amount' => 0,
                        'credit_amount' => $totalReturnCost,
                    ]);
                }
            }

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        });
    }

    /**
     * Create journal entry for purchase invoice
     */
    public function createPurchaseInvoiceEntry(PurchaseOrder $purchaseOrder): ?JournalEntry
    {
        $companyId = $purchaseOrder->company_id ?: (auth()->user()?->current_company_id ?? 1);

        // Prioritize Inventory Asset Account (COA 1040 / Merchandise Inventory Asset) for Perpetual Inventory System
        $inventoryAssetAccountId = $this->accountingSettings->inventory_asset_account_id 
            ?: $this->accountingSettings->purchase_invoice_expense_account_id;

        if (!$inventoryAssetAccountId) {
            $invAcc = Account::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '1040')
                      ->orWhere('account_code', '10100')
                      ->orWhere('account_name', 'LIKE', '%Inventory%');
                })->first();
            if (!$invAcc) {
                $invAcc = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1040',
                    'account_name' => 'Inventory',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }
            $inventoryAssetAccountId = $invAcc->id;
        }

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

        return DB::transaction(function () use ($purchaseOrder, $companyId, $inventoryAssetAccountId, $payableAccountId) {
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('PI', $companyId),
                'entry_date' => $purchaseOrder->order_date,
                'reference' => "Purchase Order #{$purchaseOrder->po_number}",
                'description' => "Purchase Receipt from " . ($purchaseOrder->supplier_name ?: ($purchaseOrder->supplier?->name ?? 'Supplier')),
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $purchaseOrder->total_amount,
                'total_credit' => $purchaseOrder->total_amount,
                'created_by' => $purchaseOrder->user_id,
                'posted_by' => $purchaseOrder->user_id,
                'posted_at' => now(),
                'source_type' => 'purchase_order',
                'source_id' => $purchaseOrder->id,
            ]);

            // DEBIT: Merchandise Inventory Asset Account (COA 10100 / Stock in Hand)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAssetAccountId,
                'description' => "Merchandise Inventory Receipt #{$purchaseOrder->po_number}",
                'debit_amount' => $purchaseOrder->subtotal,
                'credit_amount' => 0,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // DEBIT: Tax Account (if tax exists)
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

            // CREDIT: Accounts Payable Liability Account (COA 20100)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $payableAccountId,
                'description' => "Accounts Payable #{$purchaseOrder->po_number}",
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
                ? ($this->accountingSettings->cash_account_id ?: $this->accountingSettings->expense_payable_account_id) 
                : $this->accountingSettings->expense_payable_account_id;

            if (!$creditAccount) {
                throw new \Exception("No cash or payable account configured for expenses.");
            }

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
        $companyId = $purchaseReturn->company_id ?: (auth()->user()?->current_company_id ?? 1);

        $payableAccountId = $this->accountingSettings->purchase_return_payable_account_id 
            ?: $this->accountingSettings->purchase_invoice_payable_account_id;

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

        $inventoryAssetAccountId = $this->accountingSettings->inventory_asset_account_id 
            ?: $this->accountingSettings->purchase_return_expense_account_id 
            ?: $this->accountingSettings->purchase_invoice_expense_account_id;

        if (!$inventoryAssetAccountId) {
            $invAcc = Account::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '1040')
                      ->orWhere('account_code', '10100')
                      ->orWhere('account_name', 'LIKE', '%Inventory%');
                })->first();
            if (!$invAcc) {
                $invAcc = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1040',
                    'account_name' => 'Inventory',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }
            $inventoryAssetAccountId = $invAcc->id;
        }

        // Cash Vault Account (COA 10100)
        $cashAcc = Account::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('account_code', '10100')
                  ->orWhere('account_code', '1010')
                  ->orWhere('account_name', 'LIKE', '%Cash Vault%')
                  ->orWhere('account_name', 'LIKE', '%Cash in Hand%');
            })->first();
        if (!$cashAcc) {
            $cashAcc = Account::create([
                'company_id' => $companyId,
                'account_code' => '10100',
                'account_name' => 'Cash Vault',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }
        $cashAccountId = $cashAcc->id;

        // Advance to Suppliers Account (COA 1310)
        $vendorAdvanceAcc = Account::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('account_code', '1310')
                  ->orWhere('account_name', 'Advance to Suppliers');
            })->first();

        if (!$vendorAdvanceAcc) {
            $vendorAdvanceAcc = Account::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '10500')
                      ->orWhere('account_name', 'LIKE', '%Advance to Suppliers%')
                      ->orWhere('account_name', 'LIKE', '%Vendor Advance%')
                      ->orWhere('account_name', 'LIKE', '%Vendor Credit%');
                })->first();
        }

        if (!$vendorAdvanceAcc) {
            $vendorAdvanceAcc = Account::create([
                'company_id' => $companyId,
                'account_code' => '1310',
                'account_name' => 'Advance to Suppliers',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }
        $vendorAdvanceAccountId = $vendorAdvanceAcc->id;

        return DB::transaction(function () use ($purchaseReturn, $companyId, $payableAccountId, $inventoryAssetAccountId, $cashAccountId, $vendorAdvanceAccountId) {
            $totalAmount = (float) $purchaseReturn->total_amount;

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('PRR'),
                'entry_date' => $purchaseReturn->return_date,
                'reference' => "Purchase Return #{$purchaseReturn->return_number}",
                'description' => "Purchase Return Refund to " . ($purchaseReturn->supplier?->name ?? 'Supplier'),
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $totalAmount,
                'total_credit' => $totalAmount,
                'created_by' => $purchaseReturn->user_id ?: auth()->id(),
                'posted_by' => $purchaseReturn->user_id ?: auth()->id(),
                'posted_at' => now(),
                'source_type' => 'purchase_return',
                'source_id' => $purchaseReturn->id,
            ]);

            // Resolve refund splits/payment channels
            $splits = [];
            if (!empty($purchaseReturn->refund_splits) && is_array($purchaseReturn->refund_splits)) {
                $splits = $purchaseReturn->refund_splits;
            } else {
                $method = $purchaseReturn->payment_method ?? 'cash';
                $splits[] = [
                    'type' => $method,
                    'bank_id' => $purchaseReturn->bank_account_id,
                    'amount' => $totalAmount,
                    'reference_number' => $purchaseReturn->reference_number,
                ];
            }

            foreach ($splits as $split) {
                $splitAmt = (float) ($split['amount'] ?? 0);
                if ($splitAmt <= 0) continue;

                $type = $split['type'] ?? 'cash';
                $bankId = $split['bank_id'] ?? null;
                $refNo = $split['reference_number'] ?? $purchaseReturn->reference_number;

                if ($type === 'bank' && $bankId) {
                    $bankAccount = BankAccount::find($bankId);
                    $debitAccountId = $bankAccount?->chart_of_account_id ?: $cashAccountId;

                    // DEBIT: Bank Account
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $debitAccountId,
                        'description' => "Purchase Return Bank Refund #{$purchaseReturn->return_number}" . ($refNo ? " (Ref: {$refNo})" : ""),
                        'debit_amount' => $splitAmt,
                        'credit_amount' => 0,
                        'partner_type' => Supplier::class,
                        'partner_id' => $purchaseReturn->supplier_id,
                    ]);

                    // Sync BankTransaction & update Bank Account balance
                    if ($bankAccount) {
                        $lastTx = BankTransaction::where('bank_account_id', $bankAccount->id)->orderBy('id', 'desc')->first();
                        $runningBalance = ($lastTx ? (float)$lastTx->running_balance : (float)$bankAccount->opening_balance) + $splitAmt;

                        BankTransaction::create([
                            'company_id' => $companyId,
                            'bank_account_id' => $bankAccount->id,
                            'transaction_date' => $purchaseReturn->return_date,
                            'reference_number' => $refNo ?: $purchaseReturn->return_number,
                            'description' => "Purchase Return Refund #{$purchaseReturn->return_number} - " . ($purchaseReturn->supplier?->name ?? 'Supplier'),
                            'transaction_type' => 'credit',
                            'amount' => $splitAmt,
                            'running_balance' => $runningBalance,
                            'status' => 'cleared',
                            'journal_entry_id' => $journalEntry->id,
                            'partner_type' => Supplier::class,
                            'partner_id' => $purchaseReturn->supplier_id,
                        ]);

                        $bankAccount->current_balance = $runningBalance;
                        $bankAccount->save();
                    }
                } elseif ($type === 'vendor_credit') {
                    // DEBIT: Vendor Advance (COA 10500)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $vendorAdvanceAccountId,
                        'description' => "Purchase Return Vendor Credit #{$purchaseReturn->return_number}",
                        'debit_amount' => $splitAmt,
                        'credit_amount' => 0,
                        'partner_type' => Supplier::class,
                        'partner_id' => $purchaseReturn->supplier_id,
                    ]);

                    // Increase Supplier Advance Balance
                    if ($purchaseReturn->supplier) {
                        $purchaseReturn->supplier->creditAdvance($splitAmt);
                    }
                } elseif ($type === 'ap_credit' || $type === 'ap') {
                    // DEBIT: Accounts Payable (COA 20100)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $payableAccountId,
                        'description' => "Purchase Return AP Adjustment #{$purchaseReturn->return_number}",
                        'debit_amount' => $splitAmt,
                        'credit_amount' => 0,
                        'partner_type' => Supplier::class,
                        'partner_id' => $purchaseReturn->supplier_id,
                    ]);
                } else {
                    // Default: Cash Vault (COA 10100)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $cashAccountId,
                        'description' => "Purchase Return Cash Refund #{$purchaseReturn->return_number}",
                        'debit_amount' => $splitAmt,
                        'credit_amount' => 0,
                        'partner_type' => Supplier::class,
                        'partner_id' => $purchaseReturn->supplier_id,
                    ]);

                    // Sync Cash Vault Bank Account if present
                    $cashBankAcc = BankAccount::where('company_id', $companyId)
                        ->where(function ($q) {
                            $q->where('is_default', true)
                              ->orWhere('account_name', 'LIKE', '%Cash Vault%');
                        })->first();
                    if ($cashBankAcc) {
                        $lastTx = BankTransaction::where('bank_account_id', $cashBankAcc->id)->orderBy('id', 'desc')->first();
                        $runningBalance = ($lastTx ? (float)$lastTx->running_balance : (float)$cashBankAcc->opening_balance) + $splitAmt;

                        BankTransaction::create([
                            'company_id' => $companyId,
                            'bank_account_id' => $cashBankAcc->id,
                            'transaction_date' => $purchaseReturn->return_date,
                            'reference_number' => $purchaseReturn->return_number,
                            'description' => "Purchase Return Cash Refund #{$purchaseReturn->return_number}",
                            'transaction_type' => 'credit',
                            'amount' => $splitAmt,
                            'running_balance' => $runningBalance,
                            'status' => 'cleared',
                            'journal_entry_id' => $journalEntry->id,
                            'partner_type' => Supplier::class,
                            'partner_id' => $purchaseReturn->supplier_id,
                        ]);

                        $cashBankAcc->current_balance = $runningBalance;
                        $cashBankAcc->save();
                    }
                }
            }

            // CREDIT: Merchandise Inventory Asset Account (COA 1040)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAssetAccountId,
                'description' => "Purchase Return Inventory Deduction #{$purchaseReturn->return_number}",
                'debit_amount' => 0,
                'credit_amount' => $totalAmount,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseReturn->supplier_id,
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        });
    }

    /**
     * Scenario 1: Process Credit Sale Invoice (0 Upfront Payment)
     * 
     * Double Entries Created:
     *  1. Revenue Entry:
     *     - Debit:  1030 Accounts Receivable (Selling Price, e.g. 60,000)
     *     - Credit: 4010 Sales Revenue       (Selling Price, e.g. 60,000)
     * 
     *  2. Perpetual Inventory COGS Entry:
     *     - Debit:  5010 Cost of Goods Sold  (Purchase Cost, e.g. 50,000)
     *     - Credit: 1040 Inventory           (Purchase Cost, e.g. 50,000)
     */
    public function processCreditSaleInvoice(Sale $sale): JournalEntry
    {
        return DB::transaction(function () use ($sale) {
            $companyId = $sale->company_id ?: auth()->user()?->current_company_id;

            // 1. Resolve Accounts Receivable (1030) Account
            $arAccount = Account::where('company_id', $companyId)
                ->where('account_type', 'asset')
                ->where(function ($q) {
                    $q->where('account_code', '1030')
                      ->orWhere('account_name', 'LIKE', '%Accounts Receivable%');
                })->first();

            if (!$arAccount) {
                $arAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1030',
                    'account_name' => 'Accounts Receivable',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }

            // 2. Resolve Sales Revenue (4010) Account
            $revenueAccount = Account::where('company_id', $companyId)
                ->where('account_type', 'revenue')
                ->where(function ($q) {
                    $q->where('account_code', '4010')
                      ->orWhere('account_name', 'LIKE', '%Sales Revenue%');
                })->first();

            if (!$revenueAccount) {
                $revenueAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '4010',
                    'account_name' => 'Sales Revenue',
                    'account_type' => 'revenue',
                    'account_subtype' => 'operating_revenue',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }

            $saleAmount = (float) $sale->total_amount;

            // 3. Create Double-Entry Journal Entry for Revenue
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('SI-CR'),
                'entry_date' => $sale->sale_date ?? now()->toDateString(),
                'reference' => "Credit Sales Invoice #{$sale->sale_number}",
                'description' => "Credit sale to customer",
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $saleAmount,
                'total_credit' => $saleAmount,
                'created_by' => $sale->user_id ?? auth()->id(),
                'posted_by' => $sale->user_id ?? auth()->id(),
                'posted_at' => now(),
                'source_type' => 'sale',
                'source_id' => $sale->id,
            ]);

            // Debit: 1030 Accounts Receivable (Asset increases)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $arAccount->id,
                'description' => "Accounts Receivable for Invoice #{$sale->sale_number}",
                'debit_amount' => $saleAmount,
                'credit_amount' => 0,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // Credit: 4010 Sales Revenue (Revenue increases)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $revenueAccount->id,
                'description' => "Sales Revenue for Invoice #{$sale->sale_number}",
                'debit_amount' => 0,
                'credit_amount' => $saleAmount,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // Update balances in chart_of_accounts
            $arAccount->current_balance = (float) $arAccount->current_balance + $saleAmount;
            $arAccount->save();

            $revenueAccount->current_balance = (float) $revenueAccount->current_balance + $saleAmount;
            $revenueAccount->save();

            // Update Sale status & due amount
            $sale->update([
                'paid_amount' => 0,
                'due_amount' => $saleAmount,
                'payment_status' => 'unpaid',
            ]);

            // Process COGS Perpetual Inventory Deduction (5010 COGS / 1040 Inventory)
            $this->processPerpetualInventoryCOGS($sale);

            return $journalEntry;
        });
    }

    /**
     * Scenario 2: Process Partial or Full Payment Receipt for Credit Invoice
     * 
     * Double Entries Created:
     *  - Debit:  Selected Cash/Bank Account (Asset increases, e.g. +20,000)
     *  - Credit: 1030 Accounts Receivable  (Asset decreases, e.g. -20,000)
     */
    public function processPartialPaymentReceipt(
        Sale $sale, 
        float $paymentAmount, 
        string $paymentMethod = 'cash', 
        ?int $bankAccountId = null
    ): JournalEntry {
        return DB::transaction(function () use ($sale, $paymentAmount, $paymentMethod, $bankAccountId) {
            $companyId = $sale->company_id ?: auth()->user()?->current_company_id;

            // 1. Resolve Accounts Receivable Account (1030)
            $arAccount = Account::where('company_id', $companyId)
                ->where('account_type', 'asset')
                ->where(function ($q) {
                    $q->where('account_code', '1030')
                      ->orWhere('account_name', 'LIKE', '%Accounts Receivable%');
                })->first();

            if (!$arAccount) {
                $arAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1030',
                    'account_name' => 'Accounts Receivable',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                    'is_system_account' => true,
                ]);
            }

            // 2. Resolve Receiving Liquid Account (Cash vs Bank)
            $receivingAccount = null;
            if (strtolower($paymentMethod) === 'cash') {
                $receivingAccount = Account::where('company_id', $companyId)
                    ->where('account_type', 'asset')
                    ->where(function ($q) {
                        $q->where('account_code', '1010')
                          ->orWhere('account_name', 'LIKE', '%Cash%');
                    })->first();

                if (!$receivingAccount) {
                    $receivingAccount = Account::create([
                        'company_id' => $companyId,
                        'account_code' => '1010',
                        'account_name' => 'Default Cash',
                        'account_type' => 'asset',
                        'opening_balance' => 0,
                        'current_balance' => 0,
                        'is_active' => true,
                        'is_system_account' => true,
                    ]);
                }

                // Sync Default Cash Vault in bank_accounts table
                $cashVault = \App\Models\BankAccount::where('company_id', $companyId)
                    ->where(function ($q) {
                        $q->where('account_name', 'LIKE', '%Cash%')
                          ->orWhere('bank_name', 'LIKE', '%Cash%');
                    })->first();

                if ($cashVault) {
                    $newCashBalance = (float) $cashVault->current_balance + $paymentAmount;

                    \App\Models\BankTransaction::create([
                        'company_id' => $companyId,
                        'bank_account_id' => $cashVault->id,
                        'transaction_date' => now()->toDateString(),
                        'reference_number' => $sale->sale_number,
                        'description' => "Partial Cash Receipt for Invoice #{$sale->sale_number}",
                        'transaction_type' => 'debit',
                        'amount' => $paymentAmount,
                        'running_balance' => $newCashBalance,
                        'status' => 'cleared',
                    ]);

                    $cashVault->update(['current_balance' => $newCashBalance]);
                }
            } else {
                // Bank Account Payment
                $bankAccount = \App\Models\BankAccount::where('company_id', $companyId)->findOrFail($bankAccountId);
                $newBankBalance = (float) $bankAccount->current_balance + $paymentAmount;

                \App\Models\BankTransaction::create([
                    'company_id' => $companyId,
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now()->toDateString(),
                    'reference_number' => $sale->sale_number,
                    'description' => "Partial Bank Payment for Invoice #{$sale->sale_number}",
                    'transaction_type' => 'debit',
                    'amount' => $paymentAmount,
                    'running_balance' => $newBankBalance,
                    'status' => 'cleared',
                ]);

                $bankAccount->update(['current_balance' => $newBankBalance]);

                $receivingAccount = Account::find($bankAccount->chart_account_id);
                if (!$receivingAccount) {
                    $receivingAccount = Account::where('company_id', $companyId)
                        ->where('account_type', 'asset')
                        ->where('account_name', 'LIKE', "%{$bankAccount->bank_name}%")
                        ->first();
                }
            }

            // 3. Create Journal Entry
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('PAY'),
                'entry_date' => now()->toDateString(),
                'reference' => "Payment Receipt - Invoice #{$sale->sale_number}",
                'description' => "Partial payment receipt of {$paymentAmount} for Invoice #{$sale->sale_number}",
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $paymentAmount,
                'total_credit' => $paymentAmount,
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
                'source_type' => 'sale_payment',
                'source_id' => $sale->id,
            ]);

            // Debit: Cash/Bank Asset Account (+20,000)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $receivingAccount->id,
                'description' => "Payment Receipt - Invoice #{$sale->sale_number}",
                'debit_amount' => $paymentAmount,
                'credit_amount' => 0,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // Credit: Accounts Receivable (-20,000)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $arAccount->id,
                'description' => "Receivable Reduction - Invoice #{$sale->sale_number}",
                'debit_amount' => 0,
                'credit_amount' => $paymentAmount,
                'partner_type' => Customer::class,
                'partner_id' => $sale->customer_id,
            ]);

            // 4. Update ground-truth balances in chart_of_accounts
            $receivingAccount->updateCurrentBalance();
            $arAccount->updateCurrentBalance();

            // 5. Update Sale Invoice balances
            $newPaidAmount = (float) $sale->paid_amount + $paymentAmount;
            $newDueAmount = max(0, (float) $sale->total_amount - $newPaidAmount);
            $paymentStatus = $newDueAmount == 0 ? 'paid' : 'partially_paid';

            $sale->update([
                'paid_amount' => $newPaidAmount,
                'due_amount' => $newDueAmount,
                'payment_status' => $paymentStatus,
            ]);

            return $journalEntry;
        });
    }

    /**
     * Create journal entry for applying Vendor Advance/Store Credit to a Purchase Order.
     */
    public function createVendorAdvanceApplicationEntry(PurchaseOrder $purchaseOrder, float $advanceApplied): ?JournalEntry
    {
        if ($advanceApplied <= 0) return null;

        $companyId = $purchaseOrder->company_id ?: (auth()->user()?->current_company_id ?? 1);

        // 1. Resolve Accounts Payable Account (Code 2010 / 20100)
        $payableAccountId = $this->accountingSettings->purchase_invoice_payable_account_id;
        $apAcc = null;

        if ($payableAccountId) {
            $apAcc = Account::withoutGlobalScopes()->find($payableAccountId);
        }

        if (!$apAcc) {
            $apAcc = Account::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '2010')
                      ->orWhere('account_code', '20100')
                      ->orWhere('account_name', 'LIKE', '%Accounts Payable%')
                      ->orWhere('account_name', 'LIKE', '%Payable%');
                })->first();
        }

        if (!$apAcc) {
            $apAcc = Account::create([
                'company_id' => $companyId,
                'account_code' => '2010',
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Supplier accounts payable',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }
        $payableAccountId = $apAcc->id;

        // 2. Resolve Advance to Suppliers Account (Strict Priority: 1310 / Advance to Suppliers)
        $vendorAdvanceAcc = Account::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('account_code', '1310')
                  ->orWhere('account_name', 'Advance to Suppliers');
            })->first();

        if (!$vendorAdvanceAcc) {
            $vendorAdvanceAcc = Account::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '10500')
                      ->orWhere('account_name', 'LIKE', '%Advance to Suppliers%')
                      ->orWhere('account_name', 'LIKE', '%Vendor Advance%')
                      ->orWhere('account_name', 'LIKE', '%Vendor Prepayments%');
                })->first();
        }

        if (!$vendorAdvanceAcc) {
            $vendorAdvanceAcc = Account::create([
                'company_id' => $companyId,
                'account_code' => '1310',
                'account_name' => 'Advance to Suppliers',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Advance payments and overpayments made to suppliers for future merchandise orders',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }
        $vendorAdvanceAccountId = $vendorAdvanceAcc->id;

        return DB::transaction(function () use ($purchaseOrder, $companyId, $payableAccountId, $vendorAdvanceAccountId, $vendorAdvanceAcc, $apAcc, $advanceApplied) {
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('ADV', $companyId),
                'entry_date' => $purchaseOrder->order_date ?? now()->toDateString(),
                'reference' => "Vendor Credit Applied - PO #{$purchaseOrder->po_number}",
                'description' => "Vendor Credit Application to PO #{$purchaseOrder->po_number}",
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $advanceApplied,
                'total_credit' => $advanceApplied,
                'created_by' => auth()->id() ?? $purchaseOrder->user_id ?? \App\Models\User::first()?->id ?? 1,
                'posted_by' => auth()->id() ?? $purchaseOrder->user_id ?? \App\Models\User::first()?->id ?? 1,
                'posted_at' => now(),
                'source_type' => 'purchase_order',
                'source_id' => $purchaseOrder->id,
            ]);

            // DEBIT: Accounts Payable (2010 / 20100)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $payableAccountId,
                'description' => "Vendor Credit Application - PO #{$purchaseOrder->po_number}",
                'debit_amount' => $advanceApplied,
                'credit_amount' => 0,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // CREDIT: Advance to Suppliers (1310)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $vendorAdvanceAccountId,
                'description' => "Vendor Credit Utilized - PO #{$purchaseOrder->po_number}",
                'debit_amount' => 0,
                'credit_amount' => $advanceApplied,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // Recalculate & synchronize account balances
            $vendorAdvanceAcc->updateCurrentBalance();
            $apAcc->updateCurrentBalance();

            return $journalEntry;
        });
    }

    /**
     * Create journal entry for reversing applied Vendor Advance when a Purchase Order is Voided / Cancelled.
     * Debit: 1310 - Advance to Suppliers (Restores asset balance)
     * Credit: 2010 - Accounts Payable (Reverses liability offset)
     */
    public function createVendorAdvanceApplicationReversalEntry(PurchaseOrder $purchaseOrder, float $usedAdvanceAmount): ?JournalEntry
    {
        if ($usedAdvanceAmount <= 0) return null;

        $companyId = $purchaseOrder->company_id ?: (auth()->user()?->current_company_id ?? 1);

        // 1. Resolve Accounts Payable Account (Code 2010 / 20100)
        $payableAccountId = $this->accountingSettings->purchase_invoice_payable_account_id;
        $apAcc = null;

        if ($payableAccountId) {
            $apAcc = Account::withoutGlobalScopes()->find($payableAccountId);
        }

        if (!$apAcc) {
            $apAcc = Account::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '2010')
                      ->orWhere('account_code', '20100')
                      ->orWhere('account_name', 'LIKE', '%Accounts Payable%')
                      ->orWhere('account_name', 'LIKE', '%Payable%');
                })->first();
        }

        if (!$apAcc) {
            $apAcc = Account::create([
                'company_id' => $companyId,
                'account_code' => '2010',
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Supplier accounts payable',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }
        $payableAccountId = $apAcc->id;

        // 2. Resolve Advance to Suppliers Account (Code 1310)
        $vendorAdvanceAcc = Account::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('account_code', '1310')
                  ->orWhere('account_name', 'Advance to Suppliers');
            })->first();

        if (!$vendorAdvanceAcc) {
            $vendorAdvanceAcc = Account::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '10500')
                      ->orWhere('account_name', 'LIKE', '%Advance to Suppliers%')
                      ->orWhere('account_name', 'LIKE', '%Vendor Advance%')
                      ->orWhere('account_name', 'LIKE', '%Vendor Prepayments%');
                })->first();
        }

        if (!$vendorAdvanceAcc) {
            $vendorAdvanceAcc = Account::create([
                'company_id' => $companyId,
                'account_code' => '1310',
                'account_name' => 'Advance to Suppliers',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Advance payments and overpayments made to suppliers for future merchandise orders',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }
        $vendorAdvanceAccountId = $vendorAdvanceAcc->id;

        return DB::transaction(function () use ($purchaseOrder, $companyId, $payableAccountId, $vendorAdvanceAccountId, $vendorAdvanceAcc, $apAcc, $usedAdvanceAmount) {
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('REV-ADV', $companyId),
                'entry_date' => now()->toDateString(),
                'reference' => "Vendor Credit Restored - PO #{$purchaseOrder->po_number}",
                'description' => "Reversal of Vendor Credit Application for Voided PO #{$purchaseOrder->po_number}",
                'entry_type' => 'adjustment',
                'status' => 'posted',
                'is_reversal' => true,
                'total_debit' => $usedAdvanceAmount,
                'total_credit' => $usedAdvanceAmount,
                'created_by' => auth()->id() ?? $purchaseOrder->user_id ?? \App\Models\User::first()?->id ?? 1,
                'posted_by' => auth()->id() ?? $purchaseOrder->user_id ?? \App\Models\User::first()?->id ?? 1,
                'posted_at' => now(),
                'source_type' => 'purchase_order',
                'source_id' => $purchaseOrder->id,
            ]);

            // DEBIT: Advance to Suppliers (1310) -> Increases asset balance
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $vendorAdvanceAccountId,
                'description' => "Restored Advance Balance - Voided PO #{$purchaseOrder->po_number}",
                'debit' => $usedAdvanceAmount,
                'credit' => 0,
                'debit_amount' => $usedAdvanceAmount,
                'credit_amount' => 0,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // CREDIT: Accounts Payable (2010) -> Offsets liability
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $payableAccountId,
                'description' => "Accounts Payable Offset - Voided PO #{$purchaseOrder->po_number}",
                'debit' => 0,
                'credit' => $usedAdvanceAmount,
                'debit_amount' => 0,
                'credit_amount' => $usedAdvanceAmount,
                'partner_type' => Supplier::class,
                'partner_id' => $purchaseOrder->supplier_id,
            ]);

            // Recalculate & synchronize account balances
            $vendorAdvanceAcc->updateCurrentBalance();
            $apAcc->updateCurrentBalance();

            return $journalEntry;
        });
    }

    /**
     * Reverse all GL accounting entries for a Purchase Order (PO Void / Cancellation).
     * Reverses Invoice Entry and creates explicit reversal for Utilized Advance (Debit 1310, Credit 2010).
     */
    public function reversePurchaseOrderAccounting(PurchaseOrder $purchaseOrder, float $usedAdvanceAmount = 0): void
    {
        DB::transaction(function () use ($purchaseOrder, $usedAdvanceAmount) {
            $companyId = $purchaseOrder->company_id ?: (auth()->user()?->current_company_id ?? 1);

            $existingEntries = JournalEntry::where('source_type', 'purchase_order')
                ->where('source_id', $purchaseOrder->id)
                ->where('status', 'posted')
                ->get();

            $affectedAccountIds = [];

            foreach ($existingEntries as $entry) {
                $cleanNumber = preg_replace('/^(REV-)+/i', '', $entry->entry_number);
                $reversalNumber = 'REV-' . $cleanNumber;
                $reversalDesc = 'REVERSAL: ' . preg_replace('/^(REVERSAL:\s*)+/i', '', $entry->description) . " (PO #{$purchaseOrder->po_number} Voided)";

                $reversalEntry = JournalEntry::create([
                    'company_id' => $entry->company_id,
                    'entry_number' => $reversalNumber,
                    'entry_date' => now()->toDateString(),
                    'reference' => $entry->reference,
                    'description' => $reversalDesc,
                    'entry_type' => 'adjustment',
                    'status' => 'posted',
                    'is_reversal' => true,
                    'total_debit' => $entry->total_credit,
                    'total_credit' => $entry->total_debit,
                    'created_by' => auth()->id() ?? $purchaseOrder->user_id ?? 1,
                    'posted_by' => auth()->id() ?? $purchaseOrder->user_id ?? 1,
                    'posted_at' => now(),
                    'source_type' => 'purchase_order',
                    'source_id' => $purchaseOrder->id,
                ]);

                foreach ($entry->journalEntryLines as $line) {
                    $debit = (float) ($line->credit_amount ?: $line->credit ?: 0);
                    $credit = (float) ($line->debit_amount ?: $line->debit ?: 0);

                    JournalEntryLine::create([
                        'journal_entry_id' => $reversalEntry->id,
                        'account_id' => $line->account_id,
                        'description' => 'REVERSAL: ' . $line->description,
                        'debit' => $debit,
                        'credit' => $credit,
                        'debit_amount' => $debit,
                        'credit_amount' => $credit,
                        'partner_type' => $line->partner_type,
                        'partner_id' => $line->partner_id,
                    ]);

                    if ($line->account_id) {
                        $affectedAccountIds[] = $line->account_id;
                    }
                }

                $entry->update(['status' => 'reversed']);
            }

            // If usedAdvanceAmount > 0 and no advance journal entry was previously present in $existingEntries
            $hasAdvanceReversal = false;
            foreach ($existingEntries as $entry) {
                if (str_starts_with($entry->entry_number, 'ADV') || str_contains($entry->description, 'Vendor Credit')) {
                    $hasAdvanceReversal = true;
                    break;
                }
            }

            if (!$hasAdvanceReversal && $usedAdvanceAmount > 0) {
                $this->createVendorAdvanceApplicationReversalEntry($purchaseOrder, $usedAdvanceAmount);
            }

            // Always resolve and recalculate 1310 and 2010
            $vendorAdvanceAcc = Account::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '1310')
                      ->orWhere('account_name', 'Advance to Suppliers');
                })->first();

            $apAcc = Account::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '2010')
                      ->orWhere('account_code', '20100')
                      ->orWhere('account_name', 'LIKE', '%Accounts Payable%');
                })->first();

            if ($vendorAdvanceAcc) {
                $affectedAccountIds[] = $vendorAdvanceAcc->id;
            }
            if ($apAcc) {
                $affectedAccountIds[] = $apAcc->id;
            }

            // Recalculate balances for all affected accounts
            foreach (array_unique($affectedAccountIds) as $accId) {
                $acc = Account::find($accId);
                if ($acc) {
                    $acc->updateCurrentBalance();
                }
            }
        });
    }

    /**
     * Generate journal entry number cleanly with withTrashed guard, company_id scope, and accurate sequence extraction
     */
    private function generateEntryNumber(string $prefix, ?int $companyId = null): string
    {
        $companyId = $companyId ?? (auth()->user()?->current_company_id ?? 1);
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
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
