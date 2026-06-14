<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->cookie('theme') === 'dark' ? 'dark' : '' }}">
<head>
    <script>
        (function() {
            // Synchronously check localStorage or cookies before the DOM paints
            const savedTheme = localStorage.getItem('theme') || '{{ request()->cookie("theme") }}' || 'light';
            if (savedTheme === 'dark' || (savedTheme === 'match system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <style>
        html.dark, html.dark body {
            background-color: #111827 !important;
            color: #f9fafb !important;
        }
    </style>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS System') }}</title>

    <script>
        window.systemTimezones = @json(\DateTimeZone::listIdentifiers());
        window.baseCurrency = @json(auth()->check() && auth()->user()->company ? auth()->user()->company->base_currency : 'USD');
    </script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.0.91/build/spline-viewer.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased text-[13px] font-medium text-gray-600 tracking-wide">
    <div id="app"></div>

    <!-- THE VARIANT PRICING LAYOUT MODAL -->
    <div x-data="{ 
            isOpen: false, 
            productName: '', 
            priceRows: [] 
         }"
         @open-variations-modal.window="
            isOpen = true; 
            productName = $event.detail.name; 
            priceRows = $event.detail.variants;
         "
         x-show="isOpen"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <div @click.outside="isOpen = false" class="w-full max-w-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl p-5 shadow-2xl flex flex-col max-h-[75vh]">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3 mb-4">
                <div>
                    <span class="text-[10px] font-black uppercase text-indigo-600 tracking-wider">Pricing Breakdown</span>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white" x-text="'Prices for: ' + productName"></h3>
                </div>
                <button type="button" @click="isOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 font-bold text-lg focus:outline-none">&times;</button>
            </div>

            <div class="w-full overflow-y-auto border border-slate-200/60 dark:border-zinc-800 rounded-xl shadow-inner overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-max table-auto align-middle divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
                    <thead class="bg-slate-50 dark:bg-zinc-800/40 text-[10px] font-bold uppercase tracking-wider text-slate-500 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left bg-slate-50 dark:bg-zinc-800/40">Variant Profile Combo</th>
                            <th class="px-4 py-3 text-left bg-slate-50 dark:bg-zinc-800/40">Purchase Cost ($)</th>
                            <th class="px-4 py-3 text-left bg-slate-50 dark:bg-zinc-800/40">Retail Price ($) *</th>
                            <th class="px-4 py-3 text-left bg-slate-50 dark:bg-zinc-800/40">Wholesale Price ($) *</th>
                            <th class="px-4 py-3 text-left bg-slate-50 dark:bg-zinc-800/40">Tax Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-[11px]">
                        <template x-for="(row, idx) in priceRows" :key="idx">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-zinc-200" x-text="row.variation_name_string || row.combination_key"></td>
                                
                                <td class="px-4 py-3 font-medium text-slate-600 dark:text-zinc-400">
                                    <span x-text="'$ ' + parseFloat(row.purchase_cost || row.cost_price || 0).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-3 font-bold text-emerald-600">
                                    <span x-text="'$ ' + parseFloat(row.retail_price || 0).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-3 font-bold text-indigo-600">
                                    <span x-text="'$ ' + parseFloat(row.wholesale_price || 0).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 font-medium" x-text="(row.tax_rate || '0.00') + '%'"></td>

                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" @click="isOpen = false" class="px-4 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-200 rounded-xl transition-all focus:outline-none">
                    Close View
                </button>
            </div>
        </div>
    </div>
</body>
</html>
