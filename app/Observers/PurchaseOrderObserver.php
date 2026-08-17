<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Services\FbrService;

class PurchaseOrderObserver
{
    /**
     * Handle the PurchaseOrder "created" event.
     */
    public function created(PurchaseOrder $purchaseOrder): void
    {
        app(FbrService::class)->recordPurchase($purchaseOrder);
    }
}
