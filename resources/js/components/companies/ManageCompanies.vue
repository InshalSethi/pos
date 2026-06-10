<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between pl-6 pt-2">
      <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Companies</h1>
      <div class="flex items-center gap-3">
        <!-- Drafts Trigger Button -->
        <button 
          v-if="drafts.length > 0"
          type="button" 
          @click="showDraftsModal = true"
          class="relative flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100/80 border border-amber-200/60 rounded-lg transition-all active:scale-95 focus:outline-none focus:ring-2 focus:ring-amber-400"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
            <span>Draft Setup</span>
            <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm">
                {{ drafts.length }}
            </span>
        </button>

        <a 
          href="/company-setup?start_fresh_flow=true"
          class="inline-flex items-center px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
          <svg class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>New Company</span>
        </a>
      </div>
    </div>

    <!-- Success Message Toast -->
    <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="trangray-y-2 opacity-0 sm:trangray-y-0 sm:trangray-x-2" enter-to-class="trangray-y-0 opacity-100 sm:trangray-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="successMessage" class="fixed bottom-4 right-4 z-50 p-4 rounded-lg bg-gray-900 border border-gray-700 text-white shadow-2xl flex items-center gap-3 w-80">
        <svg class="h-6 w-6 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-semibold">{{ successMessage }}</span>
      </div>
    </transition>

    <!-- Card Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Search Bar -->
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <div class="relative w-full">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input 
            type="text" 
            v-model="search"
            @input="debounceSearch"
            placeholder="Search or filter results.." 
            class="w-full pl-10 pr-4 py-2.5 bg-white border-none rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-0 focus:outline-none shadow-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-shadow"
          >
        </div>
      </div>

      <!-- Data Grid -->
      <div class="overflow-x-auto relative min-h-[200px]">
        <!-- Loading Overlay -->
        <div v-if="loading" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <table class="w-full whitespace-nowrap text-left text-sm">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase tracking-wider text-xs font-semibold">
              <th scope="col" class="px-6 py-4 w-12 text-center">
                <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
              </th>
              <th scope="col" class="px-4 py-4 w-20">ID</th>
              <th scope="col" class="px-6 py-4">
                <div class="flex items-center space-x-1 cursor-pointer hover:text-gray-700">
                  <span>Name</span>
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
              </th>
              <th scope="col" class="px-6 py-4">Contact</th>
              <th scope="col" class="px-6 py-4 text-left">Address</th>
              <th scope="col" class="px-6 py-4 text-left">Region</th>
              <th scope="col" class="px-6 py-4 w-32 text-center">Actions</th> <!-- Actions Placeholder -->
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-if="companies.length === 0 && !loading" class="hover:bg-gray-50 transition-colors duration-150">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="text-base font-medium text-gray-900">No companies found</p>
                <p class="text-sm mt-1">Try adjusting your search query or create a new company.</p>
              </td>
            </tr>
            <tr v-for="company in companies" :key="company.id" class="hover:bg-gray-50 transition-colors duration-150 group relative">
              <td class="px-6 py-4 text-center">
                <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
              </td>
              <td class="px-4 py-4 text-gray-500 font-medium">#{{ company.id }}</td>
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <button 
                    @click="switchCompany(company.id)" 
                    :disabled="switchingId === company.id"
                    class="text-left font-bold text-indigo-600 hover:text-indigo-800 transition-colors text-base disabled:opacity-70"
                  >
                    {{ company.company_name }}
                    <span v-if="switchingId === company.id" class="inline-flex ml-2 items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 animate-pulse">
                      Switching...
                    </span>
                    <span v-else-if="currentCompanyId == company.id" class="inline-flex ml-2 items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                      Active
                    </span>
                  </button>
                  <span class="text-gray-400 text-xs mt-0.5">
                    {{ company.tax_number || company.registration_number || 'N/A' }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="text-gray-900 font-medium">{{ company.company_email || 'N/A' }}</span>
                  <span class="text-gray-500 text-xs mt-0.5">{{ company.company_phone || 'N/A' }}</span>
                </div>
              </td>
              <td class="text-left px-6 py-4 align-middle">
                <span class="text-gray-500 text-xs truncate max-w-[180px] inline-block" :title="company.business_address">
                  {{ company.business_address || 'N/A' }}
                </span>
              </td>
              <td class="text-left px-6 py-4 align-middle">
                <div class="flex flex-col items-start">
                  <span class="text-gray-900 font-medium">{{ company.country || 'N/A' }}</span>
                  <span class="text-gray-500 text-xs mt-0.5">{{ company.base_currency || 'N/A' }}</span>
                </div>
              </td>
              <td class="text-center px-6 py-4 whitespace-nowrap w-32 align-middle">
                <!-- Actions Bar -->
                <div class="flex items-center justify-center gap-3 opacity-100 relative">
                  <button @click="switchCompany(company.id)" class="p-1.5 text-blue-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Switch Workspace">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                  </button>
                  <button @click="viewCompany(company)" class="p-1.5 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="View">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </button>
                  <button @click="goToEditCompany(company.id)" class="p-1.5 text-emerald-500 hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors" title="Edit">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  </button>
                  <button class="p-1.5 text-red-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer Pagination Details -->
      <div v-if="total > 0" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <div class="text-sm text-gray-500 font-medium">
          {{ perPage }} per page. {{ from }}-{{ to }} of {{ total }} records.
        </div>
        <div class="flex space-x-1">
          <!-- Simplified pagination buttons for Vue -->
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="px-3 py-1 border border-gray-200 bg-white rounded text-sm disabled:opacity-50 hover:bg-gray-50"
          >
            Prev
          </button>
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage === lastPage"
            class="px-3 py-1 border border-gray-200 bg-white rounded text-sm disabled:opacity-50 hover:bg-gray-50"
          >
            Next
          </button>
        </div>
      </div>

    </div>
    <!-- Drafts Modal -->
    <div v-if="showDraftsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm" @click.self="showDraftsModal = false">
      <div class="w-full max-w-xl mx-4 p-6 bg-white rounded-2xl shadow-2xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900">Saved Draft Workspaces</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ drafts.length }} incomplete pending
                </p>
            </div>
            <button @click="showDraftsModal = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Draft Rows -->
        <div class="mt-4 max-h-[360px] overflow-y-auto space-y-2.5 pr-1 custom-scrollbar">
            <div v-for="draft in drafts" :key="draft.id" class="flex items-center justify-between gap-3 p-3.5 bg-gray-50 border border-gray-100 hover:border-gray-200 rounded-xl transition-all">
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-gray-800 truncate">
                        {{ draft.company_name || 'Untitled Draft Workspace' }}
                    </h4>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        Paused at <span class="text-amber-500 font-semibold">Step {{ draft.draft_step || 1 }} of 4</span>
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a :href="`/company-setup?continue_draft_id=${draft.id}`" class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors">
                        Continue
                    </a>
                    <form :action="`/onboarding/draft/purge/${draft.id}`" method="POST" @submit="confirmPurge">
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <div v-if="drafts.length === 0" class="text-sm text-center text-gray-400 py-6">
                No drafts found.
            </div>
        </div>
      </div>
    </div>

    <!-- View Company Premium Modal -->
    <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 trangray-y-4 sm:trangray-y-0 sm:scale-95" enter-to-class="opacity-100 trangray-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 trangray-y-0 sm:scale-100" leave-to-class="opacity-0 trangray-y-4 sm:trangray-y-0 sm:scale-95">
      <div v-if="selectedCompany" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="selectedCompany = null"></div>

        <!-- Modal Panel -->
        <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden max-w-2xl w-full transform transition-all border border-gray-100 flex flex-col max-h-[90vh]">
          <!-- Premium Header -->
          <div class="px-8 py-6 bg-gradient-to-br from-gray-50 to-white border-b border-gray-100 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-5">
              <div class="flex-shrink-0 h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-indigo-500/30 overflow-hidden relative">
                <img v-if="selectedCompany.company_logo" :src="`/storage/${selectedCompany.company_logo}`" class="w-full h-full object-cover absolute inset-0 z-10" />
                <span :class="{'z-0': selectedCompany.company_logo}">{{ selectedCompany.company_name ? selectedCompany.company_name.charAt(0).toUpperCase() : '?' }}</span>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ selectedCompany.company_name }}</h3>
                <div class="flex items-center gap-2 mt-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 tracking-wider uppercase">
                    ID: #{{ selectedCompany.id }}
                  </span>
                  <span v-if="currentCompanyId == selectedCompany.id" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-600 tracking-wider uppercase">
                    Active Workspace
                  </span>
                </div>
              </div>
            </div>
            <button @click="selectedCompany = null" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all active:scale-95">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="px-8 py-8 overflow-y-auto custom-scrollbar flex-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
              
              <!-- Contact Details Group -->
              <div class="space-y-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">Contact & Identification</h4>
                
                <div class="space-y-1">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Email Address</p>
                  <p class="text-sm font-semibold text-gray-900">{{ selectedCompany.company_email || 'Not Provided' }}</p>
                </div>

                <div class="space-y-1">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Phone Number</p>
                  <p class="text-sm font-semibold text-gray-900">{{ selectedCompany.company_phone || 'Not Provided' }}</p>
                </div>

                <div class="space-y-1">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tax Number (TRN/VAT)</p>
                  <p class="text-sm font-semibold text-gray-900">{{ selectedCompany.tax_number || 'Not Provided' }}</p>
                </div>

                <div class="space-y-1">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Registration Number</p>
                  <p class="text-sm font-semibold text-gray-900">{{ selectedCompany.registration_number || 'Not Provided' }}</p>
                </div>
              </div>

              <!-- Localization Group -->
              <div class="space-y-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">Localization Settings</h4>
                
                <div class="space-y-1">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Country/Region</p>
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm font-semibold text-gray-900">{{ selectedCompany.country || 'Not Set' }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Base Currency</p>
                    <p class="text-sm font-bold text-indigo-600 bg-indigo-50 inline-block px-2 py-0.5 rounded">{{ selectedCompany.base_currency || 'Not Set' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Timezone</p>
                    <p class="text-sm font-semibold text-gray-900">{{ selectedCompany.timezone_offset || 'UTC' }}</p>
                  </div>
                </div>

                <div class="space-y-1">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">System Language</p>
                  <p class="text-sm font-semibold text-gray-900">{{ selectedCompany.system_language === 'en' ? 'English' : (selectedCompany.system_language || 'English') }}</p>
                </div>
              </div>
            </div>

            <!-- Full Width Address Group -->
            <div class="mt-8 space-y-1 bg-gray-50 p-4 rounded-xl border border-gray-100">
              <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Business Address</p>
              <p class="text-sm font-medium text-gray-800 leading-relaxed">{{ selectedCompany.business_address || 'No physical address provided.' }}</p>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 sticky bottom-0 z-10">
            <button @click="selectedCompany = null" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-all shadow-sm">
              Close
            </button>
            <button @click="goToEditCompany(selectedCompany.id)" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/20 rounded-xl transition-all flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
              Edit Details
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const companies = ref([]);
const drafts = ref([]);
const showDraftsModal = ref(false);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const authStore = useAuthStore();
const router = useRouter();

const currentCompanyId = ref(null);
const search = ref('');
const loading = ref(false);
const switchingId = ref(null);
const successMessage = ref('');
const selectedCompany = ref(null);

const viewCompany = (company) => {
  selectedCompany.value = company;
};

const goToEditCompany = (id) => {
  if (selectedCompany.value) {
    selectedCompany.value = null; // Close modal gracefully before navigating
  }
  router.push(`/companies/edit?id=${id}`);
};

const confirmPurge = (e) => {
  if (!confirm('Permanently remove this draft? This cannot be undone.')) {
    e.preventDefault();
  }
};

// Pagination state
const currentPage = ref(1);
const perPage = ref(25);
const total = ref(0);
const from = ref(0);
const to = ref(0);
const lastPage = ref(1);

let searchTimeout = null;

const fetchCompanies = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/companies', {
      params: {
        page: page,
        search: search.value
      }
    });
    
    const data = response.data.companies;
    companies.value = data.data;
    drafts.value = response.data.drafts || [];
    currentCompanyId.value = response.data.current_company_id;
    
    // Pagination data
    currentPage.value = data.current_page;
    perPage.value = data.per_page;
    total.value = data.total;
    from.value = data.from || 0;
    to.value = data.to || 0;
    lastPage.value = data.last_page;
  } catch (error) {
    console.error('Error fetching companies:', error);
  } finally {
    loading.value = false;
  }
};

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchCompanies(1);
  }, 300);
};

const changePage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchCompanies(page);
  }
};

const switchCompany = async (id) => {
  switchingId.value = id;
  successMessage.value = '';
  
  try {
    const response = await axios.post(`/api/companies/switch/${id}`);
    successMessage.value = response.data.message;
    currentCompanyId.value = id;
    
    // Refresh global user/company context in Pinia store
    await authStore.fetchUser();
    window.dispatchEvent(new CustomEvent('company-switched-globally'));
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    console.error('Error switching company:', error);
  } finally {
    switchingId.value = null;
  }
};



onMounted(() => {
  fetchCompanies();
});
</script>
