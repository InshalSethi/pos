<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountingSetting;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class AccountingSettingsController extends Controller
{
    /**
     * Get accounting settings
     */
    public function index(): JsonResponse
    {
        $settings = AccountingSetting::getSettings();
        
        // Load all related accounts
        $settings->load([
            'salesInvoiceRevenueAccount',
            'salesInvoiceReceivableAccount',
            'salesInvoiceTaxAccount',
            'salesReturnRevenueAccount',
            'salesReturnReceivableAccount',
            'salesReturnTaxAccount',
            'purchaseInvoiceExpenseAccount',
            'purchaseInvoicePayableAccount',
            'purchaseInvoiceTaxAccount',
            'purchaseReturnExpenseAccount',
            'purchaseReturnPayableAccount',
            'purchaseReturnTaxAccount',
            'expenseDefaultAccount',
            'expensePayableAccount',
            'cashAccount',
            'bankAccount',
            'inventoryAssetAccount',
            'costOfGoodsSoldAccount'
        ]);
        
        return response()->json($settings);
    }

    /**
     * Update accounting settings
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'sales_invoice_revenue_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_invoice_receivable_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_invoice_tax_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_return_revenue_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_return_receivable_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'sales_return_tax_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_invoice_expense_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_invoice_payable_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_invoice_tax_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_return_expense_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_return_payable_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_return_tax_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'expense_default_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'expense_payable_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'cash_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'bank_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'inventory_asset_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'cost_of_goods_sold_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
        ]);

        $settings = AccountingSetting::updateSettings($request->all());

        // Load all related accounts for response
        $settings->load([
            'salesInvoiceRevenueAccount',
            'salesInvoiceReceivableAccount',
            'salesInvoiceTaxAccount',
            'salesReturnRevenueAccount',
            'salesReturnReceivableAccount',
            'salesReturnTaxAccount',
            'purchaseInvoiceExpenseAccount',
            'purchaseInvoicePayableAccount',
            'purchaseInvoiceTaxAccount',
            'purchaseReturnExpenseAccount',
            'purchaseReturnPayableAccount',
            'purchaseReturnTaxAccount',
            'expenseDefaultAccount',
            'expensePayableAccount',
            'cashAccount',
            'bankAccount',
            'inventoryAssetAccount',
            'costOfGoodsSoldAccount'
        ]);

        return response()->json([
            'message' => 'Accounting settings updated successfully',
            'settings' => $settings
        ]);
    }

    /**
     * Get accounts grouped by type for dropdowns
     */
    public function getAccountsForDropdowns(): JsonResponse
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('account_code')
            ->get()
            ->groupBy('account_type');

        $groupedAccounts = [];
        
        foreach ($accounts as $type => $typeAccounts) {
            $groupedAccounts[$type] = $typeAccounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_type' => $account->account_type,
                    'account_subtype' => $account->account_subtype,
                    'display_name' => $account->account_code . ' - ' . $account->account_name
                ];
            });
        }

        return response()->json($groupedAccounts);
    }

    /**
     * Get default account mappings based on account types
     */
    public function getDefaultMappings(): JsonResponse
    {
        $defaults = [
            'sales_invoice_revenue_account_id' => Account::where('account_code', '4000')->first()?->id,
            'sales_invoice_receivable_account_id' => Account::where('account_code', '1030')->first()?->id,
            'sales_invoice_tax_account_id' => Account::where('account_code', '2030')->first()?->id,
            'sales_return_revenue_account_id' => Account::where('account_code', '4100')->first()?->id,
            'sales_return_receivable_account_id' => Account::where('account_code', '1030')->first()?->id,
            'sales_return_tax_account_id' => Account::where('account_code', '2030')->first()?->id,
            'purchase_invoice_expense_account_id' => Account::where('account_code', '5000')->first()?->id,
            'purchase_invoice_payable_account_id' => Account::where('account_code', '2010')->first()?->id,
            'purchase_invoice_tax_account_id' => Account::where('account_code', '1040')->first()?->id,
            'purchase_return_expense_account_id' => Account::where('account_code', '5100')->first()?->id,
            'purchase_return_payable_account_id' => Account::where('account_code', '2010')->first()?->id,
            'purchase_return_tax_account_id' => Account::where('account_code', '1040')->first()?->id,
            'expense_default_account_id' => Account::where('account_code', '6000')->first()?->id,
            'expense_payable_account_id' => Account::where('account_code', '2010')->first()?->id,
            'cash_account_id' => Account::where('account_code', '1010')->first()?->id,
            'bank_account_id' => Account::where('account_code', '1020')->first()?->id,
            'inventory_asset_account_id' => Account::where('account_code', '1050')->first()?->id,
            'cost_of_goods_sold_account_id' => Account::where('account_code', '5000')->first()?->id,
        ];

        return response()->json($defaults);
    }
}
