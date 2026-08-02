<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    protected DoubleEntryAccountingService $accountingService;

    public function __construct(DoubleEntryAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Handle the Product "created" event.
     * Triggers automatic double-entry journal entry for initial opening stock.
     */
    public function created(Product $product): void
    {
        try {
            $this->accountingService->createOpeningStockEntry($product);
        } catch (\Throwable $e) {
            Log::error("Failed creating Opening Stock Journal Entry for Product #{$product->id}: " . $e->getMessage(), [
                'product_id' => $product->id,
                'exception' => $e
            ]);
        }
    }
}
