<?php

namespace App\Observers;

use App\Models\PaymentReceipt;
use App\Services\FbrService;

class PaymentReceiptObserver
{
    /**
     * Handle the PaymentReceipt "created" event.
     */
    public function created(PaymentReceipt $paymentReceipt): void
    {
        app(FbrService::class)->recordPayment($paymentReceipt);
    }
}
