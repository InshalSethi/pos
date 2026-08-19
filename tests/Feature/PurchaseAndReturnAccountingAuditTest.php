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
use App\Models\PurchaseReturn;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class PurchaseAndReturnAccountingAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected BankAccount $bankAccount;
    protected BankAccount $cashBankAccount;
    protected Account $bankChartAccount;
    protected Account $cashChartAccount;
    protected Account $apAccount;
    protected Account $inventoryAccount;
    protected Account $vendorAdvanceAccount;
    protected Product $product;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected DoubleEntryAccountingService $accountingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureCompanySetup::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);

        $this->user = User::factory()->create([
            'name' => 'Auditor User',
            'email' => 'auditor@erp.com',
            'password' => bcrypt('password123'),
        ]);

        $this->company = Company::create([
            'user_id' => $this->user->id,
            'company_name' => 'ERP Audit Company',
            'company_email' => 'auditor@erp.com',
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

        // Chart of Accounts Setup
        $this->inventoryAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '1040'],
            [
                'account_name' => 'Inventory Asset',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'current_balance' => 0,
                'is_active' => true,
            ]
        );

        $this->cashChartAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '10100'],
            [
                'account_name' => 'Cash Vault',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'opening_balance' => 100000.00,
                'current_balance' => 100000.00,
                'is_active' => true,
            ]
        );
        $this->cashChartAccount->update([
            'opening_balance' => 100000.00,
            'current_balance' => 100000.00,
        ]);

        $this->bankChartAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '10200'],
            [
                'account_name' => 'Meezan Bank Account',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'opening_balance' => 100000.00,
                'current_balance' => 100000.00,
                'is_active' => true,
            ]
        );
        $this->bankChartAccount->update([
            'opening_balance' => 100000.00,
            'current_balance' => 100000.00,
        ]);

        $this->apAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '20100'],
            [
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'current_balance' => 0,
                'is_active' => true,
            ]
        );

        $this->vendorAdvanceAccount = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'account_code' => '10500'],
            [
                'account_name' => 'Vendor Advance',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'current_balance' => 0,
                'is_active' => true,
            ]
        );

        // Bank Accounts Setup
        $existingCash = BankAccount::where('company_id', $this->company->id)->first();
        if ($existingCash) {
            $existingCash->update([
                'account_name' => 'Default Cash Vault',
                'bank_name' => 'Cash Vault',
                'account_number' => 'CASH-001',
                'account_type' => 'checking',
                'chart_account_id' => $this->cashChartAccount->id,
                'chart_of_account_id' => $this->cashChartAccount->id,
                'opening_balance' => 100000.00,
                'current_balance' => 100000.00,
                'is_active' => true,
            ]);
            $this->cashBankAccount = $existingCash;
        } else {
            $this->cashBankAccount = BankAccount::create([
                'company_id' => $this->company->id,
                'account_name' => 'Default Cash Vault',
                'bank_name' => 'Cash Vault',
                'account_number' => 'CASH-001',
                'account_type' => 'checking',
                'chart_account_id' => $this->cashChartAccount->id,
                'chart_of_account_id' => $this->cashChartAccount->id,
                'opening_balance' => 100000.00,
                'current_balance' => 100000.00,
                'is_active' => true,
            ]);
        }

        $this->bankAccount = BankAccount::create([
            'company_id' => $this->company->id,
            'account_name' => 'Meezan Corporate',
            'bank_name' => 'Meezan Bank',
            'account_number' => 'MB-6344',
            'account_type' => 'checking',
            'chart_account_id' => $this->bankChartAccount->id,
            'chart_of_account_id' => $this->bankChartAccount->id,
            'opening_balance' => 100000,
            'current_balance' => 100000,
            'is_active' => true,
        ]);

        // Supplier & Warehouse
        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Ali Supplier',
            'email' => 'ali@supplier.com',
            'phone' => '03001234567',
            'advance_balance' => 0,
        ]);

        $this->warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'name' => 'Default Branch Warehouse',
            'code' => 'MAIN-WH',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Widget Item',
            'sku' => 'WIDGET-001',
            'cost_price' => 10.00,
            'purchase_price' => 10.00,
            'unit_price' => 15.00,
            'stock_quantity' => 100,
            'track_inventory' => true,
        ]);

        $this->accountingService = new DoubleEntryAccountingService();
    }

    /** @test */
    public function test_scenario_1_purchase_order_lifecycle()
    {
        // 1. PO Create Fully Paid ($100 = 10 items @ $10)
        $poDataPaid = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'received',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'bank_account_id' => $this->cashBankAccount->id,
            'amount_paid' => 100.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 10,
                    'quantity_ordered' => 10,
                    'unit_cost' => 10.00,
                    'subtotal' => 100.00,
                    'total_cost' => 100.00,
                ]
            ]
        ];

        $responsePaid = $this->postJson('/api/purchase-orders', $poDataPaid);
        $responsePaid->assertStatus(201);
        $poPaid = PurchaseOrder::find($responsePaid->json('purchase_order.id'));

        // Check GL Entry: Debit Inventory ($100), Credit Cash ($100)
        $glPaid = JournalEntry::where('source_type', 'purchase_order')->where('source_id', $poPaid->id)->first();
        $this->assertNotNull($glPaid);
        $this->assertEquals(100.00, $glPaid->total_debit);
        $this->assertEquals(100.00, $glPaid->total_credit);

        // 2. PO Create Partial Paid ($100 = 10 items @ $10, Paid Cash $50, AP Credit $50)
        $poDataPartial = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'received',
            'payment_status' => 'partial',
            'payment_method' => 'cash',
            'bank_account_id' => $this->cashBankAccount->id,
            'amount_paid' => 50.00,
            'due_amount' => 50.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 10,
                    'quantity_ordered' => 10,
                    'unit_cost' => 10.00,
                    'subtotal' => 100.00,
                    'total_cost' => 100.00,
                ]
            ]
        ];

        $responsePartial = $this->postJson('/api/purchase-orders', $poDataPartial);
        $responsePartial->assertStatus(201);
        $poPartial = PurchaseOrder::find($responsePartial->json('purchase_order.id'));

        $glPartial = JournalEntry::where('source_type', 'purchase_order')->where('source_id', $poPartial->id)->first();
        $this->assertNotNull($glPartial);
        $this->assertEquals(100.00, $glPartial->total_debit);

        // 3. PO Edit (Change Qty from 10 [$100] to 15 [$150])
        $updateData = $poDataPartial;
        $updateData['items'][0]['quantity'] = 15;
        $updateData['items'][0]['quantity_ordered'] = 15;
        $updateData['items'][0]['subtotal'] = 150.00;
        $updateData['items'][0]['total_cost'] = 150.00;
        $updateData['amount_paid'] = 50.00;
        $updateData['due_amount'] = 100.00;

        $responseEdit = $this->putJson("/api/purchase-orders/{$poPartial->id}", $updateData);
        $responseEdit->assertStatus(200);

        // 4. PO Void / Cancel ($150 Voided)
        $responseVoid = $this->postJson("/api/purchase-orders/{$poPartial->id}/void");
        $responseVoid->assertStatus(200);

        $poVoided = PurchaseOrder::find($poPartial->id);
        $this->assertEquals('cancelled', $poVoided->status);
    }

    /** @test */
    public function test_scenario_2_purchase_return_lifecycle_and_split_refund()
    {
        // Setup Base Purchase Order ($100)
        $po = PurchaseOrder::create([
            'company_id' => $this->company->id,
            'po_number' => 'PO-AUDIT-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'user_id' => $this->user->id,
            'order_date' => now()->toDateString(),
            'status' => 'received',
            'payment_status' => 'partial',
            'subtotal' => 100.00,
            'total_amount' => 100.00,
            'amount_paid' => 50.00,
            'due_amount' => 50.00,
        ]);

        $po->purchaseOrderItems()->create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
            'quantity_ordered' => 10,
            'unit_cost' => 10.00,
            'subtotal' => 100.00,
            'total_cost' => 100.00,
            'quantity_received' => 10,
        ]);

        // Case A: Unpaid PO PR ($20 AP Credit)
        $prDataA = [
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged Goods',
            'status' => 'approved',
            'payment_method' => 'ap_credit',
            'bank_account_id' => null,
            'amount_received' => 20.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 2,
                    'unit_cost' => 10.00,
                ]
            ]
        ];

        $respA = $this->postJson('/api/purchase-returns', $prDataA);
        $respA->assertStatus(201);
        $prA = PurchaseReturn::find($respA->json('purchase_return.id'));

        $glA = JournalEntry::where('source_type', 'purchase_return')->where('source_id', $prA->id)->first();
        $this->assertNotNull($glA);
        $this->assertEquals(20.00, $glA->total_debit);
        $this->assertEquals(20.00, $glA->total_credit);

        // Case B: Fully Paid PR ($20 via Cash $10 + Multi-Bank $10)
        $bankBalBefore = $this->bankAccount->fresh()->current_balance;

        $prDataB = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Defective',
            'status' => 'approved',
            'payment_method' => 'mixed',
            'cash_amount' => 10.00,
            'bank_splits' => [
                ['bank_account_id' => $this->bankAccount->id, 'amount' => 10.00]
            ],
            'refund_splits' => [
                ['type' => 'cash', 'account_id' => 'COA_10100', 'amount' => 10.00],
                ['type' => 'bank', 'bank_account_id' => $this->bankAccount->id, 'bank_id' => $this->bankAccount->id, 'amount' => 10.00],
            ],
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 2,
                    'unit_cost' => 10.00,
                ]
            ]
        ];

        $respB = $this->postJson('/api/purchase-returns', $prDataB);
        $respB->assertStatus(201);
        $prB = PurchaseReturn::find($respB->json('purchase_return.id'));

        $bankBalAfter = $this->bankAccount->fresh()->current_balance;
        $this->assertEquals($bankBalBefore + 10.00, $bankBalAfter);

        // Case C: Partially Paid PR ($20 via AP Credit $10 + Vendor Store Credit $10)
        $advanceBefore = $this->supplier->fresh()->advance_balance;

        $prDataC = [
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Excess Quantity',
            'status' => 'approved',
            'payment_method' => 'mixed',
            'ap_credit_amount' => 10.00,
            'vendor_credit_amount' => 10.00,
            'refund_splits' => [
                ['type' => 'ap_credit', 'account_id' => 'COA_20100', 'amount' => 10.00],
                ['type' => 'vendor_credit', 'account_id' => 'COA_10500', 'amount' => 10.00],
            ],
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 2,
                    'unit_cost' => 10.00,
                ]
            ]
        ];

        $respC = $this->postJson('/api/purchase-returns', $prDataC);
        $respC->assertStatus(201);
        $prC = PurchaseReturn::find($respC->json('purchase_return.id'));

        $advanceAfter = $this->supplier->fresh()->advance_balance;
        $this->assertEquals($advanceBefore + 10.00, $advanceAfter);

        // PR Edit: Change Return Qty from 2 ($20) to 4 ($40)
        $editPrCData = $prDataC;
        $editPrCData['items'][0]['quantity'] = 4;
        $editPrCData['ap_credit_amount'] = 20.00;
        $editPrCData['vendor_credit_amount'] = 20.00;
        $editPrCData['refund_splits'] = [
            ['type' => 'ap_credit', 'account_id' => 'COA_20100', 'amount' => 20.00],
            ['type' => 'vendor_credit', 'account_id' => 'COA_10500', 'amount' => 20.00],
        ];

        $respEditPr = $this->putJson("/api/purchase-returns/{$prC->id}", $editPrCData);
        $respEditPr->assertStatus(200);

        // PR Void / Cancel ($40 PR Voided)
        $respReject = $this->postJson("/api/purchase-returns/{$prC->id}/reject");
        $respReject->assertStatus(200);

        $prVoided = PurchaseReturn::find($prC->id);
        $this->assertEquals('rejected', $prVoided->status);
    }

    /** @test */
    public function test_scenario_3_purchase_order_void_and_advance_restoration()
    {
        // 1. Give supplier an initial advance balance of $100 & post initial advance to COA 1310
        $this->supplier->update(['advance_balance' => 100.00]);
        $advJe = JournalEntry::create([
            'company_id' => $this->company->id,
            'entry_number' => 'ADV-INIT-001',
            'entry_date' => now()->toDateString(),
            'reference' => 'Initial Supplier Advance',
            'description' => 'Initial Advance Payment to Supplier',
            'entry_type' => 'manual',
            'status' => 'posted',
            'total_debit' => 100.00,
            'total_credit' => 100.00,
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => now(),
            'source_type' => 'manual',
            'source_id' => 1,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $advJe->id,
            'account_id' => $this->vendorAdvanceAccount->id,
            'debit_amount' => 100.00,
            'credit_amount' => 0,
            'description' => 'Supplier Advance',
            'partner_type' => Supplier::class,
            'partner_id' => $this->supplier->id,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $advJe->id,
            'account_id' => $this->cashChartAccount->id,
            'debit_amount' => 0,
            'credit_amount' => 100.00,
            'description' => 'Cash Vault',
        ]);
        $this->vendorAdvanceAccount->updateCurrentBalance();
        $this->cashChartAccount->updateCurrentBalance();

        $this->assertEquals(100.00, (float) $this->supplier->fresh()->advance_balance);
        $this->assertEquals(100.00, (float) $this->vendorAdvanceAccount->fresh()->current_balance);

        // 2. Create Purchase Order for $100 utilizing $100 advance balance
        $poData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'use_advance_balance' => true,
            'used_advance_amount' => 100.00,
            'advance_applied' => 100.00,
            'amount_paid' => 100.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 10,
                    'quantity_ordered' => 10,
                    'unit_cost' => 10.00,
                    'subtotal' => 100.00,
                    'total_cost' => 100.00,
                ]
            ]
        ];

        $response = $this->postJson('/api/purchase-orders', $poData);
        $response->assertStatus(201);
        $po = PurchaseOrder::find($response->json('purchase_order.id'));

        // Advance was consumed
        $this->assertEquals(0.00, (float) $this->supplier->fresh()->advance_balance);
        $this->assertEquals(0.00, (float) $this->vendorAdvanceAccount->fresh()->current_balance);

        // 3. Void the Purchase Order
        $voidResponse = $this->postJson("/api/purchase-orders/{$po->id}/void");
        $voidResponse->assertStatus(200);

        // Advance balance restored to supplier
        $this->assertEquals(100.00, (float) $this->supplier->fresh()->advance_balance);

        // COA 1310 Advance to Suppliers ledger restored to $100
        $this->assertEquals(100.00, (float) $this->vendorAdvanceAccount->fresh()->current_balance);

        // Check reversal journal entry
        $reversalEntries = JournalEntry::where('source_type', 'purchase_order')
            ->where('source_id', $po->id)
            ->where('is_reversal', true)
            ->get();
        $this->assertNotEmpty($reversalEntries);

        // PO status is cancelled
        $this->assertEquals('cancelled', $po->fresh()->status);
    }
}
