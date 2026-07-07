<template>
  <div class="w-full mx-auto py-4 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-slate-950 min-h-screen font-sans">
    <div class="w-full max-w-7xl mx-auto">
      
      <!-- HEADER SECTION -->
      <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Stock Adjustments</h1>
          <span class="bg-indigo-150 text-indigo-850 dark:bg-indigo-950/60 dark:text-indigo-300 text-xs px-2.5 py-1 rounded-full font-bold">
            {{ pagination.total || 0 }} Logs
          </span>
        </div>
        
        <div class="flex items-center gap-3 self-end sm:self-auto">
          <!-- Upload Inventory Trigger -->
          <button
            @click="showUploadModal = true"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 font-bold rounded-full shadow-sm transition-all duration-200 text-xs cursor-pointer active:scale-95"
          >
            <svg class="w-4 h-4 mr-1.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            Import File
          </button>

          <!-- New Adjustment Button -->
          <button
            @click="openAdjustmentModal"
            class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-full shadow-md hover:shadow-lg transition-all duration-200 text-xs cursor-pointer active:scale-95"
          >
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            New Adjustment
          </button>
        </div>
      </div>

      <!-- FEEDBACK NOTIFICATION BANNER -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="feedbackMsg" :class="feedbackClass" class="mb-6 p-4 rounded-2xl border flex items-center justify-between text-xs font-semibold shadow-xs">
          <span class="flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ feedbackMsg }}
          </span>
          <button @click="feedbackMsg = ''" class="text-current opacity-70 hover:opacity-100 text-lg font-bold leading-none">&times;</button>
        </div>
      </transition>

      <!-- STATS SUMMARY CARDS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Total Adjustments -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
          </div>
          <div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Total Logs</span>
            <span class="text-xl font-black text-slate-800 dark:text-white leading-tight mt-0.5 block">{{ summary.total_adjustments || 0 }}</span>
          </div>
        </div>

        <!-- Card 2: Increases -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
          <div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Increases</span>
            <span class="text-xl font-black text-slate-800 dark:text-white leading-tight mt-0.5 block">{{ summary.total_increases || 0 }}</span>
          </div>
        </div>

        <!-- Card 3: Decreases -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path>
            </svg>
          </div>
          <div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Decreases</span>
            <span class="text-xl font-black text-slate-800 dark:text-white leading-tight mt-0.5 block">{{ summary.total_decreases || 0 }}</span>
          </div>
        </div>

        <!-- Card 4: Low Stock Products -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-650 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Low Stock Alerts</span>
            <span class="text-xl font-black text-slate-800 dark:text-white leading-tight mt-0.5 block">{{ summary.low_stock_products || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- MAIN FILTER BAR & ACTIVITY TABLE CARD -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/85 dark:border-slate-800 shadow-sm overflow-hidden p-6 space-y-4">
        
        <!-- Search, Filter & Date controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex flex-wrap items-center gap-3">
            <!-- Search field -->
            <div class="relative w-full sm:w-64">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input
                v-model="filters.search"
                @input="debouncedFetch"
                type="text"
                placeholder="Search adjustment number..."
                class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-all placeholder:text-slate-400"
              />
            </div>

            <!-- Adjustment type filter -->
            <select
              v-model="filters.adjustment_type"
              @change="fetchAdjustments(1)"
              class="px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer"
            >
              <option value="">All Types</option>
              <option value="increase">Stock Increase</option>
              <option value="decrease">Stock Decrease</option>
              <option value="recount">Stock Recount</option>
            </select>
          </div>

          <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
            <span>Show</span>
            <select
              v-model="filters.per_page"
              @change="fetchAdjustments(1)"
              class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
            <span>entries</span>
          </div>
        </div>

        <!-- Activity Table -->
        <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
          <div v-if="loading" class="text-center py-20 bg-white dark:bg-slate-900">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-650 mx-auto"></div>
            <p class="mt-3 text-slate-550 text-xs font-bold uppercase tracking-wider">Loading stock adjustments...</p>
          </div>

          <div v-else-if="adjustments.length === 0" class="text-center py-16 px-4 bg-white dark:bg-slate-900">
            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-850 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-800">
              <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <h3 class="text-base font-bold text-slate-850 dark:text-slate-200">No adjustments logged</h3>
            <p class="text-xs text-slate-450 mt-1 max-w-xs mx-auto">You can manually adjust stocks using the "+ New Adjustment" flow above.</p>
          </div>

          <table v-else class="w-full table-auto border-collapse text-xs font-medium">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-200/80 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <th class="px-6 py-4 text-left">Adjustment #</th>
                <th class="px-6 py-4 text-left">Product / Variation</th>
                <th class="px-6 py-4 text-left">Warehouse</th>
                <th class="px-6 py-4 text-center">Type</th>
                <th class="px-6 py-4 text-center">Qty Shift</th>
                <th class="px-6 py-4 text-left">Reason / Cost Impact</th>
                <th class="px-6 py-4 text-center">Date / Operator</th>
                <th class="px-6 py-4 text-center w-20">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
              <tr v-for="adj in adjustments" :key="adj.id" class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <!-- Adjustment No -->
                <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white whitespace-nowrap">
                  {{ adj.adjustment_number }}
                </td>
                
                <!-- Product / Variant -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg overflow-hidden border border-slate-100 dark:border-slate-850 bg-slate-50 shrink-0 shadow-xs">
                      <img :src="adj.product?.image || '/placeholder.png'" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <span class="font-bold text-slate-900 dark:text-white text-xs block leading-tight">{{ adj.product?.name }}</span>
                      
                      <!-- Variant info badge -->
                      <span v-if="adj.variation" class="inline-flex mt-1 text-[9px] font-bold text-indigo-750 bg-indigo-50 dark:bg-indigo-950/40 dark:text-indigo-400 px-1.5 py-0.5 rounded">
                        {{ adj.variation.variation_name_string }}
                      </span>
                    </div>
                  </div>
                </td>
                
                <!-- Warehouse -->
                <td class="px-6 py-4 text-slate-700 dark:text-slate-300 font-bold whitespace-nowrap">
                  {{ adj.warehouse?.name || 'N/A' }}
                </td>

                <!-- Adjustment Type -->
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full',
                      adj.adjustment_type === 'increase'
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                        : adj.adjustment_type === 'decrease'
                        ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                        : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400'
                    ]"
                  >
                    {{ adj.adjustment_type }}
                  </span>
                </td>

                <!-- Quantity shift details -->
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <div class="font-bold text-slate-850 dark:text-slate-200">
                    {{ adj.quantity_before }} &rarr; {{ adj.quantity_after }}
                  </div>
                  <div class="text-[10px] text-slate-400 dark:text-slate-500 font-extrabold mt-0.5">
                    {{ adj.adjustment_type === 'increase' ? '+' : adj.adjustment_type === 'decrease' ? '-' : '' }}{{ Math.abs(adj.quantity_adjusted) }}
                  </div>
                </td>

                <!-- Reason / Cost Impact -->
                <td class="px-6 py-4">
                  <div class="text-slate-800 dark:text-slate-300 font-bold text-xs truncate max-w-[160px]">{{ adj.reason }}</div>
                  <div class="text-[10px] font-bold mt-0.5" :class="adj.cost_impact >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                    Cost: {{ adj.cost_impact >= 0 ? '+' : '-' }}${{ Math.abs(parseFloat(adj.cost_impact || 0)).toFixed(2) }}
                  </div>
                </td>

                <!-- Date / Operator -->
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <div class="font-bold text-slate-900 dark:text-white">{{ formatDate(adj.adjustment_date) }}</div>
                  <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold mt-0.5">By: {{ adj.user?.name || 'System' }}</div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-center whitespace-nowrap align-middle">
                  <button
                    type="button"
                    @click="showAdjustmentDetailModal(adj)"
                    class="p-1.5 text-slate-450 hover:text-indigo-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none cursor-pointer"
                    title="View Log Details"
                  >
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div v-if="pagination.total > 0" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
          <div class="text-xs font-bold text-slate-400">
            Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }} entries
          </div>

          <div class="flex items-center gap-1.5 self-center sm:self-auto">
            <button
              type="button"
              :disabled="pagination.current_page === 1"
              @click="fetchAdjustments(pagination.current_page - 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:hover:bg-transparent text-xs font-bold transition-all focus:outline-none cursor-pointer"
            >
              Previous
            </button>

            <button
              v-for="p in pagination.last_page"
              :key="p"
              type="button"
              @click="fetchAdjustments(p)"
              class="w-8 h-8 rounded-lg text-xs font-bold transition-all focus:outline-none cursor-pointer"
              :class="pagination.current_page === p ? 'bg-indigo-600 text-white shadow-sm' : 'border border-slate-200 dark:border-slate-800 text-slate-600 hover:bg-slate-50'"
            >
              {{ p }}
            </button>

            <button
              type="button"
              :disabled="pagination.current_page === pagination.last_page"
              @click="fetchAdjustments(pagination.current_page + 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:hover:bg-transparent text-xs font-bold transition-all focus:outline-none cursor-pointer"
            >
              Next
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- VIEW DETAILS MODAL -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showDetailModal = false"></div>

        <div class="relative bg-white dark:bg-slate-900 w-full max-w-lg p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10 text-xs">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">
              Adjustment Log Details
            </h3>
            <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 font-bold text-lg focus:outline-none">&times;</button>
          </div>

          <div class="space-y-4 divide-y divide-slate-100 dark:divide-slate-850">
            <!-- Basic info -->
            <div class="grid grid-cols-2 gap-4 pb-3">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Adjustment #</label>
                <div class="font-bold text-slate-900 dark:text-white text-sm">{{ detailLog?.adjustment_number }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Warehouse Location</label>
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ detailLog?.warehouse?.name }}</div>
              </div>
            </div>

            <!-- Product details -->
            <div class="py-3 flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-100 dark:border-slate-800 bg-slate-50 shrink-0">
                <img :src="detailLog?.product?.image || '/placeholder.png'" class="w-full h-full object-cover">
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Adjusted Product</label>
                <div class="font-bold text-slate-900 dark:text-white text-xs">{{ detailLog?.product?.name }}</div>
                <div v-if="detailLog?.variation" class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">
                  Variation: {{ detailLog.variation.variation_name_string }}
                </div>
              </div>
            </div>

            <!-- stock detail -->
            <div class="grid grid-cols-3 gap-4 py-3 text-center">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Qty Before</label>
                <div class="font-black text-slate-800 dark:text-white text-sm mt-1">{{ detailLog?.quantity_before }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Qty Adjusted</label>
                <div class="font-black text-sm mt-1" :class="detailLog?.adjustment_type === 'increase' ? 'text-emerald-600' : 'text-rose-600'">
                  {{ detailLog?.adjustment_type === 'increase' ? '+' : detailLog?.adjustment_type === 'decrease' ? '-' : '' }}{{ Math.abs(detailLog?.quantity_adjusted) }}
                </div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Qty After</label>
                <div class="font-black text-slate-800 dark:text-white text-sm mt-1">{{ detailLog?.quantity_after }}</div>
              </div>
            </div>

            <!-- details & reasoning -->
            <div class="py-3 space-y-2">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Reason</label>
                <div class="font-bold text-slate-900 dark:text-white text-xs">{{ detailLog?.reason }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Notes</label>
                <div class="font-medium text-slate-700 dark:text-slate-300 leading-relaxed">{{ detailLog?.notes || 'No description notes entered.' }}</div>
              </div>
            </div>

            <!-- Batch / Expiry / Impact -->
            <div class="grid grid-cols-3 gap-4 py-3">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Batch No</label>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ detailLog?.batch_number || 'N/A' }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Expiry Date</label>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ detailLog?.expiry_date ? formatDate(detailLog.expiry_date) : 'N/A' }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Cost Impact</label>
                <div class="font-bold" :class="detailLog?.cost_impact >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                  {{ detailLog?.cost_impact >= 0 ? '+' : '-' }}${{ Math.abs(parseFloat(detailLog?.cost_impact || 0)).toFixed(2) }}
                </div>
              </div>
            </div>

            <!-- Operator and attachments -->
            <div class="grid grid-cols-2 gap-4 py-3">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Operator</label>
                <div class="font-bold text-slate-800 dark:text-slate-250">{{ detailLog?.user?.name || 'System' }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Attachment</label>
                <div v-if="detailLog?.attachment" class="mt-1">
                  <a :href="detailLog.attachment" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Attachment
                  </a>
                </div>
                <div v-else class="text-slate-400 italic mt-0.5">No file attached</div>
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="showDetailModal = false"
              class="px-5 py-2.5 bg-slate-850 hover:bg-slate-900 text-white font-bold rounded-xl shadow cursor-pointer transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- NEW ADJUSTMENT MODAL (SLIDE-OVER / FULL DIALOG FLOW) -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showFormModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="closeFormModal"></div>

        <!-- Form container -->
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-4xl shadow-2xl rounded-[32px] overflow-hidden border border-slate-200 dark:border-slate-800 transform transition-all flex flex-col max-h-[90vh]">
          
          <!-- Header -->
          <div class="px-8 py-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800 shrink-0">
            <div>
              <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase">Manual Stock Adjustment</h3>
              <p class="text-indigo-600 dark:text-indigo-400 text-[10px] font-bold uppercase tracking-widest mt-1">Multi-Warehouse & Variation Enabled</p>
            </div>
            <button @click="closeFormModal" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors group cursor-pointer">
              <svg class="w-6 h-6 text-slate-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Content Form -->
          <div class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1 text-xs">
            <div v-if="formErrors.length > 0" class="mb-4 p-3 bg-rose-50 text-rose-700 rounded-xl border border-rose-200 font-bold">
              <ul class="list-disc pl-4 space-y-0.5">
                <li v-for="(err, idx) in formErrors" :key="idx">{{ err }}</li>
              </ul>
            </div>

            <form @submit.prevent="submitAdjustmentForm" class="space-y-6">
              
              <!-- STEP 1: Search & Product Selection -->
              <div class="space-y-3">
                <h4 class="text-xs font-black uppercase tracking-widest text-slate-450 dark:text-slate-500">1. Select Item to Adjust</h4>
                
                <div class="relative">
                  <!-- Search input -->
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                      </svg>
                    </span>
                    <input
                      v-model="productSearchQuery"
                      @focus="showProductDropdown = true"
                      type="text"
                      placeholder="Search items by SKU, name, or barcode..."
                      class="w-full pl-11 pr-10 py-3 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-2xl focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-all font-bold"
                    />
                    <!-- Reset button if selected -->
                    <button
                      v-if="selectedProductDetail"
                      type="button"
                      @click="clearSelectedProduct"
                      class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 font-bold cursor-pointer"
                    >
                      &times; Clear Selection
                    </button>
                  </div>

                  <!-- Dropdown options list -->
                  <div
                    v-show="showProductDropdown && filteredProducts.length > 0"
                    class="absolute left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 max-h-56 overflow-y-auto custom-scrollbar"
                  >
                    <button
                      v-for="p in filteredProducts"
                      :key="p.id"
                      type="button"
                      @click="selectProduct(p)"
                      class="w-full text-left px-4 py-3 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 border-b border-slate-50 dark:border-slate-850 flex items-center gap-3 transition-colors cursor-pointer"
                    >
                      <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-100 dark:border-slate-850 bg-slate-50 shrink-0">
                        <img :src="p.image || '/placeholder.png'" class="w-full h-full object-cover">
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="font-bold text-slate-850 dark:text-slate-200 truncate">{{ p.name }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">SKU: {{ p.sku }} | Barcode: {{ p.barcode || 'N/A' }}</div>
                      </div>
                      <div class="text-right">
                        <span v-if="p.has_variations" class="px-2 py-0.5 text-[9px] font-extrabold uppercase bg-indigo-50 text-indigo-750 dark:bg-indigo-950/40 dark:text-indigo-400 rounded-full">Variants</span>
                        <span v-else class="text-[11px] font-black text-slate-800 dark:text-white">Stock: {{ p.stock_quantity }}</span>
                      </div>
                    </button>
                  </div>
                </div>

                <!-- Dynamic Meta Grid Detail Card -->
                <transition
                  enter-active-class="transition duration-300 ease-out"
                  enter-from-class="opacity-0 translate-y-2 scale-98"
                  enter-to-class="opacity-100 translate-y-0 scale-100"
                >
                  <div v-if="selectedProductDetail" class="bg-indigo-50/20 dark:bg-indigo-950/10 border border-indigo-100/50 dark:border-indigo-900/30 rounded-2xl p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                    <div class="w-16 h-16 rounded-xl overflow-hidden border border-indigo-100 dark:border-indigo-950 bg-white shadow-sm shrink-0">
                      <img :src="selectedProductDetail.image || '/placeholder.png'" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 space-y-1">
                      <div class="flex flex-wrap items-center gap-2">
                        <h5 class="text-sm font-black text-slate-900 dark:text-white">{{ selectedProductDetail.name }}</h5>
                        
                        <!-- badge -->
                        <span
                          :class="[
                            'px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full',
                            selectedProductDetail.has_variations ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'
                          ]"
                        >
                          {{ selectedProductDetail.has_variations ? 'Variants' : 'Standard' }}
                        </span>
                      </div>
                      <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 text-slate-500 dark:text-slate-400 text-[11px]">
                        <div>Base Price: <span class="font-bold text-slate-750 dark:text-slate-200">${{ parseFloat(selectedProductDetail.selling_price || 0).toFixed(2) }}</span></div>
                        <div>Category: <span class="font-bold text-slate-750 dark:text-slate-200">{{ selectedProductDetail.category?.name || 'Uncategorized' }}</span></div>
                        <div>Base SKU: <span class="font-bold text-slate-750 dark:text-slate-200">{{ selectedProductDetail.sku }}</span></div>
                        <div v-if="!selectedProductDetail.has_variations">Current Stock: <span class="font-bold text-slate-750 dark:text-slate-250">{{ selectedProductDetail.stock_quantity }}</span></div>
                      </div>
                    </div>
                  </div>
                </transition>
              </div>

              <!-- STEP 2: Warehouse Selection & Dynamic Adjustment Matrix -->
              <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
              >
                <div v-if="selectedProductDetail" class="space-y-4">
                  <h4 class="text-xs font-black uppercase tracking-widest text-slate-450 dark:text-slate-500">2. Warehouse Allocation & Adjustments</h4>
                  
                  <!-- Warehouse Selection Checkboxes -->
                  <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-2xl border border-slate-150 dark:border-slate-800">
                    <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2">Adjust Stock in Warehouses:</label>
                    <div class="flex flex-wrap gap-4">
                      <label
                        v-for="wh in warehouses"
                        :key="wh.id"
                        class="flex items-center gap-2 cursor-pointer select-none font-bold text-slate-700 dark:text-slate-300 text-xs"
                      >
                        <input
                          v-model="checkedWarehouseIds"
                          type="checkbox"
                          :value="wh.id"
                          class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        {{ wh.name }}
                        <span v-if="wh.is_default" class="text-[9px] font-black text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 px-1 rounded uppercase">Default</span>
                      </label>
                    </div>
                  </div>

                  <!-- WARNING FOR EMPTY WAREHOUSES -->
                  <div v-if="checkedWarehouseIds.length === 0" class="text-center py-6 text-slate-400 font-bold bg-slate-50 dark:bg-slate-850 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                    Please check at least one warehouse location above.
                  </div>

                  <div v-else>
                    <!-- FLOW A: STANDARD PRODUCT MATRIX -->
                    <div v-if="!selectedProductDetail.has_variations" class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
                      <table class="w-full text-left table-auto border-collapse">
                        <thead>
                          <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-3">Warehouse Location</th>
                            <th class="px-4 py-3 text-center">Current Qty</th>
                            <th class="px-4 py-3 text-center w-36">Adjustment Type</th>
                            <th class="px-4 py-3 text-center w-28">Quantity</th>
                            <th class="px-4 py-3 text-center">Resulting Qty</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                          <tr v-for="whId in checkedWarehouseIds" :key="whId">
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                              {{ getWarehouseName(whId) }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-slate-500">
                              {{ getStandardCurrentStock(whId) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                              <select
                                v-model="standardAdjustments[whId].adjustment_type"
                                class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer"
                              >
                                <option value="increase">Stock Increase</option>
                                <option value="decrease">Stock Decrease</option>
                                <option value="recount">Stock Recount</option>
                              </select>
                            </td>
                            <td class="px-4 py-2">
                              <input
                                v-model.number="standardAdjustments[whId].quantity_adjusted"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-center focus:outline-none focus:ring-1 focus:ring-indigo-500"
                              />
                            </td>
                            <td class="px-4 py-3 text-center font-black text-slate-800 dark:text-slate-200">
                              {{ calculateFinalStock(getStandardCurrentStock(whId), standardAdjustments[whId].adjustment_type, standardAdjustments[whId].quantity_adjusted) }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <!-- FLOW B: VARIABLE PRODUCT DYNAMIC MATRIX -->
                    <div v-else class="space-y-3">
                      <!-- Global helper for variable product matrix -->
                      <div class="flex items-center justify-between gap-4 p-3 bg-indigo-50/10 dark:bg-indigo-950/10 rounded-xl border border-indigo-150/40 dark:border-indigo-850">
                        <span class="font-bold text-slate-750 dark:text-slate-350">Set Adjustment Type for all rows:</span>
                        <select
                          v-model="globalAdjustmentType"
                          @change="applyGlobalAdjustmentType"
                          class="px-3 py-1.5 bg-white dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-750 rounded-lg text-xs font-black focus:outline-none cursor-pointer"
                        >
                          <option value="">-- Apply type globally --</option>
                          <option value="increase">Stock Increase</option>
                          <option value="decrease">Stock Decrease</option>
                          <option value="recount">Stock Recount</option>
                        </select>
                      </div>

                      <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
                        <table class="w-full text-left table-auto border-collapse">
                          <thead>
                            <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                              <th class="px-4 py-3">Variant Profile Combo</th>
                              <th class="px-4 py-3">SKU Code</th>
                              <th class="px-4 py-3">Warehouse Location</th>
                              <th class="px-4 py-3 text-center">Current Qty</th>
                              <th class="px-4 py-3 text-center w-36">Adjustment Type</th>
                              <th class="px-4 py-3 text-center w-28">Quantity</th>
                              <th class="px-4 py-3 text-center">Resulting Qty</th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <template v-for="variant in selectedProductDetail.variations" :key="variant.id">
                              <tr v-for="whId in checkedWarehouseIds" :key="`${variant.id}-${whId}`">
                                <!-- Variant combination details -->
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                  {{ variant.variation_name_string }}
                                </td>
                                <!-- Variant SKU -->
                                <td class="px-4 py-3 text-slate-500 font-bold whitespace-nowrap">
                                  {{ variant.sku }}
                                </td>
                                <!-- Warehouse location -->
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-350">
                                  {{ getWarehouseName(whId) }}
                                </td>
                                <!-- Current Stock -->
                                <td class="px-4 py-3 text-center font-bold text-slate-550">
                                  {{ getVariantCurrentStock(variant, whId) }}
                                </td>
                                <!-- Adj type -->
                                <td class="px-4 py-2 text-center">
                                  <select
                                    v-model="variableAdjustments[`${variant.id}-${whId}`].adjustment_type"
                                    class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer"
                                  >
                                    <option value="increase">Stock Increase</option>
                                    <option value="decrease">Stock Decrease</option>
                                    <option value="recount">Stock Recount</option>
                                  </select>
                                </td>
                                <!-- Adj Qty -->
                                <td class="px-4 py-2">
                                  <input
                                    v-model.number="variableAdjustments[`${variant.id}-${whId}`].quantity_adjusted"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-center focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                  />
                                </td>
                                <!-- Resulting stock -->
                                <td class="px-4 py-3 text-center font-black text-slate-850 dark:text-slate-250">
                                  {{ calculateFinalStock(getVariantCurrentStock(variant, whId), variableAdjustments[`${variant.id}-${whId}`].adjustment_type, variableAdjustments[`${variant.id}-${whId}`].quantity_adjusted) }}
                                </td>
                              </tr>
                            </template>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </transition>

              <!-- STEP 3: Metadata -->
              <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
              >
                <div v-if="selectedProductDetail && checkedWarehouseIds.length > 0" class="space-y-4">
                  <h4 class="text-xs font-black uppercase tracking-widest text-slate-450 dark:text-slate-500">3. Adjustment Attributes & Documentation</h4>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Reason -->
                    <div>
                      <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Adjustment Reason *</label>
                      <select
                        v-model="metadataForm.reason"
                        required
                        class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                      >
                        <option value="" disabled>-- Select Reason --</option>
                        <option value="Damaged goods">Damaged goods</option>
                        <option value="Expired products">Expired products</option>
                        <option value="Theft/Loss">Theft/Loss</option>
                        <option value="Supplier return">Supplier return</option>
                        <option value="Physical count correction">Physical count correction</option>
                        <option value="System error correction">System error correction</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>

                    <!-- Attachment -->
                    <div>
                      <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Reference Attachment (PDF/Image)</label>
                      <input
                        type="file"
                        @change="handleFileAttachment"
                        accept=".pdf,.png,.jpg,.jpeg"
                        class="w-full text-xs text-slate-550 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Batch No -->
                    <div>
                      <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Batch Code / Lot ID</label>
                      <input
                        v-model="metadataForm.batch_number"
                        type="text"
                        placeholder="e.g. BATCH-A99"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white"
                      />
                    </div>

                    <!-- Expiry Date -->
                    <div>
                      <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Expiry Date (if applicable)</label>
                      <input
                        v-model="metadataForm.expiry_date"
                        type="date"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white"
                      />
                    </div>
                  </div>

                  <!-- Notes -->
                  <div>
                    <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Internal Notes / Description</label>
                    <textarea
                      v-model="metadataForm.notes"
                      rows="2"
                      placeholder="Add supplementary explanations for this adjustment..."
                      class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white"
                    ></textarea>
                  </div>
                </div>
              </transition>

              <!-- Footer actions -->
              <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button
                  type="button"
                  @click="closeFormModal"
                  class="px-5 py-2.5 text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  v-if="selectedProductDetail && checkedWarehouseIds.length > 0"
                  type="submit"
                  :disabled="submitting"
                  class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition-all flex items-center gap-1.5 disabled:opacity-50 cursor-pointer active:scale-95"
                >
                  <svg v-if="submitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Commit Adjustment
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- UPLOAD MODAL -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showUploadModal = false"></div>

        <div class="relative bg-white dark:bg-slate-900 w-full max-w-lg shadow-2xl rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 transform transition-all">
          <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-850/50">
            <div>
              <h3 class="text-lg font-extrabold text-slate-850 dark:text-white">Upload Adjustment Inventory</h3>
              <p class="text-xs text-slate-450 mt-1">Import product configurations and stocks from an Excel/CSV file.</p>
            </div>
            <button @click="showUploadModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-250 rounded-full transition-colors cursor-pointer">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="p-6 text-xs">
            <div 
              @dragover.prevent="uploadDragging = true"
              @dragleave.prevent="uploadDragging = false"
              @drop.prevent="handleFileUpload"
              :class="[
                'border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center transition-all duration-200 text-center cursor-pointer',
                uploadDragging ? 'border-indigo-500 bg-indigo-50/20 scale-[1.01]' : 'border-slate-250 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 hover:bg-slate-100/50 dark:hover:bg-slate-800'
              ]"
              @click="$refs.fileInput.click()"
            >
              <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" accept=".xlsx,.xls,.csv" />
              
              <div v-if="!uploadFile" class="flex flex-col items-center pointer-events-none">
                <div class="w-12 h-12 bg-white dark:bg-slate-900 rounded-xl shadow-xs flex items-center justify-center mb-3 text-indigo-500 border border-slate-100 dark:border-slate-800">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">Click to select or drag & drop file</h4>
                <p class="text-[10px] text-slate-400">Microsoft Excel (.xlsx, .xls) or CSV format</p>
              </div>

              <div v-else class="flex flex-col items-center">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-3">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-1 truncate max-w-[200px]">{{ uploadFile.name }}</h4>
                <p class="text-[10px] text-emerald-600 font-bold">Ready to process</p>
                <button @click.stop="uploadFile = null" class="mt-3 text-[10px] text-rose-500 hover:text-rose-700 font-bold px-3 py-1 bg-rose-50 dark:bg-rose-950/40 rounded-full cursor-pointer">
                  Remove File
                </button>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2">
              <button
                @click="showUploadModal = false"
                class="px-4 py-2.5 text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
              >
                Cancel
              </button>
              <button
                @click="processUpload"
                :disabled="!uploadFile || isUploading"
                :class="[
                  'px-5 py-2.5 font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer',
                  uploadFile && !isUploading ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed'
                ]"
              >
                <span v-if="isUploading">Processing...</span>
                <span v-else>Import Data</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// LOGS STATE
const adjustments = ref([]);
const loading = ref(false);
const summary = ref({
  total_adjustments: 0,
  total_increases: 0,
  total_decreases: 0,
  low_stock_products: 0
});
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10
});

// FILTERS
const filters = ref({
  search: '',
  adjustment_type: '',
  per_page: 10
});

// DETAIL MODAL STATE
const showDetailModal = ref(false);
const detailLog = ref(null);

// NEW ADJUSTMENT STATE
const showFormModal = ref(false);
const submitting = ref(false);
const formErrors = ref([]);

const products = ref([]);
const warehouses = ref([]);
const productSearchQuery = ref('');
const showProductDropdown = ref(false);
const selectedProductDetail = ref(null);

// WAREHOUSE ALLOCATIONS
const checkedWarehouseIds = ref([]);
const standardAdjustments = ref({}); // maps warehouse_id => { adjustment_type, quantity_adjusted }
const variableAdjustments = ref({}); // maps `${variant_id}-${warehouse_id}` => { adjustment_type, quantity_adjusted }
const globalAdjustmentType = ref('');

// METADATA FORM
const metadataForm = ref({
  reason: '',
  notes: '',
  batch_number: '',
  expiry_date: '',
  attachment: null
});

// UPLOAD DATA STATE
const showUploadModal = ref(false);
const uploadDragging = ref(false);
const uploadFile = ref(null);
const isUploading = ref(false);
const fileInput = ref(null);

// FEEDBACK STATS
const feedbackMsg = ref('');
const feedbackClass = ref('');

// Computed filtered products for searchable dropdown
const filteredProducts = computed(() => {
  if (!productSearchQuery.value.trim()) return products.value.slice(0, 10);
  const q = productSearchQuery.value.toLowerCase();
  return products.value.filter(p => 
    p.name.toLowerCase().includes(q) ||
    p.sku.toLowerCase().includes(q) ||
    (p.barcode && p.barcode.toLowerCase().includes(q))
  ).slice(0, 20);
});

// METHODS
const fetchSummary = async () => {
  try {
    const res = await axios.get('/api/inventory/summary');
    summary.value = res.data;
  } catch (err) {
    console.error('Failed fetching summary stats:', err);
  }
};

const fetchAdjustments = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get('/api/inventory-adjustments', {
      params: {
        page,
        per_page: filters.value.per_page,
        search: filters.value.search,
        adjustment_type: filters.value.adjustment_type
      }
    });
    adjustments.value = res.data.data || [];
    pagination.value = {
      current_page: res.data.current_page || 1,
      last_page: res.data.last_page || 1,
      total: res.data.total || 0,
      per_page: res.data.per_page || 10
    };
  } catch (err) {
    console.error('Failed fetching adjustments:', err);
    showFeedback('Failed loading stock adjustments.', 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    loading.value = false;
  }
};

