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

        $adminEmail = $admin ? $admin->email : 'admin@gmail.com';
        $license = License::first();

        // Only create initial license if no record exists in database
        if (!$license) {
            $startDate = now()->toDateString();
            $expiresAt = now()->addYear()->toDateString();
            $plan = 'elite';

            $license = License::provisionLicense($adminEmail, $plan, $startDate, $expiresAt);
        } else {
            // Upgrade legacy or unencrypted key to secure encrypted key encoding user email, plan, start_date, and expires_at
            if (!License::decryptKey($license->license_key)) {
                $encryptedKey = License::generateEncryptedKey(
                    $adminEmail,
                    $license->plan ?: 'elite',
                    $license->start_date ? $license->start_date->toDateString() : now()->toDateString(),
                    $license->expires_at ? $license->expires_at->toDateString() : now()->addYear()->toDateString()
                );
                $license->update(['license_key' => $encryptedKey, 'last_opened_at' => now()]);
            } else {
                $license->update(['last_opened_at' => now()]);
            }
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

    // Activate License by calling the Live Server or decrypting key payload
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'device_id' => 'required|string'
        ]);

        try {
            $decryptedPayload = License::decryptKey($request->license_key);

            if ($decryptedPayload) {
                $plan = $decryptedPayload['plan'];
                $startDate = $decryptedPayload['start_date'];
                $expiresAt = $decryptedPayload['expires_at'];
                $encryptedKey = $request->license_key;
            } else {
                // If activating with a legacy string key, generate a fresh encrypted token
                $user = $request->user() ?: \App\Models\User::first();
                $email = $user ? $user->email : 'admin@gmail.com';
                $plan = 'basic';
                $startDate = now()->toDateString();
                $expiresAt = now()->addDays(30)->toDateString();
                $encryptedKey = License::generateEncryptedKey($email, $plan, $startDate, $expiresAt);
            }

            // Wipe existing database and set up fresh for new license if database empty
            if (!License::exists()) {
                Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            }

            $license = License::updateOrCreate(
                ['id' => 1],
                [
                    'license_key'    => $encryptedKey,
                    'device_id'     => $request->device_id,
                    'plan'          => $plan,
                    'status'        => 'active',
                    'start_date'    => $startDate,
                    'expires_at'    => $expiresAt,
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
        $user = $request->user() ?: \App\Models\User::first();
        $email = $user ? $user->email : 'admin@gmail.com';

        $license = License::first();
        if ($license) {
            $newStartDate = $license->start_date ? $license->start_date->toDateString() : now()->toDateString();
            $newExpiresAt = now()->addYear()->toDateString();
            $plan = $license->plan ?: 'basic';

            $newEncryptedKey = License::generateEncryptedKey($email, $plan, $newStartDate, $newExpiresAt);

            $license->update([
                'license_key'    => $newEncryptedKey,
                'status'        => 'active',
                'start_date'    => $newStartDate,
                'expires_at'    => $newExpiresAt,
                'last_opened_at' => now()
            ]);
            return response()->json(['message' => 'License renewed successfully', 'license' => $license]);
        }
        return response()->json(['message' => 'License not found'], 404);
    }
}
