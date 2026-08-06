<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;

$sales = Sale::with(['refunds.saleItems', 'saleItems'])->get();
foreach ($sales as $sale) {
    if ($sale->is_fully_returned || $sale->is_void) {
        $data = $sale->toArray();
        echo "ID: {$sale->id} | Num: {$sale->sale_number} | Status: {$sale->status} | is_fully_returned: " . ($data['is_fully_returned'] ? 'TRUE' : 'FALSE') . " | is_void: " . ($data['is_void'] ? 'TRUE' : 'FALSE') . "\n";
    }
}
