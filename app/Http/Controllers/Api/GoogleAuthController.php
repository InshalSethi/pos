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
    public function redirectToGoogle(\Illuminate\Http\Request $request)
    {
        $flow = $request->query('flow', $request->query('action', 'login'));
        session(['google_auth_action' => $flow]);

        if (app()->environment('local') && $request->has('mock')) {
            return redirect()->to('/auth/google/callback?mock=true&state=' . $flow);
        }

        if ($flow === 'calendar_sync') {
            return Socialite::driver('google')
                ->stateless()
                ->scopes(['https://www.googleapis.com/auth/calendar.events.readonly', 'https://www.googleapis.com/auth/calendar'])
                ->with([
                    'access_type' => 'offline',
                    'prompt' => 'consent select_account',
                    'state' => $flow
                ])
                ->redirect();
        }

        return Socialite::driver('google')
            ->stateless()
            ->with([
                'prompt' => 'select_account',
                'state' => $flow
            ])
            ->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            // Get user data from Google
            if (app()->environment('local') && request()->has('mock')) {
                $googleUser = new class {
                    public function getEmail() { return 'sethiasad1@gmail.com'; }
                    public function getName() { return 'Asad Sethi'; }
                    public function getId() { return '112404372010860874010'; }
                    public function getAvatar() { return 'https://lh3.googleusercontent.com/a/default-user'; }
                };
            } else {
                $googleUser = Socialite::driver('google')->stateless()->user();
            }

            // Get flow state
            $flow = request()->query('state', session('google_auth_action', 'login'));

            if ($flow === 'calendar_sync') {
                $authUser = Auth::user() ?: User::where('email', $googleUser->getEmail())->first();
                if ($authUser) {
                    \App\Models\GoogleCalendarSetting::updateOrCreate(
                        ['user_id' => $authUser->id],
                        [
                            'company_id' => $authUser->current_company_id,
                            'is_synced' => true,
                            'calendar_id' => 'primary',
                            'google_account_email' => $googleUser->getEmail(),
                            'access_token' => property_exists($googleUser, 'token') ? $googleUser->token : ($googleUser->token ?? null),
                            'refresh_token' => property_exists($googleUser, 'refreshToken') ? $googleUser->refreshToken : ($googleUser->refreshToken ?? null),
                            'token_expires_at' => property_exists($googleUser, 'expiresIn') && $googleUser->expiresIn ? now()->addSeconds($googleUser->expiresIn) : null,
                            'last_synced_at' => now(),
                        ]
                    );
                    if (!Auth::check()) {
                        Auth::guard('web')->login($authUser, true);
                    }
                }
                return redirect()->to('/calendar?google_sync=success');
            }

            // 1. Check if user already exists in database
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($flow === 'login') {
                if (!$user) {
                    return redirect()->to('/login?error=' . urlencode("No account found with this Google email. Please sign up first."));
                }
            } else {
                // signup flow
                if (!$user) {
                    // NEW USER REGISTRATION: Create initial uncompleted record
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                        'email_verified_at' => now(),
                        'password' => bcrypt(Str::random(16)),
                        'is_active' => true,
                        'company_id' => null, // Explicitly NULL for new users
                        'is_setup_completed' => false,
                    ]);

                    // Assign default admin role with all permissions
                    if (\Spatie\Permission\Models\Permission::where('guard_name', 'web')->count() === 0) {
                        (new \Database\Seeders\RolePermissionSeeder())->run();
                    }

                    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                    $adminRole->syncPermissions(\Spatie\Permission\Models\Permission::where('guard_name', 'web')->get());
                    $user->assignRole($adminRole);
                }
            }

            // Log user in
            Auth::guard('web')->login($user, true);

            if (request()->hasSession()) {
                request()->session()->regenerate();
            }

            // Create token for SPA
            $token = $user->createToken('auth-token')->plainTextToken;

            // Update Google ID and avatar if not set
            $updateData = [];
            if (!$user->google_id) {
                $updateData['google_id'] = $googleUser->getId();
            }
            if (!$user->profile_image) {
                $updateData['profile_image'] = $googleUser->getAvatar();
            }
            if (!empty($updateData)) {
                $user->update($updateData);
            }

            // Check if user came from desktop app login flow
            if (session('desktop_auth_pending') || $flow === 'desktop') {
                session()->forget('desktop_auth_pending');
                return redirect()->to('/desktop-login');
            }

            // If user has no company linked, force them to setup
            $destination = (is_null($user->company_id) || !$user->is_setup_completed)
                ? '/company-setup'
                : '/dashboard';

            return redirect()->to('/login?token=' . urlencode($token) . '&redirect=' . urlencode($destination));

        }
        catch (Exception $e) {
            return redirect()->to('/login?error=' . urlencode("Google login failed: " . $e->getMessage()));
        }
    }

    /**
     * Cancel the onboarding setup, purge the uncompleted user record, and redirect.
     */
    public function cancelSetup(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Check if user->company_id is NULL and user->is_setup_completed is false
            $shouldDelete = is_null($user->company_id) && !$user->is_setup_completed;

            // Log out and invalidate active session first to avoid re-inserting user model during remember token cycle
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($shouldDelete) {
                // Soft-delete this uncompleted User record
                $user->delete();
            }
        }

        return redirect()->to('/login?error=' . urlencode('Signup cancelled. Your account details were not saved because company setup was incomplete.'));
    }
}