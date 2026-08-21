<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\License;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Services\LicenseKeyService;
use App\Services\SubscriptionPaymentService;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Tier ranking definitions (Higher number = Higher Tier)
     */
    protected static array $tierRanks = [
        'standard'   => 0,
        'starter'    => 0,
        'free'       => 0,
        'basic'      => 1,
        'advance'    => 2,
        'master'     => 2,
        'enterprise' => 3,
        'elite'      => 3,
        'custom'     => 4,
    ];

    /**
     * Get Current Active Subscription details for authenticated user
     */
    public function getCurrentSubscription(Request $request)
    {
        $user = Auth::user() ?: $request->user();
        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'subscription' => null,
            ]);
        }

        $payment = SubscriptionPayment::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        $license = License::first();

        $planSlug = 'standard';
        if ($payment && $payment->plan_name) {
            $planSlug = strtolower(trim($payment->plan_name));
        } elseif ($license && $license->plan) {
            $planSlug = strtolower(trim($license->plan));
        }

        $dbPlan = SubscriptionPlan::whereRaw('LOWER(slug) = ?', [$planSlug])
            ->orWhereRaw('LOWER(name) = ?', [$planSlug])
            ->first();

        $planName = $dbPlan ? $dbPlan->name : ucfirst($planSlug);
        $maxCompanies = $dbPlan ? (int) $dbPlan->max_companies : (self::$tierRanks[$planSlug] ?? 1);
        $billingCycle = $payment ? strtolower($payment->billing_cycle ?? 'monthly') : 'monthly';

        $expiryDate = null;
        if ($license && $license->expires_at) {
            $expiryDate = Carbon::parse($license->expires_at)->format('M d, Y');
        } elseif ($payment && $payment->paid_at) {
            $expiryDate = Carbon::parse($payment->paid_at)->addMonth()->format('M d, Y');
        } else {
            $expiryDate = now()->addDays(14)->format('M d, Y');
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'subscription' => [
                'plan_slug' => $planSlug,
                'plan_name' => $planName,
                'tier_rank' => self::$tierRanks[$planSlug] ?? 1,
                'billing_cycle' => $billingCycle,
                'expires_at' => $expiryDate,
                'max_companies' => $maxCompanies,
                'status' => 'active',
                'has_used_trial' => true,
            ]
        ]);
    }

    /**
     * Process Subscription Upgrade
     */
    public function upgrade(Request $request)
    {
        $user = Auth::user() ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'plan' => 'required|string',
            'billing_cycle' => 'nullable|string|in:monthly,yearly,annual',
            'payment_method' => 'nullable|string|in:existing,new',
        ]);

        $targetPlanSlug = strtolower(trim($request->input('plan')));
        $cycle = strtolower($request->input('billing_cycle', 'monthly'));
        $paymentMethod = $request->input('payment_method', 'new');

        // 1. Block downgrade or switch to Free Trial
        if (in_array($targetPlanSlug, ['standard', 'starter', 'free'])) {
            return response()->json([
                'message' => 'The Free Trial cannot be re-claimed by existing accounts.',
                'error' => 'TRIAL_NOT_ALLOWED'
            ], 422);
        }

        // 2. Validate current subscription level
        $payment = SubscriptionPayment::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        $license = License::first();

        $currentPlanSlug = 'standard';
        if ($payment && $payment->plan_name) {
            $currentPlanSlug = strtolower(trim($payment->plan_name));
        } elseif ($license && $license->plan) {
            $currentPlanSlug = strtolower(trim($license->plan));
        }

        $currentRank = self::$tierRanks[$currentPlanSlug] ?? 0;
        $targetRank = self::$tierRanks[$targetPlanSlug] ?? 1;

        if ($targetRank <= $currentRank && $targetPlanSlug === $currentPlanSlug) {
            return response()->json([
                'message' => "You are already subscribed to the {$currentPlanSlug} plan.",
                'error' => 'ALREADY_CURRENT_PLAN'
            ], 422);
        }

        // 3. Card Validation when new card is used
        if ($paymentMethod === 'new' || $request->has('cardNumber') || $request->has('card_number')) {
            $cardNumberKey = $request->has('cardNumber') ? 'cardNumber' : 'card_number';
            $cardExpiryKey = $request->has('cardExpiry') ? 'cardExpiry' : 'card_expiry';
            $cardCvcKey    = $request->has('cardCvc') ? 'cardCvc' : 'card_cvc';

            $request->validate([
                $cardNumberKey => ['required', new \App\Rules\ValidCardNumber()],
                $cardExpiryKey => ['required', new \App\Rules\ValidCardExpiry()],
                $cardCvcKey    => ['required', new \App\Rules\ValidCardCvc()],
            ]);
        }

        return DB::transaction(function () use ($user, $targetPlanSlug, $cycle, $paymentMethod, $request) {
            // 4. Calculate Dates
            $startDate = now()->toDateString();
            $expiresAt = ($cycle === 'yearly' || $cycle === 'annual')
                ? now()->addYear()->toDateString()
                : now()->addDays(30)->toDateString();

            // 5. Update License
            $encryptedKey = LicenseKeyService::generateEncryptedKey($user->email, $targetPlanSlug, $startDate, $expiresAt);
            License::updateOrCreate(
                ['id' => 1],
                [
                    'license_key' => $encryptedKey,
                    'device_id'   => 'USER-' . $user->id,
                    'plan'        => $targetPlanSlug,
                    'status'      => 'active',
                    'start_date'  => $startDate,
                    'expires_at'  => $expiresAt,
                    'last_opened_at' => now(),
                ]
            );

            // 6. Record Subscription Payment
            $cardNumber = $request->input('cardNumber', $request->input('card_number'));
            $paymentRecord = SubscriptionPaymentService::recordPayment(
                $user->id,
                $user->name,
                $user->email,
                $targetPlanSlug,
                $cycle,
                $cardNumber,
                $paymentMethod === 'existing' ? 'Saved Card' : 'Credit Card',
                $request->input('coupon_code', $request->input('couponCode'))
            );

            // 7. Resolve Max Companies
            $dbPlan = SubscriptionPlan::whereRaw('LOWER(slug) = ?', [$targetPlanSlug])
                ->orWhereRaw('LOWER(name) = ?', [$targetPlanSlug])
                ->first();

            $planLimits = [
                'standard'   => 1,
                'starter'    => 1,
                'basic'      => 1,
                'advance'    => 2,
                'enterprise' => 10,
                'custom'     => 999,
            ];

            $planName = $dbPlan ? $dbPlan->name : ucfirst($targetPlanSlug) . ' Plan';
            $maxCompanies = $dbPlan ? (int) $dbPlan->max_companies : ($planLimits[$targetPlanSlug] ?? 2);

            return response()->json([
                'success' => true,
                'message' => "Subscription successfully upgraded to {$planName}!",
                'plan_slug' => $targetPlanSlug,
                'plan_name' => $planName,
                'max_companies' => $maxCompanies,
                'expires_at' => Carbon::parse($expiresAt)->format('M d, Y'),
                'billing_cycle' => ucfirst($cycle),
                'payment' => [
                    'transaction_id' => $paymentRecord->transaction_id,
                    'amount' => $paymentRecord->amount,
                ]
            ]);
        });
    }
}
