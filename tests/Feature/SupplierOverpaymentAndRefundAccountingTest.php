<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\PaymentService;
use App\Services\PaymentReceiptService;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierOverpaymentAndRefundAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected BankAccount $bankAccount;
    protected Account $bankChartAccount;
    protected Account $apAccount;
    protected Account $inventoryAccount;
    protected Account $vendorAdvanceAccount;
    protected Product $product;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected PaymentService $paymentService;
    protected PaymentReceiptService $paymentReceiptService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        $this->user = User::factory()->create([
            'name' => 'Auditor User',
            'email' => 'auditor@erp.com',
            'password' => bcrypt('password123'),
        ]);

        $this->company = Company::create([
            'user_id' => $this->user->id,
            'company_name' => 'ERP Overpayment & Refund Co',
            'company_email' => 'audit@erp.com',
            'company_phone' => '1234567890',
            'owner_role' => 'Owner',
            'team_size' => '1-10',
            'intended_tasks' => ['accounting', 'inventory'],
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
        \Laravel\Sanctum\Sanctum::actingAs($this->user, ['*']);
        $this->actingAs($this->user);
        $this->withHeaders(['X-Company-ID' => (string) $this->company->id]);

        // Chart of accounts
        $this->inventoryAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '1040'],
            [
                'account_name' => 'Merchandise Inventory',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
            ]
        );

        $this->bankChartAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '1010'],
            [
                'account_name' => 'Cash Account (Cash)',
                'account_type' => 'asset',
                'account_subtype' => 'cash_and_bank',
                'opening_balance' => 10000,
                'current_balance' => 10000,
                'is_active' => true,
            ]
        );
        $this->bankChartAccount->update([
            'opening_balance' => 10000,
            'current_balance' => 10000,
        ]);

        $this->apAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '2010'],
            [
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
            ]
        );

        $this->vendorAdvanceAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '1310'],
            [
                'account_name' => 'Advance to Suppliers',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
            ]
        );

        $this->bankAccount = BankAccount::firstOrCreate(
            ['company_id' => $this->company->id, 'account_number' => 'CASH-001'],
            [
                'account_name' => 'Main Cash Vault',
                'account_type' => 'cash',
                'opening_balance' => 10000,
                'current_balance' => 10000,
                'chart_account_id' => $this->bankChartAccount->id,
                'is_active' => true,
                'is_default' => true,
            ]
        );
        $this->bankAccount->update([
            'chart_account_id' => $this->bankChartAccount->id,
            'opening_balance' => 10000,
            'current_balance' => 10000,
        ]);

        $this->warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'name' => 'Central Warehouse',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Apex Suppliers',
            'email' => 'apex@supplier.com',
            'phone' => '03001234567',
            'is_active' => true,
            'advance_balance' => 0.00,
        ]);

        $this->product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Product Alpha',
            'sku' => 'ALPHA-001',
            'cost_price' => 100.00,
            'selling_price' => 150.00,
            'stock_quantity' => 0,
            'track_inventory' => true,
            'is_active' => true,
        ]);

        $this->paymentService = new PaymentService();
        $this->paymentReceiptService = new PaymentReceiptService();
    }

    /**
     * Test end-to-end accounting:
     * Stage 1: Supplier has existing payable due of Rs 200.
     * Stage 2: Payment Out of Rs 300 (Overpayment) -> Due settled (Rs 200), Advance created (Rs 100).
     * Stage 3: Payment In (Supplier Refund) of Rs 100 -> Advance deducted (Rs 100), 1310 ledger cleared back to Rs 0.00.
     */
    public function test_supplier_overpayment_and_advance_refund_lifecycle()
    {
        // -------------------------------------------------------------
        // STAGE 1: Create Purchase Order for Rs 200 (2 items @ Rs 100)
        // -------------------------------------------------------------
        $poData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'due',
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'amount_paid' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 2,
                    'quantity_ordered' => 2,
                    'unit_cost' => 100.00,
                    'subtotal' => 200.00,
                    'total_cost' => 200.00,
                ]
            ]
        ];

        $poResponse = $this->postJson('/api/purchase-orders', $poData);
        $poResponse->assertStatus(201);
        $po = PurchaseOrder::find($poResponse->json('purchase_order.id'));

        $this->assertEquals(200.00, (float) $po->grand_total);
        $this->assertEquals(200.00, (float) $po->due_amount);
        $this->assertEquals(0.00, (float) $this->supplier->fresh()->advance_balance);

        // Check COA 2010 AP = 200.00
        $this->assertEquals(200.00, (float) $this->apAccount->fresh()->calculateBalance());
        $this->assertEquals(0.00, (float) $this->vendorAdvanceAccount->fresh()->calculateBalance());

        // -------------------------------------------------------------
        // STAGE 2: "Payment Out" of Rs 300 to Supplier (Overpayment)
        // -------------------------------------------------------------
        $paymentData = [
            'company_id' => $this->company->id,
            'payment_type' => 'supplier_payment',
            'payee_type' => Supplier::class,
            'payee_id' => $this->supplier->id,
            'payee_name' => $this->supplier->name,
            'bank_account_id' => $this->bankAccount->id,
            'amount' => 300.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $po->id,
            'reference_number' => $po->po_number,
            'description' => "Overpayment of Rs 300 for PO #{$po->po_number}",
            'status' => 'completed',
            'created_by' => $this->user->id,
            'paid_by' => $this->user->id,
            'paid_at' => now(),
        ];

        $payment = $this->paymentService->createPayment($paymentData);

        // Check PO is fully paid (due = 0)
        $this->assertEquals(0.00, (float) $po->fresh()->due_amount);
        $this->assertEquals(200.00, (float) $po->fresh()->amount_paid);

        // Check Supplier Advance balance = Rs 100.00
        $this->assertEquals(100.00, (float) $this->supplier->fresh()->advance_balance);

        // Check COA balances:
        // AP (2010) cleared to 0.00
        $this->assertEquals(0.00, (float) $this->apAccount->fresh()->calculateBalance());
        // Advance to Suppliers (1310) increased to 100.00
        $this->assertEquals(100.00, (float) $this->vendorAdvanceAccount->fresh()->calculateBalance());
        // Bank account balance reduced from 10000 to 9700
        $this->assertEquals(9700.00, (float) $this->bankChartAccount->fresh()->calculateBalance());

        // -------------------------------------------------------------
        // STAGE 3: "Payment In" via type `Supplier Refund` of Rs 100
        // -------------------------------------------------------------
        $receiptData = [
            'company_id' => $this->company->id,
            'receipt_type' => 'supplier_refund',
            'payer_type' => 'supplier',
            'payer_id' => $this->supplier->id,
            'payer_name' => $this->supplier->name,
            'bank_account_id' => $this->bankAccount->id,
            'amount' => 100.00,
            'receipt_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'description' => "Advance Refund from {$this->supplier->name}",
            'status' => 'deposited',
            'created_by' => $this->user->id,
        ];

        $receipt = $this->paymentReceiptService->createPaymentReceipt($receiptData);

        // 1. Supplier Advance Balance is reduced back to 0.00
        $this->assertEquals(0.00, (float) $this->supplier->fresh()->advance_balance);

        // 2. Payable due remains untouched (0.00)
        $this->assertEquals(0.00, (float) $po->fresh()->due_amount);

        // 3. COA Balances:
        // Advance to Suppliers (1310) credited back to 0.00
        $this->assertEquals(0.00, (float) $this->vendorAdvanceAccount->fresh()->calculateBalance());
        // Accounts Payable (2010) remains 0.00
        $this->assertEquals(0.00, (float) $this->apAccount->fresh()->calculateBalance());
        // Bank account increased back by 100 (9700 + 100 = 9800)
        $this->assertEquals(9800.00, (float) $this->bankChartAccount->fresh()->calculateBalance());

        // 4. Check Supplier General Ledger API response
        $ledgerResponse = $this->getJson("/api/suppliers/{$this->supplier->id}/ledger");
        $ledgerResponse->assertStatus(200);
        $transactions = $ledgerResponse->json('transactions');

        $this->assertNotEmpty($transactions);
        $types = array_column($transactions, 'type');
        $this->assertContains('Purchase Invoice', $types);
        $this->assertContains('Payment Out', $types);
        $this->assertContains('Supplier Refund', $types);

        // 5. Test Reversal on Cancellation of Refund Receipt
        $this->paymentReceiptService->updatePaymentReceipt($receipt, ['status' => 'cancelled']);

        // Supplier Advance restored back to 100.00
        $this->assertEquals(100.00, (float) $this->supplier->fresh()->advance_balance);
        // COA 1310 restored back to 100.00
        $this->assertEquals(100.00, (float) $this->vendorAdvanceAccount->fresh()->calculateBalance());
    }
}
