<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\AccountingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Laravel\Sanctum\Sanctum;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $bankAccount;
    protected $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'payments.approve',
            'payments.pay',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        $this->user = User::factory()->create();

        $this->company = Company::create([
            'user_id' => $this->user->id,
            'company_name' => 'Test Payment Company',
            'company_email' => 'payment@test.com',
            'company_phone' => '1234567890',
            'owner_role' => 'Owner',
            'team_size' => '1-10',
            'intended_tasks' => ['accounting'],
            'business_type' => 'Retail',
            'business_scale' => 'Small',
            'country' => 'US',
            'system_language' => 'en',
            'base_currency' => 'USD',
            'timezone_offset' => '+00:00',
            'fiscal_year_start' => '2026-01-01',
            'status' => 'completed',
        ]);

        $this->user->update([
            'company_id' => $this->company->id,
            'current_company_id' => $this->company->id,
            'is_setup_completed' => true,
            'onboarding_completed' => true,
        ]);
        $this->user->givePermissionTo($permissions);
        Sanctum::actingAs($this->user, ['*']);

        // Create required accounts for accounting
        $bankAccountChartAccount = Account::create([
            'company_id' => $this->company->id,
            'account_code' => '1001',
            'account_name' => 'Test Bank Account',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'is_active' => true,
            'opening_balance' => 10000,
            'current_balance' => 10000,
        ]);

        $payableAccount = Account::create([
            'company_id' => $this->company->id,
            'account_code' => '2001',
            'account_name' => 'Accounts Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'is_active' => true,
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        // Create accounting settings
        AccountingSetting::create([
            'company_id' => $this->company->id,
            'purchase_invoice_payable_account_id' => $payableAccount->id,
            'expense_payable_account_id' => $payableAccount->id,
        ]);

        // Create bank account
        $this->bankAccount = BankAccount::create([
            'company_id' => $this->company->id,
            'account_name' => 'Test Bank Account',
            'bank_name' => 'Test Bank',
            'account_number' => '123456789',
            'account_type' => 'checking',
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'is_active' => true,
            'chart_account_id' => $bankAccountChartAccount->id,
        ]);

        // Create supplier
        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Test Supplier',
            'company_name' => 'Test Supplier Inc.',
            'email' => 'supplier@test.com',
            'phone' => '123-456-7890',
            'is_active' => true,
        ]);
    }

    public function test_can_create_supplier_payment()
    {
        $paymentData = [
            'payment_type' => 'supplier_payment',
            'amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'description' => 'Payment to test supplier',
            'bank_account_id' => $this->bankAccount->id,
            'payee_type' => 'supplier',
            'payee_id' => $this->supplier->id,
            'payee_name' => $this->supplier->display_name,
            'status' => 'draft',
        ];

        $response = $this->postJson('/api/payments', $paymentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'payment' => [
                        'id',
                        'payment_number',
                        'payment_type',
                        'amount',
                        'status',
                    ]
                ]);

        $this->assertDatabaseHas('payments', [
            'payment_type' => 'supplier_payment',
            'amount' => 1000.00,
            'payee_name' => $this->supplier->display_name,
            'status' => 'draft',
        ]);
    }

    public function test_can_approve_payment()
    {
        $payment = Payment::create([
            'company_id' => $this->company->id,
            'payment_number' => 'PAY202501010001',
            'payment_type' => 'supplier_payment',
            'amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'description' => 'Test payment',
            'bank_account_id' => $this->bankAccount->id,
            'payee_type' => 'supplier',
            'payee_id' => $this->supplier->id,
            'payee_name' => $this->supplier->display_name,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/payments/{$payment->id}/approve", [
            'approval_notes' => 'Approved for payment'
        ]);

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('approved', $payment->status);
        $this->assertEquals($this->user->id, $payment->approved_by);
        $this->assertEquals('Approved for payment', $payment->approval_notes);
        $this->assertNotNull($payment->approved_at);
    }

    public function test_can_mark_draft_payment_as_paid()
    {
        $payment = Payment::create([
            'company_id' => $this->company->id,
            'payment_number' => 'PAY202501010002',
            'payment_type' => 'supplier_payment',
            'amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'description' => 'Test payment',
            'bank_account_id' => $this->bankAccount->id,
            'payee_type' => 'supplier',
            'payee_id' => $this->supplier->id,
            'payee_name' => $this->supplier->display_name,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/payments/{$payment->id}/mark-as-paid");

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals($this->user->id, $payment->paid_by);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->journal_entry_id);
        $this->assertNotNull($payment->bank_transaction_id);
    }

    public function test_can_cancel_draft_payment()
    {
        $payment = Payment::create([
            'company_id' => $this->company->id,
            'payment_number' => 'PAY202501010005',
            'payment_type' => 'supplier_payment',
            'amount' => 500.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'description' => 'Draft payment to cancel',
            'bank_account_id' => $this->bankAccount->id,
            'payee_name' => $this->supplier->display_name,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/payments/{$payment->id}/cancel");

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('cancelled', $payment->status);
    }

    public function test_can_get_payment_statistics()
    {
        Payment::create([
            'company_id' => $this->company->id,
            'payment_number' => 'PAY202501010003',
            'payment_type' => 'supplier_payment',
            'amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'description' => 'Test payment 1',
            'bank_account_id' => $this->bankAccount->id,
            'payee_name' => 'Test Supplier',
            'status' => 'paid',
            'created_by' => $this->user->id,
        ]);

        Payment::create([
            'company_id' => $this->company->id,
            'payment_number' => 'PAY202501010004',
            'payment_type' => 'expense_payment',
            'amount' => 500.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'description' => 'Test payment 2',
            'bank_account_id' => $this->bankAccount->id,
            'payee_name' => 'Test Vendor',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/payments-statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'total_payments',
                    'total_amount',
                    'pending_payments',
                    'pending_amount',
                    'by_type',
                    'by_status',
                ]);
    }

    public function test_can_get_payment_options()
    {
        $response = $this->getJson('/api/payment-options');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'bank_accounts',
                    'suppliers',
                    'employees',
                    'customers',
                    'payment_types',
                    'payment_methods',
                    'statuses',
                ]);
    }
}
