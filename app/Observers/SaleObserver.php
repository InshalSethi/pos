<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\FbrService;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        app(FbrService::class)->recordSale($sale);
    }
}
