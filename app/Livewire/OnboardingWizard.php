<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OnboardingWizard extends Component
{
    use WithFileUploads;

    public $step = 1;

    /**
     * True when an already-onboarded user is creating an additional company.
     * Seeded from the ?new_company_flow=1 query parameter in mount().
     * Livewire serialises this property so it survives every AJAX roundtrip.
     */
    public $new_company_flow = false;

    // Step 1: Business Identity & Team Profiling
    public $company_name;
    public $company_email;
    public $company_phone;
    public $company_logo;
    public $registration_number;
    public $owner_role = 'Owner/CEO';
    public $team_size = 'Just Me';

    // Step 2: Brand Assets & Additional Info
    public $tax_number;
    public $business_address;

    // Step 2: Intended System Usage Matrices
    public $intended_tasks = [];

    // Step 3: Regional Localization & Finance Constraints
    public $business_type = '';
    public $business_scale = 'Single Outlet';
    public $country = 'United States';
    public $system_language = 'en';
    public $base_currency = 'USD';
    public $timezone_offset = 'UTC';
    public $fiscal_year_start;

    protected $rules = [
        1 => [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            'owner_role' => 'required|string',
            'team_size' => 'required|string',
        ],
        2 => [
            'company_logo' => 'nullable|image|max:2048',
            'tax_number' => 'nullable|string|max:100',
            'business_address' => 'nullable|string|max:255',
        ],
        3 => [
            'intended_tasks' => 'required|array|min:1',
        ],
        4 => [
            'business_type' => 'required|string',
            'business_scale' => 'required|string',
            'country' => 'required|string',
            'system_language' => 'required|string',
            'base_currency' => 'required|string',
            'timezone_offset' => 'required|string',
            'fiscal_year_start' => 'required|date',
        ]
    ];

    public $company_id = null;
    public $currentStep = 1;
    public $hasExistingActiveCompany = false;

    public function mount(?Company $company = null, int $currentStep = 1, bool $hasExistingActiveCompany = false): void
    {
        // Guard: redirect guests to login
        if (!Auth::check()) {
            redirect()->to('/login');
            return;
        }

        $this->hasExistingActiveCompany = $hasExistingActiveCompany;
        $this->currentStep = $currentStep;
        $this->step = $currentStep; // keep backward compatibility for internal step tracking

        if ($company && $company->status === 'draft') {
            $this->company_id           = $company->id;
            $this->company_id       = $company->id; // backward compat
            $this->company_name         = $company->company_name       ?? '';
            $this->company_email        = $company->company_email      ?: Auth::user()->email ?? '';
            $this->company_phone        = $company->company_phone      ?? '';
            $this->base_currency        = $company->base_currency      ?? '';
            $this->system_language      = $company->system_language    ?? '';
            $this->timezone_offset      = $company->timezone_offset    ?? '';
            $this->business_type        = $company->business_type      ?? '';
            $this->country              = $company->country            ?? '';
            $this->business_address     = $company->business_address   ?? '';
            $this->registration_number  = $company->registration_number ?? '';
            $this->tax_number           = $company->tax_number         ?? '';
            $this->owner_role           = $company->owner_role         ?? 'Owner/CEO';
            $this->team_size            = $company->team_size          ?? 'Just Me';
            $this->intended_tasks       = is_string($company->intended_tasks) ? json_decode($company->intended_tasks, true) : ($company->intended_tasks ?? []);
            $this->business_scale       = $company->business_scale     ?? 'Single Outlet';
            $this->fiscal_year_start    = $company->fiscal_year_start  ?? date('Y-01-01');
        } else {
            $user = Auth::user();
            $this->company_email        = $user->email ?? '';
            $this->company_name         = 'Untitled Draft Workspace';
            $this->fiscal_year_start    = date('Y-01-01');
        }
    }

    public function resumeDraft()
    {
        $draft = Company::find($this->company_id);
        if ($draft) {
            $this->company_name = $draft->company_name;
            $this->company_email = $draft->company_email;
            $this->company_phone = $draft->company_phone;
            $this->registration_number = $draft->registration_number;
            $this->owner_role = $draft->owner_role ?? 'Owner/CEO';
            $this->team_size = $draft->team_size ?? 'Just Me';
            $this->tax_number = $draft->tax_number;
            $this->business_address = $draft->business_address;
            
            // Cast intended_tasks array safely
            $this->intended_tasks = is_string($draft->intended_tasks) ? json_decode($draft->intended_tasks, true) : ($draft->intended_tasks ?? []);
            
            $this->business_type = $draft->business_type ?? '';
            $this->business_scale = $draft->business_scale ?? 'Single Outlet';
            $this->country = $draft->country ?? 'United States';
            $this->system_language = $draft->system_language ?? 'en';
            $this->base_currency = $draft->base_currency ?? 'USD';
            $this->timezone_offset = $draft->timezone_offset ?? 'UTC';
            $this->fiscal_year_start = $draft->fiscal_year_start;

            $this->step = $this->resumeStep;
        }
        $this->promptResume = false;
    }

    public function startFresh()
    {
        // Discard the old draft to start completely fresh
        if ($this->company_id) {
            Company::where('id', $this->company_id)->delete();
            $this->company_id = null;
        }
        $this->promptResume = false;
    }

    public function saveDraft()
    {
        $user = Auth::user();

        // Update or create draft company record
        $company = Company::updateOrCreate(
            ['id' => $this->company_id, 'user_id' => $user->id],
            [
                'company_name' => $this->company_name ?? 'Draft Company',
                'company_email' => $this->company_email ?? '',
                'company_phone' => $this->company_phone ?? '',
                'tax_number' => $this->tax_number ?? '',
                'business_address' => $this->business_address ?? '',
                'registration_number' => $this->registration_number ?? '',
                'owner_role' => $this->owner_role ?? 'Owner/CEO',
                'team_size' => $this->team_size ?? 'Just Me',
                'intended_tasks' => is_array($this->intended_tasks) ? $this->intended_tasks : [],
                'business_type' => $this->business_type ?? '',
                'business_scale' => $this->business_scale ?? 'Single Outlet',
                'country' => $this->country ?? 'United States',
                'system_language' => $this->system_language ?? 'en',
                'base_currency' => $this->base_currency ?? 'USD',
                'timezone_offset' => $this->timezone_offset ?? 'UTC',
                'fiscal_year_start' => $this->fiscal_year_start ?? date('Y-01-01'),
                'status' => 'draft',
                'draft_step' => $this->step,
            ]
        );

        session()->forget('creating_subsequent_company');

        return redirect('/')->with('status', 'Progress saved as draft.');
    }

    public function discardSetup()
    {
        if ($this->company_id) {
            Company::where('id', $this->company_id)->where('status', 'draft')->delete();
        }
        session()->forget('creating_subsequent_company');
        return redirect('/')->with('status', 'Company setup discarded.');
    }

    public function nextStep()
    {
        $this->validate($this->rules[$this->step]);
        $this->persistDraft();
        $this->step++;
        $this->currentStep = $this->step;
    }

    public function previousStep()
    {
        $this->persistDraft();
        $this->step--;
        $this->currentStep = $this->step;
    }

    private function persistDraft()
    {
        if (!$this->company_id) return;

        $logoPath = $this->company_logo instanceof \Illuminate\Http\UploadedFile 
            ? $this->company_logo->store('company_logos', 'public') 
            : null;

        $updateData = [
            'company_name' => $this->company_name ?? 'Untitled Draft Workspace',
            'company_email' => $this->company_email,
            'company_phone' => $this->company_phone,
            'registration_number' => $this->registration_number,
            'owner_role' => $this->owner_role ?? 'Owner/CEO',
            'team_size' => $this->team_size ?? 'Just Me',
            'tax_number' => $this->tax_number,
            'business_address' => $this->business_address,
            'intended_tasks' => json_encode($this->intended_tasks ?? []),
            'business_type' => $this->business_type,
            'business_scale' => $this->business_scale ?? 'Single Outlet',
            'country' => $this->country ?? 'United States',
            'system_language' => $this->system_language ?? 'en',
            'base_currency' => $this->base_currency ?? 'USD',
            'timezone_offset' => $this->timezone_offset ?? 'UTC',
            'fiscal_year_start' => $this->fiscal_year_start ?? date('Y-01-01'),
            'draft_step' => $this->step,
        ];

        if ($logoPath) {
            $updateData['company_logo'] = $logoPath;
        }

        Company::where('id', $this->company_id)
            ->where('user_id', Auth::id())
            ->update($updateData);
    }

    public function skipStep()
    {
        $this->step++;
    }

    public function submit()
    {
        // Validate all steps
        $this->validate(array_merge($this->rules[1], $this->rules[2], $this->rules[3], $this->rules[4]));

        DB::transaction(function () {
            $logoPath = null;
            if ($this->company_logo) {
                $logoPath = $this->company_logo->store('company-logos', 'public');
            }

            $user = Auth::user();

            $company = Company::updateOrCreate(
                ['id' => $this->company_id, 'user_id' => $user->id],
                [
                    'company_name' => $this->company_name ?? 'Draft Company',
                    'company_email' => $this->company_email ?? '',
                    'company_phone' => $this->company_phone ?? '',
                    'company_logo' => $logoPath,
                    'tax_number' => $this->tax_number ?? '',
                    'business_address' => $this->business_address ?? '',
                    'registration_number' => $this->registration_number ?? '',
                    'owner_role' => $this->owner_role ?? 'Owner/CEO',
                    'team_size' => $this->team_size ?? 'Just Me',
                    'intended_tasks' => is_array($this->intended_tasks) ? $this->intended_tasks : [],
                    'business_type' => $this->business_type ?? '',
                    'business_scale' => $this->business_scale ?? 'Single Outlet',
                    'country' => $this->country ?? 'United States',
                    'system_language' => $this->system_language ?? 'en',
                    'base_currency' => $this->base_currency ?? 'USD',
                    'timezone_offset' => $this->timezone_offset ?? 'UTC',
                    'fiscal_year_start' => $this->fiscal_year_start ?? date('Y-01-01'),
                    'status' => 'active', // Mark as active, ending draft phase
                    'draft_step' => null, // Clear the draft checkpoint
                ]
            );

            $user->current_company_id = $company->id;

            // Only flip onboarding_completed for first-time setup.
            // For new_company_flow (second+ company), the flag is already true.
            if (!$this->hasExistingActiveCompany) {
                $user->onboarding_completed = true;
            }

            $user->save();

            // Link the user to the company in the pivot table with owner role
            if (!$user->companies()->where('company_id', $company->id)->exists()) {
                $user->companies()->attach($company->id, ['role' => 'owner']);
            }

            if (!$user->hasRole('admin')) {
                $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                $adminRole->syncPermissions(\Spatie\Permission\Models\Permission::where('guard_name', 'web')->get());
                $user->assignRole($adminRole);
            }

            // ── Baseline Seeding ──
            // Seed Default Branch Warehouse
            \App\Models\Warehouse::firstOrCreate([
                'company_id' => $company->id,
                'is_default' => true,
            ], [
                'name' => 'Default Branch Warehouse',
                'email' => $company->company_email ?: $user->email,
                'phone' => $company->company_phone ?: '',
                'address' => $company->business_address ?: '',
                'is_active' => true,
                'is_saleable' => true,
            ]);

            // Seed Default Cash Vault Bank Account
            $cashAccount = \App\Models\Account::where('account_code', '1010')
                ->orWhere('account_name', 'like', '%Cash%')
                ->first();

            if (!$cashAccount) {
                $cashAccount = \App\Models\Account::create([
                    'account_code' => '1010',
                    'account_name' => 'Cash on Hand',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'is_active' => true,
                    'is_system_account' => true,
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                ]);
            }

            \App\Models\BankAccount::firstOrCreate([
                'company_id' => $company->id,
                'account_name' => 'Default Cash Vault',
            ], [
                'bank_name' => 'Cash Account',
                'account_number' => 'CASH-001',
                'account_type' => 'checking',
                'chart_account_id' => $cashAccount->id,
                'is_active' => true,
                'is_default' => true,
                'opening_balance' => 0.00,
                'opening_date' => now()->format('Y-m-d'),
            ]);
        });

        // Clear the bypass session token now that setup is complete.
        session()->forget('creating_subsequent_company');

        // ── Apply system-wide settings from Step 4 ──────────────────────────────

        // 1. Apply locale/language
        if ($this->system_language) {
            \Illuminate\Support\Facades\App::setLocale($this->system_language);
            \Illuminate\Support\Facades\Config::set('app.locale', $this->system_language);
        }

        // 2. Apply timezone — persist to cache so SetSystemTimezone middleware
        //    reads the correct value on every subsequent request (same as
        //    TimezoneController::setTimezone does from the Settings page).
        if ($this->timezone_offset) {
            // Map the short-code aliases used in the wizard to valid IANA identifiers
            $timezoneMap = [
                'UTC' => 'UTC',
                'EST' => 'America/New_York',
                'PST' => 'America/Los_Angeles',
                'GMT' => 'Europe/London',
                'PKT' => 'Asia/Karachi',
            ];
            $ianaTimezone = $timezoneMap[$this->timezone_offset] ?? $this->timezone_offset;

            \Illuminate\Support\Facades\Cache::forever('system_timezone', $ianaTimezone);
            date_default_timezone_set($ianaTimezone);
            \Illuminate\Support\Facades\Config::set('app.timezone', $ianaTimezone);
        }

        // 3. Apply currency — find the currency record by ISO code and mark it
        //    active (same endpoint the Settings page currency selector calls).
        if ($this->base_currency) {
            $currency = \App\Models\Currency::where('code', $this->base_currency)->first();
            if ($currency) {
                \App\Models\Currency::query()->update(['is_active' => false]);
                $currency->update(['is_active' => true]);
            }

            \Illuminate\Support\Facades\Config::set('app.currency', $this->base_currency);
            session([
                'tenant_currency' => $this->base_currency,
                'app_currency'    => $this->base_currency,
            ]);
        }

        session()->flash('success', 'Onboarding completed successfully! Welcome to Aura.');

        return redirect()->to('/');
    }


    public function render()
    {
        return view('livewire.onboarding-wizard')
               ->layout('components.layouts.app'); // Or base layout
    }
}
