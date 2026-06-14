<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $productName,
        public readonly int    $currentStock,
        public readonly int    $minAlert,
        public readonly string $variantLabel = '',
        public readonly int    $productId    = 0,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $isVariant  = $this->variantLabel !== '';
        $subject    = $isVariant
            ? "{$this->productName} ({$this->variantLabel})"
            : $this->productName;

        return [
            'type'          => 'low_stock',
            'product_id'    => $this->productId,
            'product_name'  => $this->productName,
            'variant_label' => $this->variantLabel,
            'current_stock' => $this->currentStock,
            'min_alert'     => $this->minAlert,
            'title'         => '⚠️ Low Stock Alert',
            'message'       => "'{$subject}' is running low — only {$this->currentStock} unit(s) remaining (threshold: {$this->minAlert}).",
        ];
    }
}
