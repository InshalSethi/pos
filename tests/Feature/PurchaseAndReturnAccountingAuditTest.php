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
use App\Models\AccountingSetting;
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

        $this->withoutMiddleware();

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
        $this->withHeaders(['X-Company-ID' => (string) $this->company->id]);

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

        \App\Models\Inventory::firstOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'product_variation_id' => null,
            ],
            [
                'company_id' => $this->company->id,
                'stock_qty' => 100,
                'min_stock_level' => 0,
            ]
        );

        AccountingSetting::updateOrCreate(
            ['company_id' => $this->company->id],
            [
                'inventory_asset_account_id' => $this->inventoryAccount->id,
                'purchase_invoice_payable_account_id' => $this->apAccount->id,
                'purchase_return_payable_account_id' => $this->apAccount->id,
                'cash_account_id' => $this->cashChartAccount->id,
                'bank_account_id' => $this->bankChartAccount->id,
            ]
        );

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
        $this->vendorAdvanceAccount->updateCurrentBalance();
        $this->assertEquals(0.00, (float) $this->vendorAdvanceAccount->fresh()->current_balance);

        // 3. Void the Purchase Order
        $voidResponse = $this->postJson("/api/purchase-orders/{$po->id}/void");
        $voidResponse->assertStatus(200);

        // Advance balance restored to supplier
        $this->assertEquals(100.00, (float) $this->supplier->fresh()->advance_balance);

        // COA 1310 Advance to Suppliers ledger restored to $100
        $this->vendorAdvanceAccount->updateCurrentBalance();
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

    /** @test */
    public function test_purchase_return_rejects_quantity_exceeding_warehouse_stock()
    {
        // Set physical warehouse stock to 5 units
        \App\Models\Inventory::updateOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'product_variation_id' => null,
            ],
            [
                'company_id' => $this->company->id,
                'stock_qty' => 5,
            ]
        );

        $returnData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged Goods',
            'status' => 'approved',
            'payment_method' => 'cash',
            'refund_status' => 'refunded',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 10, // Exceeds available stock of 5
                    'unit_cost' => 10.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]
            ]
        ];

        $response = $this->postJson('/api/purchase-returns', $returnData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function test_purchase_return_rejects_quantity_exceeding_po_received_limit()
    {
        // 1. Create a PO with 10 units received
        $poData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'amount_paid' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 10,
                    'quantity_ordered' => 10,
                    'quantity_received' => 10,
                    'unit_cost' => 10.00,
                    'subtotal' => 100.00,
                    'total_cost' => 100.00,
                ]
            ]
        ];

        $poRes = $this->postJson('/api/purchase-orders', $poData);
        $poRes->assertStatus(201);
        $poId = $poRes->json('purchase_order.id');

        // Set plenty of warehouse stock
        \App\Models\Inventory::updateOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'product_variation_id' => null,
            ],
            [
                'company_id' => $this->company->id,
                'stock_qty' => 50,
            ]
        );

        // 2. Attempt to return 15 units (exceeds PO received limit of 10)
        $returnData = [
            'purchase_order_id' => $poId,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Defective / Quality Issue',
            'status' => 'approved',
            'payment_method' => 'ap_credit',
            'refund_status' => 'refunded',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 15,
                    'unit_cost' => 10.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]
            ]
        ];

        $returnRes = $this->postJson('/api/purchase-returns', $returnData);
        $returnRes->assertStatus(422);
    }

    /** @test */
    public function test_purchase_return_unpaid_po_ap_credit_deduction_and_reconciliation()
    {
        // 1. Create a 100% unpaid PO ($150 = 15 items @ $10)
        $poData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => 'received',
            'amount_paid' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 15,
                    'quantity_ordered' => 15,
                    'quantity_received' => 15,
                    'unit_cost' => 10.00,
                    'subtotal' => 150.00,
                    'total_cost' => 150.00,
                ]
            ]
        ];

        $poRes = $this->postJson('/api/purchase-orders', $poData);
        $poRes->assertStatus(201);
        $poId = $poRes->json('purchase_order.id');

        // Set stock for return
        \App\Models\Inventory::updateOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'product_variation_id' => null,
            ],
            [
                'company_id' => $this->company->id,
                'stock_qty' => 15,
            ]
        );

        $this->apAccount->updateCurrentBalance();
        $this->assertEquals(150.00, (float) $this->apAccount->fresh()->current_balance);

        // 2. Submit full Return for $150 via AP Credit
        $returnData = [
            'purchase_order_id' => $poId,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged Goods',
            'status' => 'approved',
            'payment_method' => 'ap_credit',
            'bank_account_id' => null,
            'refund_status' => 'refunded',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 15,
                    'unit_cost' => 10.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]
            ]
        ];

        $returnRes = $this->postJson('/api/purchase-returns', $returnData);
        $returnRes->assertStatus(201);

        // 3. Verify COA 20100 liability is reduced back to $0
        $this->apAccount->updateCurrentBalance();
        $this->assertEquals(0.00, (float) $this->apAccount->fresh()->current_balance);

        // 4. Verify Journal Entry
        $entry = JournalEntry::where('source_type', 'purchase_return')
            ->where('source_id', $returnRes->json('purchase_return.id'))
            ->with('journalEntryLines')
            ->first();

        $this->assertNotNull($entry);
        $apDebitLine = $entry->journalEntryLines->firstWhere('account_id', $this->apAccount->id);
        $this->assertNotNull($apDebitLine);
        $this->assertEquals(150.00, (float) $apDebitLine->debit_amount);

        $invCreditLine = $entry->journalEntryLines->firstWhere('account_id', $this->inventoryAccount->id);
        $this->assertNotNull($invCreditLine);
        $this->assertEquals(150.00, (float) $invCreditLine->credit_amount);
    }

    /**
     * Test getPoItems caps max_returnable and max_allowed_qty at min(po_limit, available_stock).
     */
    public function test_purchase_return_get_po_items_caps_at_physical_stock_and_po_limit(): void
    {
        // 1. Create a PO with 10 units
        $poData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'notes' => 'PO Limit vs Physical Stock Test',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 10,
                    'quantity_ordered' => 10,
                    'quantity_received' => 10,
                    'unit_cost' => 15.00,
                    'subtotal' => 150.00,
                    'total_cost' => 150.00,
                ]
            ]
        ];

        $poRes = $this->postJson('/api/purchase-orders', $poData);
        $poRes->assertStatus(201);
        $poId = $poRes->json('purchase_order.id');

        // Case A: Stock is 9 (1 unit sold via POS)
        \App\Models\Inventory::updateOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'product_variation_id' => null,
            ],
            [
                'company_id' => $this->company->id,
                'stock_qty' => 9,
            ]
        );

        $resA = $this->getJson("/api/purchase-returns/po-items/{$poId}");
        $resA->assertStatus(200);
        $itemA = $resA->json('items.0');

        $this->assertEquals(10, $itemA['po_limit']);
        $this->assertEquals(9, $itemA['available_stock']);
        $this->assertEquals(9, $itemA['max_returnable']);
        $this->assertEquals(9, $itemA['max_allowed_qty']);

        // Case B: Stock is 15 (e.g. existing stock + PO) -> Capped at PO Limit (10)
        \App\Models\Inventory::updateOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'product_variation_id' => null,
            ],
            [
                'company_id' => $this->company->id,
                'stock_qty' => 15,
            ]
        );

        $resB = $this->getJson("/api/purchase-returns/po-items/{$poId}");
        $resB->assertStatus(200);
        $itemB = $resB->json('items.0');

        $this->assertEquals(10, $itemB['po_limit']);
        $this->assertEquals(15, $itemB['available_stock']);
        $this->assertEquals(10, $itemB['max_returnable']);
        $this->assertEquals(10, $itemB['max_allowed_qty']);

        // Case C: Partial previous return of 3 units, available stock is 8 -> Capped at PO limit (7)
        $returnData = [
            'purchase_order_id' => $poId,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Defective / Quality Issue',
            'status' => 'approved',
            'payment_method' => 'ap_credit',
            'bank_account_id' => null,
            'refund_status' => 'refunded',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 3,
                    'unit_cost' => 15.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]
            ]
        ];

        $returnRes = $this->postJson('/api/purchase-returns', $returnData);
        $returnRes->assertStatus(201);

        \App\Models\Inventory::updateOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'product_variation_id' => null,
            ],
            [
                'company_id' => $this->company->id,
                'stock_qty' => 8,
            ]
        );

        $resC = $this->getJson("/api/purchase-returns/po-items/{$poId}");
        $resC->assertStatus(200);
        $itemC = $resC->json('items.0');

        $this->assertEquals(7, $itemC['po_limit']);
        $this->assertEquals(8, $itemC['available_stock']);
        $this->assertEquals(7, $itemC['max_returnable']);
        $this->assertEquals(7, $itemC['max_allowed_qty']);
    }

    /**
     * Test Scenario: 10 units purchased on 100% Unpaid PO ($150 total due),
     * 1 unit sold via POS (Physical stock = 9 units),
     * Return created for 9 units ($135 total):
     * - Stock reduces to 0
     * - Accounts Payable liability reduces from $150 to $15
     * - Supplier ledger shows $15 net balance due for the 1 sold unit.
     */
    public function test_unpaid_po_9_unit_return_and_remaining_supplier_balance_reconciliation()
    {
        // Create a product with 0 stock
        $testProduct = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Sooper Biscuit',
            'sku' => '125',
            'cost_price' => 15.00,
            'selling_price' => 30.00,
            'stock_quantity' => 0,
            'track_inventory' => true,
        ]);

        // 1. Create a 100% Unpaid PO with 10 units at $15/unit = $150
        $poData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'received',
            'payment_status' => 'due',
            'amount_paid' => 0,
            'items' => [
                [
                    'product_id' => $testProduct->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity_ordered' => 10,
                    'quantity_received' => 10,
                    'unit_cost' => 15.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]
            ]
        ];

        $poRes = $this->postJson('/api/purchase-orders', $poData);
        $poRes->assertStatus(201);
        $poId = $poRes->json('purchase_order.id');
        $po = PurchaseOrder::find($poId);

        // Verify stock is 10
        $inv = \App\Models\Inventory::where('warehouse_id', $this->warehouse->id)->where('product_id', $testProduct->id)->first();
        $this->assertEquals(10, $inv->stock_qty);

        // 2. Simulate 1 unit sold via POS / Invoice -> Stock drops to 9
        $inventoryService = new \App\Services\WarehouseInventoryService();
        $inventoryService->adjustStock(
            $this->warehouse->id,
            $testProduct->id,
            null,
            -1,
            $this->company->id,
            'Invoice Sale',
            'INV-0001'
        );

        $inv->refresh();
        $this->assertEquals(9, $inv->stock_qty);

        // Verify getPoItems returns 9 available stock and 9 max returnable
        $itemsRes = $this->getJson("/api/purchase-returns/po-items/{$poId}");
        $itemsRes->assertStatus(200);
        $itemMeta = $itemsRes->json('items.0');
        $this->assertEquals(10, $itemMeta['po_limit']);
        $this->assertEquals(9, $itemMeta['available_stock']);
        $this->assertEquals(9, $itemMeta['max_returnable']);

        // 3. Create Purchase Return for 9 units with AP Credit
        $returnData = [
            'purchase_order_id' => $poId,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->format('Y-m-d'),
            'reason' => 'Defective batch return',
            'status' => 'approved',
            'payment_method' => 'ap_credit',
            'bank_account_id' => null,
            'amount_received' => 135.00,
            'refund_status' => 'refunded',
            'items' => [
                [
                    'product_id' => $testProduct->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 9,
                    'unit_cost' => 15.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]
            ]
        ];

        $returnRes = $this->postJson('/api/purchase-returns', $returnData);
        $returnRes->assertStatus(201);
        $returnId = $returnRes->json('purchase_return.id');

        // 4. Verify Stock is now 0 (9 units returned from 9 in stock)
        $inv->refresh();
        $this->assertEquals(0, $inv->stock_qty);

        // 5. Verify Accounting GL Entries for Return
        $returnJournal = JournalEntry::where('source_type', 'purchase_return')
            ->where('source_id', $returnId)
            ->first();
        $this->assertNotNull($returnJournal);
        $this->assertEquals(135.00, (float) $returnJournal->total_debit);
        $this->assertEquals(135.00, (float) $returnJournal->total_credit);

        // Check Debit: Accounts Payable (20100) -> 135.00
        $debitLine = $returnJournal->journalEntryLines->where('debit_amount', '>', 0)->first();
        $this->assertEquals(135.00, (float) $debitLine->debit_amount);
        $this->assertEquals('Accounts Payable', $debitLine->account->account_name);

        // Check Credit: Inventory (1040) -> 135.00
        $creditLine = $returnJournal->journalEntryLines->where('credit_amount', '>', 0)->first();
        $this->assertEquals(135.00, (float) $creditLine->credit_amount);
        $this->assertEquals('Inventory', $creditLine->account->account_name);

        // 6. Verify Supplier Ledger Net Due Balance = $15.00 ($150 PO - $135 Return)
        $ledgerRes = $this->getJson("/api/suppliers/{$this->supplier->id}/ledger");
        $ledgerRes->assertStatus(200);
        $ledgerData = $ledgerRes->json();

        $this->assertEquals(150.00, (float) $ledgerData['total_credits']); // PO Bill
        $this->assertEquals(135.00, (float) $ledgerData['total_debits']);  // PR Debit Note
        $this->assertEquals(15.00, (float) $ledgerData['closing_balance']); // Remaining Due
        $this->assertEquals(15.00, (float) $ledgerData['net_payable_due']);

        // 7. Verify COA 20100 Accounts Payable liability balance = $15.00
        $debitLine->account->refresh();
        $this->assertEquals(15.00, (float) $debitLine->account->calculateBalance());
        $this->assertEquals(15.00, (float) $debitLine->account->current_balance);

        // 8. Verify Mark as Completed status update
        $completeRes = $this->patchJson("/api/purchase-returns/{$returnId}/status", ['status' => 'completed']);
        $completeRes->assertStatus(200);
        $completeRes->assertJsonFragment(['status' => 'completed']);
        $this->assertEquals('completed', PurchaseReturn::find($returnId)->status);
    }
}

