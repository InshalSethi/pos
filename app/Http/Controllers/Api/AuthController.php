<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSettings;
use App\Models\Employee;
use App\Services\EmployeeUserService;
use App\Events\EmployeeDeactivatedEvent;
use App\Events\EmployeeUpdatedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || empty($user->password) || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // System Access & Employee Active Status Checks
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        if ($employee) {
            if ($employee->status === 'inactive' || !$employee->is_active) {
                throw ValidationException::withMessages([
                    'email' => ['Your account is currently inactive. Please contact your administrator.'],
                ]);
            }
            if (!$employee->has_system_access) {
                throw ValidationException::withMessages([
                    'email' => ['System login access is disabled for this account.'],
                ]);
            }
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is currently inactive. Please contact your administrator.'],
            ]);
        }

        if (!$user->hasLoginAccess()) {
            throw ValidationException::withMessages([
                'email' => ['System login access is disabled for this account.'],
            ]);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Invalidate stale session before establishing new one if session exists
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Establish Web Session for Blade/Livewire integration
        Auth::guard('web')->login($user, $request->boolean('rememberMe'));
        
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // Get user permissions and roles
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();
        if ($user->current_company_id) {
            $companyPivot = $user->companies()->where('companies.id', $user->current_company_id)->first();
            if ($companyPivot && $companyPivot->pivot && $companyPivot->pivot->role) {
                $pivotRole = strtolower($companyPivot->pivot->role);
                if (!in_array($pivotRole, $roles)) {
                    $roles[] = $pivotRole;
                }
            }
        }

        // Eagerly resolve the active company for localization context
        $company = $user->current_company_id
            ? $user->currentCompany()->select('id', 'base_currency', 'system_language', 'timezone_offset')->first()
            : null;

        $redirectUrl = ($user->onboarding_completed && $user->current_company_id) ? '/dashboard' : '/owner/companies';
        if (session('desktop_auth_pending') || $request->input('redirect') === '/desktop-login') {
            $redirectUrl = '/desktop-login';
        }

        return response()->json([
            'token'           => $token,
            'user'            => $user,
            'permissions'     => $permissions,
            'roles'           => $roles,
            'redirect_url'    => $redirectUrl,
            'company_context' => $company ? [
                'base_currency'   => $company->base_currency   ?? 'USD',
                'system_language' => $company->system_language ?? 'en',
                'timezone_offset' => $company->timezone_offset ?? 'UTC',
            ] : null,
        ]);
    }

    public function register(Request $request)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
        ];

        if ($request->has('name') && !$request->has('first_name') && !$request->has('last_name')) {
            $rules['name'] = 'required|string|max:255';
            unset($rules['first_name'], $rules['last_name']);
        }

        $plan = strtolower($request->input('plan', 'basic'));
        if ($plan !== 'starter' && $plan !== 'standard' && $plan !== 'free') {
            $rules['cardNumber'] = ['required', new \App\Rules\ValidCardNumber()];
            $rules['cardExpiry'] = ['required', new \App\Rules\ValidCardExpiry()];
            $rules['cardCvc']    = ['required', new \App\Rules\ValidCardCvc()];
        }

        $request->validate($rules);

        $fullName = trim($request->input('first_name', '') . ' ' . $request->input('last_name', ''));
        if (empty($fullName)) {
            $fullName = trim($request->input('name', ''));
        }

        return DB::transaction(function () use ($request, $fullName) {
            // Create user
            $user = User::create([
                'name' => $fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'onboarding_completed' => false,
                'is_active' => true,
            ]);

            // Assign default admin role with all permissions
            if (\Spatie\Permission\Models\Permission::where('guard_name', 'web')->count() === 0) {
                (new \Database\Seeders\RolePermissionSeeder())->run();
            }

            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $adminRole->syncPermissions(\Spatie\Permission\Models\Permission::where('guard_name', 'web')->get());
            $user->assignRole($adminRole);

            // Create default user settings
            UserSettings::create([
                'user_id' => $user->id,
                'email_notifications' => true,
                'sales_alerts' => false,
                'low_stock_alerts' => false,
                'theme' => 'light',
                'items_per_page' => 15,
                'default_payment_method' => 'cash',
                'auto_print_receipts' => false,
                'sound_effects' => true,
                'session_timeout' => 60,
                'two_factor_auth' => false,
            ]);

            // Create encrypted license key for new registered user storing email, plan, start date and end date
            $plan = strtolower($request->input('plan', 'basic'));
            $cycle = strtolower($request->input('cycle', 'monthly'));
            $startDate = now()->toDateString();
            $expiresAt = ($plan === 'starter') ? now()->addDays(14)->toDateString() : ($cycle === 'yearly' ? now()->addYear()->toDateString() : now()->addDays(30)->toDateString());
            $encryptedKey = \App\Services\LicenseKeyService::generateEncryptedKey($user->email, $plan, $startDate, $expiresAt);

            \App\Models\License::updateOrCreate(
                ['id' => 1],
                [
                    'license_key'    => $encryptedKey,
                    'device_id'     => 'USER-' . $user->id,
                    'plan'          => $plan,
                    'status'        => 'active',
                    'start_date'    => $startDate,
                    'expires_at'    => $expiresAt,
                    'last_opened_at' => now(),
                ]
            );

            // Record Subscription Payment History
            \App\Services\SubscriptionPaymentService::recordPayment(
                $user->id,
                $user->name,
                $user->email,
                $plan,
                $cycle,
                $request->input('cardNumber'),
                'Credit Card',
                $request->input('coupon_code', $request->input('couponCode'))
            );

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Invalidate stale session before establishing new one if session exists
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Establish Web Session for Blade/Livewire integration
            Auth::guard('web')->login($user);
            
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // Get user permissions and roles
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
            $roles = $user->getRoleNames()->toArray();
            if ($user->current_company_id) {
                $companyPivot = $user->companies()->where('companies.id', $user->current_company_id)->first();
                if ($companyPivot && $companyPivot->pivot && $companyPivot->pivot->role) {
                    $pivotRole = strtolower($companyPivot->pivot->role);
                    if (!in_array($pivotRole, $roles)) {
                        $roles[] = $pivotRole;
                    }
                }
            }

            return response()->json([
                'token' => $token,
                'user' => $user,
                'permissions' => $permissions,
                'roles' => $roles,
                'redirect_url' => '/owner/companies',
            ], 201);
        });
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $user->load(['employee.company', 'employee.department', 'employee.position', 'currentCompany', 'roles']);
        
        // Get user permissions and roles
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();
        if ($user->current_company_id) {
            $companyPivot = $user->companies()->where('companies.id', $user->current_company_id)->first();
            if ($companyPivot && $companyPivot->pivot && $companyPivot->pivot->role) {
                $pivotRole = strtolower($companyPivot->pivot->role);
                if (!in_array($pivotRole, $roles)) {
                    $roles[] = $pivotRole;
                }
            }
        }

        // Eagerly resolve the active company for localization context
        $company = $user->current_company_id
            ? $user->currentCompany()->select('id', 'base_currency', 'system_language', 'timezone_offset')->first()
            : null;

        return response()->json([
            'user'            => $user,
            'permissions'     => $permissions,
            'roles'           => $roles,
            'company_context' => $company ? [
                'base_currency'   => $company->base_currency   ?? 'USD',
                'system_language' => $company->system_language ?? 'en',
                'timezone_offset' => $company->timezone_offset ?? 'UTC',
            ] : null,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:30',
            'country' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'emergency_contact_email' => 'nullable|email',
            'company_id' => 'nullable|exists:companies,id',
            'hire_date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'employment_status' => 'nullable|in:active,inactive,on_leave,terminated',
            'basic_salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|in:monthly,hourly,daily',
            'hourly_rate' => 'nullable|numeric|min:0',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Extract names
        $firstName = trim($request->input('first_name', ''));
        $middleName = trim($request->input('middle_name', ''));
        $lastName = trim($request->input('last_name', ''));

        if (!$firstName && $request->has('name')) {
            $nameParts = explode(' ', trim($request->name));
            $firstName = array_shift($nameParts) ?: $request->name;
            $lastName = count($nameParts) > 0 ? array_pop($nameParts) : '';
            $middleName = count($nameParts) > 0 ? implode(' ', $nameParts) : '';
        }

        $fullName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);

        // Password check
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }
        }

        $userUpdate = [
            'name' => $fullName ?: $user->name,
            'email' => $request->email,
        ];

        if ($request->has('phone') || $request->has('mobile')) {
            $userUpdate['phone'] = $request->input('phone', $request->input('mobile', $user->phone));
        }

        if ($request->has('address')) {
            $userUpdate['address'] = $request->address;
        }

        if ($request->filled('new_password')) {
            $userUpdate['password'] = Hash::make($request->new_password);
        }

        $user->update($userUpdate);

        // Handle profile image upload if included in request
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $imgPath = $request->file('profile_image')->store('profile-images', 'public');
            $user->update(['profile_image' => $imgPath]);
        }

        // DIRECT EMPLOYEES TABLE SYNC
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        if ($employee) {
            $employeeData = $request->only([
                'first_name', 'middle_name', 'last_name', 'email', 'phone', 'mobile', 'fax',
                'date_of_birth', 'gender', 'marital_status', 'address', 'city', 'state',
                'postal_code', 'country', 'emergency_contact_name', 'emergency_contact_relationship',
                'emergency_contact_phone', 'emergency_contact_email', 'company_id', 'hire_date',
                'department_id', 'position_id', 'employment_type', 'employment_status',
                'basic_salary', 'salary_type', 'hourly_rate', 'bank_account_number', 'bank_name', 'bank_branch'
            ]);

            // Ensure null for empty optional values
            foreach ($employeeData as $k => $v) {
                if ($v === '') {
                    $employeeData[$k] = null;
                }
            }

            if ($firstName) $employeeData['first_name'] = $firstName;
            if ($lastName) $employeeData['last_name'] = $lastName;
            $employeeData['middle_name'] = $middleName ?: null;
            $employeeData['email'] = $request->email;
            $employeeData['user_id'] = $user->id;

            if (isset($imgPath)) {
                $employeeData['profile_image'] = $imgPath;
            }

            // Handle attachment file uploads if present
            if ($request->hasFile('attachments')) {
                $existing = is_array($employee->attachments) ? $employee->attachments : [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('employee-attachments', 'public');
                    $existing[] = [
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ];
                }
                $employeeData['attachments'] = array_slice($existing, 0, 5);
            } elseif ($request->has('existing_attachments')) {
                $rawExist = $request->get('existing_attachments');
                $parsed = is_string($rawExist) ? json_decode($rawExist, true) : $rawExist;
                if (is_array($parsed)) {
                    $employeeData['attachments'] = $parsed;
                }
            }

            $employee->update($employeeData);

            try {
                event(new EmployeeUpdatedEvent($employee->fresh()));
            } catch (\Exception $e) {}
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(['employee.department', 'employee.position'])
        ]);
    }

    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Delete old profile image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new image
        $path = $request->file('profile_image')->store('profile-images', 'public');

        $user->update(['profile_image' => $path]);

        // DIRECT EMPLOYEES TABLE SYNC
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        if ($employee) {
            if ($employee->profile_image && $employee->profile_image !== $path) {
                Storage::disk('public')->delete($employee->profile_image);
            }
            $employee->update([
                'profile_image' => $path,
                'user_id' => $user->id,
            ]);
            try {
                event(new EmployeeUpdatedEvent($employee->fresh()));
            } catch (\Exception $e) {}
        }

        return response()->json([
            'message' => 'Profile image uploaded successfully',
            'profile_image_url' => asset('storage/' . $path)
        ]);
    }

    public function getSettings(Request $request)
    {
        $user = $request->user();
        $settings = $user->settings;

        if (!$settings) {
            // Create default settings if they don't exist
            $settings = UserSettings::create(['user_id' => $user->id]);
        }

        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();
        $settings = $user->settings;

        if (!$settings) {
            $settings = UserSettings::create(['user_id' => $user->id]);
        }

        $validator = Validator::make($request->all(), [
            'email_notifications' => 'boolean',
            'sales_alerts' => 'boolean',
            'low_stock_alerts' => 'boolean',
            'theme' => 'in:light,dark,auto',
            'items_per_page' => 'integer|min:5|max:100',
            'default_payment_method' => 'in:cash,card,digital',
            'auto_print_receipts' => 'boolean',
            'sound_effects' => 'boolean',
            'session_timeout' => 'integer|min:5|max:480',
            'two_factor_auth' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $settings->update($request->only([
            'email_notifications',
            'sales_alerts',
            'low_stock_alerts',
            'theme',
            'items_per_page',
            'default_payment_method',
            'auto_print_receipts',
            'sound_effects',
            'session_timeout',
            'two_factor_auth',
        ]));

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $settings->fresh()
        ]);
    }

    /**
     * Send password reset link to user's email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email address.',
                'status' => 'success'
            ]);
        }

        return response()->json([
            'message' => 'Unable to send password reset link. Please try again.',
            'status' => 'error'
        ], 400);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully.',
                'status' => 'success'
            ]);
        }

        return response()->json([
            'message' => 'Failed to reset password. Please check your token and try again.',
            'status' => 'error',
            'errors' => ['email' => [__($status)]]
        ], 400);
    }

    public function syncSession(Request $request)
    {
        $user = $request->user();
        if ($user) {
            Auth::guard('web')->login($user, true);
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }
            return response()->json(['message' => 'Web session synchronized successfully']);
        }
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    public function checkStatus(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'UNAUTHENTICATED'], 401);
        }

        $isInactive = !$user->is_active;
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        if ($employee && ($employee->status === 'inactive' || !$employee->is_active || $employee->employment_status === 'inactive')) {
            $isInactive = true;
        }

        if ($isInactive) {
            if (method_exists($user, 'tokens')) {
                try { $user->tokens()->delete(); } catch (\Exception $e) {}
            }
            return response()->json([
                'error' => 'ACCOUNT_INACTIVE',
                'message' => 'Your account has been deactivated by the administrator.'
            ], 403);
        }

        return response()->json([
            'status' => 'active',
            'user_id' => $user->id
        ]);
    }

    public function cloudAuthSync(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'nullable|string',
            'name'  => 'nullable|string',
        ]);

        $email = $request->input('email');
        $name = $request->input('name');

        // Find existing user or provision local account for cloud user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'                 => $name ?: 'Cloud User',
                'email'                => $email,
                'password'             => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                'is_active'            => true,
                'onboarding_completed' => true,
                'is_setup_completed'   => true,
            ]);

            try {
                $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                $user->assignRole($adminRole);
            } catch (\Throwable $th) {
                // Ignore permission seeder errors if already existing
            }
        } else {
            $user->update([
                'is_active'            => true,
                'onboarding_completed' => true,
                'is_setup_completed'   => true,
            ]);
        }

        // Sync license details dynamically from incoming cloud authentication payload
        $plan       = $request->input('plan', 'basic');
        $startDate  = $request->input('start_date') ?: now()->toDateString();
        $expiresAt  = $request->input('expires_at') ?: now()->addYear()->toDateString();
        $encryptedKey = \App\Services\LicenseKeyService::generateEncryptedKey($user->email, $plan, $startDate, $expiresAt);

        \App\Models\License::updateOrCreate(
            ['id' => 1],
            [
                'license_key'    => $encryptedKey,
                'plan'          => $plan,
                'status'        => 'active',
                'start_date'    => $startDate,
                'expires_at'    => $expiresAt,
                'device_id'     => 'CLOUD-AUTHENTICATED',
                'last_opened_at' => now(),
            ]
        );

        // Issue a local Sanctum token valid for this local Desktop installation
        $localToken = $user->createToken('desktop-local-session')->plainTextToken;
        $localToken = $user->createToken('desktop-local-session')->plainTextToken;

        // Establish Web Session for local Blade/Vue middleware
        Auth::guard('web')->login($user, true);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $permissions = method_exists($user, 'getAllPermissions') ? $user->getAllPermissions()->pluck('name')->toArray() : [];
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];

        $company = $user->current_company_id
            ? $user->currentCompany()->select('id', 'base_currency', 'system_language', 'timezone_offset')->first()
            : null;

        return response()->json([
            'token'           => $localToken,
            'user'            => $user->fresh(['roles']),
            'permissions'     => $permissions,
            'roles'           => $roles,
            'redirect_url'    => '/dashboard',
            'company_context' => $company ? [
                'base_currency'   => $company->base_currency   ?? 'USD',
                'system_language' => $company->system_language ?? 'en',
                'timezone_offset' => $company->timezone_offset ?? 'UTC',
            ] : null,
        ]);
    }
}

