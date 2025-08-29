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
        $cashAccount = Account::firstOrCreate(
            ['account_code' => '1010'],
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

        // Create the default "Cash in Hand" bank account
        $cashInHandAccount = BankAccount::firstOrCreate(
            ['account_name' => 'Cash in Hand'],
            [
                'account_number' => 'CASH001',
                'bank_name' => 'Cash',
                'bank_branch' => null,
                'account_type' => 'other',
                'routing_number' => null,
                'swift_code' => null,
                'iban' => null,
                'currency' => 'USD',
                'opening_balance' => 0,
                'current_balance' => 0,
                'opening_date' => now()->toDateString(),
                'description' => 'Default cash in hand account for cash transactions',
                'is_active' => true,
                'is_default' => true,
                'chart_account_id' => $cashAccount->id,
                'notes' => 'System default cash account',
            ]
        );

        // Ensure this is set as the default bank account
        if ($cashInHandAccount->wasRecentlyCreated || !$cashInHandAccount->is_default) {
            // Set all other bank accounts to not default
            BankAccount::where('id', '!=', $cashInHandAccount->id)->update(['is_default' => false]);
            // Set this account as default
            $cashInHandAccount->update(['is_default' => true]);
        }
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
