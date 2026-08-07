<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'cash_and_bank' and 'operating_income' to the account_subtype ENUM
     * to support proper classification of bank/cash accounts and sales revenue.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `chart_of_accounts` MODIFY COLUMN `account_subtype` ENUM(
                'current_asset', 'fixed_asset', 'other_asset', 'cash_and_bank',
                'current_liability', 'long_term_liability', 'other_liability',
                'equity', 'owner_equity',
                'operating_revenue', 'other_revenue', 'operating_income',
                'cost_of_goods_sold', 'cost_of_sales', 'operating_expense', 'other_expense'
            ) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `chart_of_accounts` MODIFY COLUMN `account_subtype` ENUM(
                'current_asset', 'fixed_asset', 'other_asset',
                'current_liability', 'long_term_liability', 'other_liability',
                'equity',
                'operating_revenue', 'other_revenue',
                'cost_of_goods_sold', 'operating_expense', 'other_expense'
            ) NOT NULL");
        }
    }
};
