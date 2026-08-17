<template>
  <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-black/60 backdrop-blur-sm transition-all duration-200" @click.self="$emit('close')">
    <div class="relative mx-auto border border-slate-200/80 dark:border-zinc-800 w-full max-w-7xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 transition-all duration-300 z-10 max-h-[92vh] flex flex-col overflow-hidden my-auto" @click.stop>
      
      <!-- Modal Top Header Bar -->
      <div class="px-6 py-4 bg-white dark:bg-zinc-900 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <div class="flex items-center space-x-2">
              <h2 class="text-lg font-black text-slate-800 dark:text-zinc-100 tracking-tight">Supplier General Ledger</h2>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 uppercase tracking-wider">
                Statement
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 font-medium">
              Real-time statement for <span class="font-bold text-slate-700 dark:text-zinc-200">{{ activeSupplier?.name || supplier?.name }}</span>
              <span v-if="activeSupplier?.company_name" class="text-slate-400 dark:text-zinc-500"> ({{ activeSupplier.company_name }})</span>
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <!-- PDF Export Button -->
          <button
            @click="downloadPDF"
            :disabled="downloadingPdf"
            class="px-3.5 py-1.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hover:shadow flex items-center space-x-1.5 cursor-pointer disabled:opacity-50"
            title="Download PDF Ledger Report"
          >
            <svg v-if="!downloadingPdf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <svg v-else class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ downloadingPdf ? 'Generating PDF...' : 'PDF' }}</span>
          </button>

          <!-- Floating Date Range Picker Component -->
          <FloatingDateRangePicker
            v-model="dateRange"
            @update:modelValue="handleDateFilterChange"
          />

          <!-- Close Modal Button -->
          <button
            @click="$emit('close')"
            class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-all flex items-center justify-center cursor-pointer ml-2"
          >
            ✕
          </button>
        </div>
      </div>

      <!-- Main Body Container: Left 25% (col-3) & Right 75% (col-9) -->
      <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50 dark:bg-zinc-950">
        <div class="grid grid-cols-12 gap-6">
          
          <!-- LEFT SIDE: 25% Width (col-span-12 lg:col-span-3) - Supplier Basic Info -->
          <div class="col-span-12 lg:col-span-3 space-y-4">
            <!-- Profile Info Card -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl p-5 shadow-xs relative overflow-hidden">
              <div class="absolute -top-12 -right-12 w-28 h-28 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>
              
              <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 text-white font-black text-xl flex items-center justify-center shadow-md shadow-indigo-500/20 mb-3 border-2 border-white dark:border-zinc-800">
                  {{ getInitials(activeSupplier?.name || supplier?.name) }}
                </div>
                <h3 class="text-base font-extrabold text-slate-800 dark:text-zinc-100 line-clamp-1">
                  {{ activeSupplier?.name || supplier?.name || 'Supplier' }}
                </h3>
                <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mt-0.5 line-clamp-1">
                  {{ activeSupplier?.company_name || supplier?.company_name || 'Individual Supplier' }}
                </p>
                <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
                  ID: #{{ strPad(activeSupplier?.id || supplier?.id) }}
                </div>
              </div>

              <!-- Contact & Details Grid -->
              <div class="mt-5 pt-4 border-t border-slate-100 dark:border-zinc-800/80 space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Email</span>
                  <span class="font-bold text-slate-700 dark:text-zinc-200 truncate max-w-[140px] text-right" :title="activeSupplier?.email">
                    {{ activeSupplier?.email || supplier?.email || 'N/A' }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Phone</span>
                  <span class="font-bold text-slate-700 dark:text-zinc-200">
                    {{ activeSupplier?.phone || supplier?.phone || 'N/A' }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">City/Location</span>
                  <span class="font-bold text-slate-700 dark:text-zinc-200">
                    {{ activeSupplier?.city || supplier?.city || 'N/A' }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Payment Terms</span>
                  <span class="font-bold text-slate-700 dark:text-zinc-200">
                    {{ (activeSupplier?.payment_terms_days || supplier?.payment_terms_days) ? (activeSupplier?.payment_terms_days || supplier?.payment_terms_days) + ' days' : 'Standard' }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Credit Limit</span>
                  <span class="font-bold text-indigo-600 dark:text-indigo-400">
                    {{ currencySymbol }}{{ formatAmount(activeSupplier?.credit_limit || supplier?.credit_limit || 0) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Advance & Due Balances Box -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl p-4 shadow-xs space-y-3">
              <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 flex items-center justify-between">
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Advance Balance</p>
                  <p class="text-sm font-black text-emerald-700 dark:text-emerald-300 mt-0.5">
                    {{ currencySymbol }}{{ formatAmount(activeSupplier?.advance_balance || supplier?.advance_balance || 0) }}
                  </p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                  💰
                </div>
              </div>

              <div class="p-3 rounded-xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-900/40 flex items-center justify-between">
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Outstanding Due</p>
                  <p class="text-sm font-black text-rose-700 dark:text-rose-300 mt-0.5">
                    {{ currencySymbol }}{{ formatAmount(activeSupplier?.due_amount || supplier?.due_amount || 0) }}
                  </p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                  ⚠️
                </div>
              </div>
            </div>

          </div>

          <!-- RIGHT SIDE: 75% Width (col-span-12 lg:col-span-9) - Stats Cards & 3 Datatables -->
          <div class="col-span-12 lg:col-span-9 space-y-6">
            
            <!-- Top Stats Cards: Payment Pending, Payment Made, Balance -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Stats 1: Payment Pending -->
              <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs relative overflow-hidden group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-500 rounded-l-2xl"></div>
                <div class="flex items-center justify-between pl-2">
                  <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Payment Pending</p>
                    <h4 class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">
                      {{ currencySymbol }}{{ formatAmount(stats.paymentPending) }}
                    </h4>
                  </div>
                  <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Stats 2: Payment Made -->
              <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs relative overflow-hidden group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500 rounded-l-2xl"></div>
                <div class="flex items-center justify-between pl-2">
                  <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Payment Made</p>
                    <h4 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                      {{ currencySymbol }}{{ formatAmount(stats.paymentMade) }}
                    </h4>
                  </div>
                  <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Stats 3: Balance -->
              <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs relative overflow-hidden group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-600 rounded-l-2xl"></div>
                <div class="flex items-center justify-between pl-2">
                  <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Closing Balance</p>
                    <h4 class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                      {{ currencySymbol }}{{ formatAmount(stats.balance) }}
                    </h4>
                  </div>
                  <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- TABLE 1: Purchase Orders -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs">
              <div class="px-5 py-3.5 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-900/50">
                <div class="flex items-center space-x-2">
                  <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                  <h3 class="text-sm font-black text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Purchase Orders</h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                    {{ ordersPagination.total || 0 }} total
                  </span>
                </div>

                <div class="flex items-center space-x-3">
                  <!-- Search -->
                  <div class="relative w-48 sm:w-56">
                    <input
                      v-model="ordersSearch"
                      @input="debouncedFetchOrders"
                      type="text"
                      placeholder="Search PO #, status..."
                      class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-zinc-200 placeholder-slate-400"
                    />
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                  </div>

                  <!-- Limit Selection Dropdown -->
                  <div class="flex items-center space-x-1.5 text-xs text-slate-500 dark:text-zinc-400">
                    <span class="hidden sm:inline text-[11px] font-medium">Show</span>
                    <select
                      v-model="ordersPerPage"
                      @change="fetchOrders(1)"
                      class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-2 py-1 text-xs font-bold text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                    >
                      <option :value="5">5</option>
                      <option :value="10">10</option>
                      <option :value="25">25</option>
                      <option :value="50">50</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Orders Table Content -->
              <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 font-bold uppercase tracking-wider border-b border-slate-200/80 dark:border-zinc-800 text-[10px]">
                      <th @click="toggleOrdersSort('po_number')" class="py-2.5 px-4 cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>PO #</span>
                          <span v-if="ordersSortKey === 'po_number'">{{ ordersSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                      <th @click="toggleOrdersSort('order_date')" class="py-2.5 px-4 cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Date</span>
                          <span v-if="ordersSortKey === 'order_date'">{{ ordersSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                      <th class="py-2.5 px-4">Purchaser</th>
                      <th @click="toggleOrdersSort('status')" class="py-2.5 px-4 text-center cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center justify-center space-x-1">
                          <span>Status</span>
                          <span v-if="ordersSortKey === 'status'">{{ ordersSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                      <th @click="toggleOrdersSort('total_amount')" class="py-2.5 px-4 text-right cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Total</span>
                          <span v-if="ordersSortKey === 'total_amount'">{{ ordersSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                      <th class="py-2.5 px-4 text-right">Paid</th>
                      <th @click="toggleOrdersSort('due')" class="py-2.5 px-4 text-right cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Due</span>
                          <span v-if="ordersSortKey === 'due'">{{ ordersSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-slate-700 dark:text-zinc-200">
                    <tr v-if="ordersLoading">
                      <td colspan="7" class="py-8 text-center text-slate-400 dark:text-zinc-500">
                        <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-indigo-500 border-t-transparent"></div>
                        <p class="mt-1 text-xs">Loading purchase orders...</p>
                      </td>
                    </tr>
                    <tr v-else-if="!sortedPurchaseOrders.length">
                      <td colspan="7" class="py-8 text-center text-slate-400 dark:text-zinc-500 italic">
                        No purchase orders found for this supplier.
                      </td>
                    </tr>
                    <tr v-else v-for="po in sortedPurchaseOrders" :key="po.id" class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                      <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-400">{{ po.po_number }}</td>
                      <td class="py-3 px-4 whitespace-nowrap text-slate-500 dark:text-zinc-400">{{ formatDate(po.order_date) }}</td>
                      <td class="py-3 px-4 font-medium">{{ po.user?.name || 'System' }}</td>
                      <td class="py-3 px-4 text-center">
                        <span :class="getPoStatusBadge(po.status)" class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wide">
                          {{ po.status || 'PENDING' }}
                        </span>
                      </td>
                      <td class="py-3 px-4 text-right font-black">{{ currencySymbol }}{{ formatAmount(po.total_amount || po.grand_total || 0) }}</td>
                      <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                        {{ currencySymbol }}{{ formatAmount(po.amount_paid || po.paid_amount || 0) }}
                      </td>
                      <td class="py-3 px-4 text-right font-bold" :class="getPoDue(po) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400'">
                        {{ currencySymbol }}{{ formatAmount(getPoDue(po)) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Orders Pagination Bar -->
              <div v-if="ordersPagination.last_page > 1" class="px-4 py-2.5 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-zinc-400 font-medium">
                  Showing {{ ordersPagination.from || 0 }} to {{ ordersPagination.to || 0 }} of {{ ordersPagination.total || 0 }} orders
                </span>
                <div class="flex items-center space-x-1">
                  <button
                    @click="fetchOrders(ordersPagination.current_page - 1)"
                    :disabled="ordersPagination.current_page === 1"
                    class="px-2 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 disabled:opacity-40 cursor-pointer font-bold"
                  >
                    «
                  </button>
                  <span class="px-2 font-bold text-slate-700 dark:text-zinc-200">
                    {{ ordersPagination.current_page }} / {{ ordersPagination.last_page }}
                  </span>
                  <button
                    @click="fetchOrders(ordersPagination.current_page + 1)"
                    :disabled="ordersPagination.current_page === ordersPagination.last_page"
                    class="px-2 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 disabled:opacity-40 cursor-pointer font-bold"
                  >
                    »
                  </button>
                </div>
              </div>
            </div>

            <!-- TABLE 2: Purchase Returns -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs">
              <div class="px-5 py-3.5 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-900/50">
                <div class="flex items-center space-x-2">
                  <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                  <h3 class="text-sm font-black text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Purchase Returns</h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400">
                    {{ returnsPagination.total || 0 }} total
                  </span>
                </div>

                <div class="flex items-center space-x-3">
                  <!-- Search -->
                  <div class="relative w-48 sm:w-56">
                    <input
                      v-model="returnsSearch"
                      @input="debouncedFetchReturns"
                      type="text"
                      placeholder="Search Return #, notes..."
                      class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-zinc-200 placeholder-slate-400"
                    />
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                  </div>

                  <!-- Limit Selection Dropdown -->
                  <div class="flex items-center space-x-1.5 text-xs text-slate-500 dark:text-zinc-400">
                    <span class="hidden sm:inline text-[11px] font-medium">Show</span>
                    <select
                      v-model="returnsPerPage"
                      @change="fetchReturns(1)"
                      class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-2 py-1 text-xs font-bold text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                    >
                      <option :value="5">5</option>
                      <option :value="10">10</option>
                      <option :value="25">25</option>
                      <option :value="50">50</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Returns Table Content -->
              <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 font-bold uppercase tracking-wider border-b border-slate-200/80 dark:border-zinc-800 text-[10px]">
                      <th @click="toggleReturnsSort('return_number')" class="py-2.5 px-4 cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Return #</span>
                          <span v-if="returnsSortKey === 'return_number'">{{ returnsSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                      <th @click="toggleReturnsSort('return_date')" class="py-2.5 px-4 cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Date</span>
                          <span v-if="returnsSortKey === 'return_date'">{{ returnsSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                      <th class="py-2.5 px-4">Original PO #</th>
                      <th class="py-2.5 px-4 text-center">Refund Method</th>
                      <th @click="toggleReturnsSort('total_amount')" class="py-2.5 px-4 text-right cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700/50 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Refund Amount</span>
                          <span v-if="returnsSortKey === 'total_amount'">{{ returnsSortOrder === 'asc' ? '↑' : '↓' }}</span>
                        </div>
                      </th>
                      <th class="py-2.5 px-4">Notes / Reason</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-slate-700 dark:text-zinc-200">
                    <tr v-if="returnsLoading">
                      <td colspan="6" class="py-8 text-center text-slate-400 dark:text-zinc-500">
                        <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-rose-500 border-t-transparent"></div>
                        <p class="mt-1 text-xs">Loading purchase returns...</p>
                      </td>
                    </tr>
                    <tr v-else-if="!sortedPurchaseReturns.length">
                      <td colspan="6" class="py-8 text-center text-slate-400 dark:text-zinc-500 italic">
                        No purchase returns recorded for this supplier.
                      </td>
                    </tr>
                    <tr v-else v-for="ret in sortedPurchaseReturns" :key="ret.id" class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                      <td class="py-3 px-4 font-bold text-rose-600 dark:text-rose-400">{{ ret.return_number }}</td>
                      <td class="py-3 px-4 whitespace-nowrap text-slate-500 dark:text-zinc-400">{{ formatDate(ret.return_date) }}</td>
                      <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-400">
                        {{ ret.purchase_order?.po_number || ret.purchase_order_id || 'N/A' }}
                      </td>
                      <td class="py-3 px-4 text-center font-bold uppercase text-[10px] text-slate-600 dark:text-zinc-400">
                        {{ ret.payment_method || 'CREDIT NOTE' }}
                      </td>
                      <td class="py-3 px-4 text-right font-black text-rose-600 dark:text-rose-400">
                        {{ currencySymbol }}{{ formatAmount(ret.total_amount || 0) }}
                      </td>
                      <td class="py-3 px-4 text-slate-500 dark:text-zinc-400 truncate max-w-[200px]" :title="ret.reason || ret.notes">
                        {{ ret.reason || ret.notes || 'No reason specified' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Returns Pagination Bar -->
              <div v-if="returnsPagination.last_page > 1" class="px-4 py-2.5 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-zinc-400 font-medium">
                  Showing {{ returnsPagination.from || 0 }} to {{ returnsPagination.to || 0 }} of {{ returnsPagination.total || 0 }} returns
                </span>
                <div class="flex items-center space-x-1">
                  <button
                    @click="fetchReturns(returnsPagination.current_page - 1)"
                    :disabled="returnsPagination.current_page === 1"
                    class="px-2 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 disabled:opacity-40 cursor-pointer font-bold"
                  >
                    «
                  </button>
                  <span class="px-2 font-bold text-slate-700 dark:text-zinc-200">
                    {{ returnsPagination.current_page }} / {{ returnsPagination.last_page }}
                  </span>
                  <button
                    @click="fetchReturns(returnsPagination.current_page + 1)"
                    :disabled="returnsPagination.current_page === returnsPagination.last_page"
                    class="px-2 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 disabled:opacity-40 cursor-pointer font-bold"
                  >
                    »
                  </button>
                </div>
              </div>
            </div>

            <!-- TABLE 3: Transactions -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs">
              <div class="px-5 py-3.5 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-900/50">
                <div class="flex items-center space-x-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  <h3 class="text-sm font-black text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Transactions</h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                    {{ transactionsPagination.total || 0 }} total
                  </span>
                </div>

                <div class="flex items-center space-x-3">
                  <!-- Search -->
                  <div class="relative w-48 sm:w-56">
                    <input
                      v-model="transactionsSearch"
                      @input="debouncedFetchTransactions"
                      type="text"
                      placeholder="Search ref, desc..."
                      class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-zinc-200 placeholder-slate-400"
                    />
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                  </div>

                  <!-- Limit Selection Dropdown -->
                  <div class="flex items-center space-x-1.5 text-xs text-slate-500 dark:text-zinc-400">
                    <span class="hidden sm:inline text-[11px] font-medium">Show</span>
                    <select
                      v-model="transactionsPerPage"
                      @change="fetchTransactions(1)"
                      class="bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-2 py-1 text-xs font-bold text-slate-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                    >
                      <option :value="5">5</option>
                      <option :value="10">10</option>
                      <option :value="25">25</option>
                      <option :value="50">50</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Transactions Table Content -->
              <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 font-bold uppercase tracking-wider border-b border-slate-200/80 dark:border-zinc-800 text-[10px]">
                      <th class="py-2.5 px-4">Date</th>
                      <th class="py-2.5 px-4">Reference</th>
                      <th class="py-2.5 px-4">Description</th>
                      <th class="py-2.5 px-4 text-center">Type</th>
                      <th class="py-2.5 px-4 text-right">Debit ($)</th>
                      <th class="py-2.5 px-4 text-right">Credit ($)</th>
                      <th class="py-2.5 px-4 text-right">Balance ($)</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-slate-700 dark:text-zinc-200">
                    <tr v-if="transactionsLoading">
                      <td colspan="7" class="py-8 text-center text-slate-400 dark:text-zinc-500">
                        <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-emerald-500 border-t-transparent"></div>
                        <p class="mt-1 text-xs">Loading transactions...</p>
                      </td>
                    </tr>
                    <tr v-else-if="!transactionsList.length">
                      <td colspan="7" class="py-8 text-center text-slate-400 dark:text-zinc-500 italic">
                        No transactions registered for this supplier.
                      </td>
                    </tr>
                    <tr v-else v-for="(tx, idx) in transactionsList" :key="idx" class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                      <td class="py-3 px-4 whitespace-nowrap text-slate-500 dark:text-zinc-400 font-medium">{{ formatDate(tx.date) }}</td>
                      <td class="py-3 px-4 font-bold text-slate-800 dark:text-zinc-100">{{ tx.reference }}</td>
                      <td class="py-3 px-4 text-slate-600 dark:text-zinc-300 max-w-[220px] truncate" :title="tx.description">{{ tx.description }}</td>
                      <td class="py-3 px-4 text-center">
                        <span :class="tx.debit > 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wide">
                          {{ tx.type }}
                        </span>
                      </td>
                      <td class="py-3 px-4 text-right font-bold text-rose-600 dark:text-rose-400">
                        {{ tx.debit > 0 ? currencySymbol + formatAmount(tx.debit) : '-' }}
                      </td>
                      <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                        {{ tx.credit > 0 ? currencySymbol + formatAmount(tx.credit) : '-' }}
                      </td>
                      <td class="py-3 px-4 text-right font-black text-slate-800 dark:text-zinc-100">
                        {{ currencySymbol }}{{ formatAmount(tx.running_balance) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Transactions Pagination Bar -->
              <div v-if="transactionsPagination.last_page > 1" class="px-4 py-2.5 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-zinc-400 font-medium">
                  Showing {{ transactionsPagination.from || 0 }} to {{ transactionsPagination.to || 0 }} of {{ transactionsPagination.total || 0 }} transactions
                </span>
                <div class="flex items-center space-x-1">
                  <button
                    @click="fetchTransactions(transactionsPagination.current_page - 1)"
                    :disabled="transactionsPagination.current_page === 1"
                    class="px-2 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 disabled:opacity-40 cursor-pointer font-bold"
                  >
                    «
                  </button>
                  <span class="px-2 font-bold text-slate-700 dark:text-zinc-200">
                    {{ transactionsPagination.current_page }} / {{ transactionsPagination.last_page }}
                  </span>
                  <button
                    @click="fetchTransactions(transactionsPagination.current_page + 1)"
                    :disabled="transactionsPagination.current_page === transactionsPagination.last_page"
                    class="px-2 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 disabled:opacity-40 cursor-pointer font-bold"
                  >
                    »
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-3.5 bg-white dark:bg-zinc-900 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-between shrink-0">
        <p class="text-xs text-slate-500 dark:text-zinc-400 font-medium">
          Default period: <strong class="text-slate-700 dark:text-zinc-300">This Month</strong>
        </p>
        <button
          @click="$emit('close')"
          class="px-5 py-2 bg-slate-200 dark:bg-zinc-800 hover:bg-slate-300 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-xl text-xs font-bold transition-all cursor-pointer"
        >
          Close
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';
import { debounce } from '@/utils/debounce';
import FloatingDateRangePicker from '@/components/common/FloatingDateRangePicker.vue';

const props = defineProps({
  show: Boolean,
  supplier: Object
});

const emit = defineEmits(['close']);

const currencyStore = useCurrencyStore();

const currencySymbol = computed(() => {
  return currencyStore.symbol || '$';
});

const getFirstDayOfMonth = () => {
  const date = new Date();
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  return `${y}-${m}-01`;
};

const getLastDayOfMonth = () => {
  const date = new Date();
  const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
  const y = lastDay.getFullYear();
  const m = String(lastDay.getMonth() + 1).padStart(2, '0');
  const d = String(lastDay.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
};

const dateRange = ref({
  start_date: getFirstDayOfMonth(),
  end_date: getLastDayOfMonth(),
  start: getFirstDayOfMonth(),
  end: getLastDayOfMonth()
});

const getStartDate = () => {
  if (dateRange.value?.start_date !== undefined) return dateRange.value.start_date;
  if (dateRange.value?.start !== undefined) return dateRange.value.start;
  return getFirstDayOfMonth();
};

const getEndDate = () => {
  if (dateRange.value?.end_date !== undefined) return dateRange.value.end_date;
  if (dateRange.value?.end !== undefined) return dateRange.value.end;
  return getLastDayOfMonth();
};

const downloadingPdf = ref(false);

const downloadPDF = async () => {
  if (!props.supplier?.id) return;
  downloadingPdf.value = true;
  try {
    const start = getStartDate();
    const end = getEndDate();
    
    const response = await api.get(`/suppliers/${props.supplier.id}/ledger/pdf`, {
      params: {
        start_date: start,
        end_date: end
      },
      responseType: 'blob'
    });

    const blobData = response.data instanceof Blob 
      ? response.data 
      : new Blob([response.data], { type: 'application/pdf' });

    const url = window.URL.createObjectURL(blobData);
    const link = document.createElement('a');
    link.href = url;
    const name = (activeSupplier.value?.name || props.supplier?.name || 'supplier').toLowerCase().replace(/[^a-z0-9]/g, '_');
    const dateStr = new Date().toISOString().split('T')[0];
    link.setAttribute('download', `supplier_ledger_${name}_${dateStr}.pdf`);
    document.body.appendChild(link);
    link.click();
    setTimeout(() => {
      if (document.body.contains(link)) {
        document.body.removeChild(link);
      }
      window.URL.revokeObjectURL(url);
    }, 200);
  } catch (err) {
    console.error('Error downloading supplier ledger PDF:', err);
  } finally {
    downloadingPdf.value = false;
  }
};

const activeSupplier = ref(null);

const stats = ref({
  paymentPending: 0,
  paymentMade: 0,
  balance: 0
});

// Purchase Orders State
const ordersLoading = ref(false);
const purchaseOrders = ref([]);
const ordersSearch = ref('');
const ordersPerPage = ref(5);
const ordersSortKey = ref('order_date');
const ordersSortOrder = ref('desc');
const ordersPagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

// Purchase Returns State
const returnsLoading = ref(false);
const purchaseReturns = ref([]);
const returnsSearch = ref('');
const returnsPerPage = ref(5);
const returnsSortKey = ref('return_date');
const returnsSortOrder = ref('desc');
const returnsPagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

// Transactions State
const transactionsLoading = ref(false);
const transactionsList = ref([]);
const transactionsSearch = ref('');
const transactionsPerPage = ref(5);
const transactionsPagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

// Fetch Overall Ledger Summary
const fetchLedgerStats = async () => {
  if (!props.supplier?.id) return;
  try {
    const response = await api.get(`/suppliers/${props.supplier.id}/ledger`, {
      params: {
        start_date: getStartDate(),
        end_date: getEndDate()
      }
    });

    if (response.data) {
      activeSupplier.value = response.data.supplier || props.supplier;
      stats.value = {
        paymentPending: response.data.stats?.payment_pending || 0,
        paymentMade: response.data.stats?.payment_made || 0,
        balance: response.data.closing_balance || 0
      };
    }
  } catch (err) {
    console.error('Error fetching supplier ledger summary:', err);
  }
};

// Fetch Purchase Orders
const fetchOrders = async (page = 1) => {
  if (!props.supplier?.id) return;
  ordersLoading.value = true;
  try {
    const response = await api.get(`/suppliers/${props.supplier.id}/purchase-orders`, {
      params: {
        page,
        per_page: ordersPerPage.value,
        start_date: getStartDate(),
        end_date: getEndDate(),
        search: ordersSearch.value,
        sort_by: ordersSortKey.value,
        sort_dir: ordersSortOrder.value
      }
    });

    purchaseOrders.value = response.data.data || [];
    ordersPagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      from: response.data.from || 0,
      to: response.data.to || 0,
      total: response.data.total || 0
    };
  } catch (err) {
    console.error('Error fetching supplier purchase orders:', err);
  } finally {
    ordersLoading.value = false;
  }
};

const debouncedFetchOrders = debounce(() => fetchOrders(1), 300);

const toggleOrdersSort = (key) => {
  if (ordersSortKey.value === key) {
    ordersSortOrder.value = ordersSortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    ordersSortKey.value = key;
    ordersSortOrder.value = 'asc';
  }
  fetchOrders(1);
};

const sortedPurchaseOrders = computed(() => {
  return purchaseOrders.value;
});

// Fetch Purchase Returns
const fetchReturns = async (page = 1) => {
  if (!props.supplier?.id) return;
  returnsLoading.value = true;
  try {
    const response = await api.get(`/suppliers/${props.supplier.id}/purchase-returns`, {
      params: {
        page,
        per_page: returnsPerPage.value,
        start_date: getStartDate(),
        end_date: getEndDate(),
        search: returnsSearch.value,
        sort_by: returnsSortKey.value,
        sort_dir: returnsSortOrder.value
      }
    });

    purchaseReturns.value = response.data.data || [];
    returnsPagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      from: response.data.from || 0,
      to: response.data.to || 0,
      total: response.data.total || 0
    };
  } catch (err) {
    console.error('Error fetching supplier purchase returns:', err);
  } finally {
    returnsLoading.value = false;
  }
};

const debouncedFetchReturns = debounce(() => fetchReturns(1), 300);

const toggleReturnsSort = (key) => {
  if (returnsSortKey.value === key) {
    returnsSortOrder.value = returnsSortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    returnsSortKey.value = key;
    returnsSortOrder.value = 'asc';
  }
  fetchReturns(1);
};

const sortedPurchaseReturns = computed(() => {
  return purchaseReturns.value;
});

// Fetch Transactions
const fetchTransactions = async (page = 1) => {
  if (!props.supplier?.id) return;
  transactionsLoading.value = true;
  try {
    const response = await api.get(`/suppliers/${props.supplier.id}/transactions`, {
      params: {
        page,
        per_page: transactionsPerPage.value,
        start_date: getStartDate(),
        end_date: getEndDate(),
        search: transactionsSearch.value
      }
    });

    transactionsList.value = response.data.data || [];
    transactionsPagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      from: response.data.from || 0,
      to: response.data.to || 0,
      total: response.data.total || 0
    };
  } catch (err) {
    console.error('Error fetching supplier transactions:', err);
  } finally {
    transactionsLoading.value = false;
  }
};

const debouncedFetchTransactions = debounce(() => fetchTransactions(1), 300);

// Reload all data
const loadAllLedgerData = () => {
  fetchLedgerStats();
  fetchOrders(1);
  fetchReturns(1);
  fetchTransactions(1);
};

const handleDateFilterChange = () => {
  loadAllLedgerData();
};

const getPoDue = (po) => {
  const total = parseFloat(po.total_amount || po.grand_total || 0);
  const paid = parseFloat(po.amount_paid || po.paid_amount || 0);
  return Math.max(0, total - paid);
};

const getPoStatusBadge = (status) => {
  const s = (status || '').toLowerCase();
  if (['received', 'completed', 'paid'].includes(s)) return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300';
  if (['cancelled', 'void'].includes(s)) return 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300';
  return 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300';
};

const formatAmount = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num || 0);
};

const formatDate = (d) => {
  if (!d) return 'N/A';
  return new Date(d).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getInitials = (name) => {
  if (!name) return 'SP';
  return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
};

const strPad = (val) => {
  if (!val) return '0000';
  return String(val).padStart(4, '0');
};

watch(() => props.show, (newVal) => {
  if (newVal && props.supplier) {
    activeSupplier.value = props.supplier;
    loadAllLedgerData();
  }
});

watch(() => props.supplier, (newSupplier) => {
  if (newSupplier && props.show) {
    activeSupplier.value = newSupplier;
    loadAllLedgerData();
  }
});

onMounted(() => {
  if (props.show && props.supplier) {
    activeSupplier.value = props.supplier;
    loadAllLedgerData();
  }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.4);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}
</style>
