<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;

class SubscriptionPaymentService
{
    /**
     * Get price for a plan and billing cycle
     */
    public static function getPlanPrice(string $planName, string $cycle = 'monthly'): float
    {
        $normalizedPlan = strtolower(trim($planName));
        
        // Try finding in database first
        $dbPlan = SubscriptionPlan::where(function($query) use ($normalizedPlan) {
            $query->whereRaw('LOWER(name) = ?', [$normalizedPlan])
                  ->orWhereRaw('LOWER(slug) = ?', [$normalizedPlan]);
        })->first();

        if ($dbPlan) {
            $price = ($cycle === 'yearly' || $cycle === 'annual')
                ? ($dbPlan->yearly_price ?? $dbPlan->price_yearly ?? 0)
                : ($dbPlan->monthly_price ?? $dbPlan->price_monthly ?? 0);
            return (float) $price;
        }

        // Fallback default pricing map
        $pricing = [
            'standard'   => 0.00,
            'starter'    => 0.00,
            'basic'      => ($cycle === 'yearly' || $cycle === 'annual') ? 192.00 : 20.00,
            'advance'    => ($cycle === 'yearly' || $cycle === 'annual') ? 480.00 : 50.00,
            'master'     => ($cycle === 'yearly' || $cycle === 'annual') ? 480.00 : 50.00,
            'enterprise' => ($cycle === 'yearly' || $cycle === 'annual') ? 960.00 : 100.00,
            'elite'      => ($cycle === 'yearly' || $cycle === 'annual') ? 960.00 : 100.00,
            'custom'     => ($cycle === 'yearly' || $cycle === 'annual') ? 14400.00 : 1500.00,
        ];

        return $pricing[$normalizedPlan] ?? 20.00;
    }

    /**
     * Record a new subscription payment
     */
    public static function recordPayment(
        ?int $userId,
        string $userName,
        string $userEmail,
        string $planName,
        string $cycle = 'monthly',
        ?string $cardNumber = null,
        string $paymentMethod = 'Credit Card',
        ?string $couponCode = null
    ): SubscriptionPayment {
        $originalAmount = self::getPlanPrice($planName, $cycle);
        $discountAmount = 0.00;
        $finalCouponCode = null;

        if (!empty($couponCode)) {
            $coupon = \App\Models\Coupon::where('code', strtoupper(trim($couponCode)))->first();
            if ($coupon) {
                $validation = $coupon->isValidForAmount($originalAmount);
                if ($validation['valid']) {
                    $discountAmount = $coupon->calculateDiscount($originalAmount);
                    $finalCouponCode = $coupon->code;
                    $coupon->increment('used_count');
                }
            }
        }

        $netAmount = max(0.00, round($originalAmount - $discountAmount, 2));

        // Extract last 4 digits if provided
        $cardLastFour = '4242';
        if ($cardNumber) {
            $digits = preg_replace('/\D/', '', $cardNumber);
            if (strlen($digits) >= 4) {
                $cardLastFour = substr($digits, -4);
            }
        }

        $txnId = 'TXN-' . strtoupper(now()->format('Ymd')) . '-' . strtoupper(Str::random(6));

        return SubscriptionPayment::create([
            'user_id'         => $userId,
            'user_name'       => $userName,
            'user_email'      => $userEmail,
            'plan_name'       => ucfirst($planName),
            'billing_cycle'   => strtolower($cycle),
            'amount'          => $netAmount,
            'coupon_code'     => $finalCouponCode,
            'discount_amount' => $discountAmount,
            'original_amount' => $originalAmount,
            'currency'        => 'USD',
            'payment_method'  => $paymentMethod,
            'card_last_four'  => $cardLastFour,
            'transaction_id'  => $txnId,
            'status'          => 'paid',
            'paid_at'         => now(),
        ]);
    }
}