let debounceTimeout = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchAdjustments(1);
  }, 350);
};

const fetchWarehouses = async () => {
  try {
    const res = await axios.get('/api/warehouses');
    warehouses.value = res.data || [];
  } catch (err) {
    console.error('Failed to load warehouses:', err);
  }
};

const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/products', {
      params: { per_page: 500, is_active: true }
    });
    products.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to fetch products:', err);
  }
};

const showAdjustmentDetailModal = (adj) => {
  detailLog.value = adj;
  showDetailModal.value = true;
};

// Form Opening
const openAdjustmentModal = () => {
  productSearchQuery.value = '';
  showProductDropdown.value = false;
  selectedProductDetail.value = null;
  formErrors.value = [];
  
  // Set default warehouse checked
  const defaultWh = warehouses.value.find(wh => wh.is_default);
  checkedWarehouseIds.value = defaultWh ? [defaultWh.id] : (warehouses.value[0] ? [warehouses.value[0].id] : []);
  
  metadataForm.value = {
    reason: '',
    notes: '',
    batch_number: '',
    expiry_date: '',
    attachment: null
  };
  globalAdjustmentType.value = '';
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
};

// Search Dropdown Product selection
const selectProduct = async (product) => {
  showProductDropdown.value = false;
  productSearchQuery.value = product.name;
  
  // Get product complete detail with warehouse inventories
  try {
    const res = await axios.get(`/api/products/${product.id}`);
    selectedProductDetail.value = res.data;
    
    // Initialize matrices
    initAdjustmentMatrices();
  } catch (err) {
    console.error('Failed loading product details:', err);
    showFeedback('Failed to load item configuration.', 'bg-rose-50 text-rose-800 border-rose-200');
  }
};

