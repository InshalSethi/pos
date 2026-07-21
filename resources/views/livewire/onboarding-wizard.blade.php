<div>

    {{-- DRAFT RESUME PROMPT REMOVED - NOW HANDLED BY DASHBOARD --}}

    {{-- ═══ WIZARD HEADER BAR & EXIT MODAL ═══ --}}
    <div x-data="{
        hasActiveCompany:      {{ $hasExistingActiveCompany ? 'true' : 'false' }},
        showFreshUserModal:    false,
        showExistingUserModal: false,
        activeCompanyId:       {{ $company_id ?? 'null' }},
        currentStep:           {{ $step ?? 1 }}
    }" class="absolute top-0 w-full z-50">

        {{-- ═══ WIZARD HEADER BAR ═══ --}}
        <div class="flex items-center justify-between p-4 bg-transparent pointer-events-none">

            {{-- Brand Logo Slot --}}
            <div class="flex items-center gap-2 pointer-events-auto">
                {{-- <x-application-logo /> --}}
            </div>

            {{-- Home / Cancel Button — forks to correct action based on user lifecycle path --}}
            @if(!$hasExistingActiveCompany)
                <button
                    type="button"
                    @click="showFreshUserModal = true"
                    class="pointer-events-auto flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl
                           text-rose-600 dark:text-rose-400
                           bg-rose-50/80 dark:bg-rose-950/30 backdrop-blur-md
                           hover:bg-rose-100/80 dark:hover:bg-rose-950/55
                           border border-rose-200/50 dark:border-rose-900/50 shadow-xs
                           active:scale-95 transition-all duration-150
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500"
                    aria-label="Cancel Setup"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Cancel Setup</span>
                </button>
            @else
                <button
                    type="button"
                    @click="showExistingUserModal = true"
                    class="pointer-events-auto flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl
                           text-slate-700 dark:text-zinc-300
                           bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md
                           hover:bg-white dark:hover:bg-zinc-800
                           border border-slate-200 dark:border-zinc-800 shadow-sm
                           active:scale-95 transition-all duration-150
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                    aria-label="Return to Home"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.75" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12
                                 M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875
                                 c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21
                                 h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                    </svg>
                    <span>Home</span>
                </button>
            @endif
        </div>


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
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm pointer-events-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="fresh-modal-title"
            @keydown.escape.window="showFreshUserModal = false"
            style="display: none;"
        >
            <div
                @click.outside="showFreshUserModal = false"
                class="w-full max-w-md mx-4 p-6 bg-white dark:bg-zinc-900
                       border border-slate-200 dark:border-zinc-800
                       rounded-2xl shadow-2xl text-center"
            >
                {{-- Warning Icon --}}
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center
                            rounded-full bg-amber-50 dark:bg-amber-500/10">
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
                    class="text-lg font-bold text-slate-900 dark:text-white">
                    Exit Account Onboarding?
                </h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400 leading-relaxed">
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
                               text-slate-700 dark:text-zinc-200
                               bg-slate-100 hover:bg-slate-200
                               dark:bg-zinc-800 dark:hover:bg-zinc-700/80
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm pointer-events-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tenant-modal-title"
            @keydown.escape.window="showExistingUserModal = false"
            style="display: none;"
        >
            <div
                @click.outside="showExistingUserModal = false"
                class="w-full max-w-md mx-4 p-6 bg-white dark:bg-zinc-900
                       border border-slate-200 dark:border-zinc-800
                       rounded-2xl shadow-2xl"
            >
                <h3 id="tenant-modal-title"
                    class="text-lg font-bold text-slate-900 dark:text-white">
                    Save Sub-Company Progress?
                </h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400 leading-relaxed">
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
                            class="w-full px-4 py-2.5 text-sm font-semibold text-white
                                   bg-rose-500 hover:bg-rose-600 active:bg-rose-700
                                   rounded-xl shadow-sm transition-colors duration-150
                                   focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                            🗑 Discard Current Setup
                        </button>
                    </form>

                    {{-- B2: Save as resumable draft --}}
                    <form action="{{ route('onboarding.save-draft') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company_id }}">
                        <input type="hidden" name="current_step" value="{{ $step }}">
                        <button
                            type="submit"
                            class="w-full px-4 py-2.5 text-sm font-semibold
                                   text-slate-700 dark:text-zinc-200
                                   bg-slate-100 hover:bg-slate-200
                                   dark:bg-zinc-800 dark:hover:bg-zinc-700/80
                                   rounded-xl transition-colors duration-150
                                   focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                            💾 Save as Draft &amp; Exit
                        </button>
                    </form>

                    {{-- B3: Dismiss and keep editing --}}
                    <button
                        @click="showExistingUserModal = false"
                        type="button"
                        class="w-full px-4 py-2.5 text-sm font-medium
                               text-slate-400 hover:text-slate-700
                               dark:text-zinc-500 dark:hover:text-zinc-300
                               transition-colors duration-150
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300">
                        ← Keep Editing
                    </button>

                </div>
            </div>
        </div>

    </div>

