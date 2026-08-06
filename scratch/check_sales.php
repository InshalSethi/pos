<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;

function checkIsFullyReturned(Sale $sale): bool {
    if ($sale->is_refund) {
        return false;
    }

    $refunds = $sale->relationLoaded('refunds') ? $sale->refunds : $sale->refunds()->with('saleItems')->get();
    if ($refunds->isEmpty()) {
        return false;
    }

    $saleItems = $sale->relationLoaded('saleItems') ? $sale->saleItems : $sale->saleItems()->get();
    $origQty = $saleItems->sum('quantity');

    $returnedQty = 0;
    foreach ($refunds as $refund) {
        $refItems = $refund->relationLoaded('saleItems') ? $refund->saleItems : $refund->saleItems()->get();
        $returnedQty += abs($refItems->sum('quantity'));
    }

    if ($origQty > 0 && $returnedQty >= $origQty) {
        return true;
    }

    $origTotal = abs((float)$sale->total_amount);
    $returnedTotal = abs((float)$refunds->sum('total_amount'));
    if ($origTotal > 0 && $returnedTotal >= ($origTotal - 0.01)) {
        return true;
    }

    return $refunds->isNotEmpty() && ($returnedQty >= $origQty || ($origTotal > 0 && $returnedTotal >= $origTotal));
}

$sales = Sale::with(['refunds.saleItems', 'saleItems'])->where('is_refund', false)->get();
foreach ($sales as $s) {
    $isFullyReturned = checkIsFullyReturned($s);
    echo "ID: {$s->id} | Num: {$s->sale_number} | is_fully_returned: " . ($isFullyReturned ? 'TRUE' : 'FALSE') . "\n";
}