const clearSelectedProduct = () => {
  selectedProductDetail.value = null;
  productSearchQuery.value = '';
  standardAdjustments.value = {};
  variableAdjustments.value = {};
};

// Matrix Init
const initAdjustmentMatrices = () => {
  const p = selectedProductDetail.value;
  if (!p) return;

  standardAdjustments.value = {};
  variableAdjustments.value = {};

  warehouses.value.forEach(wh => {
    // Standard product
    standardAdjustments.value[wh.id] = {
      warehouse_id: wh.id,
      adjustment_type: 'increase',
      quantity_adjusted: 0
    };

    // Variable product
    if (p.variations) {
      p.variations.forEach(variant => {
        variableAdjustments.value[`${variant.id}-${wh.id}`] = {
          warehouse_id: wh.id,
          product_variation_id: variant.id,
          adjustment_type: 'increase',
          quantity_adjusted: 0
        };
      });
    }
  });
};

const applyGlobalAdjustmentType = () => {
  if (!globalAdjustmentType.value) return;
  
  // Apply globally to all matrix rows
  Object.keys(variableAdjustments.value).forEach(key => {
    variableAdjustments.value[key].adjustment_type = globalAdjustmentType.value;
  });
};

// Helper stock retrievers
const getWarehouseName = (id) => {
  const wh = warehouses.value.find(w => w.id === id);
  return wh ? wh.name : 'Unknown';
};

