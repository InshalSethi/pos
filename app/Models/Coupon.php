<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if coupon is valid for given amount.
     */
    public function isValidForAmount($amount = 0)
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Coupon code is inactive.'];
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'Coupon code is not yet active.'];
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'Coupon code has expired.'];
        }

        if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit has been reached.'];
        }

        if ($amount > 0 && $this->min_order_amount > 0 && $amount < $this->min_order_amount) {
            return ['valid' => false, 'message' => "Minimum order amount of $" . number_format($this->min_order_amount, 2) . " required."];
        }

        return ['valid' => true, 'message' => 'Coupon applied successfully!'];
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount($originalAmount)
    {
        $originalAmount = (float) $originalAmount;
        if ($originalAmount <= 0) {
            return 0.00;
        }

        $discount = 0.00;
        if ($this->type === 'percentage') {
            $discount = ($originalAmount * (float) $this->value) / 100;
            if (!is_null($this->max_discount_amount) && $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = (float) $this->value;
        }

        return round(min($discount, $originalAmount), 2);
    }
}