<div
    class="w-screen h-screen min-h-screen max-h-screen grid grid-cols-1 lg:grid-cols-2 overflow-hidden bg-slate-50 font-sans">
    <div
        class="w-full h-full h-screen min-h-[100vh] max-h-screen bg-[#232526] text-white flex flex-col justify-between p-12 overflow-hidden select-none relative">
        <!-- 3D Card Asset Background -->
        <div wire:ignore
            class="absolute inset-0 w-full h-full flex items-center justify-center overflow-hidden pointer-events-none z-0">
            <script type="module" src="https://unpkg.com/@splinetool/viewer@1.0.17/build/spline-viewer.js"></script>
            <spline-viewer url="https://prod.spline.design/0P82uUM5-3-4R85v/scene.splinecode" loading-type="eager"
                hint="false" class="absolute -bottom-12 w-full h-[108%] scale-95 lg:scale-100 origin-center"
                background-color="#232526"></spline-viewer>
        </div>



        <div class="relative z-10">
            <!-- Branding -->
            <div class="flex items-center space-x-3 mb-12">
                <div class="w-10 h-10 bg-white rounded-xl shadow-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-700" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 22h20L12 2zm0 4.5l6.5 13h-13L12 6.5z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-black tracking-tight">Aura</h1>
            </div>

            <!-- Dynamic Center Content Block -->
            <div class="space-y-4 animate-fade-in" key="step-info-{{ $step }}">
                @if($step == 1)
                    <div
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-500 border border-indigo-400 mb-4 shadow-sm">
                        <svg class="w-6 h-6 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold leading-tight">Let's setup your business identity</h2>
                    <p class="text-indigo-200 text-base leading-relaxed">Provide your primary enterprise registration
                        metrics, brand assets, and operational role parameters to structure your unique isolated root tenant
                        profile safely.</p>
                @elseif($step == 2)
                    <div
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-500 border border-indigo-400 mb-4 shadow-sm">
                        <svg class="w-6 h-6 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold leading-tight">Upload Brand Assets</h2>
                    <p class="text-indigo-200 text-base leading-relaxed">Provide your official company logo to personalize
                        your workspace. This branding will appear on your invoices, receipts, and internal dashboards.</p>
                @elseif($step == 3)
                    <div
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-500 border border-indigo-400 mb-4 shadow-sm">
                        <svg class="w-6 h-6 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold leading-tight">Tell us what you want to achieve</h2>
                    <p class="text-indigo-200 text-base leading-relaxed">Select your daily operational target items.
                        Checking these feature attributes dynamically shapes your core administrative dashboard menus and
                        background automated configurations.</p>
                @elseif($step == 4)
                    <div
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-500 border border-indigo-400 mb-4 shadow-sm">
                        <svg class="w-6 h-6 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold leading-tight">Configure regional localization settings</h2>
                    <p class="text-indigo-200 text-base leading-relaxed">Establish your legal country jurisdiction, local
                        timezone anchors, and default baseline ledger currency codes to initialize accurate double-entry
                        ledger ledgers.</p>
                @endif
            </div>
        </div>

        <!-- Bottom Footnote -->
        <div
            class="mt-auto relative z-10 flex items-center space-x-2 text-indigo-300 text-xs font-semibold uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>Secured by Aura Multi-Tenant Cloud Architecture Layer</span>
        </div>
    </div>

    <!-- RIGHT-SIDE: INTERACTIVE OPERATIONS FORM PANEL -->
    <div
        class="w-full h-full h-screen max-h-screen bg-slate-50 flex flex-col justify-center items-center p-6 lg:p-12 overflow-hidden relative">
        <div class="w-full max-w-xl flex flex-col space-y-5 h-full max-h-full">



            <!-- PROGRESS BAR LAYOUT -->
            <div class="w-full flex flex-col space-y-2 mb-2 pt-4 shrink-0">
                <div class="flex justify-between items-center text-sm font-semibold text-slate-500">
                    <span>SETUP PROGRESS</span>
                    <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs font-bold">Step {{ $step }}
                        of 4</span>
                </div>
                <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                    <div class="bg-indigo-600 h-full rounded-full transition-all duration-300"
                        style="width: {{ ($step / 4) * 100 }}%;"></div>
                </div>
            </div>

            <!-- FORM WRAPPER -->
            <form wire:submit.prevent="submit" class="flex-grow flex flex-col relative overflow-visible">

                <div class="flex-grow">
                    <!-- STEP 1 -->
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
                        <div class="space-y-5 animate-fade-in w-full pb-2 relative">
                            <div class="border-b border-gray-100 pb-3 mb-4">
                                <h3 class="text-xl font-bold text-gray-900">Enterprise Metrics</h3>
                                <p class="text-sm text-gray-500 mt-1">Please enter your accurate legal entity details.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Company Name</label>
                                    <input type="text" style="outline: none !important; box-shadow: none !important;"
                                        wire:model="company_name"
                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:outline-0 focus:ring-0 focus:ring-transparent focus:border-slate-200 transition-all text-sm shadow-sm"
                                        placeholder="e.g. Acme Corp">
                                    @error('company_name') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Registration Number</label>
                                    <input type="text" style="outline: none !important; box-shadow: none !important;"
                                        wire:model="registration_number"
                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:outline-0 focus:ring-0 focus:ring-transparent focus:border-slate-200 transition-all text-sm shadow-sm"
                                        placeholder="e.g. 123456789">
                                    @error('registration_number') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Company Email</label>
                                    <input type="email" style="outline: none !important; box-shadow: none !important;"
                                        wire:model="company_email"
                                        readonly
                                        class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-xl text-slate-500 cursor-not-allowed focus:outline-none focus:outline-0 focus:ring-0 focus:ring-transparent focus:border-slate-200 transition-all text-sm shadow-sm"
                                        placeholder="contact@company.com">
                                    <span class="text-[11px] text-slate-400 mt-1 block">Tied to your registered account email.</span>
                                    @error('company_email') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Company Phone</label>
                                    <input type="text" style="outline: none !important; box-shadow: none !important;"
                                        wire:model="company_phone"
                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:outline-0 focus:ring-0 focus:ring-transparent focus:border-slate-200 transition-all text-sm shadow-sm"
                                        placeholder="+1 (555) 000-0000">
                                    @error('company_phone') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Owner Role -->
                                <div class="relative"
                                    x-data="{ open: false, selected: '{{ $owner_role && isset($ownerRoles[$owner_role]) ? $ownerRoles[$owner_role] : 'Choose an option' }}' }">
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Owner Role</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all text-sm shadow-sm flex justify-between items-center cursor-pointer text-slate-700">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $owner_role == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('owner_role') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Team Size -->
                                <div class="relative"
                                    x-data="{ open: false, selected: '{{ $team_size && isset($teamSizes[$team_size]) ? $teamSizes[$team_size] : 'Choose an option' }}' }">
                                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Team Size</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all text-sm shadow-sm flex justify-between items-center cursor-pointer text-slate-700">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $team_size == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('team_size') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- STEP 2: BRAND ASSETS & INFORMATION -->
                    @if($step == 2)
                        <div class="space-y-5 animate-fade-in w-full pb-2">
                            <div class="border-b border-gray-100 pb-3 mb-4">
                                <h3 class="text-xl font-bold text-gray-900">Brand Assets</h3>
                                <p class="text-sm text-gray-500 mt-1">Upload your official company logo and location
                                    details.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 w-full items-start">
                                <!-- LEFT SIDE: TAX & ADDRESS -->
                                <div class="flex flex-col space-y-4 w-full">
                                    <div class="flex flex-col space-y-1 w-full">
                                        <label class="text-sm font-semibold text-slate-700">Tax Number / STRN</label>
                                        <input type="text" style="outline: none !important; box-shadow: none !important;"
                                            wire:model.defer="tax_number"
                                            placeholder="Enter company tax registration number"
                                            class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm focus:outline-none focus:outline-0 focus:ring-0 focus:ring-transparent focus:border-slate-200 placeholder-slate-400">
                                        @error('tax_number') <span
                                        class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex flex-col space-y-1 w-full">
                                        <label class="text-sm font-semibold text-slate-700">Address</label>
                                        <textarea style="outline: none !important; box-shadow: none !important;"
                                            wire:model.defer="business_address" rows="3" placeholder="Address"
                                            class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm focus:outline-none focus:outline-0 focus:ring-0 focus:ring-transparent focus:border-slate-200 resize-none h-[100px]"></textarea>
                                        @error('business_address') <span
                                        class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- RIGHT SIDE: LOGO DROPZONE -->
                                <div class="flex flex-col space-y-1 w-full">
                                    <label class="text-sm font-semibold text-slate-700">Company Logo</label>
                                    <label
                                        class="w-full h-28 max-h-28 border-2 border-dashed border-indigo-200 hover:border-indigo-400 bg-indigo-50/20 rounded-xl flex flex-col justify-center items-center p-3 cursor-pointer transition-colors group relative overflow-hidden">
                                        @if ($company_logo)
                                            <img src="{{ $company_logo->temporaryUrl() }}"
                                                class="w-full h-full object-contain p-2 z-10">
                                            <div
                                                class="absolute inset-0 bg-black bg-opacity-40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20">
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
                                                    class="w-8 h-8 text-indigo-500 bg-indigo-100/50 rounded-full flex items-center justify-center mb-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <p class="text-xs font-semibold text-slate-700">Click to upload or drag and drop
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

                    <!-- STEP 3 -->
                    @if($step == 3)
                        <div class="space-y-5 animate-fade-in w-full pb-2">
                            <div class="border-b border-gray-100 pb-3 mb-4">
                                <h3 class="text-xl font-bold text-gray-900">Target Objectives</h3>
                                <p class="text-sm text-gray-500 mt-1">Select all core features you plan to utilize
                                    immediately.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                        class="relative flex items-center p-4 cursor-pointer rounded-2xl border-2 transition-all shadow-sm group @if(in_array($key, $intended_tasks)) border-indigo-600 bg-indigo-50/50 @else border-gray-200 bg-white hover:border-indigo-300 hover:bg-gray-50 @endif">
                                        <div class="flex-shrink-0 mr-3">
                                            <div
                                                class="w-8 h-8 rounded-full flex items-center justify-center transition-colors @if(in_array($key, $intended_tasks)) bg-indigo-600 text-white @else bg-gray-100 text-gray-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 @endif">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="{{ $data[1] }}" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 text-sm leading-5">
                                            <span class="font-bold text-gray-900 block">{{ $data[0] }}</span>
                                        </div>
                                        <div class="flex h-5 items-center ml-3">
                                            <input type="checkbox" wire:model="intended_tasks" value="{{ $key }}"
                                                class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 transition-colors cursor-pointer">
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

                    <!-- STEP 4 -->
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
                        <div class="space-y-5 animate-fade-in w-full pb-2 relative">
                            <div class="border-b border-gray-100 pb-3 mb-4">
                                <h3 class="text-xl font-bold text-gray-900">Framework Configuration</h3>
                                <p class="text-sm text-gray-500 mt-1">Establish the baseline regional settings for your
                                    ledger.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                                <!-- Business Type -->
                                <div class="flex flex-col space-y-1 w-full relative"
                                    x-data="{ open: false, selected: '{{ $business_type && isset($businessTypes[$business_type]) ? $businessTypes[$business_type] : 'Choose an option' }}' }">
                                    <label class="text-sm font-semibold text-slate-700">What does your company do?</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 hover:border-indigo-400 focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $business_type == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('business_type') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Business Scale -->
                                <div class="flex flex-col space-y-1 w-full relative"
                                    x-data="{ open: false, selected: '{{ $business_scale && isset($businessScales[$business_scale]) ? $businessScales[$business_scale] : 'Choose an option' }}' }">
                                    <label class="text-sm font-semibold text-slate-700">Business Scale</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 hover:border-indigo-400 focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $business_scale == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('business_scale') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Country Jurisdiction -->
                                <div class="flex flex-col space-y-1 w-full relative"
                                    x-data="{ open: false, selected: '{{ $country && isset($countries[$country]) ? $countries[$country] : 'Choose an option' }}' }">
                                    <label class="text-sm font-semibold text-slate-700">Country Jurisdiction</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 hover:border-indigo-400 focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $country == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('country') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- System Language -->
                                <div class="flex flex-col space-y-1 w-full relative"
                                    x-data="{ open: false, selected: '{{ $system_language && isset($systemLanguages[$system_language]) ? $systemLanguages[$system_language] : 'Choose an option' }}' }">
                                    <label class="text-sm font-semibold text-slate-700">System Language</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 hover:border-indigo-400 focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $system_language == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('system_language') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Base Ledger Currency -->
                                <div class="flex flex-col space-y-1 w-full relative"
                                    x-data="{ open: false, selected: '{{ $base_currency && isset($baseCurrencies[$base_currency]) ? $baseCurrencies[$base_currency] : 'Choose an option' }}' }">
                                    <label class="text-sm font-semibold text-slate-700">Base Ledger Currency</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 hover:border-indigo-400 focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $base_currency == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('base_currency') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Timezone Anchor -->
                                <div class="flex flex-col space-y-1 w-full relative"
                                    x-data="{ open: false, selected: '{{ $timezone_offset && isset($timezones[$timezone_offset]) ? $timezones[$timezone_offset] : 'Choose an option' }}' }">
                                    <label class="text-sm font-semibold text-slate-700">Timezone Anchor</label>
                                    <button type="button" style="outline: none !important; box-shadow: none !important;"
                                        @click="open = !open" @click.outside="open = false"
                                        class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm flex justify-between items-center cursor-pointer text-slate-700 hover:border-indigo-400 focus:outline-none focus:outline-0 focus:ring-0 active:outline-none active:outline-0 transition-all">
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
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center {{ $timezone_offset == $key ? 'bg-indigo-50/50 text-indigo-700' : 'text-slate-700' }}">
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('timezone_offset') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Fiscal Year Start -->
                                <div class="flex flex-col space-y-1 w-full relative">
                                    <label class="text-sm font-semibold text-slate-700">Fiscal Year Start</label>
                                    <input type="date" style="outline: none !important; box-shadow: none !important;"
                                        wire:model="fiscal_year_start"
                                        class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-sm focus:outline-none focus:outline-0 focus:ring-0 focus:ring-transparent focus:border-slate-200 text-gray-700 transition-all">
                                    @error('fiscal_year_start') <span
                                    class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- ACTION CONTROLS ENGINE -->
                <div
                    class="pt-4 pb-2 bg-slate-50 sticky bottom-0 border-t border-gray-100 flex items-center justify-between mt-auto">
                    <div>
                        @if($step > 1)
                            <button type="button" wire:click="previousStep"
                                class="flex items-center text-gray-500 hover:text-indigo-600 font-bold transition-colors group">
                                <svg class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                Back
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center space-x-4">
                        @if($step == 2)
                            <button type="button" wire:click="skipStep"
                                class="text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                                Skip this step
                            </button>
                        @endif

                        @if($step < 4)
                            <button type="button" wire:click="nextStep"
                                class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all hover:shadow-md">
                                Continue to Next Step
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @else
                            <button type="submit"
                                class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all hover:shadow-md relative overflow-hidden group">
                                <span wire:loading.remove wire:target="submit" class="relative z-10 flex items-center">
                                    Finish Setup & Launch Dashboard
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

    /* Custom Scrollbar for Right Form Panel */
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