const getStandardCurrentStock = (whId) => {
  const p = selectedProductDetail.value;
  if (!p || !p.warehouses) return 0;
  const whAlloc = p.warehouses.find(w => w.id === whId);
  return whAlloc ? (whAlloc.opening_stock || 0) : 0;
};

const getVariantCurrentStock = (variant, whId) => {
  if (!variant || !variant.warehouse_stocks) return 0;
  return variant.warehouse_stocks[whId] || 0;
};

const calculateFinalStock = (current, type, qty) => {
  const parsedQty = parseInt(qty) || 0;
  if (type === 'increase') return current + parsedQty;
  if (type === 'decrease') return Math.max(0, current - parsedQty);
  if (type === 'recount') return parsedQty;
  return current;
};

const handleFileAttachment = (e) => {
  const file = e.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert('File exceeds 5MB limit.');
      return;
    }
    metadataForm.value.attachment = file;
  }
};

// SUBMIT BULK ADJUSTMENT
const submitAdjustmentForm = async () => {
  if (checkedWarehouseIds.value.length === 0) {
    formErrors.value = ['Please select at least one warehouse location.'];
    return;
  }

  submitting.value = true;
  formErrors.value = [];

  try {
    const list = [];
    const isVariable = selectedProductDetail.value.has_variations;

    if (!isVariable) {
      // Collect standard adjustments
      checkedWarehouseIds.value.forEach(whId => {
        const item = standardAdjustments.value[whId];
        if (item.quantity_adjusted > 0 || item.adjustment_type === 'recount') {
          list.push({
            warehouse_id: whId,
            product_variation_id: null,
            adjustment_type: item.adjustment_type,
            quantity_adjusted: item.quantity_adjusted || 0
          });
        }
      });
    } else {
      // Collect variable adjustments
      selectedProductDetail.value.variations.forEach(variant => {
        checkedWarehouseIds.value.forEach(whId => {
          const item = variableAdjustments.value[`${variant.id}-${whId}`];
          if (item.quantity_adjusted > 0 || item.adjustment_type === 'recount') {
            list.push({
              warehouse_id: whId,
              product_variation_id: variant.id,
              adjustment_type: item.adjustment_type,
              quantity_adjusted: item.quantity_adjusted || 0
            });
          }
        });
      });
    }

    if (list.length === 0) {
      formErrors.value = ['Please enter an adjustment quantity greater than 0 on at least one item row.'];
      submitting.value = false;
      return;
    }

    // Setup Form Data
    const formData = new FormData();
    formData.append('product_id', selectedProductDetail.value.id);
    formData.append('reason', metadataForm.value.reason);
    formData.append('notes', metadataForm.value.notes || '');
    formData.append('batch_number', metadataForm.value.batch_number || '');
    formData.append('expiry_date', metadataForm.value.expiry_date || '');
    if (metadataForm.value.attachment) {
      formData.append('attachment', metadataForm.value.attachment);
    }

    // Append adjustments list
    list.forEach((adj, index) => {
      formData.append(`adjustments[${index}][warehouse_id]`, adj.warehouse_id);
      if (adj.product_variation_id) {
        formData.append(`adjustments[${index}][product_variation_id]`, adj.product_variation_id);
      }
      formData.append(`adjustments[${index}][adjustment_type]`, adj.adjustment_type);
      formData.append(`adjustments[${index}][quantity_adjusted]`, adj.quantity_adjusted);
    });

    const res = await axios.post('/api/inventory-adjustments', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    showFeedback('Stock adjusted successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    closeFormModal();
    fetchAdjustments(1);
    fetchSummary();
    fetchProducts();
  } catch (err) {
    console.error(err);
    if (err.response?.status === 422) {
      formErrors.value = Object.values(err.response.data.errors || {}).flat();
    } else {
      formErrors.value = [err.response?.data?.message || 'Failed to submit inventory adjustments.'];
    }
  } finally {
    submitting.value = false;
  }
};

// UPLOADING FLOW
const handleFileUpload = (e) => {
  let file = e.type === 'drop' ? e.dataTransfer.files[0] : e.target.files[0];
  if (file && (
    file.name.endsWith('.xlsx') || 
    file.name.endsWith('.xls') || 
    file.name.endsWith('.csv')
  )) {
    uploadFile.value = file;
  } else {
    alert('Please import a valid Microsoft Excel sheet or CSV file.');
  }
};

const processUpload = async () => {
  if (!uploadFile.value) return;
  isUploading.value = true;
  
  try {
    const formData = new FormData();
    formData.append('file', uploadFile.value);

    await axios.post('/api/inventory-adjustments/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    showFeedback('File adjustment uploaded successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    showUploadModal.value = false;
    uploadFile.value = null;

    fetchSummary();
    fetchAdjustments(1);
    fetchProducts();
  } catch (error) {
    console.error(error);
    alert(error.response?.data?.message || 'Error occurred while uploading the document.');
  } finally {
    isUploading.value = false;
  }
};

const showFeedback = (msg, cls) => {
  feedbackMsg.value = msg;
  feedbackClass.value = cls;
  setTimeout(() => {
    if (feedbackMsg.value === msg) {
      feedbackMsg.value = '';
    }
  }, 4000);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString(undefined, {
    year: 'numeric', month: 'short', day: 'numeric'
  });
};

onMounted(() => {
  fetchSummary();
  fetchAdjustments(1);
  fetchWarehouses();
  fetchProducts();
});
</script>

<style scoped>
/* Custom Scrollbars */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 8px;
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
