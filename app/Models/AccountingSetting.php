<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_invoice_revenue_account_id',
        'sales_invoice_receivable_account_id',
        'sales_invoice_tax_account_id',
        'sales_return_revenue_account_id',
        'sales_return_receivable_account_id',
        'sales_return_tax_account_id',
        'purchase_invoice_expense_account_id',
        'purchase_invoice_payable_account_id',
        'purchase_invoice_tax_account_id',
        'purchase_return_expense_account_id',
        'purchase_return_payable_account_id',
        'purchase_return_tax_account_id',
        'expense_default_account_id',
        'expense_payable_account_id',
        'cash_account_id',
        'bank_account_id',
        'inventory_asset_account_id',
        'cost_of_goods_sold_account_id',
    ];

    // Sales Invoice Accounts
    public function salesInvoiceRevenueAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_invoice_revenue_account_id');
    }

    public function salesInvoiceReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_invoice_receivable_account_id');
    }

    public function salesInvoiceTaxAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_invoice_tax_account_id');
    }

    // Sales Return Accounts
    public function salesReturnRevenueAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_return_revenue_account_id');
    }

    public function salesReturnReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_return_receivable_account_id');
    }

    public function salesReturnTaxAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_return_tax_account_id');
    }

    // Purchase Invoice Accounts
    public function purchaseInvoiceExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_invoice_expense_account_id');
    }

    public function purchaseInvoicePayableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_invoice_payable_account_id');
    }

    public function purchaseInvoiceTaxAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_invoice_tax_account_id');
    }

    // Purchase Return Accounts
    public function purchaseReturnExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_return_expense_account_id');
    }

    public function purchaseReturnPayableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_return_payable_account_id');
    }

    public function purchaseReturnTaxAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_return_tax_account_id');
    }

    // Expense Accounts
    public function expenseDefaultAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_default_account_id');
    }

    public function expensePayableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_payable_account_id');
    }

    // Cash and Bank Accounts
    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    // Inventory Accounts
    public function inventoryAssetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_asset_account_id');
    }

    public function costOfGoodsSoldAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cost_of_goods_sold_account_id');
    }

    /**
     * Get the singleton accounting settings instance
     */
    public static function getSettings(): self
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([]);
        }
        
        return $settings;
    }

    /**
     * Update accounting settings
     */
    public static function updateSettings(array $data): self
    {
        $settings = self::getSettings();
        $settings->update($data);
        
        return $settings;
    }
}
