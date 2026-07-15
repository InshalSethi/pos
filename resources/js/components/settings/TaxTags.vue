<template>
  <div class="w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-7xl mx-auto">
      
      <!-- Toast Notification System (Top Right) -->
      <div class="fixed top-20 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none">
        <transition-group
          enter-active-class="transform ease-out duration-300 transition"
          enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
          enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div 
            v-for="alert in alerts" 
            :key="alert.id"
            class="pointer-events-auto w-full overflow-hidden rounded-2xl bg-[#0f172a] border border-white/5 shadow-2xl p-4 flex items-center gap-3 text-slate-50"
          >
            <div class="flex-shrink-0">
              <svg v-if="alert.type === 'error'" class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg v-else class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p v-if="alert.title" class="text-xs font-bold leading-normal text-white dark:text-white" style="color: #ffffff !important;">{{ alert.title }}</p>
              <p class="text-[11px] font-semibold leading-relaxed text-slate-300 dark:text-slate-300" style="color: #cbd5e1 !important;">{{ alert.message }}</p>
            </div>
            <div class="flex-shrink-0 flex">
              <button @click="removeAlert(alert.id)" class="flex-shrink-0 p-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-all focus:outline-none cursor-pointer">
                <span class="sr-only">Close</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </transition-group>
      </div>

      <!-- Header Section -->
      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Tax and Tags Management</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Create and configure tax codes and tags to apply to your products and variant selections.</p>
      </div>

      <!-- Main Content Layout (Grid) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- LEFT COLUMN: TAXES -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-sm overflow-hidden flex flex-col h-[680px]">
          <!-- Card Header -->
          <div class="p-6 border-b border-slate-200 dark:border-[#2E2E2E] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Taxes</h2>
              <span class="bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 text-2xs px-2 py-0.5 rounded-full font-bold uppercase">
                {{ taxes.length }} Types
              </span>
            </div>
            <button 
              type="button" 
              @click="openTaxModal()" 
              class="h-[34px] px-4 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
              </svg>
              Add Tax
            </button>
          </div>

          <!-- Search Bar -->
          <div class="px-6 py-4 border-b border-slate-100 dark:border-[#2E2E2E] bg-slate-50/50 dark:bg-zinc-950">
            <div class="relative w-full">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 dark:text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input 
                v-model="taxSearch"
                type="text" 
                placeholder="Search tax by name..."
                class="w-full pl-9 pr-4 py-2 bg-slate-100 dark:bg-[#1E1E1E] dark:text-slate-100 border border-transparent dark:border-[#2E2E2E] rounded-xl text-xs font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-all placeholder:text-slate-400"
              />
            </div>
          </div>

          <!-- Table Container -->
          <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div v-if="loadingTaxes" class="text-center py-20">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-3 text-slate-400 text-xs font-medium dark:text-slate-400">Loading taxes...</p>
            </div>

            <div v-else-if="filteredTaxes.length === 0" class="text-center py-16 px-4">
              <div class="w-12 h-12 bg-slate-50 dark:bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100 dark:border-[#2E2E2E]">
                <svg class="w-6 h-6 text-slate-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v11m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                </svg>
              </div>
              <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No taxes found</h3>
              <p class="text-2xs text-slate-400 mt-1 max-w-xs mx-auto dark:text-slate-400">Taxes allow billing system rules to automatically add sales rates on checkouts.</p>
            </div>

            <table v-else class="w-full table-auto border-collapse">
              <thead>
                <tr class="bg-slate-50 dark:bg-[#252525] border-b border-slate-200 dark:border-[#2E2E2E] text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 sticky top-0 z-10">
                  <th class="px-6 py-3.5 text-left">Tax Code / Name</th>
                  <th class="px-6 py-3.5 text-left">Value / Type</th>
                  <th class="px-6 py-3.5 text-center">Status</th>
                  <th class="px-6 py-3.5 text-center w-28">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-[#2E2E2E]">
                <tr 
                  v-for="tax in filteredTaxes" 
                  :key="tax.id"
                  class="hover:bg-slate-50/60 dark:hover:bg-[#2D2D2D]/80 transition-colors"
                >
                  <td class="px-6 py-4">
                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs">
                      {{ tax.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-350">
                      {{ tax.value }}{{ tax.type === 'percentage' ? '%' : '' }}
                    </div>
                    <div class="text-[10px] font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">
                      {{ tax.type }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span 
                      :class="[
                        'px-2 py-0.5 text-[9px] font-bold uppercase rounded-full',
                        tax.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-450' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                      ]"
                    >
                      {{ tax.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center align-middle">
                    <div class="flex items-center justify-center gap-1.5">
                      <button 
                        type="button" 
                        @click="openTaxModal(tax)"
                        class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 dark:hover:bg-[#252525] transition-all focus:outline-none cursor-pointer dark:text-slate-400"
                        title="Edit Tax"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button 
                        type="button" 
                        @click="deleteTax(tax.id)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-[#252525] transition-all focus:outline-none cursor-pointer dark:text-slate-400"
                        title="Delete Tax"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- RIGHT COLUMN: TAGS -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-sm overflow-hidden flex flex-col h-[680px]">
          <!-- Card Header -->
          <div class="p-6 border-b border-slate-200 dark:border-[#2E2E2E] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Tags</h2>
              <span class="bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 text-2xs px-2 py-0.5 rounded-full font-bold uppercase">
                {{ tags.length }} Created
              </span>
            </div>
            <button 
              type="button" 
              @click="openTagModal()" 
              class="h-[34px] px-4 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
              </svg>
              Add Tag
            </button>
          </div>

          <!-- Search Bar -->
          <div class="px-6 py-4 border-b border-slate-100 dark:border-[#2E2E2E] bg-slate-50/50 dark:bg-zinc-950">
            <div class="relative w-full">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 dark:text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input 
                v-model="tagSearch"
                type="text" 
                placeholder="Search tag by name..."
                class="w-full pl-9 pr-4 py-2 bg-slate-100 dark:bg-[#1E1E1E] dark:text-slate-100 border border-transparent dark:border-[#2E2E2E] rounded-xl text-xs font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-all placeholder:text-slate-400"
              />
            </div>
          </div>

          <!-- Table Container -->
          <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div v-if="loadingTags" class="text-center py-20">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-3 text-slate-400 text-xs font-medium dark:text-slate-400">Loading tags...</p>
            </div>

            <div v-else-if="filteredTags.length === 0" class="text-center py-16 px-4">
              <div class="w-12 h-12 bg-slate-50 dark:bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100 dark:border-[#2E2E2E]">
                <svg class="w-6 h-6 text-slate-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No tags found</h3>
              <p class="text-2xs text-slate-400 mt-1 max-w-xs mx-auto dark:text-slate-400">Tags are identifiers that can be attached to item categories or standard/variation options.</p>
            </div>

            <table v-else class="w-full table-auto border-collapse">
              <thead>
                <tr class="bg-slate-50 dark:bg-[#252525] border-b border-slate-200 dark:border-[#2E2E2E] text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 sticky top-0 z-10">
                  <th class="px-6 py-3.5 text-left">Tag Name</th>
                  <th class="px-6 py-3.5 text-center w-28">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-[#2E2E2E]">
                <tr 
                  v-for="tag in filteredTags" 
                  :key="tag.id"
                  class="hover:bg-slate-50/60 dark:hover:bg-[#2D2D2D]/80 transition-colors"
                >
                  <td class="px-6 py-4">
                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs">
                      <span class="inline-block px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-[#252525] text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-[#2E2E2E]/60">
                        # {{ tag.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center align-middle">
                    <div class="flex items-center justify-center gap-1.5">
                      <button 
                        type="button" 
                        @click="openTagModal(tag)"
                        class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 dark:hover:bg-[#252525] transition-all focus:outline-none cursor-pointer dark:text-slate-400"
                        title="Edit Tag"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button 
                        type="button" 
                        @click="deleteTag(tag.id)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-[#252525] transition-all focus:outline-none cursor-pointer dark:text-slate-400"
                        title="Delete Tag"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- TAX DIALOG MODAL -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showTaxModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeTaxModal"></div>

          <div class="relative bg-white dark:bg-[#1E1E1E] w-full max-w-md p-6 rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-2xl space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-[#2E2E2E]">
              <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                {{ isEditingTax ? 'Edit Tax Rule' : 'New Tax Rule' }}
              </h3>
              <button @click="closeTaxModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg focus:outline-none dark:text-slate-400">&times;</button>
            </div>

            <form @submit.prevent="submitTax" class="space-y-4 text-xs font-medium">
              <div>
                <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1 uppercase tracking-wider">Tax Code / Name *</label>
                <input 
                  v-model="taxForm.name"
                  type="text" 
                  placeholder="e.g., VAT, GST 18%, Sales Tax" 
                  class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                  required
                />
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1 uppercase tracking-wider">Rate / Value *</label>
                  <input 
                    v-model.number="taxForm.value"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="e.g., 18.00" 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                    required
                  />
                </div>
                <div>
                  <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1 uppercase tracking-wider">Value Type *</label>
                  <select 
                    v-model="taxForm.type"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500 cursor-pointer font-semibold"
                  >
                    <option value="percentage">Percentage (%)</option>
                    <option value="flat">Flat ($)</option>
                  </select>
                </div>
              </div>

              <div class="flex items-center justify-between bg-slate-50 dark:bg-[#252525] p-3 rounded-2xl border border-slate-200/50 dark:border-[#2E2E2E]/80">
                <div>
                  <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200">Is Active</span>
                  <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">Toggle to enable/disable applying this tax rate on billing</p>
                </div>
                <input 
                  v-model="taxForm.is_active"
                  type="checkbox" 
                  class="w-4.5 h-4.5 rounded border-slate-350 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                />
              </div>

              <div class="flex justify-end gap-2 pt-2">
                <button 
                  type="button" 
                  @click="closeTaxModal" 
                  class="px-4 py-2.5 text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="submittingTax" 
                  class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition-all flex items-center gap-1.5 disabled:opacity-50 cursor-pointer"
                >
                  <svg v-if="submittingTax" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Save Tax Rule
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- TAG DIALOG MODAL -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showTagModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeTagModal"></div>

          <div class="relative bg-white dark:bg-[#1E1E1E] w-full max-w-md p-6 rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-2xl space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-[#2E2E2E]">
              <h3 class="text-sm font-extrabold text-slate-808 dark:text-slate-100 uppercase tracking-wider">
                {{ isEditingTag ? 'Edit Tag' : 'New Tag' }}
              </h3>
              <button @click="closeTagModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg focus:outline-none dark:text-slate-400">&times;</button>
            </div>

            <form @submit.prevent="submitTag" class="space-y-4 text-xs font-medium">
              <div>
                <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1 uppercase tracking-wider">Tag Name *</label>
                <input 
                  v-model="tagForm.name"
                  type="text" 
                  placeholder="e.g., Summer-Collection, Imported, Organic" 
                  class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                  required
                />
              </div>

              <div class="flex justify-end gap-2 pt-2">
                <button 
                  type="button" 
                  @click="closeTagModal" 
                  class="px-4 py-2.5 text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="submittingTag" 
                  class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition-all flex items-center gap-1.5 disabled:opacity-50 cursor-pointer"
                >
                  <svg v-if="submittingTag" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Save Tag
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// States
const taxes = ref([]);
const tags = ref([]);
const loadingTaxes = ref(false);
const loadingTags = ref(false);

const taxSearch = ref('');
const tagSearch = ref('');

// Modals state
const showTaxModal = ref(false);
const isEditingTax = ref(false);
const taxEditId = ref(null);
const submittingTax = ref(false);
const taxForm = ref({
  name: '',
  value: 0,
  type: 'percentage',
  is_active: true
});

const showTagModal = ref(false);
const isEditingTag = ref(false);
const tagEditId = ref(null);
const submittingTag = ref(false);
const tagForm = ref({
  name: ''
});

// System Alert Toast System
const alerts = ref([]);
let nextAlertId = 1;

const triggerAlert = (title, message, type = 'success') => {
  const alertId = nextAlertId++;
  alerts.value.push({ id: alertId, title, message, type });
  setTimeout(() => {
    removeAlert(alertId);
  }, 5000);
};

const removeAlert = (id) => {
  const index = alerts.value.findIndex(a => a.id === id);
  if (index !== -1) {
    alerts.value.splice(index, 1);
  }
};

// Filtered Computed Lists
const filteredTaxes = computed(() => {
  if (!taxSearch.value.trim()) return taxes.value;
  const q = taxSearch.value.toLowerCase();
  return taxes.value.filter(t => t.name.toLowerCase().includes(q));
});

const filteredTags = computed(() => {
  if (!tagSearch.value.trim()) return tags.value;
  const q = tagSearch.value.toLowerCase();
  return tags.value.filter(t => t.name.toLowerCase().includes(q));
});

// API Calls - Taxes
const fetchTaxes = async () => {
  loadingTaxes.value = true;
  try {
    const res = await axios.get('/api/taxes');
    taxes.value = res.data || [];
  } catch (err) {
    console.error('Failed to load taxes:', err);
    triggerAlert('Error', 'Failed to load tax records from database.', 'error');
  } finally {
    loadingTaxes.value = false;
  }
};

const openTaxModal = (tax = null) => {
  if (tax) {
    isEditingTax.value = true;
    taxEditId.value = tax.id;
    taxForm.value = {
      name: tax.name,
      value: parseFloat(tax.value),
      type: tax.type || 'percentage',
      is_active: !!tax.is_active
    };
  } else {
    isEditingTax.value = false;
    taxEditId.value = null;
    taxForm.value = {
      name: '',
      value: 0,
      type: 'percentage',
      is_active: true
    };
  }
  showTaxModal.value = true;
};

const closeTaxModal = () => {
  showTaxModal.value = false;
};

const submitTax = async () => {
  if (!taxForm.value.name.trim()) {
    triggerAlert('Validation Error', 'Tax name is required.', 'error');
    return;
  }
  if (taxForm.value.value === null || taxForm.value.value === undefined || taxForm.value.value < 0) {
    triggerAlert('Validation Error', 'Tax value must be at least 0.', 'error');
    return;
  }

  submittingTax.value = true;
  try {
    if (isEditingTax.value) {
      const res = await axios.put(`/api/taxes/${taxEditId.value}`, taxForm.value);
      triggerAlert('Success', res.data.message || 'Tax rule updated successfully.');
    } else {
      const res = await axios.post('/api/taxes', taxForm.value);
      triggerAlert('Success', res.data.message || 'Tax rule created successfully.');
    }
    closeTaxModal();
    fetchTaxes();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.errors?.name?.[0] || err.response?.data?.message || 'Failed to save tax rule.';
    triggerAlert('Failed', msg, 'error');
  } finally {
    submittingTax.value = false;
  }
};

const deleteTax = async (id) => {
  if (!confirm('Are you sure you want to delete this tax rule?')) return;
  try {
    const res = await axios.delete(`/api/taxes/${id}`);
    triggerAlert('Success', res.data.message || 'Tax rule deleted.');
    fetchTaxes();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to delete tax rule.';
    triggerAlert('Error', msg, 'error');
  }
};

// API Calls - Tags
const fetchTags = async () => {
  loadingTags.value = true;
  try {
    const res = await axios.get('/api/tags');
    tags.value = res.data || [];
  } catch (err) {
    console.error('Failed to load tags:', err);
    triggerAlert('Error', 'Failed to load tag records from database.', 'error');
  } finally {
    loadingTags.value = false;
  }
};

const openTagModal = (tag = null) => {
  if (tag) {
    isEditingTag.value = true;
    tagEditId.value = tag.id;
    tagForm.value = {
      name: tag.name
    };
  } else {
    isEditingTag.value = false;
    tagEditId.value = null;
    tagForm.value = {
      name: ''
    };
  }
  showTagModal.value = true;
};

const closeTagModal = () => {
  showTagModal.value = false;
};

const submitTag = async () => {
  if (!tagForm.value.name.trim()) {
    triggerAlert('Validation Error', 'Tag name is required.', 'error');
    return;
  }

  submittingTag.value = true;
  try {
    if (isEditingTag.value) {
      const res = await axios.put(`/api/tags/${tagEditId.value}`, tagForm.value);
      triggerAlert('Success', res.data.message || 'Tag updated successfully.');
    } else {
      const res = await axios.post('/api/tags', tagForm.value);
      triggerAlert('Success', res.data.message || 'Tag created successfully.');
    }
    closeTagModal();
    fetchTags();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.errors?.name?.[0] || err.response?.data?.message || 'Failed to save tag.';
    triggerAlert('Failed', msg, 'error');
  } finally {
    submittingTag.value = false;
  }
};

const deleteTag = async (id) => {
  if (!confirm('Are you sure you want to delete this tag?')) return;
  try {
    const res = await axios.delete(`/api/tags/${id}`);
    triggerAlert('Success', res.data.message || 'Tag deleted.');
    fetchTags();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to delete tag.';
    triggerAlert('Error', msg, 'error');
  }
};

// Lifecycles
onMounted(() => {
  fetchTaxes();
  fetchTags();
});
</script>

<style scoped>
/* Thin scrollbar styling */
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}
</style>
