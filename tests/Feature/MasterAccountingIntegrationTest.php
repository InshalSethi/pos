<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\AccountingSetting;
use App\Models\Tax;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class MasterAccountingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected BankAccount $bankAccount;
    protected BankAccount $cashBankAccount;
    protected Account $bankChartAccount;
    protected Account $cashChartAccount;
    protected Account $salesRevenueAccount;
    protected Account $salesReturnAccount;
    protected Account $taxLiabilityAccount;
    protected Account $arAccount;
    protected Account $inventoryAccount;
    protected Account $cogsAccount;
    protected Account $apAccount;
    protected Account $equityAccount;
    protected Tax $tax;
    protected Product $product;
    protected Customer $customer;
    protected Supplier $supplier;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Permissions & Test User (azlantester@gmail.com)
        $permissions = [
            'sales.view', 'sales.create', 'sales.edit', 'sales.delete', 'sales.refund',
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete', 'purchases.approve',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            'banking.view', 'banking.create'
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        $this->user = User::factory()->create([
            'name' => 'Azlan Tester',
            'email' => 'azlantester@gmail.com',
            'password' => bcrypt('123456789'),
        ]);

        // 2. Create Company linked to user with all required non-null fields
        $this->company = Company::create([
            'user_id' => $this->user->id,
            'company_name' => 'Azlan Test Company',
            'company_email' => 'azlantester@gmail.com',
            'company_phone' => '1234567890',
            'owner_role' => 'Owner',
            'team_size' => '1-10',
            'intended_tasks' => ['accounting', 'sales', 'inventory'],
            'business_type' => 'Retail',
            'business_scale' => 'Small',
            'country' => 'US',
            'system_language' => 'en',
            'base_currency' => 'USD',
            'timezone_offset' => '+00:00',
            'fiscal_year_start' => '2026-01-01',
            'status' => 'completed',
        ]);

        $this->user->update(['current_company_id' => $this->company->id]);
        $this->user->givePermissionTo($permissions);
        $this->actingAs($this->user);

        // 3. Resolve or Create Required Chart of Accounts (COA)
        $this->bankChartAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '1610',
        ], [
            'account_name' => 'Azlan Test Bank',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'opening_balance' => 1000.00,
            'current_balance' => 1000.00,
            'is_active' => true,
        ]);

        $this->cashChartAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '1010',
        ], [
            'account_name' => 'Cash',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'opening_balance' => 100.00,
            'current_balance' => 100.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->salesRevenueAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '4010',
        ], [
            'account_name' => 'Retail Sales Revenue',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->salesReturnAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '4020',
        ], [
            'account_name' => 'Sales Returns & Allowances',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_income',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->taxLiabilityAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '2020',
        ], [
            'account_name' => 'Sales Tax Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->arAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '1030',
        ], [
            'account_name' => 'Accounts Receivable',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->inventoryAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '1040',
        ], [
            'account_name' => 'Inventory',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->cogsAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '5010',
        ], [
            'account_name' => 'Cost of Goods Sold',
            'account_type' => 'expense',
            'account_subtype' => 'cost_of_goods_sold',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->apAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '20100',
        ], [
            'account_name' => 'Accounts Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        $this->equityAccount = Account::firstOrCreate([
            'company_id' => $this->company->id,
            'account_code' => '3010',
        ], [
            'account_name' => "Owner's Equity",
            'account_type' => 'equity',
            'account_subtype' => 'owner_equity',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => true,
            'is_system_account' => true,
        ]);

        // 4. Configure Accounting Settings
        AccountingSetting::updateSettings([
            'company_id' => $this->company->id,
            'sales_invoice_revenue_account_id' => $this->salesRevenueAccount->id,
            'sales_invoice_receivable_account_id' => $this->arAccount->id,
            'sales_invoice_tax_account_id' => $this->taxLiabilityAccount->id,
            'sales_return_revenue_account_id' => $this->salesReturnAccount->id,
            'sales_return_receivable_account_id' => $this->arAccount->id,
            'sales_return_tax_account_id' => $this->taxLiabilityAccount->id,
            'purchase_invoice_payable_account_id' => $this->apAccount->id,
            'inventory_asset_account_id' => $this->inventoryAccount->id,
            'cost_of_goods_sold_account_id' => $this->cogsAccount->id,
            'cash_account_id' => $this->cashChartAccount->id,
            'bank_account_id' => $this->bankChartAccount->id,
        ]);

        // 5. Create Bank Accounts
        $this->bankAccount = BankAccount::create([
            'company_id' => $this->company->id,
            'account_name' => 'Azlan Test Bank',
            'bank_name' => 'Azlan Test Bank',
            'account_number' => '987654321',
            'account_type' => 'checking',
            'opening_balance' => 1000.00,
            'current_balance' => 1000.00,
            'is_active' => true,
            'chart_account_id' => $this->bankChartAccount->id,
        ]);

        $this->cashBankAccount = BankAccount::create([
            'company_id' => $this->company->id,
            'account_name' => 'Cash Vault',
            'bank_name' => 'Cash Vault',
            'account_number' => 'CASH-001',
            'account_type' => 'other',
            'opening_balance' => 100.00,
            'current_balance' => 100.00,
            'is_active' => true,
            'chart_account_id' => $this->cashChartAccount->id,
        ]);

        // 6. Create VAT Tax (2%)
        $this->tax = Tax::create([
            'company_id' => $this->company->id,
            'name' => 'VAT Tax 2%',
            'code' => 'VAT2',
            'type' => 'percentage',
            'value' => 2.00,
            'is_active' => true,
        ]);

        // 7. Create Default Warehouse
        $this->warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_default' => true,
            'is_active' => true,
            'is_saleable' => true,
        ]);

        // 8. Create Item 'Audit Product' (Cost: $10.00, Price: $20.00, Tax: 2%, Stock: 10 units)
        // ProductObserver automatically creates Opening Stock Journal Entry ($100.00 Debit COA 1040 / Credit COA 3010)
        $this->product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Audit Product',
            'sku' => 'AUDIT-PROD-001',
            'cost_price' => 10.00,
            'purchase_price' => 10.00,
            'selling_price' => 20.00,
            'unit_price' => 20.00,
            'stock_quantity' => 10,
            'quantity' => 10,
            'track_inventory' => true,
            'is_active' => true,
            'tax_rate' => 2.00,
            'taxes' => [$this->tax->id],
        ]);

        Inventory::create([
            'company_id' => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'stock_qty' => 10,
        ]);

        // 9. Create Customer & Supplier
        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Audit Customer',
            'email' => 'customer@test.com',
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Audit Supplier',
            'company_name' => 'Audit Supplier Co',
            'email' => 'supplier@test.com',
            'phone' => '0987654321',
            'is_active' => true,
        ]);

        // Update current balances for initial ledgers
        $this->inventoryAccount->updateCurrentBalance();
    }

    /**
     * Master End-to-End Accounting & Banking Audit Test Suite
     */
    public function test_master_accounting_and_banking_integration_lifecycle()
    {
        // =========================================================================
        // PART 1: SALE INVOICE LIFECYCLE (Create, Edit, Void, Return & Assertions)
        // =========================================================================

        // -------------------------------------------------------------------------
        // Step 1: Create Sale Invoice for 5 units of 'Audit Product'
        // ($100 subtotal + 2% VAT = $102.00 total)
        // -------------------------------------------------------------------------
        $sale1Data = [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 0, // Unpaid credit invoice
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 5,
                    'unit_price' => 20.00,
                    'tax_rate' => 2.00,
                    'tax_id' => $this->tax->id,
                ]
            ]
        ];

        $response1 = $this->postJson('/api/sales', $sale1Data);
        $response1->assertStatus(201);
        $sale1 = Sale::find($response1->json('sale.id'));
        $this->assertNotNull($sale1);

        // Financial & Accounting Assertions (Step 1)
        $this->salesRevenueAccount->refresh();
        $this->taxLiabilityAccount->refresh();
        $this->arAccount->refresh();
        $this->inventoryAccount->refresh();
        $this->cogsAccount->refresh();

        $this->assertEquals(100.00, (float) $this->salesRevenueAccount->current_balance, 'COA 4010 (Sales Revenue) must increase by +$100.00');
        $this->assertEquals(2.00, (float) $this->taxLiabilityAccount->current_balance, 'COA 2020 (Sales Tax Liability) must increase by +$2.00');
        $this->assertEquals(102.00, (float) $this->arAccount->current_balance, 'COA 1030 (Accounts Receivable) must increase by +$102.00');
        $this->assertEquals(50.00, (float) $this->inventoryAccount->current_balance, 'COA 1040 (Inventory) must decrease to $50.00 ($100 - $50)');
        $this->assertEquals(50.00, (float) $this->cogsAccount->current_balance, 'COA 5010 (COGS) must increase to $50.00');

        $journalEntriesCount1 = JournalEntry::where('source_type', 'sale')->where('source_id', $sale1->id)->count();
        $this->assertGreaterThanOrEqual(1, $journalEntriesCount1, 'Journal Entry #1 must be created');

        // -------------------------------------------------------------------------
        // Step 2: Edit Sale Invoice - Quantity Up from 5 units to 8 units
        // ($160 subtotal + 2% VAT = $163.20 total)
        // -------------------------------------------------------------------------
        $sale1EditData = [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'paid_amount' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 8,
                    'unit_price' => 20.00,
                    'tax_rate' => 2.00,
                    'tax_id' => $this->tax->id,
                ]
            ]
        ];

        $response2 = $this->putJson("/api/sales/{$sale1->id}", $sale1EditData);
        $response2->assertStatus(200);

        // Dynamic Assertions (Step 2)
        $this->salesRevenueAccount->refresh();
        $this->taxLiabilityAccount->refresh();
        $this->arAccount->refresh();
        $this->inventoryAccount->refresh();
        $this->cogsAccount->refresh();

        $this->assertEquals(160.00, (float) $this->salesRevenueAccount->current_balance, 'COA 4010 (Sales Revenue) must update to $160.00');
        $this->assertEquals(3.20, (float) $this->taxLiabilityAccount->current_balance, 'COA 2020 (Sales Tax Liability) must update to $3.20');
        $this->assertEquals(163.20, (float) $this->arAccount->current_balance, 'COA 1030 (Accounts Receivable) must update to $163.20');
        $this->assertEquals(20.00, (float) $this->inventoryAccount->current_balance, 'COA 1040 (Inventory) must update to $20.00 ($100 - $80)');
        $this->assertEquals(80.00, (float) $this->cogsAccount->current_balance, 'COA 5010 (COGS) must update to $80.00');

        // -------------------------------------------------------------------------
        // Step 3: Void Sale Invoice
        // -------------------------------------------------------------------------
        $response3 = $this->postJson("/api/sales/{$sale1->id}/void");
        $response3->assertStatus(200);

        // Assertions (Step 3)
        $this->salesRevenueAccount->refresh();
        $this->taxLiabilityAccount->refresh();
        $this->arAccount->refresh();
        $this->inventoryAccount->refresh();
        $this->cogsAccount->refresh();

        $this->assertEquals(0.00, (float) $this->salesRevenueAccount->current_balance, 'COA 4010 (Sales Revenue) drops back to $0.00');
        $this->assertEquals(0.00, (float) $this->taxLiabilityAccount->current_balance, 'COA 2020 (Sales Tax) drops back to $0.00');
        $this->assertEquals(0.00, (float) $this->arAccount->current_balance, 'COA 1030 (Accounts Receivable) drops back to $0.00');
        $this->assertEquals(100.00, (float) $this->inventoryAccount->current_balance, 'COA 1040 (Inventory) returns to $100.00');
        $this->assertEquals(0.00, (float) $this->cogsAccount->current_balance, 'COA 5010 (COGS) drops back to $0.00');

        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock_quantity, 'Stock restored to 10 units');

        // -------------------------------------------------------------------------
        // Step 4: New Sale Invoice + Sales Return
        // Action A: Create new Sale Invoice for 5 units ($100 subtotal + $2 VAT = $102.00)
        // -------------------------------------------------------------------------
        $sale2Data = [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'warehouse_id' => $this->warehouse->id,
                    'quantity' => 5,
                    'unit_price' => 20.00,
                    'tax_rate' => 2.00,
                    'tax_id' => $this->tax->id,
                ]
            ]
        ];

        $response4A = $this->postJson('/api/sales', $sale2Data);
        $response4A->assertStatus(201);
        $sale2 = Sale::find($response4A->json('sale.id'));

        $this->salesRevenueAccount->refresh();
        $this->assertEquals(100.00, (float) $this->salesRevenueAccount->current_balance, 'Action A: COA 4010 = $100.00');

        // Action B: Process Sales Return for 2 units ($40 subtotal + $0.80 VAT = $40.80 refund)
        $sale2Item = $sale2->saleItems()->first();
        $returnData = [
            'original_sale_id' => $sale2->id,
            'return_date' => now()->toDateString(),
            'return_reason' => 'wrong_item',
            'refund_method' => 'store_credit',
            'return_items' => [
                [
                    'original_item_id' => $sale2Item->id,
                    'quantity' => 2,
                    'return_amount' => 40.80,
                ]
            ]
        ];

        $response4B = $this->postJson('/api/sales/returns', $returnData);
        $response4B->assertStatus(201);

        // Assertions (Step 4)
        $this->salesRevenueAccount->refresh();
        $this->salesReturnAccount->refresh();
        $this->taxLiabilityAccount->refresh();
        $this->arAccount->refresh();
        $this->inventoryAccount->refresh();
        $this->cogsAccount->refresh();

        $this->assertEquals(100.00, (float) $this->salesRevenueAccount->current_balance, 'COA 4010 (Gross Revenue) REMAINS $100.00');
        $this->assertEquals(-40.00, (float) $this->salesReturnAccount->current_balance, 'COA 4020 (Sales Returns & Allowances) DEBITS +$40.00');
        $this->assertEquals(1.20, (float) $this->taxLiabilityAccount->current_balance, 'COA 2020 (Sales Tax) DEBITS +$0.80 (balance = $1.20)');

        $netRevenue = (float) $this->salesRevenueAccount->current_balance + (float) $this->salesReturnAccount->current_balance;
        $this->assertEquals(60.00, $netRevenue, 'Net Revenue (4010 + 4020) == $60.00');

        $this->assertEquals(61.20, (float) $this->arAccount->current_balance, 'Accounts Receivable decreases by $40.80 (from $102.00 to $61.20)');

        $this->product->refresh();
        $this->assertEquals(7, $this->product->stock_quantity, 'Stock restored by +2 units (5 + 2 = 7 units)');
        $this->assertEquals(70.00, (float) $this->inventoryAccount->current_balance, 'COA 1040 (Inventory) restored by +$20.00 (from $50 to $70)');
        $this->assertEquals(30.00, (float) $this->cogsAccount->current_balance, 'COA 5010 (COGS) credited by $20.00 (drops to $30.00)');

        $returnJournalEntry = JournalEntry::where('source_type', 'sale_return')->latest()->first();
        $this->assertNotNull($returnJournalEntry, 'Journal Entry for Sales Return verified');

        // =========================================================================
        // PART 2: PURCHASE ORDER LIFECYCLE (Create, Upfront Payments, Edit, Return)
        // =========================================================================

        // -------------------------------------------------------------------------
        // Step 5: Customer Receivables & Cash Inflow
        // Receive payment for customer due ($61.20) into Azlan Test Bank
        // -------------------------------------------------------------------------
        $accountingService = new DoubleEntryAccountingService();
        $accountingService->processPartialPaymentReceipt($sale2, 61.20, 'bank', $this->bankAccount->id);

        $this->arAccount->refresh();
        $this->bankAccount->refresh();
        $this->bankChartAccount->refresh();

        $this->assertEquals(0.00, (float) $this->arAccount->current_balance, 'Accounts Receivable cleared to $0.00');
        $this->assertEquals(1061.20, (float) $this->bankAccount->current_balance, 'Azlan Test Bank subledger updated to $1,061.20');
        $this->assertEquals(1061.20, (float) $this->bankChartAccount->current_balance, 'COA 1610 Bank updated to $1,061.20');
        $this->assertEquals((float) $this->bankAccount->current_balance, (float) $this->bankChartAccount->current_balance, 'Zero Mismatch Rule: Subledger matches COA 1610 EXACTLY');

        // -------------------------------------------------------------------------
        // Step 6: Create Purchase Order with Upfront Split Payment
        // PO for 10 units ($100 total). Pay $8.00 Cash + $92.00 Azlan Test Bank.
        // -------------------------------------------------------------------------
        $poData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'expected_delivery_date' => now()->addDays(3)->toDateString(),
            'amount_paid' => 100.00,
            'payment_details' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 8.00,
                    'bank_account_id' => $this->cashBankAccount->id,
                ],
                [
                    'payment_method' => 'bank_transfer',
                    'amount' => 92.00,
                    'bank_account_id' => $this->bankAccount->id,
                ]
            ],
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_ordered' => 10,
                    'unit_cost' => 10.00,
                ]
            ]
        ];

        $response6 = $this->postJson('/api/purchase-orders', $poData);
        $response6->assertStatus(201);
        $po = PurchaseOrder::find($response6->json('purchase_order.id'));
        $this->assertNotNull($po);

        // Assertions (Step 6)
        $this->inventoryAccount->refresh();
        $this->apAccount->refresh();
        $this->cashBankAccount->refresh();
        $this->bankAccount->refresh();
        $this->cashChartAccount->refresh();
        $this->bankChartAccount->refresh();

        $this->assertEquals(170.00, (float) $this->inventoryAccount->current_balance, 'Inventory Asset (COA 1040) increases by +$100.00 (to $170.00)');
        $this->assertEquals(0.00, (float) $this->apAccount->current_balance, 'Accounts Payable (COA 20100) Net Payable Due = $0.00');

        $this->assertEquals(-8.00, (float) $this->cashBankAccount->current_balance, 'Cash Vault balance DECREMENTS by -$8.00');
        $this->assertEquals(969.20, (float) $this->bankAccount->current_balance, 'Azlan Test Bank balance DECREMENTS by -$92.00 (from $1,061.20 to $969.20)');

        $this->assertEquals((float) $this->cashBankAccount->current_balance, (float) $this->cashChartAccount->current_balance, 'COA 1010 Cash matches Cash Vault subledger EXACTLY');
        $this->assertEquals((float) $this->bankAccount->current_balance, (float) $this->bankChartAccount->current_balance, 'COA 1610 Bank matches Azlan Test Bank subledger EXACTLY');

        $poJournal = JournalEntry::where('source_type', 'purchase_order')->where('source_id', $po->id)->first();
        $this->assertNotNull($poJournal, 'Outbound Payment / Purchase Journal Entry verified');

        // -------------------------------------------------------------------------
        // Step 7: Edit Purchase Order (Quantity 10 to 15 units = $150.00 total)
        // -------------------------------------------------------------------------
        $poEditData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'amount_paid' => 100.00,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_ordered' => 15,
                    'unit_cost' => 10.00,
                ]
            ]
        ];

        $response7 = $this->putJson("/api/purchase-orders/{$po->id}", $poEditData);
        $response7->assertStatus(200);

        // Assertions (Step 7)
        $this->inventoryAccount->refresh();
        $this->apAccount->refresh();
        $this->bankAccount->refresh();
        $this->bankChartAccount->refresh();

        $this->assertEquals(220.00, (float) $this->inventoryAccount->current_balance, 'Inventory Asset (COA 1040) updates to $220.00 ($70 + $150)');
        $this->assertEquals(50.00, (float) $this->apAccount->current_balance, 'Accounts Payable (COA 20100) reflects remaining $50.00 due ($150 - $100 paid)');

        $this->assertEquals((float) $this->bankAccount->current_balance, (float) $this->bankChartAccount->current_balance, 'COA Bank Subledger re-synced atomically');

        // -------------------------------------------------------------------------
        // Step 8: Purchase Return / Supplier Return (5 units back = -$50.00)
        // -------------------------------------------------------------------------
        $purchaseReturnData = [
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Defective batch',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_cost' => 10.00,
                ]
            ]
        ];

        $response8 = $this->postJson('/api/purchase-returns', $purchaseReturnData);
        $response8->assertStatus(201);

        // Assertions (Step 8)
        $this->inventoryAccount->refresh();
        $this->apAccount->refresh();

        $this->assertEquals(170.00, (float) $this->inventoryAccount->current_balance, 'Inventory Asset (COA 1040) decreases by -$50.00 (from $220 to $170)');
        $this->assertEquals(0.00, (float) $this->apAccount->current_balance, 'Accounts Payable (COA 20100) debited by $50.00 (reducing AP balance to $0.00)');

        $prJournal = JournalEntry::where('source_type', 'purchase_return')->latest()->first();
        $this->assertNotNull($prJournal, 'Journal Entry for Purchase Return verified');

        // -------------------------------------------------------------------------
        // FINAL INTEGRITY CHECK & ZERO MISMATCH RULE
        // -------------------------------------------------------------------------
        $this->bankAccount->refresh();
        $this->bankChartAccount->refresh();
        $this->cashBankAccount->refresh();
        $this->cashChartAccount->refresh();

        $this->assertEquals(
            (float) $this->bankAccount->current_balance,
            (float) $this->bankChartAccount->current_balance,
            'ZERO MISMATCH RULE: bank_accounts.current_balance MUST EQUAL linked COA current_balance'
        );

        $this->assertEquals(
            (float) $this->cashBankAccount->current_balance,
            (float) $this->cashChartAccount->current_balance,
            'ZERO MISMATCH RULE: Cash Vault subledger MUST EQUAL linked Cash COA current_balance'
        );
    }
}
