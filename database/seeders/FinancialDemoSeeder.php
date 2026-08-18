<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\DoubleEntryAccountingService;
use App\Services\ExpenseAccountingService;
use Carbon\Carbon;

class FinancialDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Financial Real Data Seeding via Eloquent Factories...');

        // 1. Resolve Active Company and User
        $user = User::firstWhere('email', 'admin@gmail.com') ?? User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
            ]);
        }

        $company = Company::first();
        if (!$company) {
            $company = Company::factory()->create([
                'company_name' => 'Aura Enterprise (Sample)',
                'user_id' => $user->id,
                'status' => 'active',
            ]);
        }

        $user->current_company_id = $company->id;
        $user->save();
        $companyId = $company->id;

        auth()->setUser($user);

        // 2. Ensure Chart of Accounts & Bank Accounts are Provisioned
        $this->call(ChartOfAccountsSeeder::class);
        $this->call(DefaultBankAccountSeeder::class);

        $cashCoa = ChartOfAccount::where('company_id', $companyId)->where('account_code', '1010')->first();
        $bankCoa = ChartOfAccount::where('company_id', $companyId)->where('account_code', '1020')->first();
        $arCoa   = ChartOfAccount::where('company_id', $companyId)->where('account_code', '1030')->first();
        $equityCoa = ChartOfAccount::where('company_id', $companyId)->where('account_code', '3010')->first();

        $mainBank = BankAccount::where('company_id', $companyId)->first();

        // 3. Seed Initial Owner Capital Entry
        if (!JournalEntry::where('company_id', $companyId)->where('reference', 'INIT-CAPITAL')->exists()) {
            $capEntry = JournalEntry::factory()->create([
                'company_id' => $companyId,
                'entry_number' => 'JE-CAP-001',
                'entry_date' => Carbon::now()->subDays(60)->toDateString(),
                'reference' => 'INIT-CAPITAL',
                'description' => 'Initial Owner Capital Investment',
                'status' => 'posted',
                'total_debit' => 100000.00,
                'total_credit' => 100000.00,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $capEntry->id,
                'account_id' => $bankCoa?->id ?? $cashCoa?->id,
                'description' => 'Initial Capital Cash Deposit',
                'debit_amount' => 100000.00,
                'credit_amount' => 0,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $capEntry->id,
                'account_id' => $equityCoa?->id,
                'description' => 'Owner Capital Contribution',
                'debit_amount' => 0,
                'credit_amount' => 100000.00,
            ]);
        }

        // 4. Create Categories via CategoryFactory
        $catElectronics = Category::firstOrCreate(['company_id' => $companyId, 'slug' => 'electronics'], ['name' => 'Electronics', 'is_active' => true]);
        $catFurniture   = Category::firstOrCreate(['company_id' => $companyId, 'slug' => 'furniture'], ['name' => 'Office Furniture', 'is_active' => true]);
        $catAccessories = Category::firstOrCreate(['company_id' => $companyId, 'slug' => 'accessories'], ['name' => 'Computer Accessories', 'is_active' => true]);

        // 5. Create Products via ProductFactory
        $products = collect();
        $sampleProducts = [
            ['name' => 'MacBook Pro 16" M3 Max', 'sku' => 'LAP-MBP-16', 'cat' => $catElectronics->id, 'selling' => 2499.00, 'cost' => 1750.00],
            ['name' => 'iPhone 15 Pro Max 256GB', 'sku' => 'MOB-IPH-15P', 'cat' => $catElectronics->id, 'selling' => 1199.00, 'cost' => 820.00],
            ['name' => 'Sony WH-1000XM5 Headphones', 'sku' => 'AUD-SNY-XM5', 'cat' => $catAccessories->id, 'selling' => 399.00, 'cost' => 240.00],
            ['name' => 'Ergonomic Executive Office Chair', 'sku' => 'FUR-ERG-CHR', 'cat' => $catFurniture->id, 'selling' => 450.00, 'cost' => 260.00],
            ['name' => 'Dual Motor Electric Standing Desk', 'sku' => 'FUR-STN-DSK', 'cat' => $catFurniture->id, 'selling' => 699.00, 'cost' => 420.00],
            ['name' => 'Logitech MX Master 3S Mouse', 'sku' => 'ACC-LOG-M3S', 'cat' => $catAccessories->id, 'selling' => 99.00, 'cost' => 55.00],
        ];

        foreach ($sampleProducts as $pDef) {
            $p = Product::where('company_id', $companyId)->where('sku', $pDef['sku'])->first();
            if (!$p) {
                $p = Product::factory()->create([
                    'company_id' => $companyId,
                    'name' => $pDef['name'],
                    'sku' => $pDef['sku'],
                    'category_id' => $pDef['cat'],
                    'selling_price' => $pDef['selling'],
                    'cost_price' => $pDef['cost'],
                    'stock_quantity' => 50,
                ]);
            }
            $products->push($p);
        }

        // 6. Create Customers & Suppliers via Factories
        $customers = Customer::where('company_id', $companyId)->get();
        if ($customers->isEmpty()) {
            $customers = Customer::factory()->count(6)->create(['company_id' => $companyId]);
        }

        Supplier::factory()->count(2)->create(['company_id' => $companyId]);

        // 7. Generate Real Sales Transactions & Accounting Postings using Factories & Service
        $accountingService = app(DoubleEntryAccountingService::class);
        $lastSale = Sale::where('company_id', $companyId)->latest('id')->first();
        $saleNumber = $lastSale && is_numeric(str_replace('INV-', '', $lastSale->sale_number))
            ? ((int) str_replace('INV-', '', $lastSale->sale_number)) + 1
            : rand(2000, 9000);

        for ($dayOffset = 30; $dayOffset >= 0; $dayOffset--) {
            $saleDate = Carbon::now()->subDays($dayOffset);

            for ($k = 0; $k < rand(1, 2); $k++) {
                $customer = $customers->random();
                $selectedProducts = $products->random(rand(1, 3));

                $subtotal = 0;
                $itemsData = [];

                foreach ($selectedProducts as $prod) {
                    $qty = rand(1, 3);
                    $lineTotal = round($qty * $prod->selling_price, 2);
                    $subtotal += $lineTotal;

                    $itemsData[] = [
                        'product' => $prod,
                        'qty' => $qty,
                        'unit_price' => $prod->selling_price,
                        'line_total' => $lineTotal,
                    ];
                }

                $taxAmount = round($subtotal * 0.05, 2);
                $totalAmount = round($subtotal + $taxAmount, 2);
                $paymentMethod = fake()->randomElement(['cash', 'bank_transfer', 'card']);

                // Create Sale model via SaleFactory state
                $sale = Sale::factory()->create([
                    'company_id' => $companyId,
                    'sale_number' => 'INV-' . $saleNumber++,
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'sale_date' => $saleDate->toDateString(),
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                ]);

                foreach ($itemsData as $item) {
                    SaleItem::factory()->create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product']->id,
                        'quantity' => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'total_amount' => $item['line_total'],
                        'created_at' => $saleDate,
                        'updated_at' => $saleDate,
                    ]);
                }

                // Process double-entry ledger posting
                $accountingService->processSalesInvoiceAccounting($sale, [
                    [
                        'type' => $paymentMethod === 'cash' ? 'cash' : 'bank',
                        'method' => $paymentMethod,
                        'amount' => $totalAmount,
                        'bank_id' => $mainBank?->id,
                    ]
                ]);
            }
        }

        // 8. Generate Operating Expenses via ExpenseCategoryFactory & ExpenseFactory
        $expenseCategories = [
            'Rent & Facilities' => '6010',
            'Salaries & Payroll' => '6020',
            'Utilities & Electricity' => '6030',
            'Marketing & Advertising' => '6040',
        ];

        foreach ($expenseCategories as $catName => $code) {
            ExpenseCategory::firstOrCreate(['company_id' => $companyId, 'code' => $code], [
                'name' => $catName,
                'is_active' => true,
            ]);

            ChartOfAccount::firstOrCreate([
                'company_id' => $companyId,
                'account_code' => $code,
            ], [
                'account_name' => $catName,
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
            ]);
        }

        $expenseItems = [
            ['title' => 'Commercial Office Rent', 'code' => '6010', 'amount' => 3500.00, 'days' => 45],
            ['title' => 'Staff Salaries & Payroll', 'code' => '6020', 'amount' => 8500.00, 'days' => 30],
            ['title' => 'Utilities & Fiber Internet', 'code' => '6030', 'amount' => 780.00, 'days' => 15],
            ['title' => 'Digital Marketing Ads', 'code' => '6040', 'amount' => 1500.00, 'days' => 5],
        ];

        foreach ($expenseItems as $exp) {
            $expDate = Carbon::now()->subDays($exp['days']);
            $expenseCoa = ChartOfAccount::where('company_id', $companyId)->where('account_code', $exp['code'])->first();

            $je = JournalEntry::factory()->create([
                'company_id' => $companyId,
                'entry_number' => 'JE-EXP-' . fake()->unique()->numberBetween(10000, 99999),
                'entry_date' => $expDate->toDateString(),
                'reference' => 'EXP-' . fake()->unique()->numberBetween(10000, 99999),
                'description' => "Operating Expense: {$exp['title']}",
                'status' => 'posted',
                'total_debit' => $exp['amount'],
                'total_credit' => $exp['amount'],
                'created_by' => $user->id,
                'posted_by' => $user->id,
                'posted_at' => $expDate,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $expenseCoa->id,
                'description' => $exp['title'],
                'debit_amount' => $exp['amount'],
                'credit_amount' => 0,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $bankCoa?->id ?? $cashCoa?->id,
                'description' => "Payment from Bank - {$exp['title']}",
                'debit_amount' => 0,
                'credit_amount' => $exp['amount'],
            ]);

            $expenseCoa->updateCurrentBalance();
            if ($bankCoa) $bankCoa->updateCurrentBalance();
        }

        // 9. Update COA Balances
        $allCoas = ChartOfAccount::where('company_id', $companyId)->get();
        foreach ($allCoas as $coa) {
            $coa->updateCurrentBalance();
        }

        $this->command->info('Financial & Operational Seeding with Factories Completed Successfully!');
    }
}
