<div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Companies</h1>
            <button 
                wire:click="createNewCompany" 
                class="inline-flex items-center px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            >
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Company
            </button>
        </div>

        @if (session()->has('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm flex items-start">
                <svg class="h-5 w-5 text-emerald-500 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            
            <!-- Search Bar -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search or filter results.." 
                        class="w-full pl-10 pr-4 py-2.5 bg-white border-none rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-0 focus:outline-none shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-shadow"
                    >
                </div>
            </div>

            <!-- Data Grid -->
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                            <th scope="col" class="px-6 py-4 w-12 text-center">
                                <input type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th scope="col" class="px-4 py-4 w-20">ID</th>
                            <th scope="col" class="px-6 py-4">
                                <div class="flex items-center space-x-1 cursor-pointer hover:text-slate-700">
                                    <span>Name</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4">Contact</th>
                            <th scope="col" class="px-6 py-4 text-left">Region</th>
                            <th scope="col" class="px-6 py-4 w-24"></th> <!-- Actions Placeholder -->
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($companies as $company)
                        <tr class="hover:bg-slate-50 transition-colors duration-150 group relative">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-slate-500 font-medium">#{{ $company->id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <button 
                                        wire:click="switchCompany({{ $company->id }})" 
                                        class="text-left font-bold text-indigo-600 hover:text-indigo-800 transition-colors text-base"
                                    >
                                        {{ $company->company_name }}
                                        @if(Auth::user()->current_company_id == $company->id)
                                            <span class="inline-flex ml-2 items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                Active
                                            </span>
                                        @endif
                                    </button>
                                    <span class="text-slate-400 text-xs mt-0.5">
                                        {{ $company->tax_number ?? $company->registration_number ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-slate-900 font-medium">{{ $company->company_email ?: 'N/A' }}</span>
                                    <span class="text-slate-500 text-xs mt-0.5">{{ $company->company_phone ?: 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="text-left px-6 py-4 align-middle">
                                <div class="flex flex-col items-start">
                                    <span class="text-slate-900 font-medium">{{ $company->country ?: 'N/A' }}</span>
                                    <span class="text-slate-500 text-xs mt-0.5">{{ $company->base_currency ?: 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="text-right px-6 py-4 whitespace-nowrap w-24">
                                <!-- Actions Bar -->
                                <div class="flex items-center justify-end gap-3 opacity-100 relative">
                                    <button class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="View">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                    <button class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="text-base font-medium text-slate-900">No companies found</p>
                                <p class="text-sm mt-1">Try adjusting your search query or create a new company.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Details -->
            @if($companies->hasPages() || $companies->total() > 0)
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="text-sm text-slate-500 font-medium">
                    {{ $companies->perPage() }} per page. {{ $companies->firstItem() ?? 0 }}-{{ $companies->lastItem() ?? 0 }} of {{ $companies->total() }} records.
                </div>
                <div>
                    {{ $companies->links(data: ['scrollTo' => false]) }}
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
