<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user to Google login page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            // Get user data from Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Check if user exists by email or google_id
            $user = User::where('email', $googleUser->getEmail())
                ->orWhere('google_id', $googleUser->getId())
                ->first();

            if (!$user) {
                // Register as new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)), // Fixed: use Str::random
                    'email_verified_at' => now(),
                    'is_active' => true
                ]);

                // Assign default 'user' role
                $user->assignRole('user');
            }
            else if (!$user->google_id) {
                // Link Google account to existing email
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

           
            return redirect("http://127.0.0.1:8001/login?token={$token}");

        }
        catch (Exception $e) {
            return redirect("http://127.0.0.1:8001/login?error=Google login failed");
        }
    }
}