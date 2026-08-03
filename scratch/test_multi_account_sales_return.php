<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

echo "=== TESTING SALES RETURN MULTI-ACCOUNT SPLIT REFUND & LEDGER INTEGRATION ===\n";

$user = User::first();
if (!$user) {
    die("No user found in DB\n");
}
$companyId = $user->company_id ?: 1;
auth()->login($user);

// Ensure Cash Account has balance for testing
$cashAccount = Account::where('company_id', $companyId)
    ->where('account_type', 'asset')
    ->where(function ($q) { $q->where('account_code', '1010')->orWhere('account_name', 'LIKE', '%Cash%'); })
    ->first();

if ($cashAccount) {
    $cashAccount->update(['current_balance' => 50000]);
    echo "Set Cash Account ({$cashAccount->account_name}) balance to 50,000.\n";
}

// Find or create customer
$customer = Customer::firstOrCreate(
    ['company_id' => $companyId, 'email' => 'testreturn@example.com'],
    ['name' => 'Test Return Customer', 'phone' => '1234567890', 'total_purchases' => 10000]
);

$wh = Warehouse::where('company_id', $companyId)->first();

// Create sample original sale
$origSale = Sale::create([
    'company_id' => $companyId,
    'sale_number' => 'TEST-INV-' . rand(1000, 9999),
    'customer_id' => $customer->id,
    'user_id' => $user->id,
    'warehouse_id' => $wh->id,
    'sale_date' => today()->toDateString(),
    'status' => 'completed',
    'subtotal' => 10000,
    'tax_amount' => 0,
    'discount_amount' => 0,
    'total_amount' => 10000,
    'paid_amount' => 10000,
    'payment_method' => 'cash',
    'is_refund' => false
]);

$item = SaleItem::create([
    'sale_id' => $origSale->id,
    'product_id' => 1,
    'warehouse_id' => $wh->id,
    'quantity' => 2,
    'unit_price' => 5000,
    'total_amount' => 10000
]);

echo "Created test original sale ID: {$origSale->id} with Total Amount: 10,000\n";

// Get bank accounts
$bankAccounts = BankAccount::where('company_id', $companyId)->get();
echo "Found " . $bankAccounts->count() . " bank accounts.\n";

$b1 = $bankAccounts->first();
if ($b1) {
    $b1->update(['current_balance' => 50000]);
}

// Test Case 1: Split Refund - Rs 3,000 Cash + Rs 2,000 Bank + Rs 5,000 Unpaid (routed to Customer Ledger)
$requestData = [
    'original_sale_id' => $origSale->id,
    'return_date' => today()->toDateString(),
    'return_reason' => 'customer_change_mind',
    'return_notes' => 'Split refund test (Rs 3k Cash, Rs 2k Bank, Rs 5k Ledger)',
    'payments' => [
        ['type' => 'cash', 'amount' => 3000],
        ['type' => 'bank', 'bank_id' => $b1?->id, 'amount' => 2000]
    ],
    'return_items' => [
        [
            'original_item_id' => $item->id,
            'quantity' => 2,
            'return_amount' => 10000
        ]
    ]
];

$request = new \Illuminate\Http\Request();
$request->replace($requestData);

$controller = new \App\Http\Controllers\Api\SaleController();
$response = $controller->processReturn($request);

echo "Response Status: " . $response->getStatusCode() . "\n";
$responseData = json_decode($response->getContent(), true);

if ($response->getStatusCode() === 201) {
    echo "SUCCESS: Return Sale ID " . $responseData['return_sale']['id'] . " processed.\n";
    $returnSaleId = $responseData['return_sale']['id'];
    $returnSale = Sale::find($returnSaleId);
    echo "Return Paid Amount (Payout): " . $returnSale->paid_amount . "\n";
    echo "Payment Details JSON: " . json_encode($returnSale->payment_details) . "\n";

    // Inspect created Journal Entry
    $je = JournalEntry::with('lines.account')->where('source_type', 'sale_return')->where('source_id', $returnSaleId)->first();
    if ($je) {
        echo "\nGenerated Journal Entry #{$je->entry_number}:\n";
        foreach ($je->lines as $l) {
            echo " - Account: {$l->account->account_name} ({$l->account->account_code}) | Debit: {$l->debit_amount} | Credit: {$l->credit_amount} | Desc: {$l->description}\n";
        }
    }
} else {
    echo "ERROR: " . json_encode($responseData) . "\n";
}

// Test Case 2: Insufficient Funds Validation Test
echo "\n--- Testing Insufficient Funds Validation ---\n";
$invalidRequestData = [
    'original_sale_id' => $origSale->id,
    'return_date' => today()->toDateString(),
    'return_reason' => 'customer_change_mind',
    'payments' => [
        ['type' => 'cash', 'amount' => 99999999]
    ],
    'return_items' => [
        [
            'original_item_id' => $item->id,
            'quantity' => 1,
            'return_amount' => 5000
        ]
    ]
];

$invalidReq = new \Illuminate\Http\Request();
$invalidReq->replace($invalidRequestData);

try {
    $errRes = $controller->processReturn($invalidReq);
    echo "Validation Response Status: " . $errRes->getStatusCode() . "\n";
    echo "Validation Message: " . $errRes->getContent() . "\n";
} catch (\Exception $e) {
    echo "Caught Expected Exception: " . $e->getMessage() . "\n";
}
