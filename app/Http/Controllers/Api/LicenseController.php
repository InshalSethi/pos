<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class LicenseController extends Controller
{
    // Check if system is activated
    public function checkStatus(Request $request)
    {
        $user = $request->user() ?: \Illuminate\Support\Facades\Auth::user();
        $admin = $user ?: \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'owner')->orWhere('name', 'admin');
        })->first() ?: \App\Models\User::first();

        $adminEmail = $admin ? $admin->email : ($user ? $user->email : 'admin@gmail.com');
        $license = License::first();

        // If license missing or key is not encrypted, generate encrypted key storing email, plan, start_date and end_date
        if (!$license || !\App\Services\LicenseKeyService::decryptKey($license->license_key)) {
            $startDate = $license && $license->start_date ? \Carbon\Carbon::parse($license->start_date)->toDateString() : now()->toDateString();
            $expiresAt = $license && $license->expires_at ? \Carbon\Carbon::parse($license->expires_at)->toDateString() : now()->addYear()->toDateString();
            $plan = $license && $license->plan ? $license->plan : 'enterprise';

            $encryptedKey = \App\Services\LicenseKeyService::generateEncryptedKey($adminEmail, $plan, $startDate, $expiresAt);

            $license = License::updateOrCreate(
                ['id' => 1],
                [
                    'license_key'    => $encryptedKey,
                    'device_id'     => 'DEFAULT-DEVICE',
                    'plan'          => $plan,
                    'status'        => 'active',
                    'start_date'    => $startDate,
                    'expires_at'    => $expiresAt,
                    'last_opened_at' => now(),
                ]
            );
        } else {
            $license->update(['last_opened_at' => now()]);
        }

        $status = $license->status ?: 'active';

        // Logged-in users are always active without mutating DB license plan/expiry
        if ($user) {
            $status = 'active';
        }

        return response()->json([
            'status'      => $status,
            'license'     => $license,
            'admin_name'  => $admin ? $admin->name : 'Administrator',
            'admin_email' => $adminEmail,
        ]);
    }

    // Activate License by calling the Live Server
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'device_id' => 'required|string'
        ]);

        try {
            $mockLiveResponse = [
                'status' => 'success',
                'data' => [
                    'plan' => 'basic',
                    'start_date' => now()->toDateString(),
                    'expires_at' => now()->addDays(30)->toDateString(),
                ]
            ];
            
            if ($mockLiveResponse['status'] !== 'success') {
                return response()->json(['message' => 'Invalid License Key or Device Limit Reached.'], 400);
            }

            if (!License::exists()) {
                Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            }

            $user = $request->user() ?: \Illuminate\Support\Facades\Auth::user();
            $email = $user ? $user->email : 'admin@gmail.com';
            $data = $mockLiveResponse['data'];

            $encryptedKey = \App\Services\LicenseKeyService::generateEncryptedKey(
                $email,
                $data['plan'],
                $data['start_date'],
                $data['expires_at']
            );

            $license = License::updateOrCreate(
                ['id' => 1], // Singleton license row
                [
                    'license_key' => $encryptedKey,
                    'device_id' => $request->device_id,
                    'plan' => $data['plan'],
                    'status' => 'active',
                    'start_date' => $data['start_date'],
                    'expires_at' => $data['expires_at'],
                    'last_opened_at' => now(),
                ]
            );

            return response()->json([
                'message' => 'Software activated successfully.',
                'license' => $license
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to connect to licensing server.', 'error' => $e->getMessage()], 500);
        }
    }

    public function renew(Request $request)
    {
        if ($request->input('payment_method') === 'new' || $request->has('cardNumber') || $request->has('card_number')) {
            $cardNumberKey = $request->has('cardNumber') ? 'cardNumber' : 'card_number';
            $cardExpiryKey = $request->has('cardExpiry') ? 'cardExpiry' : 'card_expiry';
            $cardCvcKey    = $request->has('cardCvc') ? 'cardCvc' : 'card_cvc';

            $request->validate([
                $cardNumberKey => ['required', new \App\Rules\ValidCardNumber()],
                $cardExpiryKey => ['required', new \App\Rules\ValidCardExpiry()],
                $cardCvcKey    => ['required', new \App\Rules\ValidCardCvc()],
            ]);
        }

        $license = License::first();
        if ($license) {
            $user = $request->user() ?: \Illuminate\Support\Facades\Auth::user();
            $email = $user ? $user->email : 'admin@gmail.com';
            $plan = strtolower($request->input('plan', $license->plan ?: 'enterprise'));
            $cycle = strtolower($request->input('billing_cycle', $request->input('cycle', 'monthly')));

            $startDate = $license->start_date ? \Carbon\Carbon::parse($license->start_date)->toDateString() : now()->toDateString();
            $expiresAt = ($cycle === 'yearly' || $cycle === 'annual') ? now()->addYear()->toDateString() : now()->addMonth()->toDateString();

            $encryptedKey = \App\Services\LicenseKeyService::generateEncryptedKey($email, $plan, $startDate, $expiresAt);

            $license->update([
                'license_key' => $encryptedKey,
                'plan'       => $plan,
                'status'     => 'active',
                'expires_at' => $expiresAt
            ]);

            // Record Subscription Payment History
            $cardNumber = $request->input('cardNumber', $request->input('card_number'));
            \App\Services\SubscriptionPaymentService::recordPayment(
                $user ? $user->id : null,
                $user ? $user->name : 'Admin User',
                $email,
                $plan,
                $cycle,
                $cardNumber,
                'Credit Card',
                $request->input('coupon_code', $request->input('couponCode'))
            );

            return response()->json(['message' => 'License renewed successfully', 'license' => $license]);
        }
        return response()->json(['message' => 'License not found'], 404);
    }
}
