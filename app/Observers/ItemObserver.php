<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Support\Facades\Log;

class ItemObserver
{
    protected DoubleEntryAccountingService $accountingService;

    public function __construct(DoubleEntryAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Handle the Item/Product "created" event.
     * Triggers automatic double-entry journal entry for initial opening stock.
     */
    public function created($item): void
    {
        try {
            $this->accountingService->createOpeningStockEntry($item);
        } catch (\Throwable $e) {
            Log::error("Failed creating Opening Stock Journal Entry for Item #{$item->id}: " . $e->getMessage(), [
                'item_id' => $item->id,
                'exception' => $e
            ]);
        }
    }
}
