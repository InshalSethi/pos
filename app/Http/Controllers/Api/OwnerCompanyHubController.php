<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\License;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class OwnerCompanyHubController extends Controller
{
    /**
     * Get data for Owner Company Workspace Hub
     */
    public function getHubData(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 1. Resolve Subscription & Plan limits
        $planLimits = [
            'standard'   => ['name' => 'Standard (Free Trial)', 'max_companies' => 1, 'is_trial' => true],
            'starter'    => ['name' => 'Standard (Free Trial)', 'max_companies' => 1, 'is_trial' => true],
            'free'       => ['name' => 'Standard (Free Trial)', 'max_companies' => 1, 'is_trial' => true],
            'basic'      => ['name' => 'Basic Plan', 'max_companies' => 1, 'is_trial' => false],
            'advance'    => ['name' => 'Advance Plan', 'max_companies' => 2, 'is_trial' => false],
            'enterprise' => ['name' => 'Enterprise Plan', 'max_companies' => 10, 'is_trial' => false],
            'elite'      => ['name' => 'Enterprise Plan', 'max_companies' => 10, 'is_trial' => false],
            'custom'     => ['name' => 'Custom Plan', 'max_companies' => 999, 'is_trial' => false],
        ];

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

        $planName = $dbPlan ? $dbPlan->name : ($planLimits[$planSlug]['name'] ?? ucfirst($planSlug));
        $maxCompanies = $dbPlan ? (int) $dbPlan->max_companies : (int) ($planLimits[$planSlug]['max_companies'] ?? 1);

        $expiryDate = null;
        if ($license && $license->expires_at) {
            $expiryDate = Carbon::parse($license->expires_at)->format('M d, Y');
        } elseif ($payment && $payment->paid_at) {
            $expiryDate = Carbon::parse($payment->paid_at)->addMonth()->format('M d, Y');
        } else {
            $expiryDate = now()->addDays(14)->format('M d, Y');
        }

        $billingCycle = $payment ? ucfirst($payment->billing_cycle ?? 'monthly') : 'Monthly';

        // 2. Fetch Active Companies owned by user
        $activeCompanies = Company::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'asc')
            ->get()
            ->values()
            ->map(function ($c, $index) use ($user) {
                return [
                    'id' => $c->id,
                    'order' => $index + 1,
                    'company_name' => $c->company_name ?: 'Unnamed Company',
                    'company_email' => $c->company_email,
                    'company_phone' => $c->company_phone,
                    'logo_url' => $c->logo_url,
                    'business_type' => $c->business_type,
                    'country' => $c->country,
                    'base_currency' => $c->base_currency,
                    'created_at' => $c->created_at ? $c->created_at->format('M d, Y') : null,
                    'is_current' => (int) $user->current_company_id === (int) $c->id,
                ];
            });

        // 3. Fetch Draft Companies owned by user
        $draftCompanies = Company::where('user_id', $user->id)
            ->where('status', 'draft')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->values()
            ->map(function ($d, $index) {
                $step = $d->draft_step ?: 1;
                return [
                    'id' => $d->id,
                    'order' => $index + 1,
                    'company_name' => $d->company_name ? $d->company_name : 'Draft company',
                    'draft_step' => $step,
                    'step_label' => "Draft company (Step {$step} of 3)",
                    'updated_at' => $d->updated_at ? $d->updated_at->diffForHumans() : null,
                ];
            });

        // 4. Calculate Quota
        $usedCompanies = $activeCompanies->count();
        $isLimitReached = $usedCompanies >= $maxCompanies;

        // 5. User Avatar
        $avatarUrl = null;
        if ($user->profile_image) {
            $avatarUrl = str_starts_with($user->profile_image, 'http') ? $user->profile_image : asset('storage/' . $user->profile_image);
        } elseif ($user->avatar) {
            $avatarUrl = str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar);
        } elseif ($user->profile_photo_path) {
            $avatarUrl = asset('storage/' . $user->profile_photo_path);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $avatarUrl,
            ],
            'subscription' => [
                'plan_name' => $planName,
                'plan_slug' => $planSlug,
                'max_companies' => $maxCompanies,
                'billing_cycle' => $billingCycle,
                'expires_at' => $expiryDate,
                'status' => 'active',
            ],
            'quota' => [
                'used_companies' => $usedCompanies,
                'max_companies' => $maxCompanies,
                'remaining' => max(0, $maxCompanies - $usedCompanies),
                'is_limit_reached' => $isLimitReached,
            ],
            'active_companies' => $activeCompanies,
            'draft_companies' => $draftCompanies,
        ]);
    }

    /**
     * Discard an incomplete draft company
     */
    public function discardDraft(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $draft = Company::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            return response()->json(['message' => 'Draft workspace not found.'], 404);
        }

        $draft->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Draft workspace discarded successfully.'
        ]);
    }
}
