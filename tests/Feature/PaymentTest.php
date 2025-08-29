<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\AccountingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $bankAccount;
    protected $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'payments.view']);
        Permission::create(['name' => 'payments.create']);
        Permission::create(['name' => 'payments.edit']);
        Permission::create(['name' => 'payments.delete']);

        // Create user with permissions
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['payments.view', 'payments.create', 'payments.edit', 'payments.delete']);

        // Create required accounts for accounting
        $bankAccountChartAccount = Account::create([
            'account_code' => '1001',
            'account_name' => 'Test Bank Account',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'is_active' => true,
            'opening_balance' => 10000,
            'current_balance' => 10000,
        ]);

        $payableAccount = Account::create([
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
            'purchase_invoice_payable_account_id' => $payableAccount->id,
            'expense_payable_account_id' => $payableAccount->id,
        ]);

        // Create bank account
        $this->bankAccount = BankAccount::create([
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
            'display_name' => 'Test Supplier',
            'company_name' => 'Test Supplier Inc.',
            'email' => 'supplier@test.com',
            'phone' => '123-456-7890',
            'is_active' => true,
        ]);
    }

    public function test_can_create_supplier_payment()
    {
        $this->actingAs($this->user);

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
        $this->actingAs($this->user);

        $payment = Payment::create([
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

    public function test_can_mark_payment_as_paid()
    {
        $this->actingAs($this->user);

        $payment = Payment::create([
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
            'status' => 'approved',
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

    public function test_can_get_payment_statistics()
    {
        $this->actingAs($this->user);

        // Create some test payments
        Payment::create([
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
        $this->actingAs($this->user);

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
