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
            $companyId = $sale->company_id ?: auth()->user()?->current_company_id;
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $this->generateEntryNumber('SI'),
                'entry_date' => $sale->sale_date,
                'reference' => "Sales Invoice #{$sale->sale_number}",
                'description' => "Sale to " . ($sale->customer->name ?? 'Walk-in Customer'),
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $sale->total_amount,
                'total_credit' => $sale->total_amount,
                'created_by' => $sale->user_id,
                'posted_by' => $sale->user_id,
                'posted_at' => now(),
                'source_type' => 'sale',
                'source_id' => $sale->id,
            ]);

            // Debit: Accounts Receivable (or Cash if paid)
            $receivableAccount = $sale->payment_method === 'cash' 
                ? ($this->accountingSettings->cash_account_id ?: $this->accountingSettings->sales_invoice_receivable_account_id) 
                : $this->accountingSettings->sales_invoice_receivable_account_id;

            if (!$receivableAccount) {
                throw new \Exception("No cash or receivable account configured for sales invoices.");
            }

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
     * Process multi-payment entries for sales invoice (Cash and Bank ledgers)
     */
    public function processInvoicePayments(Sale $sale, array $payments): void
    {
        if (empty($payments)) {
            return;
        }

        $companyId = $sale->company_id ?: auth()->user()?->current_company_id;

        // Resolve Sales Revenue Account
        $revenueAccountId = $this->accountingSettings->sales_invoice_revenue_account_id;
        if (!$revenueAccountId) {
            $revenueAccount = Account::where('company_id', $companyId)
                ->where('account_type', 'revenue')
                ->first();
            $revenueAccountId = $revenueAccount?->id;
        }

        foreach ($payments as $payment) {
            $type = strtolower($payment['type'] ?? $payment['method'] ?? 'cash');
            $amount = (float) ($payment['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            if ($type === 'cash') {
                // Find or resolve Default Cash account
                $cashAccountId = $this->accountingSettings->cash_account_id;
                $cashAccount = null;
                if ($cashAccountId) {
                    $cashAccount = Account::where('company_id', $companyId)->find($cashAccountId);
                }
                if (!$cashAccount) {
                    $cashAccount = Account::where('company_id', $companyId)
                        ->where('account_type', 'asset')
                        ->where(function ($q) {
                            $q->where('account_name', 'LIKE', '%Cash%')
                              ->orWhere('account_code', '1010');
                        })->first();
                }
                if (!$cashAccount) {
                    $cashAccount = Account::create([
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

                // Add amount to Default Cash account balance
                $cashAccount->current_balance = (float)$cashAccount->current_balance + $amount;
                $cashAccount->save();

                // Find and update Default Cash Vault bank account in bank_accounts table
                $cashBankAccount = \App\Models\BankAccount::where('company_id', $companyId)
                    ->where(function ($q) use ($cashAccount) {
                        if ($cashAccount) {
                            $q->where('chart_account_id', $cashAccount->id);
                        }
                        $q->orWhere('account_name', 'LIKE', '%Cash%')
                          ->orWhere('bank_name', 'LIKE', '%Cash%')
                          ->orWhere('is_default', true);
                    })->first();

                if ($cashBankAccount) {
                    // Always sync 1:1 with Chart of Accounts Cash balance
                    $newCashBalance = (float) $cashAccount->current_balance;

                    \App\Models\BankTransaction::create([
                        'company_id' => $companyId,
                        'bank_account_id' => $cashBankAccount->id,
                        'transaction_date' => $sale->sale_date,
                        'reference_number' => $sale->sale_number,
                        'description' => "Cash Payment for Sales Invoice #{$sale->sale_number}",
                        'transaction_type' => 'debit',
                        'amount' => $amount,
                        'running_balance' => $newCashBalance,
                        'status' => 'cleared',
                    ]);

                    $cashBankAccount->current_balance = $newCashBalance;
                    $cashBankAccount->save();
                }

                // Record double-entry in Chart of Accounts for Cash ledger
                if ($revenueAccountId) {
                    $journalEntry = JournalEntry::create([
                        'company_id' => $companyId,
                        'entry_number' => $this->generateEntryNumber('SI-CASH'),
                        'entry_date' => $sale->sale_date,
                        'reference' => "Sales Invoice #{$sale->sale_number}",
                        'description' => "Cash Payment for Invoice #{$sale->sale_number}",
                        'entry_type' => 'automatic',
                        'status' => 'posted',
                        'total_debit' => $amount,
                        'total_credit' => $amount,
                        'created_by' => $sale->user_id ?? auth()->id(),
                        'posted_by' => $sale->user_id ?? auth()->id(),
                        'posted_at' => now(),
                        'source_type' => 'sale',
                        'source_id' => $sale->id,
                    ]);

                    // Debit Cash Account
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $cashAccount->id,
                        'description' => "Cash Received - Invoice #{$sale->sale_number}",
                        'debit_amount' => $amount,
                        'credit_amount' => 0,
                        'partner_type' => Customer::class,
                        'partner_id' => $sale->customer_id,
                    ]);

                    // Credit Sales Revenue
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $revenueAccountId,
                        'description' => "Sales Revenue - Invoice #{$sale->sale_number} (Cash)",
                        'debit_amount' => 0,
                        'credit_amount' => $amount,
                        'partner_type' => Customer::class,
                        'partner_id' => $sale->customer_id,
                    ]);
                }

            } elseif ($type === 'bank' || $type === 'card' || $type === 'bank_transfer') {
                $bankAccountId = $payment['bank_id'] ?? null;
                $bankAccount = null;
                if ($bankAccountId) {
                    $bankAccount = \App\Models\BankAccount::where('company_id', $companyId)->find($bankAccountId);
                }
                
                // Fallback to first bank account of company if bank_id not specified
                if (!$bankAccount) {
                    $bankAccount = \App\Models\BankAccount::where('company_id', $companyId)->first();
                }

                if ($bankAccount) {
                    // Update bank account balance
                    $currentBankBal = (float) ($bankAccount->current_balance ?? 0);
                    $newBalance = $currentBankBal + $amount;

                    // Record Bank Transaction
                    \App\Models\BankTransaction::create([
                        'company_id' => $companyId,
                        'bank_account_id' => $bankAccount->id,
                        'transaction_date' => $sale->sale_date,
                        'reference_number' => $sale->sale_number,
                        'description' => "Payment for Sales Invoice #{$sale->sale_number}",
                        'transaction_type' => 'debit',
                        'amount' => $amount,
                        'running_balance' => $newBalance,
                        'status' => 'cleared',
                    ]);

                    // Update current_balance field on BankAccount
                    $bankAccount->current_balance = $newBalance;
                    $bankAccount->save();

                    // Find or resolve corresponding Chart of Accounts record for this bank
                    $bankChartAccount = null;
                    if ($bankAccount->chart_account_id) {
                        $bankChartAccount = Account::where('company_id', $companyId)->find($bankAccount->chart_account_id);
                    }
                    if (!$bankChartAccount) {
                        $bankChartAccount = Account::where('company_id', $companyId)
                            ->where('account_type', 'asset')
                            ->where('account_name', 'LIKE', "%{$bankAccount->bank_name}%")
                            ->first();
                    }
                    if (!$bankChartAccount) {
                        $bankChartAccount = Account::create([
                            'company_id' => $companyId,
                            'account_code' => '1020-' . $bankAccount->id,
                            'account_name' => $bankAccount->account_name . ' (' . ($bankAccount->bank_name ?: 'Bank') . ')',
                            'account_type' => 'asset',
                            'opening_balance' => $bankAccount->opening_balance ?? 0,
                            'current_balance' => 0,
                            'is_active' => true,
                        ]);
                        $bankAccount->update(['chart_account_id' => $bankChartAccount->id]);
                    }

                    // Add amount to bank chart account balance
                    $bankChartAccount->current_balance = (float)$bankChartAccount->current_balance + $amount;
                    $bankChartAccount->save();

                    // Record double-entry in Chart of Accounts for specific Bank ledger
                    if ($revenueAccountId) {
                        $journalEntry = JournalEntry::create([
                            'company_id' => $companyId,
                            'entry_number' => $this->generateEntryNumber('SI-BANK'),
                            'entry_date' => $sale->sale_date,
                            'reference' => "Sales Invoice #{$sale->sale_number}",
                            'description' => "Bank Payment via {$bankAccount->account_name} for Invoice #{$sale->sale_number}",
                            'entry_type' => 'automatic',
                            'status' => 'posted',
                            'total_debit' => $amount,
                            'total_credit' => $amount,
                            'created_by' => $sale->user_id ?? auth()->id(),
                            'posted_by' => $sale->user_id ?? auth()->id(),
                            'posted_at' => now(),
                            'source_type' => 'sale',
                            'source_id' => $sale->id,
                        ]);

                        // Debit Bank Chart Account
                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $bankChartAccount->id,
                            'description' => "Bank Payment - Invoice #{$sale->sale_number} ({$bankAccount->account_name})",
                            'debit_amount' => $amount,
                            'credit_amount' => 0,
                            'partner_type' => Customer::class,
                            'partner_id' => $sale->customer_id,
                        ]);

                        // Credit Sales Revenue
                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $revenueAccountId,
                            'description' => "Sales Revenue - Invoice #{$sale->sale_number} (Bank)",
                            'debit_amount' => 0,
                            'credit_amount' => $amount,
                            'partner_type' => Customer::class,
                            'partner_id' => $sale->customer_id,
                        ]);
                    }
                }
            }
        }

        // Process Accounts Receivable for any remaining due balance (Unpaid amount)
        $dueAmount = (float) $sale->total_amount - (float) $sale->paid_amount;
        if ($dueAmount > 0) {
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

            if ($revenueAccountId) {
                $jeAr = JournalEntry::create([
                    'company_id' => $companyId,
                    'entry_number' => $this->generateEntryNumber('SI-AR'),
                    'entry_date' => $sale->sale_date,
                    'reference' => "Accounts Receivable - Invoice #{$sale->sale_number}",
                    'description' => "Unpaid balance receivable for Invoice #{$sale->sale_number}",
                    'entry_type' => 'automatic',
                    'status' => 'posted',
                    'total_debit' => $dueAmount,
                    'total_credit' => $dueAmount,
                    'created_by' => $sale->user_id ?? auth()->id(),
                    'posted_by' => $sale->user_id ?? auth()->id(),
                    'posted_at' => now(),
                    'source_type' => 'sale',
                    'source_id' => $sale->id,
                ]);

                // Debit Accounts Receivable
                JournalEntryLine::create([
                    'journal_entry_id' => $jeAr->id,
                    'account_id' => $arAccount->id,
                    'description' => "Accounts Receivable - Invoice #{$sale->sale_number}",
                    'debit_amount' => $dueAmount,
                    'credit_amount' => 0,
                    'partner_type' => Customer::class,
                    'partner_id' => $sale->customer_id,
                ]);

                // Credit Sales Revenue
                JournalEntryLine::create([
                    'journal_entry_id' => $jeAr->id,
                    'account_id' => $revenueAccountId,
                    'description' => "Sales Revenue (Due Balance) - Invoice #{$sale->sale_number}",
                    'debit_amount' => 0,
                    'credit_amount' => $dueAmount,
                    'partner_type' => Customer::class,
                    'partner_id' => $sale->customer_id,
                ]);

                $arAccount->current_balance = (float) $arAccount->current_balance + $dueAmount;
                $arAccount->save();
            }
        }

        // Process Perpetual Inventory System COGS & 1040 Inventory deduction
        $this->processPerpetualInventoryCOGS($sale);
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
        $sale->loadMissing(['saleItems.product', 'saleItems.variation']);

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
            $inventoryAccount = Account::where('company_id', $companyId)
                ->where('account_type', 'asset')
                ->where(function ($q) {
                    $q->where('account_code', '1040')
                      ->orWhere('account_name', 'LIKE', '%Inventory%');
                })->first();

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
            $cogsAccount = Account::where('company_id', $companyId)
                ->where('account_type', 'expense')
                ->where(function ($q) {
                    $q->where('account_code', '5010')
                      ->orWhere('account_name', 'LIKE', '%Cost of Goods Sold%')
                      ->orWhere('account_name', 'LIKE', '%COGS%');
                })->first();

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

            // 4. Update current balances in chart_of_accounts
            $cogsAccount->current_balance = (float) $cogsAccount->current_balance + $totalCogs;
            $cogsAccount->save();

            $inventoryAccount->current_balance = (float) $inventoryAccount->current_balance - $totalCogs;
            $inventoryAccount->save();

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
            // 1. Resolve '1040 Inventory' Asset Account
            $inventoryAccount = Account::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '1040')
                      ->orWhere('account_name', 'LIKE', '%Inventory%');
                })->first();

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

            // 2. Resolve '3010 Owner's Equity' Account
            $equityAccount = Account::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('account_code', '3010')
                      ->orWhere('account_name', 'LIKE', '%Owner%Equity%')
                      ->orWhere('account_name', 'LIKE', '%Owner%Capital%')
                      ->orWhere('account_name', 'LIKE', '%Opening Balance Equity%');
                })->first();

            if (!$equityAccount) {
                $equityAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '3010',
                    'account_name' => "Owner's Equity",
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
                'entry_number' => $this->generateEntryNumber('OS'),
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

            // 4. Update current_balance of both ledgers in chart_of_accounts
            $inventoryAccount->current_balance = (float) $inventoryAccount->current_balance + $totalOpeningValue;
            $inventoryAccount->save();

            $equityAccount->current_balance = (float) $equityAccount->current_balance + $totalOpeningValue;
            $equityAccount->save();

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

        $invAcc = Account::where('company_id', $companyId)->where(function ($q) {
            $q->where('account_code', '1040')->orWhere('account_name', 'LIKE', '%Inventory%');
        })->first();

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
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $saleReturn->total_amount,
                'total_credit' => $saleReturn->total_amount,
                'created_by' => $saleReturn->user_id,
                'posted_by' => $saleReturn->user_id,
                'posted_at' => now(),
                'source_type' => 'sale_return',
                'source_id' => $saleReturn->id,
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
                ? ($this->accountingSettings->cash_account_id ?: $this->accountingSettings->sales_return_receivable_account_id) 
                : $this->accountingSettings->sales_return_receivable_account_id;

            if (!$receivableAccount) {
                throw new \Exception("No cash or receivable account configured for sales returns.");
            }

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
                'entry_type' => 'automatic',
                'status' => 'posted',
                'total_debit' => $purchaseReturn->total_amount,
                'total_credit' => $purchaseReturn->total_amount,
                'created_by' => $purchaseReturn->user_id,
                'posted_by' => $purchaseReturn->user_id,
                'posted_at' => now(),
                'source_type' => 'purchase_return',
                'source_id' => $purchaseReturn->id,
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

            // 4. Update balances in chart_of_accounts
            $receivingAccount->current_balance = (float) $receivingAccount->current_balance + $paymentAmount;
            $receivingAccount->save();

            $arAccount->current_balance = (float) $arAccount->current_balance - $paymentAmount;
            $arAccount->save();

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
