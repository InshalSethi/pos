<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->id();

            // Sales Invoice Accounts
            $table->foreignId('sales_invoice_revenue_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('sales_invoice_receivable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('sales_invoice_tax_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            // Sales Return Accounts
            $table->foreignId('sales_return_revenue_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('sales_return_receivable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('sales_return_tax_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            // Purchase Invoice Accounts
            $table->foreignId('purchase_invoice_expense_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('purchase_invoice_payable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('purchase_invoice_tax_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            // Purchase Return Accounts
            $table->foreignId('purchase_return_expense_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('purchase_return_payable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('purchase_return_tax_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            // Expense Accounts
            $table->foreignId('expense_default_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('expense_payable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            // Cash and Bank Accounts
            $table->foreignId('cash_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('bank_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            // Inventory Accounts
            $table->foreignId('inventory_asset_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('cost_of_goods_sold_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_settings');
    }
};
