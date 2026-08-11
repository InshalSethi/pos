<div x-data="{
        hasActiveCompany:      {{ $hasExistingActiveCompany ? 'true' : 'false' }},
        showFreshUserModal:    false,
        showExistingUserModal: false,
        activeCompanyId:       {{ $company_id ?? 'null' }},
        currentStep:           {{ $step ?? 1 }},
        handleHomeNavigation() {
            if (this.hasActiveCompany) {
                this.showExistingUserModal = true;
            } else {
                this.showFreshUserModal = true;
            }
        }
    }"
    class="min-h-screen w-full bg-slate-50 flex flex-col justify-center items-center relative overflow-x-hidden font-sans selection:bg-slate-900 selection:text-white">

    {{-- ═══ PERSISTENT TOP NAVBAR (Shown for Brand New Users only) ═══ --}}
    @if(!$hasExistingActiveCompany)
    <header class="sticky top-0 z-50 pt-4 px-4 sm:px-6 lg:px-8 transition-all">
        <div class="max-w-6xl mx-auto bg-white/80 backdrop-blur-xl border border-slate-200/80 shadow-xl shadow-slate-200/40 rounded-full px-6 py-3 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="#" @click.prevent="handleHomeNavigation()" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-full bg-slate-950 flex items-center justify-between p-2 shadow-md group-hover:scale-105 transition-transform">
                    <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                    <div class="w-1.5 h-full rounded-full bg-white/40"></div>
                </div>
                <span class="text-xl font-black tracking-tight text-slate-950 group-hover:text-slate-700 transition-colors">POS</span>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#" @click.prevent="handleHomeNavigation()" class="hover:text-slate-950 transition-colors">Home</a>
                <div class="relative group cursor-pointer flex items-center gap-1.5 hover:text-slate-950 transition-colors">
                    <span>Features</span>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-950 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <a href="/plans" class="hover:text-slate-950 transition-colors">Pricing</a>
            </nav>

            <div class="flex items-center gap-3"></div>
        </div>
    </header>
    @endif

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- MODAL A — Path A: Fresh Registrant (Zero active companies)    --}}
        {{-- Cancel triggers full atomic account teardown                  --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div
            x-show="showFreshUserModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="fresh-modal-title"
            @keydown.escape.window="showFreshUserModal = false"
            style="display: none;"
        >
            <div
                @click.outside="showFreshUserModal = false"
                class="w-full max-w-md mx-4 p-6 bg-white
                       border border-slate-200
                       rounded-2xl shadow-2xl text-center"
            >
                {{-- Warning Icon --}}
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center
                            rounded-full bg-amber-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.75" stroke="currentColor"
                         class="w-6 h-6 text-amber-500" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71
                                 c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378
                                 c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>

                <h3 id="fresh-modal-title"
                    class="text-lg font-bold text-slate-900">
                    Exit Account Onboarding?
                </h3>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    Your organization setup is not yet complete. Cancelling will
                    <strong class="text-rose-500">permanently delete</strong>
                    your account and all entered data. This cannot be undone.
                </p>

                <div class="mt-6 flex items-center gap-3">

                    {{-- A1: Stay on wizard --}}
                    <button
                        @click="showFreshUserModal = false"
                        type="button"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold
                               text-slate-700
                               bg-slate-100 hover:bg-slate-200
                               rounded-xl transition-colors duration-150
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Continue Setup
                    </button>

                    {{-- A2: Full account teardown via GET route --}}
                    <a
                        href="{{ route('company.setup.cancel') }}"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-white text-center
                               bg-rose-500 hover:bg-rose-600 active:bg-rose-700
                               rounded-xl shadow-sm transition-colors duration-150
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400"
                    >
                        Cancel Setup
                    </a>

                </div>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- MODAL B — Path B: Existing Tenant (Has active companies)      --}}
        {{-- Discard or save the sub-company draft being built             --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div
            x-show="showExistingUserModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tenant-modal-title"
            @keydown.escape.window="showExistingUserModal = false"
            style="display: none;"
        >
            <div
                @click.outside="showExistingUserModal = false"
                class="w-full max-w-md mx-4 p-6 bg-white
                       border border-slate-200
                       rounded-2xl shadow-2xl"
            >
                <h3 id="tenant-modal-title"
                    class="text-lg font-bold text-slate-900">
                    Save Sub-Company Progress?
                </h3>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    You are registering an additional business workspace. Choose how to
                    handle this draft before returning to your main dashboard.
                    Your active companies are never affected.
                </p>

                <div class="mt-6 flex flex-col gap-2.5">

                    {{-- B1: Discard this sub-company draft only --}}
                    <form action="{{ route('onboarding.abort-registration') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company_id }}">
                        <button
                            type="submit"
                            class="w-full py-3 px-4 text-sm font-medium text-white bg-slate-900 hover:bg-black rounded-xl shadow-sm transition-all duration-150 flex items-center justify-center gap-2 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-white opacity-80">
                                <path d="M3 6h18"/>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                <line x1="10" x2="10" y1="11" y2="17"/>
                                <line x1="14" x2="14" y1="11" y2="17"/>
                            </svg>
                            <span>Discard Current Setup</span>
                        </button>
                    </form>

                    {{-- B2: Save as resumable draft --}}
                    <form action="{{ route('onboarding.save-draft') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company_id }}">
                        <input type="hidden" name="current_step" value="{{ $step }}">
                        <button
                            type="submit"
                            class="w-full py-3 px-4 text-sm font-medium text-white bg-slate-900 hover:bg-black rounded-xl shadow-sm transition-all duration-150 flex items-center justify-center gap-2 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-white opacity-80">
                                <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>
                            </svg>
                            <span>Save as Draft &amp; Exit</span>
                        </button>
                    </form>

                    {{-- B3: Dismiss and keep editing --}}
                    <button
                        @click="showExistingUserModal = false"
                        type="button"
                        class="w-full px-4 py-2.5 text-sm font-medium
                               text-slate-400 hover:text-slate-700
                               transition-colors duration-150
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300">
                        ← Keep Editing
                    </button>

                </div>
            </div>
        </div>


    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MAIN CONTENT: Light Modern Two-Column Layout                      --}}
    {{-- Matches Landing / Login / Register Page Design System             --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <main class="grow flex items-center justify-center max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 z-10 relative py-6 lg:py-10 my-auto">

        <!-- Ambient Background Radial Glow (matches Login/Register pages) -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-gradient-to-tr from-slate-200/50 via-gray-100/30 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center w-full relative">

            {{-- ═══ LEFT COLUMN: Setup Overview & Step Progress ═══ --}}
            <div class="lg:col-span-5 space-y-6 text-left lg:sticky lg:top-28">

                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-semibold text-slate-700">
                    <span>+ Business Onboarding</span>
                </div>

                <!-- Title & Subtitle -->
                <div class="space-y-3">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight tracking-tight">
                        Let's setup your business identity
                    </h1>
                    <p class="text-slate-500 text-sm leading-relaxed max-w-md">
                        Provide your primary enterprise registration metrics, brand assets, and operational parameters.
                    </p>
                </div>

                <!-- Vertical Step Progress Indicator -->
                <div class="pt-4 border-t border-slate-200/80">
                    <div class="space-y-0">
                        @php
                            $steps = [
                                1 => ['Enterprise Metrics', 'Legal entity & team details'],
                                2 => ['Branding', 'Logo, tax & location info'],
                                3 => ['Target Objectives', 'Feature usage preferences'],
                                4 => ['Regional Rules', 'Localization & finance config'],
                            ];
                        @endphp

                        @foreach($steps as $num => $info)
                            <div class="flex items-start gap-4 relative">
                                {{-- Vertical connector line --}}
                                @if($num < 4)
                                    <div class="absolute left-[15px] top-[32px] w-[2px] h-[calc(100%-8px)] {{ $num < $step ? 'bg-slate-900' : 'bg-slate-200' }}"></div>
                                @endif

                                {{-- Step Circle --}}
                                <div class="relative z-10 flex-shrink-0 mt-0.5">
                                    @if($num < $step)
                                        {{-- Completed --}}
                                        <div class="w-8 h-8 rounded-full bg-slate-900 flex items-center justify-center shadow-sm">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @elseif($num == $step)
                                        {{-- Current --}}
                                        <div class="w-8 h-8 rounded-full bg-slate-900 flex items-center justify-center shadow-md ring-4 ring-slate-200/60">
                                            <span class="text-xs font-bold text-white">{{ $num }}</span>
                                        </div>
                                    @else
                                        {{-- Upcoming --}}
                                        <div class="w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center">
                                            <span class="text-xs font-semibold text-slate-400">{{ $num }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Step Label --}}
                                <div class="pb-6">
                                    <p class="text-sm font-bold {{ $num <= $step ? 'text-slate-900' : 'text-slate-400' }}">
                                        Step {{ $num }}: {{ $info[0] }}
                                    </p>
                                    <p class="text-xs {{ $num <= $step ? 'text-slate-500' : 'text-slate-300' }} mt-0.5">
                                        {{ $info[1] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


            {{-- ═══ RIGHT COLUMN: Setup Form Card ═══ --}}
            <div class="lg:col-span-7 flex justify-center lg:justify-end">
                <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-xl max-w-xl w-full relative z-10">

                    {{-- Card Header with Progress Bar --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-xl font-extrabold text-slate-950 tracking-tight">
                                @if($step == 1) Enterprise Metrics
                                @elseif($step == 2) Brand Assets
                                @elseif($step == 3) Target Objectives
                                @elseif($step == 4) Framework Configuration
                                @endif
                            </h2>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-400 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-full">
                                    Step {{ $step }} of 4
                                </span>
                                <button
                                    type="button"
                                    @click="handleHomeNavigation()"
                                    class="w-8 h-8 text-slate-500 hover:text-black cursor-pointer transition-colors flex items-center justify-center rounded-full hover:bg-slate-100 border border-slate-200/80 shadow-xs"
                                    title="Home"
                                    aria-label="Home"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                        <polyline points="9 22 9 12 15 12 15 22"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        {{-- Horizontal Progress Bar --}}
                        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-slate-900 h-full rounded-full transition-all duration-500 ease-out"
                                style="width: {{ ($step / 4) * 100 }}%;"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2.5">
                            @if($step == 1) Please enter your accurate legal entity details.
                            @elseif($step == 2) Upload your official company logo and location details.
                            @elseif($step == 3) Select all core features you plan to utilize immediately.
                            @elseif($step == 4) Establish the baseline regional settings for your ledger.
                            @endif
                        </p>
                    </div>


                    {{-- FORM WRAPPER --}}
                    <form wire:submit.prevent="submit" class="flex flex-col">

                        <div class="flex-grow">
                            {{-- STEP 1: Enterprise Metrics --}}
                            @if($step == 1)
                                @php
                                    $ownerRoles = [
                                        'Owner/CEO' => 'Owner/CEO',
                                        'Managing Director' => 'Managing Director',
                                        'Store Manager' => 'Store Manager',
                                        'Accountant/Financial Officer' => 'Accountant/Financial Officer',
                                    ];

                                    $teamSizes = [
                                        'Just Me' => 'Just Me',
                                        '2-5 People' => '2-5 People',
                                        '6-20 People' => '6-20 People',
                                        '21-50 People' => '21-50 People',
                                        '51+ People' => '51+ People',
                                    ];
                                @endphp
                                <div class="space-y-4 animate-fade-in w-full">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Name</label>
                                            <input type="text"
                                                wire:model="company_name"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-white"
                                                placeholder="e.g. Acme Corp">
                                            @error('company_name') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Registration Number</label>
                                            <input type="text"
                                                wire:model="registration_number"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-white"
                                                placeholder="e.g. 123456789">
                                            @error('registration_number') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Email</label>
                                            <input type="email"
                                                wire:model="company_email"
                                                readonly
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-slate-50"
                                                placeholder="contact@company.com">
                                            <span class="text-[11px] text-slate-400 mt-1 block">Tied to your registered account email.</span>
                                            @error('company_email') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>
                                         <!-- International Company Phone Input Component -->
                                         <div class="relative"
                                             @click.outside="open = false; search = ''"
                                             x-data="{
                                                 open: false,
                                                 search: '',
                                                 countries: [
                                                     { code: 'PK', name: 'Pakistan', dialCode: '+92', flag: '🇵🇰', placeholder: '300 1234567', examplePattern: '300 1234567', digits: 10 },
                                                     { code: 'US', name: 'United States', dialCode: '+1', flag: '🇺🇸', placeholder: '201-555-0123', examplePattern: '201-555-0123', digits: 10 },
                                                     { code: 'UK', name: 'United Kingdom', dialCode: '+44', flag: '🇬🇧', placeholder: '7911 123456', examplePattern: '7911 123456', digits: 10 },
                                                     { code: 'AE', name: 'United Arab Emirates', dialCode: '+971', flag: '🇦🇪', placeholder: '50 1234567', examplePattern: '50 1234567', digits: 9 },
                                                     { code: 'SA', name: 'Saudi Arabia', dialCode: '+966', flag: '🇸🇦', placeholder: '50 1234567', examplePattern: '50 1234567', digits: 9 },
                                                     { code: 'CA', name: 'Canada', dialCode: '+1', flag: '🇨🇦', placeholder: '416-555-0123', examplePattern: '416-555-0123', digits: 10 },
                                                     { code: 'IN', name: 'India', dialCode: '+91', flag: '🇮🇳', placeholder: '98765 43210', examplePattern: '98765 43210', digits: 10 },
                                                     { code: 'DE', name: 'Germany', dialCode: '+49', flag: '🇩🇪', placeholder: '151 12345678', examplePattern: '151 12345678', digits: 11 },
                                                     { code: 'FR', name: 'France', dialCode: '+33', flag: '🇫🇷', placeholder: '6 12 34 56 78', examplePattern: '6 12 34 56 78', digits: 9 },
                                                     { code: 'AU', name: 'Australia', dialCode: '+61', flag: '🇦🇺', placeholder: '412 345 678', examplePattern: '412 345 678', digits: 9 },
                                                     { code: 'QA', name: 'Qatar', dialCode: '+974', flag: '🇶🇦', placeholder: '3312 3456', examplePattern: '3312 3456', digits: 8 },
                                                     { code: 'OM', name: 'Oman', dialCode: '+968', flag: '🇴🇲', placeholder: '9123 4567', examplePattern: '9123 4567', digits: 8 },
                                                     { code: 'KW', name: 'Kuwait', dialCode: '+965', flag: '🇰🇼', placeholder: '9123 4567', examplePattern: '9123 4567', digits: 8 },
                                                     { code: 'BH', name: 'Bahrain', dialCode: '+973', flag: '🇧🇭', placeholder: '3912 3456', examplePattern: '3912 3456', digits: 8 },
                                                     { code: 'MY', name: 'Malaysia', dialCode: '+60', flag: '🇲🇾', placeholder: '12-345 6789', examplePattern: '12-345 6789', digits: 9 },
                                                     { code: 'SG', name: 'Singapore', dialCode: '+65', flag: '🇸🇬', placeholder: '8123 4567', examplePattern: '8123 4567', digits: 8 },
                                                     { code: 'TR', name: 'Turkey', dialCode: '+90', flag: '🇹🇷', placeholder: '532 123 45 67', examplePattern: '532 123 45 67', digits: 10 },
                                                     { code: 'CN', name: 'China', dialCode: '+86', flag: '🇨🇳', placeholder: '138 1234 5678', examplePattern: '138 1234 5678', digits: 11 }
                                                 ],
                                                 selectedCountry: null,
                                                 phoneNumber: '',
                                                 init() {
                                                     this.selectedCountry = this.countries[0]; // Default PK (+92)
                                                     let initialVal = @js($company_phone ?? '');
                                                     if (initialVal) {
                                                         let clean = String(initialVal).trim();
                                                         if (clean.startsWith('+')) {
                                                             let sorted = [...this.countries].sort((a, b) => b.dialCode.length - a.dialCode.length);
                                                             let matched = sorted.find(c => clean.startsWith(c.dialCode));
                                                             if (matched) {
                                                                 this.selectedCountry = matched;
                                                                 clean = clean.slice(matched.dialCode.length);
                                                             }
                                                         }
                                                         this.phoneNumber = clean.replace(/\D/g, '');
                                                     }
                                                 },
                                                 get filteredCountries() {
                                                     if (!this.search.trim()) return this.countries;
                                                     let q = this.search.toLowerCase();
                                                     return this.countries.filter(c => 
                                                         c.name.toLowerCase().includes(q) || 
                                                         c.code.toLowerCase().includes(q) || 
                                                         c.dialCode.includes(q)
                                                     );
                                                 },
                                                 selectCountry(c) {
                                                     this.selectedCountry = c;
                                                     this.open = false;
                                                     this.search = '';
                                                     this.updateState();
                                                 },
                                                 handlePhoneInput(event) {
                                                     let val = event.target.value;
                                                     if (val.startsWith('+')) {
                                                         let clean = val.trim();
                                                         let sorted = [...this.countries].sort((a, b) => b.dialCode.length - a.dialCode.length);
                                                         let matched = sorted.find(c => clean.startsWith(c.dialCode));
                                                         if (matched) {
                                                             this.selectedCountry = matched;
                                                             clean = clean.slice(matched.dialCode.length);
                                                         }
                                                         this.phoneNumber = clean.replace(/\D/g, '').slice(0, this.selectedCountry.digits);
                                                     } else {
                                                         this.phoneNumber = val.replace(/\D/g, '').slice(0, this.selectedCountry.digits);
                                                     }
                                                     this.updateState();
                                                 },
                                                 updateState() {
                                                     let rawDigits = this.phoneNumber;
                                                     if (!rawDigits) {
                                                         $wire.set('company_phone', '', false);
                                                         return;
                                                     }
                                                     let fullPhoneNumber = `${this.selectedCountry.dialCode}${rawDigits}`;
                                                     $wire.set('company_phone', fullPhoneNumber, false);
                                                 }
                                             }">
                                             <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Phone</label>
                                             
                                             <div class="relative flex items-center w-full border border-slate-200 rounded-md bg-white shadow-sm transition-all focus-within:ring-2 focus-within:ring-slate-200 focus-within:border-slate-300">
                                                 <!-- Region Dropdown Button -->
                                                 <button type="button"
                                                     @click="open = !open"
                                                     class="flex items-center gap-1.5 px-3 py-2 text-sm border-r border-slate-200 bg-slate-50 rounded-l-md hover:bg-slate-100 transition-colors shrink-0 text-slate-700 outline-none select-none cursor-pointer">
                                                     <span class="font-bold text-slate-800" x-text="selectedCountry ? selectedCountry.code : 'PK'"></span>
                                                     <span class="text-xs text-slate-500 font-medium" x-text="selectedCountry ? selectedCountry.dialCode : '+92'"></span>
                                                     <svg class="w-3.5 h-3.5 text-slate-400 ml-0.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                     </svg>
                                                 </button>

                                                 <!-- Phone Input Field -->
                                                 <input type="text"
                                                     :value="phoneNumber"
                                                     @input="handlePhoneInput"
                                                     :placeholder="selectedCountry ? selectedCountry.placeholder : '300 1234567'"
                                                     class="w-full border-0 bg-transparent px-3 py-2 text-sm text-slate-800 placeholder-slate-400 outline-none focus:outline-none focus:ring-0" />
                                                 
                                                 <!-- Phone Icon -->
                                                 <div class="pr-3 text-slate-400 flex items-center shrink-0">
                                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                     </svg>
                                                 </div>

                                                 <!-- Dropdown Popover -->
                                                 <div x-show="open" @click.stop x-transition style="display: none;"
                                                     class="absolute top-full left-0 mt-1 w-72 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden py-2 focus:outline-none">
                                                     <!-- Search input -->
                                                     <div class="px-2.5 pb-2 border-b border-slate-100" @click.stop>
                                                         <div class="relative">
                                                             <input type="text"
                                                                 x-model="search"
                                                                 @click.stop
                                                                 @focus.stop
                                                                 @keydown.stop
                                                                 placeholder="Search country or code..."
                                                                 class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-700 outline-none focus:border-slate-400 focus:bg-white transition-all" />
                                                             <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                             </svg>
                                                         </div>
                                                     </div>

                                                     <!-- Country List -->
                                                     <div class="max-h-52 overflow-y-auto py-1 custom-scrollbar">
                                                         <template x-for="c in filteredCountries" :key="c.code + c.dialCode">
                                                             <button type="button"
                                                                 @click="selectCountry(c)"
                                                                 class="w-full text-left px-3 py-2 text-xs flex items-center justify-between hover:bg-slate-50 transition-colors"
                                                                 :class="selectedCountry && selectedCountry.code === c.code ? 'bg-slate-50 font-semibold text-indigo-600' : 'text-slate-700'">
                                                                 <div class="flex items-center gap-2 truncate">
                                                                     <span class="text-base leading-none" x-text="c.flag"></span>
                                                                     <span class="truncate" x-text="c.name"></span>
                                                                     <span class="text-[10px] uppercase tracking-wider px-1 py-0.5 rounded bg-slate-100 text-slate-500 font-bold" x-text="c.code"></span>
                                                                 </div>
                                                                 <span class="font-mono text-slate-400 font-medium ml-2 text-xs" x-text="c.dialCode"></span>
                                                             </button>
                                                         </template>
                                                         <div x-show="filteredCountries.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">
                                                             No country found
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>

                                             <!-- Dynamic Helper Text (Example) -->
                                             <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                                                 <span class="italic text-slate-400">Example:</span>
                                                 <span class="font-mono text-indigo-600 font-semibold italic" x-text="selectedCountry ? (selectedCountry.dialCode + ' ' + selectedCountry.examplePattern) : '+92 300 1234567'"></span>
                                             </p>

                                             @error('company_phone')
                                                 <span class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span>
                                             @enderror
                                         </div>

                                        <!-- Owner Role -->
                                        <div class="relative"
                                            x-data="{ open: false, selected: '{{ $owner_role && isset($ownerRoles[$owner_role]) ? $ownerRoles[$owner_role] : 'Choose an option' }}' }">
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Owner Role</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 text-sm shadow-sm flex justify-between items-center cursor-pointer text-slate-700">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute bottom-[100%] z-50 left-0 right-0 mb-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($ownerRoles as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('owner_role', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $owner_role == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('owner_role') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Team Size -->
                                        <div class="relative"
                                            x-data="{ open: false, selected: '{{ $team_size && isset($teamSizes[$team_size]) ? $teamSizes[$team_size] : 'Choose an option' }}' }">
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Team Size</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 text-sm shadow-sm flex justify-between items-center cursor-pointer text-slate-700">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute bottom-[100%] z-50 left-0 right-0 mb-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($teamSizes as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('team_size', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $team_size == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('team_size') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- STEP 2: Brand Assets & Information --}}
                            @if($step == 2)
                                <div class="space-y-4 animate-fade-in w-full">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 w-full items-start">
                                        <!-- LEFT SIDE: TAX & ADDRESS -->
                                        <div class="flex flex-col space-y-4 w-full">
                                            <div class="flex flex-col space-y-1 w-full">
                                                <label class="text-xs font-bold text-slate-700">Tax Number / STRN</label>
                                                <input type="text"
                                                    wire:model.defer="tax_number"
                                                    placeholder="Enter company tax registration number"
                                                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-white">
                                                @error('tax_number') <span
                                                class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="flex flex-col space-y-1 w-full">
                                                <label class="text-xs font-bold text-slate-700">Address</label>
                                                <textarea
                                                    wire:model.defer="business_address" rows="3" placeholder="Enter business address"
                                                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-white resize-none h-[100px]"></textarea>
                                                @error('business_address') <span
                                                class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <!-- RIGHT SIDE: LOGO DROPZONE -->
                                        <div class="flex flex-col space-y-1 w-full">
                                            <label class="text-xs font-bold text-slate-700">Company Logo</label>
                                            <label
                                                class="w-full h-[164px] border-2 border-dashed border-slate-200 hover:border-slate-400 bg-slate-50/50 rounded-xl flex flex-col justify-center items-center p-3 cursor-pointer transition-all group relative overflow-hidden">
                                                @if ($company_logo)
                                                    <img src="{{ $company_logo->temporaryUrl() }}"
                                                        class="w-full h-full object-contain p-2 z-10">
                                                    <div
                                                        class="absolute inset-0 bg-black bg-opacity-40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20 rounded-xl">
                                                        <svg class="w-6 h-6 text-white mb-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                        </svg>
                                                        <span class="text-white font-medium text-xs">Change Logo</span>
                                                    </div>
                                                @else
                                                    <div class="flex flex-col items-center justify-center">
                                                        <div
                                                            class="w-10 h-10 text-slate-400 bg-slate-100 rounded-full flex items-center justify-center mb-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                        <p class="text-xs font-semibold text-slate-600">Click to upload or drag and drop
                                                        </p>
                                                        <p class="text-[10px] text-slate-400 mt-0.5">PNG, JPG, GIF up to 2MB</p>
                                                    </div>
                                                @endif
                                                <input type="file" wire:model="company_logo" class="hidden" />
                                            </label>
                                            @error('company_logo') <span
                                            class="text-red-500 text-xs mt-2 block font-medium">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- STEP 3: Target Objectives --}}
                            @if($step == 3)
                                <div class="space-y-4 animate-fade-in w-full">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @php
                                            $tasks = [
                                                'manage_inventory' => ['Manage your inventory', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                                                'organize_expenses' => ['Organize your expenses', 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                                                'pay_employees' => ['Pay your employees', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                                                'send_invoices' => ['Send and track invoices', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                                'track_bills' => ['Track your bills', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                                'track_tax' => ['Track your tax', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                                            ];
                                        @endphp

                                        @foreach($tasks as $key => $data)
                                            <label
                                                class="relative flex items-center p-3.5 cursor-pointer rounded-xl border-2 transition-all shadow-sm group @if(in_array($key, $intended_tasks)) border-slate-900 bg-slate-50 @else border-slate-200 bg-white hover:border-slate-300 hover:bg-gray-50 @endif">
                                                <div class="flex-shrink-0 mr-3">
                                                    <div
                                                        class="w-8 h-8 rounded-full flex items-center justify-center transition-colors @if(in_array($key, $intended_tasks)) bg-slate-900 text-white @else bg-slate-100 text-slate-400 group-hover:bg-slate-200 group-hover:text-slate-600 @endif">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="{{ $data[1] }}" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1 text-sm leading-5">
                                                    <span class="font-bold text-slate-800 block">{{ $data[0] }}</span>
                                                </div>
                                                <div class="flex h-5 items-center ml-3">
                                                    <input type="checkbox" wire:model="intended_tasks" value="{{ $key }}"
                                                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-200 transition-colors cursor-pointer">
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('intended_tasks') <span
                                        class="text-red-500 text-sm mt-2 block font-medium p-3 bg-red-50 rounded-lg border border-red-100 flex items-center"><svg
                                            class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- STEP 4: Framework Configuration --}}
                            @if($step == 4)
                                @php
                                    $businessTypes = [
                                        'agriculture' => 'Agriculture',
                                        'art_design' => 'Art and Design',
                                        'construction_trades' => 'Construction, Trades and Home Services',
                                        'development_programming' => 'Development & Programming',
                                        'education_training' => 'Education and Training',
                                        'financial_insurance' => 'Financial services & insurance',
                                        'food_services' => 'Food Services',
                                        'health_wellness' => 'Health and Wellness',
                                        'hospitality_tourism' => 'Hospitality, Travel and Tourism',
                                        'hr_staffing' => 'Human Resources and Staffing',
                                        'it' => 'Information Technology',
                                        'manufacturing' => 'Manufacturing',
                                        'non_profit' => 'Non-Profit',
                                        'professional_services' => 'Professional Services (e.g. Legal, Accounting, Marketing, Consulting)',
                                        'real_estate' => 'Real Estate and Property Management',
                                        'retail' => 'Retail (E-Commerce and Offline)',
                                        'software_development' => 'Software Development',
                                        'wholesale_trade' => 'Wholesale Trade',
                                        'other' => 'Other',
                                    ];

                                    $businessScales = [
                                        'Single Outlet' => 'Single Outlet',
                                        'Multi-Branch/Chain' => 'Multi-Branch/Chain',
                                        'Wholesale Only' => 'Wholesale Only',
                                    ];

                                    $countries = [
                                        'United States' => 'United States',
                                        'United Kingdom' => 'United Kingdom',
                                        'Canada' => 'Canada',
                                        'Australia' => 'Australia',
                                        'Pakistan' => 'Pakistan',
                                        'India' => 'India',
                                        'United Arab Emirates' => 'United Arab Emirates',
                                    ];

                                    $systemLanguages = [
                                        'en' => 'English',
                                        'ur' => 'Urdu (اُردو)',
                                        'ar' => 'Arabic (العربية)',
                                        'es' => 'Spanish (Español)',
                                        'fr' => 'French (Français)',
                                        'de' => 'German (Deutsch)',
                                        'zh' => 'Chinese (中文)',
                                        'hi' => 'Hindi (हिन्दी)',
                                        'tr' => 'Turkish (Türkçe)',
                                        'fa' => 'Persian (فارسی)',
                                        'pt' => 'Portuguese (Português)',
                                        'ru' => 'Russian (Русский)',
                                        'ja' => 'Japanese (日本語)',
                                        'id' => 'Indonesian (Bahasa Indonesia)',
                                        'bn' => 'Bengali (বাংলা)',
                                        'pa' => 'Punjabi (پنجابی)',
                                        'it' => 'Italian (Italiano)',
                                        'nl' => 'Dutch (Nederlands)',
                                        'vi' => 'Vietnamese (Tiếng Việt)',
                                        'sw' => 'Swahili (Kiswahili)',
                                    ];

                                    $baseCurrencies = [
                                        'PKR' => 'PKR (₨) - Pakistani Rupee',
                                        'USD' => 'USD ($) - United States Dollar',
                                        'GBP' => 'GBP (£) - British Pound Sterling',
                                        'EUR' => 'EUR (€) - Euro',
                                        'AED' => 'AED (د.إ) - UAE Dirham',
                                        'SAR' => 'SAR (ر.س) - Saudi Riyal',
                                        'CAD' => 'CAD ($) - Canadian Dollar',
                                        'AUD' => 'AUD ($) - Australian Dollar',
                                        'INR' => 'INR (₹) - Indian Rupee',
                                        'CNY' => 'CNY (¥) - Chinese Yuan',
                                        'TRY' => 'TRY (₺) - Turkish Lira',
                                        'KWD' => 'KWD (د.ك) - Kuwaiti Dinar',
                                        'QAR' => 'QAR (ر.ق) - Qatari Riyal',
                                        'OMR' => 'OMR (ر.ع.) - Omani Rial',
                                        'BHD' => 'BHD (.د.ب) - Bahraini Dinar',
                                        'JPY' => 'JPY (¥) - Japanese Yen',
                                        'SGD' => 'SGD ($) - Singapore Dollar',
                                        'NZD' => 'NZD ($) - New Zealand Dollar',
                                        'CHF' => 'CHF (Fr) - Swiss Franc',
                                        'MYR' => 'MYR (RM) - Malaysian Ringgit',
                                    ];

                                    $timezones = [
                                        'UTC' => 'UTC (Standard)',
                                        'EST' => 'EST (Eastern Time)',
                                        'PST' => 'PST (Pacific Time)',
                                        'GMT' => 'GMT (Greenwich Time)',
                                        'PKT' => 'PKT (Pakistan Time)',
                                    ];
                                @endphp
                                <div class="space-y-4 animate-fade-in w-full relative">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                                        <!-- Business Type -->
                                        <div class="flex flex-col space-y-1 w-full relative"
                                            x-data="{ open: false, selected: '{{ $business_type && isset($businessTypes[$business_type]) ? $businessTypes[$business_type] : 'Choose an option' }}' }">
                                            <label class="text-xs font-bold text-slate-700">What does your company do?</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute top-[100%] z-50 left-0 right-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($businessTypes as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('business_type', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $business_type == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('business_type') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Business Scale -->
                                        <div class="flex flex-col space-y-1 w-full relative"
                                            x-data="{ open: false, selected: '{{ $business_scale && isset($businessScales[$business_scale]) ? $businessScales[$business_scale] : 'Choose an option' }}' }">
                                            <label class="text-xs font-bold text-slate-700">Business Scale</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute top-[100%] z-50 left-0 right-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($businessScales as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('business_scale', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $business_scale == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('business_scale') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Country Jurisdiction -->
                                        <div class="flex flex-col space-y-1 w-full relative"
                                            x-data="{ open: false, selected: '{{ $country && isset($countries[$country]) ? $countries[$country] : 'Choose an option' }}' }">
                                            <label class="text-xs font-bold text-slate-700">Country Jurisdiction</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute top-[100%] z-50 left-0 right-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($countries as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('country', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $country == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('country') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- System Language -->
                                        <div class="flex flex-col space-y-1 w-full relative"
                                            x-data="{ open: false, selected: '{{ $system_language && isset($systemLanguages[$system_language]) ? $systemLanguages[$system_language] : 'Choose an option' }}' }">
                                            <label class="text-xs font-bold text-slate-700">System Language</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute top-[100%] z-50 left-0 right-0 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($systemLanguages as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('system_language', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $system_language == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('system_language') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Base Ledger Currency -->
                                        <div class="flex flex-col space-y-1 w-full relative"
                                            x-data="{ open: false, selected: '{{ $base_currency && isset($baseCurrencies[$base_currency]) ? $baseCurrencies[$base_currency] : 'Choose an option' }}' }">
                                            <label class="text-xs font-bold text-slate-700">Base Ledger Currency</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute bottom-[100%] z-50 left-0 right-0 mb-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($baseCurrencies as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('base_currency', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $base_currency == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('base_currency') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Timezone Anchor -->
                                        <div class="flex flex-col space-y-1 w-full relative"
                                            x-data="{ open: false, selected: '{{ $timezone_offset && isset($timezones[$timezone_offset]) ? $timezones[$timezone_offset] : 'Choose an option' }}' }">
                                            <label class="text-xs font-bold text-slate-700">Timezone Anchor</label>
                                            <button type="button"
                                                @click="open = !open" @click.outside="open = false"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200">
                                                <span x-text="selected" class="truncate"></span>
                                                <svg :class="open ? 'rotate-180' : ''"
                                                    class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute bottom-[100%] z-50 left-0 right-0 mb-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto py-1.5 focus:outline-none custom-scrollbar">
                                                @foreach($timezones as $key => $label)
                                                    <div @click="selected = '{{ $label }}'; open = false; $wire.set('timezone_offset', '{{ $key }}')"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-slate-950 font-medium cursor-pointer transition-colors flex items-center {{ $timezone_offset == $key ? 'bg-slate-50 text-slate-950 font-bold' : 'text-slate-700' }}">
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('timezone_offset') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Fiscal Year Start -->
                                        <div class="flex flex-col space-y-1 w-full relative md:col-span-2">
                                            <label class="text-xs font-bold text-slate-700">Fiscal Year Start</label>
                                            <input type="date"
                                                wire:model="fiscal_year_start"
                                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-700 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-white">
                                            @error('fiscal_year_start') <span
                                            class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- ═══ ACTION CONTROLS ═══ --}}
                        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                @if($step > 1)
                                    <button type="button" wire:click="previousStep"
                                        class="flex items-center text-slate-500 hover:text-slate-900 font-semibold transition-colors group text-sm">
                                        <svg class="w-4 h-4 mr-1.5 transform group-hover:-translate-x-1 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                        Back
                                    </button>
                                @else
                                    {{-- Cancel Setup button (soft grey pill) --}}
                                    @if(!$hasExistingActiveCompany)
                                        <button type="button"
                                            @click="showFreshUserModal = true"
                                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-all duration-150">
                                            Cancel Setup
                                        </button>
                                    @else
                                        <button type="button"
                                            @click="showExistingUserModal = true"
                                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-all duration-150">
                                            Cancel Setup
                                        </button>
                                    @endif
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                @if($step == 2)
                                    <button type="button" wire:click="skipStep"
                                        class="text-sm font-semibold text-slate-400 hover:text-slate-600 transition-colors">
                                        Skip this step
                                    </button>
                                @endif

                                @if($step < 4)
                                    <button type="button" wire:click="nextStep"
                                        class="bg-slate-900 hover:bg-black text-white font-semibold py-3 px-6 rounded-lg flex items-center justify-center text-sm shadow-md transition-all duration-150 hover:shadow-lg">
                                        Continue
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @else
                                    <button type="submit"
                                        class="bg-slate-900 hover:bg-black text-white font-semibold py-3 px-6 rounded-lg flex items-center justify-center text-sm shadow-md transition-all duration-150 hover:shadow-lg relative overflow-hidden group">
                                        <span wire:loading.remove wire:target="submit" class="relative z-10 flex items-center">
                                            Finish Setup & Launch
                                            <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </span>
                                        <span wire:loading wire:target="submit" class="relative z-10 flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            Provisioning Workspace...
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </main>

    </div>

<style>
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Custom Scrollbar for Dropdown Panels */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }
</style>
</div>