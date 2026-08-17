<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\FbrService;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        app(FbrService::class)->recordTransaction($transaction);
    }
}
