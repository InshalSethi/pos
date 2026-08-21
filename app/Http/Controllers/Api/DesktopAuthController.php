<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesktopAuthController extends Controller
{
    /**
     * Handle desktop web browser authentication flow.
     */
    public function showDesktopLogin(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Clean up desktop auth session flag if present
            session()->forget('desktop_auth_pending');

            // Generate Sanctum access token for desktop app
            $token = $user->createToken('desktop-app')->plainTextToken;

            // Fetch actual License details from central Cloud DB
            $license = \App\Models\License::first();

            $startDate = $license && $license->start_date ? \Carbon\Carbon::parse($license->start_date)->toDateString() : now()->toDateString();
            $expiresAt = $license && $license->expires_at ? \Carbon\Carbon::parse($license->expires_at)->toDateString() : now()->addYear()->toDateString();
            $plan = $license->plan ?? 'elite';

            if (!$license || !\App\Services\LicenseKeyService::decryptKey($license->license_key)) {
                $encryptedKey = \App\Services\LicenseKeyService::generateEncryptedKey($user->email, $plan, $startDate, $expiresAt);
                $license = \App\Models\License::updateOrCreate(
                    ['id' => 1],
                    [
                        'license_key' => $encryptedKey,
                        'plan'        => $plan,
                        'status'      => 'active',
                        'start_date'  => $startDate,
                        'expires_at'  => $expiresAt,
                        'last_opened_at' => now(),
                    ]
                );
            }

            $decryptedPayload = \App\Services\LicenseKeyService::decryptKey($license->license_key);

            return view('desktop-auth-success', [
                'token' => $token,
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'license' => [
                    'license_key' => $license->license_key,
                    'decrypted'   => $decryptedPayload,
                    'plan'        => $license->plan ?? 'elite',
                    'start_date'  => $startDate,
                    'expires_at'  => $expiresAt,
                ]
            ]);
        }

        // Store flag in session indicating user initiated login from desktop app
        session(['desktop_auth_pending' => true]);

        // Redirect guest user to the login screen
        return redirect()->to('/login?redirect=/desktop-login');
    }
}
