<?php

namespace App\Observers;

use App\Models\ProductVariation;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Support\Facades\Log;

class ProductVariationObserver
{
    protected DoubleEntryAccountingService $accountingService;

    public function __construct(DoubleEntryAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Handle the ProductVariation "created" event.
     * Triggers automatic double-entry journal entry for variation initial opening stock.
     */
    public function created(ProductVariation $variation): void
    {
        try {
            $this->accountingService->createOpeningStockEntry($variation);
        } catch (\Throwable $e) {
            Log::error("Failed creating Opening Stock Journal Entry for Variation #{$variation->id}: " . $e->getMessage(), [
                'variation_id' => $variation->id,
                'exception' => $e
            ]);
        }
    }
}
