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

            return view('desktop-auth-success', [
                'token' => $token,
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'license' => [
                    'license_key' => $license->license_key ?? 'DEMO-ELITE-YEARLY',
                    'plan'        => $license->plan ?? 'elite',
                    'start_date'  => $license->start_date ? \Carbon\Carbon::parse($license->start_date)->toDateString() : now()->toDateString(),
                    'expires_at'  => $license->expires_at ? \Carbon\Carbon::parse($license->expires_at)->toDateString() : now()->addYear()->toDateString(),
                ]
            ]);
        }

        // Store flag in session indicating user initiated login from desktop app
        session(['desktop_auth_pending' => true]);

        // Redirect guest user to the login screen
        return redirect()->to('/login?redirect=/desktop-login');
    }
}
