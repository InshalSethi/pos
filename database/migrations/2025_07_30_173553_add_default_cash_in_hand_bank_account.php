<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Account;
use App\Models\BankAccount;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure we have a Cash account in chart of accounts
        $cashAccountId = DB::table('chart_of_accounts')->where('account_code', '1010')->value('id');
        if (!$cashAccountId) {
            $cashAccountId = DB::table('chart_of_accounts')->insertGetId([
                'account_code' => '1010',
                'account_name' => 'Cash',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Cash on hand',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $company = Schema::hasTable('companies') ? DB::table('companies')->first() : null;
        $companyCurrency = $company?->base_currency ?? $company?->currency_code ?? 'PKR';

        // Create the default "Cash in Hand" bank account
        $cashInHandId = DB::table('bank_accounts')->where('account_name', 'Cash in Hand')->value('id');
        if (!$cashInHandId) {
            $cashInHandId = DB::table('bank_accounts')->insertGetId([
                'account_name' => 'Cash in Hand',
                'account_number' => 'CASH001',
                'bank_name' => 'Cash',
                'bank_branch' => null,
                'account_type' => 'other',
                'routing_number' => null,
                'swift_code' => null,
                'iban' => null,
                'currency' => $companyCurrency,
                'opening_balance' => 0,
                'current_balance' => 0,
                'opening_date' => now()->toDateString(),
                'description' => 'Default cash in hand account for cash transactions',
                'is_active' => true,
                'is_default' => true,
                'chart_account_id' => $cashAccountId,
                'notes' => 'System default cash account',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('bank_accounts')->where('id', $cashInHandId)->update(['is_default' => true]);
        }

        // Set all other bank accounts to not default
        DB::table('bank_accounts')->where('id', '!=', $cashInHandId)->update(['is_default' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default cash in hand bank account
        BankAccount::where('account_name', 'Cash in Hand')
                  ->where('account_number', 'CASH001')
                  ->delete();
    }
};
