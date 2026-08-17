<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div
      class="relative w-full max-w-7xl max-h-[92vh] bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-zinc-800 flex flex-col overflow-hidden transition-all duration-200 my-auto"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shrink-0">
        <div class="flex items-center space-x-3">
          <div class="p-2.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <div class="flex items-center space-x-2">
              <h3 class="text-lg font-black text-slate-900 dark:text-zinc-100 tracking-tight">Customer General Ledger</h3>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                {{ formatCustomerType(activeCustomer?.type || customer?.type) }}
              </span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">
              Detailed financial statements, invoice history, returns, and transaction logs
            </p>
          </div>
        </div>

        <!-- Top Right Filter & Controls -->
        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
          <FloatingDateRangePicker
            v-model="dateRange"
            :show-presets="true"
            placeholder="All Time"
            @update:modelValue="fetchAllData"
            @clear="fetchAllData"
          />

          <!-- Download PDF Button -->
          <button
            type="button"
            @click="downloadPDF"
            :disabled="downloadingPdf"
            class="px-3 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl text-xs font-extrabold shadow-xs transition-all flex items-center space-x-1.5 cursor-pointer disabled:opacity-50 shrink-0"
            title="Download General Ledger PDF Report"
          >
            <svg v-if="!downloadingPdf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div v-else class="animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent"></div>
            <span>{{ downloadingPdf ? 'Exporting...' : 'PDF' }}</span>
          </button>

          <button
            type="button"
            @click="$emit('close')"
            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 hover:bg-slate-200 dark:hover:bg-zinc-700 hover:text-slate-900 dark:hover:text-white flex items-center justify-center text-xs font-bold transition-all cursor-pointer shrink-0"
            title="Close modal"
          >
            ✕
          </button>
        </div>
      </div>

      <!-- Modal Body - 25% / 75% Layout -->
      <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 bg-slate-50/50 dark:bg-black/20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

          <!-- LEFT SIDE: 25% Width Column (col-span-3) - Customer Basic Information -->
          <div class="lg:col-span-3 space-y-4">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-5">
              
              <!-- Customer Profile Header -->
              <div class="text-center pb-4 border-b border-slate-100 dark:border-zinc-800">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center font-black text-xl shadow-md mx-auto mb-3">
                  {{ getInitials(activeCustomer?.name || customer?.name) }}
                </div>
                <h4 class="font-extrabold text-slate-900 dark:text-zinc-100 text-base leading-tight">
                  {{ activeCustomer?.name || customer?.name || 'Customer Ledger' }}
                </h4>
                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                  ID: #{{ (activeCustomer?.id || customer?.id || 0).toString().padStart(4, '0') }}
                </p>
              </div>

              <!-- Customer Info Details -->
              <div class="space-y-3.5 text-xs">
                
                <!-- Email -->
                <div class="flex items-start space-x-3">
                  <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 rounded-lg text-slate-500 dark:text-zinc-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-zinc-500">Email Address</p>
                    <p class="font-medium text-slate-800 dark:text-zinc-200 truncate">
                      {{ activeCustomer?.email || customer?.email || 'N/A' }}
                    </p>
                  </div>
                </div>

                <!-- Phone -->
                <div class="flex items-start space-x-3">
                  <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 rounded-lg text-slate-500 dark:text-zinc-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-zinc-500">Phone Number</p>
                    <p class="font-medium text-slate-800 dark:text-zinc-200">
                      {{ activeCustomer?.phone || customer?.phone || 'N/A' }}
                    </p>
                  </div>
                </div>

                <!-- Address -->
                <div class="flex items-start space-x-3">
                  <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 rounded-lg text-slate-500 dark:text-zinc-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-zinc-500">Billing Address</p>
                    <p class="font-medium text-slate-800 dark:text-zinc-200 leading-snug">
                      {{ formatAddress(activeCustomer || customer) }}
                    </p>
                  </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-zinc-800 space-y-2.5">
                  <!-- Credit Limit -->
                  <div class="flex items-start space-x-3">
                    <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 rounded-lg text-slate-500 dark:text-zinc-400 shrink-0">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-zinc-500">Credit Limit</p>
                      <p class="font-extrabold text-slate-900 dark:text-zinc-100">
                        {{ formatCurrency(activeCustomer?.credit_limit || customer?.credit_limit || 0) }}
                      </p>
                    </div>
                  </div>

                  <!-- Wallet Balance -->
                  <div class="flex items-start space-x-3">
                    <div class="p-1.5 bg-slate-200/60 dark:bg-zinc-800 rounded-lg text-slate-500 dark:text-zinc-400 shrink-0">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-zinc-500">Wallet Advance Balance</p>
                      <p class="font-extrabold text-emerald-600 dark:text-emerald-400">
                        {{ formatCurrency(activeCustomer?.wallet_balance || customer?.wallet_balance || 0) }}
                      </p>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- RIGHT SIDE: 75% Width Column (col-span-9) - Basic Stats & DataTables -->
          <div class="lg:col-span-9 space-y-6">
            
            <!-- Basic Stats Cards Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Payment Pending Card -->
              <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-500/15 dark:via-amber-500/5 p-4 rounded-2xl border border-amber-200 dark:border-amber-900/60 shadow-2xs space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black uppercase tracking-wider text-amber-700 dark:text-amber-400">Payment Pending</span>
                  <div class="p-2 bg-amber-500/20 rounded-xl text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <p class="text-2xl font-black text-amber-900 dark:text-amber-200 tracking-tight">
                  {{ formatCurrency(stats.paymentPending) }}
                </p>
                <p class="text-[10px] font-semibold text-amber-600 dark:text-amber-400">Unpaid invoice dues for selected period</p>
              </div>

              <!-- Payment Received Card -->
              <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent dark:from-emerald-500/15 dark:via-emerald-500/5 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-900/60 shadow-2xs space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Payment Received</span>
                  <div class="p-2 bg-emerald-500/20 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <p class="text-2xl font-black text-emerald-900 dark:text-emerald-200 tracking-tight">
                  {{ formatCurrency(stats.paymentReceived) }}
                </p>
                <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Total payments collected in period</p>
              </div>

              <!-- Net Balance Card -->
              <div class="bg-gradient-to-br from-indigo-500/10 via-indigo-500/5 to-transparent dark:from-indigo-500/15 dark:via-indigo-500/5 p-4 rounded-2xl border border-indigo-200 dark:border-indigo-900/60 shadow-2xs space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Ledger Balance</span>
                  <div class="p-2 bg-indigo-500/20 rounded-xl text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5 5 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5 5 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                  </div>
                </div>
                <p class="text-2xl font-black text-indigo-900 dark:text-indigo-200 tracking-tight">
                  {{ formatCurrency(stats.balance) }}
                </p>
                <p class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-400">Closing net ledger position</p>
              </div>
            </div>

            <!-- SECTION 1: Sale Invoices DataTable (AJAX-based with Sorting, Limit & Pagination) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
              <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-800/20">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></div>
                  <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-zinc-200">Sale Invoices</h4>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                    {{ salesTotal }} invoices
                  </span>
                </div>

                <div class="flex items-center space-x-3 w-full sm:w-auto justify-between sm:justify-end">
                  <!-- Limit Selection Dropdown -->
                  <div class="flex items-center space-x-1.5 text-xs font-semibold text-slate-500 dark:text-zinc-400">
                    <span>Show</span>
                    <select
                      v-model="salesPerPage"
                      @change="fetchSales(1)"
                      class="px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer"
                    >
                      <option :value="5">5</option>
                      <option :value="10">10</option>
                      <option :value="25">25</option>
                      <option :value="50">50</option>
                      <option :value="100">100</option>
                    </select>
                    <span>entries</span>
                  </div>

                  <!-- Search Input -->
                  <input
                    type="text"
                    v-model="salesSearch"
                    placeholder="Search invoice..."
                    class="px-2.5 py-1 text-xs bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 w-36 sm:w-44"
                    @input="debounceFetchSales"
                  />
                </div>
              </div>

              <!-- Table Content -->
              <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-xs text-left">
                  <thead class="bg-slate-100/80 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-zinc-700">
                    <tr>
                      <th @click="toggleSalesSort('sale_number')" class="py-3 px-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Invoice #</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'sale_number' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleSalesSort('sale_date')" class="py-3 px-3 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Date</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'sale_date' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleSalesSort('salesman')" class="py-3 px-3 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Salesman</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'salesman' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleSalesSort('status')" class="py-3 px-3 text-center cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center justify-center space-x-1">
                          <span>Status</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'status' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleSalesSort('payment_method')" class="py-3 px-3 text-center cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center justify-center space-x-1">
                          <span>Method</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'payment_method' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleSalesSort('total_amount')" class="py-3 px-3 text-right cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Total</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'total_amount' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleSalesSort('paid_amount')" class="py-3 px-3 text-right cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Paid</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'paid_amount' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleSalesSort('due')" class="py-3 px-4 text-right cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Due</span>
                          <span class="text-[10px] text-indigo-500">
                            {{ salesSortKey === 'due' ? (salesSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                    <tr v-if="salesLoading" class="text-center">
                      <td colspan="8" class="py-8">
                        <div class="flex justify-center items-center space-x-2">
                          <div class="animate-spin rounded-full h-5 w-5 border-2 border-indigo-600 border-t-transparent"></div>
                          <span class="text-xs text-slate-500 dark:text-zinc-400">Loading sale invoices...</span>
                        </div>
                      </td>
                    </tr>
                    <tr v-else-if="sortedSalesInvoices.length === 0" class="text-center">
                      <td colspan="8" class="py-8 text-slate-400 dark:text-zinc-500 text-xs italic">
                        No sale invoices found for this customer in selected period.
                      </td>
                    </tr>
                    <tr
                      v-else
                      v-for="inv in sortedSalesInvoices"
                      :key="inv.id"
                      class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/30 transition-colors"
                    >
                      <td class="py-3 px-4 font-black text-indigo-600 dark:text-indigo-400">
                        {{ inv.sale_number }}
                      </td>
                      <td class="py-3 px-3 text-slate-700 dark:text-zinc-300 font-medium">
                        {{ formatDate(inv.sale_date) }}
                      </td>
                      <td class="py-3 px-3 text-slate-600 dark:text-zinc-400">
                        {{ inv.salesman?.full_name || inv.user?.name || 'N/A' }}
                      </td>
                      <td class="py-3 px-3 text-center">
                        <span :class="getStatusBadgeClass(inv)">
                          {{ getInvoiceStatusLabel(inv) }}
                        </span>
                      </td>
                      <td class="py-3 px-3 text-center text-[10px] font-bold uppercase text-slate-600 dark:text-zinc-400">
                        {{ inv.payment_method || 'Cash' }}
                      </td>
                      <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-zinc-100">
                        {{ formatCurrency(inv.total_amount) }}
                      </td>
                      <td class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                        {{ formatCurrency(inv.paid_amount) }}
                      </td>
                      <td class="py-3 px-4 text-right font-black text-rose-600 dark:text-rose-400">
                        {{ formatCurrency(Math.max(0, parseFloat(inv.total_amount || 0) - parseFloat(inv.paid_amount || 0))) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination Footer -->
              <div v-if="salesTotal > 0" class="px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800/40 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                <span class="text-slate-500 dark:text-zinc-400 font-medium">
                  Showing <strong class="text-slate-700 dark:text-zinc-200">{{ salesPagination.from }}</strong> to <strong class="text-slate-700 dark:text-zinc-200">{{ salesPagination.to }}</strong> of <strong class="text-slate-700 dark:text-zinc-200">{{ salesPagination.total }}</strong> entries
                </span>
                
                <div class="flex items-center space-x-1">
                  <button
                    @click="fetchSales(1)"
                    :disabled="salesPagination.current_page <= 1"
                    class="px-2 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    title="First Page"
                  >
                    «
                  </button>
                  <button
                    @click="fetchSales(salesPagination.current_page - 1)"
                    :disabled="salesPagination.current_page <= 1"
                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                  >
                    Prev
                  </button>
                  <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-extrabold rounded-lg border border-indigo-200 dark:border-indigo-800">
                    {{ salesPagination.current_page }} / {{ salesPagination.last_page }}
                  </span>
                  <button
                    @click="fetchSales(salesPagination.current_page + 1)"
                    :disabled="salesPagination.current_page >= salesPagination.last_page"
                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                  >
                    Next
                  </button>
                  <button
                    @click="fetchSales(salesPagination.last_page)"
                    :disabled="salesPagination.current_page >= salesPagination.last_page"
                    class="px-2 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    title="Last Page"
                  >
                    »
                  </button>
                </div>
              </div>
            </div>

            <!-- SECTION 2: Sale Returns Card & DataTable (AJAX-based with Sorting, Limit & Pagination) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
              <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-800/20">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                  <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-zinc-200">Sale Returns</h4>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">
                    {{ returnsTotal }} returns
                  </span>
                </div>

                <!-- Limit Selection Dropdown -->
                <div class="flex items-center space-x-1.5 text-xs font-semibold text-slate-500 dark:text-zinc-400">
                  <span>Show</span>
                  <select
                    v-model="returnsPerPage"
                    @change="fetchReturns(1)"
                    class="px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 font-bold focus:outline-none focus:ring-1 focus:ring-rose-500 cursor-pointer"
                  >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                  </select>
                  <span>entries</span>
                </div>
              </div>

              <!-- Table Content -->
              <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-xs text-left">
                  <thead class="bg-slate-100/80 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-zinc-700">
                    <tr>
                      <th @click="toggleReturnsSort('sale_number')" class="py-3 px-4 cursor-pointer select-none hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Return #</span>
                          <span class="text-[10px] text-rose-500">
                            {{ returnsSortKey === 'sale_number' ? (returnsSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleReturnsSort('sale_date')" class="py-3 px-3 cursor-pointer select-none hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Date</span>
                          <span class="text-[10px] text-rose-500">
                            {{ returnsSortKey === 'sale_date' ? (returnsSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleReturnsSort('original_sale')" class="py-3 px-3 cursor-pointer select-none hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Original Invoice</span>
                          <span class="text-[10px] text-rose-500">
                            {{ returnsSortKey === 'original_sale' ? (returnsSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleReturnsSort('payment_method')" class="py-3 px-3 text-center cursor-pointer select-none hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        <div class="flex items-center justify-center space-x-1">
                          <span>Refund Method</span>
                          <span class="text-[10px] text-rose-500">
                            {{ returnsSortKey === 'payment_method' ? (returnsSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleReturnsSort('total_amount')" class="py-3 px-3 text-right cursor-pointer select-none hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Refund Amount</span>
                          <span class="text-[10px] text-rose-500">
                            {{ returnsSortKey === 'total_amount' ? (returnsSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleReturnsSort('notes')" class="py-3 px-4 cursor-pointer select-none hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Notes / Reason</span>
                          <span class="text-[10px] text-rose-500">
                            {{ returnsSortKey === 'notes' ? (returnsSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                    <tr v-if="returnsLoading" class="text-center">
                      <td colspan="6" class="py-8">
                        <div class="flex justify-center items-center space-x-2">
                          <div class="animate-spin rounded-full h-5 w-5 border-2 border-rose-600 border-t-transparent"></div>
                          <span class="text-xs text-slate-500 dark:text-zinc-400">Loading sale returns...</span>
                        </div>
                      </td>
                    </tr>
                    <tr v-else-if="sortedSaleReturns.length === 0" class="text-center">
                      <td colspan="6" class="py-8 text-slate-400 dark:text-zinc-500 text-xs italic">
                        No sale returns recorded for this customer in selected period.
                      </td>
                    </tr>
                    <tr
                      v-else
                      v-for="ret in sortedSaleReturns"
                      :key="ret.id"
                      class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/30 transition-colors"
                    >
                      <td class="py-3 px-4 font-black text-rose-600 dark:text-rose-400">
                        {{ ret.sale_number }}
                      </td>
                      <td class="py-3 px-3 text-slate-700 dark:text-zinc-300 font-medium">
                        {{ formatDate(ret.sale_date) }}
                      </td>
                      <td class="py-3 px-3 text-indigo-600 dark:text-indigo-400 font-bold">
                        {{ ret.original_sale?.sale_number || ret.order_number || 'N/A' }}
                      </td>
                      <td class="py-3 px-3 text-center text-[10px] font-bold uppercase text-slate-600 dark:text-zinc-400">
                        {{ ret.payment_method || 'Store Credit' }}
                      </td>
                      <td class="py-3 px-3 text-right font-black text-rose-600 dark:text-rose-400">
                        {{ formatCurrency(ret.total_amount) }}
                      </td>
                      <td class="py-3 px-4 text-slate-500 dark:text-zinc-400 truncate max-w-xs">
                        {{ ret.notes || 'No reason specified' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination Footer -->
              <div v-if="returnsTotal > 0" class="px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800/40 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                <span class="text-slate-500 dark:text-zinc-400 font-medium">
                  Showing <strong class="text-slate-700 dark:text-zinc-200">{{ returnsPagination.from }}</strong> to <strong class="text-slate-700 dark:text-zinc-200">{{ returnsPagination.to }}</strong> of <strong class="text-slate-700 dark:text-zinc-200">{{ returnsPagination.total }}</strong> entries
                </span>

                <div class="flex items-center space-x-1">
                  <button
                    @click="fetchReturns(1)"
                    :disabled="returnsPagination.current_page <= 1"
                    class="px-2 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    title="First Page"
                  >
                    «
                  </button>
                  <button
                    @click="fetchReturns(returnsPagination.current_page - 1)"
                    :disabled="returnsPagination.current_page <= 1"
                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                  >
                    Prev
                  </button>
                  <span class="px-3 py-1 bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 font-extrabold rounded-lg border border-rose-200 dark:border-rose-800">
                    {{ returnsPagination.current_page }} / {{ returnsPagination.last_page }}
                  </span>
                  <button
                    @click="fetchReturns(returnsPagination.current_page + 1)"
                    :disabled="returnsPagination.current_page >= returnsPagination.last_page"
                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                  >
                    Next
                  </button>
                  <button
                    @click="fetchReturns(returnsPagination.last_page)"
                    :disabled="returnsPagination.current_page >= returnsPagination.last_page"
                    class="px-2 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    title="Last Page"
                  >
                    »
                  </button>
                </div>
              </div>
            </div>

            <!-- SECTION 3: General Ledger Transactions DataTable (AJAX-based with Sorting, Limit & Pagination) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
              <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-800/20">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                  <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-zinc-200">Transactions</h4>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                    {{ transactionsList.length }} entries
                  </span>
                </div>

                <!-- Limit Selection Dropdown -->
                <div class="flex items-center space-x-1.5 text-xs font-semibold text-slate-500 dark:text-zinc-400">
                  <span>Show</span>
                  <select
                    v-model="ledgerPerPage"
                    @change="ledgerCurrentPage = 1"
                    class="px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 font-bold focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                  >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                    <option value="all">All</option>
                  </select>
                  <span>entries</span>
                </div>
              </div>

              <!-- Table Content -->
              <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-xs text-left">
                  <thead class="bg-slate-100/80 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-zinc-700">
                    <tr>
                      <th @click="toggleLedgerSort('date')" class="py-3 px-4 cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Date</span>
                          <span class="text-[10px] text-emerald-500">
                            {{ ledgerSortKey === 'date' ? (ledgerSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleLedgerSort('reference')" class="py-3 px-3 cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Reference</span>
                          <span class="text-[10px] text-emerald-500">
                            {{ ledgerSortKey === 'reference' ? (ledgerSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleLedgerSort('description')" class="py-3 px-4 cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <div class="flex items-center space-x-1">
                          <span>Description</span>
                          <span class="text-[10px] text-emerald-500">
                            {{ ledgerSortKey === 'description' ? (ledgerSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleLedgerSort('type')" class="py-3 px-3 text-center cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <div class="flex items-center justify-center space-x-1">
                          <span>Type</span>
                          <span class="text-[10px] text-emerald-500">
                            {{ ledgerSortKey === 'type' ? (ledgerSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleLedgerSort('debit')" class="py-3 px-3 text-right cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Debit (+)</span>
                          <span class="text-[10px] text-emerald-500">
                            {{ ledgerSortKey === 'debit' ? (ledgerSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleLedgerSort('credit')" class="py-3 px-3 text-right cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Credit (-)</span>
                          <span class="text-[10px] text-emerald-500">
                            {{ ledgerSortKey === 'credit' ? (ledgerSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                      <th @click="toggleLedgerSort('running_balance')" class="py-3 px-4 text-right cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                        <div class="flex items-center justify-end space-x-1">
                          <span>Running Balance</span>
                          <span class="text-[10px] text-emerald-500">
                            {{ ledgerSortKey === 'running_balance' ? (ledgerSortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                          </span>
                        </div>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                    <tr v-if="ledgerLoading" class="text-center">
                      <td colspan="7" class="py-8">
                        <div class="flex justify-center items-center space-x-2">
                          <div class="animate-spin rounded-full h-5 w-5 border-2 border-emerald-600 border-t-transparent"></div>
                          <span class="text-xs text-slate-500 dark:text-zinc-400">Loading ledger transactions...</span>
                        </div>
                      </td>
                    </tr>
                    <tr v-else-if="paginatedTransactions.length === 0" class="text-center">
                      <td colspan="7" class="py-8 text-slate-400 dark:text-zinc-500 text-xs italic">
                        No transactions found for the selected date range.
                      </td>
                    </tr>
                    <tr
                      v-else
                      v-for="(tx, idx) in paginatedTransactions"
                      :key="idx"
                      class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/30 transition-colors"
                    >
                      <td class="py-3 px-4 font-bold text-slate-800 dark:text-zinc-200 whitespace-nowrap">
                        {{ formatDate(tx.date) }}
                      </td>
                      <td class="py-3 px-3 font-extrabold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                        {{ tx.reference }}
                      </td>
                      <td class="py-3 px-4 text-slate-700 dark:text-zinc-300 font-medium">
                        {{ tx.description }}
                      </td>
                      <td class="py-3 px-3 text-center whitespace-nowrap">
                        <span :class="getTxTypeClass(tx.type)">
                          {{ tx.type }}
                        </span>
                      </td>
                      <td class="py-3 px-3 text-right font-extrabold text-amber-600 dark:text-amber-400 whitespace-nowrap">
                        {{ tx.debit > 0 ? formatCurrency(tx.debit) : '-' }}
                      </td>
                      <td class="py-3 px-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                        {{ tx.credit > 0 ? formatCurrency(tx.credit) : '-' }}
                      </td>
                      <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-zinc-100 whitespace-nowrap">
                        {{ formatCurrency(tx.running_balance) }}
                      </td>
                    </tr>
                  </tbody>

                  <!-- Summary Footer -->
                  <tfoot v-if="sortedTransactions.length > 0" class="bg-slate-100/90 dark:bg-zinc-800/90 font-black text-slate-900 dark:text-zinc-100 border-t border-slate-200 dark:border-zinc-700">
                    <tr>
                      <td colspan="4" class="py-3 px-4 text-right uppercase text-[10px] tracking-wider text-slate-500">Totals:</td>
                      <td class="py-3 px-3 text-right text-amber-600 dark:text-amber-400">
                        {{ formatCurrency(totalTxDebits) }}
                      </td>
                      <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">
                        {{ formatCurrency(totalTxCredits) }}
                      </td>
                      <td class="py-3 px-4 text-right text-indigo-600 dark:text-indigo-400">
                        {{ formatCurrency(stats.balance) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <!-- Pagination Footer -->
              <div v-if="sortedTransactions.length > 0" class="px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800/40 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                <span class="text-slate-500 dark:text-zinc-400 font-medium">
                  Showing <strong class="text-slate-700 dark:text-zinc-200">{{ ledgerPaginationInfo.from }}</strong> to <strong class="text-slate-700 dark:text-zinc-200">{{ ledgerPaginationInfo.to }}</strong> of <strong class="text-slate-700 dark:text-zinc-200">{{ ledgerPaginationInfo.total }}</strong> entries
                </span>

                <div v-if="ledgerPerPage !== 'all' && ledgerTotalPages > 1" class="flex items-center space-x-1">
                  <button
                    @click="ledgerCurrentPage = 1"
                    :disabled="ledgerCurrentPage <= 1"
                    class="px-2 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    title="First Page"
                  >
                    «
                  </button>
                  <button
                    @click="ledgerCurrentPage--"
                    :disabled="ledgerCurrentPage <= 1"
                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                  >
                    Prev
                  </button>
                  <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 font-extrabold rounded-lg border border-emerald-200 dark:border-emerald-800">
                    {{ ledgerCurrentPage }} / {{ ledgerTotalPages }}
                  </span>
                  <button
                    @click="ledgerCurrentPage++"
                    :disabled="ledgerCurrentPage >= ledgerTotalPages"
                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                  >
                    Next
                  </button>
                  <button
                    @click="ledgerCurrentPage = ledgerTotalPages"
                    :disabled="ledgerCurrentPage >= ledgerTotalPages"
                    class="px-2 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    title="Last Page"
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
      <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-zinc-900 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
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
import FloatingDateRangePicker from '@/components/common/FloatingDateRangePicker.vue';

const props = defineProps({
  show: Boolean,
  customer: Object
});

const emit = defineEmits(['close']);

const currencyStore = useCurrencyStore();

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
  if (!props.customer?.id) return;
  downloadingPdf.value = true;
  try {
    const start = getStartDate();
    const end = getEndDate();
    
    const response = await api.get(`/customers/${props.customer.id}/ledger/pdf`, {
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
    const name = (activeCustomer.value?.name || props.customer?.name || 'customer').toLowerCase().replace(/[^a-z0-9]/g, '_');
    const dateStr = new Date().toISOString().split('T')[0];
    link.setAttribute('download', `customer_ledger_${name}_${dateStr}.pdf`);
    document.body.appendChild(link);
    link.click();
    setTimeout(() => {
      if (document.body.contains(link)) {
        document.body.removeChild(link);
      }
      window.URL.revokeObjectURL(url);
    }, 200);
  } catch (err) {
    console.error('Error downloading ledger PDF:', err);
  } finally {
    downloadingPdf.value = false;
  }
};

const activeCustomer = ref(null);
const loading = ref(false);

const stats = ref({
  paymentPending: 0,
  paymentReceived: 0,
  balance: 0
});

// Sales Invoices State
const salesLoading = ref(false);
const salesInvoices = ref([]);
const salesTotal = ref(0);
const salesSearch = ref('');
const salesPerPage = ref(5);
const salesSortKey = ref('sale_date');
const salesSortOrder = ref('desc');
const salesPagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

// Sale Returns State
const returnsLoading = ref(false);
const saleReturns = ref([]);
const returnsTotal = ref(0);
const returnsPerPage = ref(5);
const returnsSortKey = ref('sale_date');
const returnsSortOrder = ref('desc');
const returnsPagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

// Ledger Transactions State
const ledgerLoading = ref(false);
const transactionsList = ref([]);
const totalTxDebits = ref(0);
const totalTxCredits = ref(0);
const ledgerPerPage = ref(10);
const ledgerCurrentPage = ref(1);
const ledgerSortKey = ref('date');
const ledgerSortOrder = ref('asc');

// Sales Invoices Sorting & Computations
const toggleSalesSort = (key) => {
  if (salesSortKey.value === key) {
    salesSortOrder.value = salesSortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    salesSortKey.value = key;
    salesSortOrder.value = 'asc';
  }
  fetchSales(1);
};

const sortedSalesInvoices = computed(() => {
  let list = [...salesInvoices.value];
  if (!salesSortKey.value) return list;

  return list.sort((a, b) => {
    let valA, valB;
    if (salesSortKey.value === 'salesman') {
      valA = a.salesman?.full_name || a.user?.name || '';
      valB = b.salesman?.full_name || b.user?.name || '';
    } else if (salesSortKey.value === 'status') {
      valA = getInvoiceStatusLabel(a);
      valB = getInvoiceStatusLabel(b);
    } else if (salesSortKey.value === 'due') {
      valA = Math.max(0, parseFloat(a.total_amount || 0) - parseFloat(a.paid_amount || 0));
      valB = Math.max(0, parseFloat(b.total_amount || 0) - parseFloat(b.paid_amount || 0));
    } else {
      valA = a[salesSortKey.value];
      valB = b[salesSortKey.value];
    }

    if (valA === null || valA === undefined) valA = '';
    if (valB === null || valB === undefined) valB = '';

    if (typeof valA === 'number' && typeof valB === 'number') {
      return salesSortOrder.value === 'asc' ? valA - valB : valB - valA;
    }

    const strA = String(valA).toLowerCase();
    const strB = String(valB).toLowerCase();

    if (strA < strB) return salesSortOrder.value === 'asc' ? -1 : 1;
    if (strA > strB) return salesSortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
});

// Sale Returns Sorting & Computations
const toggleReturnsSort = (key) => {
  if (returnsSortKey.value === key) {
    returnsSortOrder.value = returnsSortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    returnsSortKey.value = key;
    returnsSortOrder.value = 'asc';
  }
  fetchReturns(1);
};

const sortedSaleReturns = computed(() => {
  let list = [...saleReturns.value];
  if (!returnsSortKey.value) return list;

  return list.sort((a, b) => {
    let valA, valB;
    if (returnsSortKey.value === 'original_sale') {
      valA = a.original_sale?.sale_number || a.order_number || '';
      valB = b.original_sale?.sale_number || b.order_number || '';
    } else {
      valA = a[returnsSortKey.value];
      valB = b[returnsSortKey.value];
    }

    if (valA === null || valA === undefined) valA = '';
    if (valB === null || valB === undefined) valB = '';

    if (typeof valA === 'number' && typeof valB === 'number') {
      return returnsSortOrder.value === 'asc' ? valA - valB : valB - valA;
    }

    const strA = String(valA).toLowerCase();
    const strB = String(valB).toLowerCase();

    if (strA < strB) return returnsSortOrder.value === 'asc' ? -1 : 1;
    if (strA > strB) return returnsSortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
});

// Ledger Transactions Sorting & Pagination Computations
const toggleLedgerSort = (key) => {
  if (ledgerSortKey.value === key) {
    ledgerSortOrder.value = ledgerSortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    ledgerSortKey.value = key;
    ledgerSortOrder.value = 'asc';
  }
  ledgerCurrentPage.value = 1;
};

const sortedTransactions = computed(() => {
  let list = [...transactionsList.value];
  if (!ledgerSortKey.value) return list;

  return list.sort((a, b) => {
    let valA = a[ledgerSortKey.value];
    let valB = b[ledgerSortKey.value];

    if (valA === null || valA === undefined) valA = '';
    if (valB === null || valB === undefined) valB = '';

    if (typeof valA === 'number' && typeof valB === 'number') {
      return ledgerSortOrder.value === 'asc' ? valA - valB : valB - valA;
    }

    const strA = String(valA).toLowerCase();
    const strB = String(valB).toLowerCase();

    if (strA < strB) return ledgerSortOrder.value === 'asc' ? -1 : 1;
    if (strA > strB) return ledgerSortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
});

const paginatedTransactions = computed(() => {
  if (ledgerPerPage.value === 'all') {
    return sortedTransactions.value;
  }
  const per = parseInt(ledgerPerPage.value, 10) || 10;
  const start = (ledgerCurrentPage.value - 1) * per;
  return sortedTransactions.value.slice(start, start + per);
});

const ledgerTotalPages = computed(() => {
  if (ledgerPerPage.value === 'all') return 1;
  const per = parseInt(ledgerPerPage.value, 10) || 10;
  return Math.ceil(sortedTransactions.value.length / per) || 1;
});

const ledgerPaginationInfo = computed(() => {
  const total = sortedTransactions.value.length;
  if (total === 0) return { from: 0, to: 0, total: 0 };
  if (ledgerPerPage.value === 'all') return { from: 1, to: total, total };

  const per = parseInt(ledgerPerPage.value, 10) || 10;
  const from = (ledgerCurrentPage.value - 1) * per + 1;
  const to = Math.min(from + per - 1, total);
  return { from, to, total };
});

const fetchLedger = async () => {
  if (!props.customer?.id) return;
  ledgerLoading.value = true;
  try {
    const res = await api.get(`/customers/${props.customer.id}/ledger`, {
      params: {
        start_date: getStartDate(),
        end_date: getEndDate()
      }
    });

    if (res.data) {
      if (res.data.customer) {
        activeCustomer.value = res.data.customer;
      }
      stats.value.paymentPending = parseFloat(res.data.payment_pending || 0);
      stats.value.paymentReceived = parseFloat(res.data.payment_received || 0);
      stats.value.balance = parseFloat(res.data.balance || res.data.closing_balance || 0);
      transactionsList.value = res.data.transactions || [];
      totalTxDebits.value = parseFloat(res.data.summary?.total_debits || 0);
      totalTxCredits.value = parseFloat(res.data.summary?.total_credits || 0);
      ledgerCurrentPage.value = 1;
    }
  } catch (err) {
    console.error('Error loading ledger:', err);
  } finally {
    ledgerLoading.value = false;
  }
};

const fetchSales = async (page = 1) => {
  if (!props.customer?.id) return;
  salesLoading.value = true;
  try {
    const res = await api.get('/sales', {
      params: {
        customer_id: props.customer.id,
        start_date: getStartDate(),
        end_date: getEndDate(),
        search: salesSearch.value,
        page,
        per_page: salesPerPage.value,
        sort_by: salesSortKey.value,
        sort_order: salesSortOrder.value
      }
    });

    if (res.data) {
      salesInvoices.value = res.data.data || [];
      salesTotal.value = res.data.total || salesInvoices.value.length;
      salesPagination.value = {
        current_page: res.data.current_page || 1,
        last_page: res.data.last_page || 1,
        from: res.data.from || (salesInvoices.value.length > 0 ? (page - 1) * salesPerPage.value + 1 : 0),
        to: res.data.to || Math.min(page * salesPerPage.value, salesTotal.value),
        total: res.data.total || salesInvoices.value.length
      };
    }
  } catch (err) {
    console.error('Error loading sales invoices:', err);
  } finally {
    salesLoading.value = false;
  }
};

let debounceTimer = null;
const debounceFetchSales = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchSales(1);
  }, 300);
};

const fetchReturns = async (page = 1) => {
  if (!props.customer?.id) return;
  returnsLoading.value = true;
  try {
    const res = await api.get('/sales/returns', {
      params: {
        customer_id: props.customer.id,
        start_date: getStartDate(),
        end_date: getEndDate(),
        page,
        per_page: returnsPerPage.value,
        sort_by: returnsSortKey.value,
        sort_order: returnsSortOrder.value
      }
    });

    if (res.data) {
      saleReturns.value = res.data.data || [];
      returnsTotal.value = res.data.total || saleReturns.value.length;
      returnsPagination.value = {
        current_page: res.data.current_page || 1,
        last_page: res.data.last_page || 1,
        from: res.data.from || (saleReturns.value.length > 0 ? (page - 1) * returnsPerPage.value + 1 : 0),
        to: res.data.to || Math.min(page * returnsPerPage.value, returnsTotal.value),
        total: res.data.total || saleReturns.value.length
      };
    }
  } catch (err) {
    console.error('Error loading sale returns:', err);
  } finally {
    returnsLoading.value = false;
  }
};

const fetchAllData = async () => {
  loading.value = true;
  await Promise.all([
    fetchLedger(),
    fetchSales(1),
    fetchReturns(1)
  ]);
  loading.value = false;
};

onMounted(() => {
  if (props.show && props.customer?.id) {
    activeCustomer.value = props.customer;
    fetchAllData();
  }
});

watch(() => props.customer, (newCust) => {
  if (newCust?.id) {
    activeCustomer.value = newCust;
    fetchAllData();
  }
}, { immediate: true });

watch(() => props.show, (newVal) => {
  if (newVal && props.customer?.id) {
    activeCustomer.value = props.customer;
    fetchAllData();
  }
});

const formatCurrency = (val) => {
  return currencyStore.formatPrice(val || 0);
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getInitials = (name) => {
  if (!name) return 'C';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.substring(0, 2).toUpperCase();
};

const formatCustomerType = (type) => {
  if (!type) return 'Registered Customer';
  const map = {
    walk_in: 'Walk-in Customer',
    retail: 'Retail Customer',
    wholesale: 'Wholesale Customer',
    corporate: 'Corporate Customer'
  };
  return map[type] || type.replace('_', ' ');
};

const formatAddress = (c) => {
  if (!c) return 'N/A';
  const parts = [c.address, c.city, c.state, c.country].filter(Boolean);
  return parts.length ? parts.join(', ') : 'No address specified';
};

const getInvoiceStatusLabel = (inv) => {
  const paid = parseFloat(inv.paid_amount || 0);
  const total = parseFloat(inv.total_amount || 0);
  if (inv.status === 'draft') return 'DRAFT';
  if (paid >= total && total > 0) return 'PAID';
  if (paid > 0 && paid < total) return 'PARTIAL';
  return 'UNPAID';
};

const getStatusBadgeClass = (inv) => {
  const lbl = getInvoiceStatusLabel(inv);
  if (lbl === 'PAID') return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
  if (lbl === 'PARTIAL') return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800';
  if (lbl === 'UNPAID') return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800';
  return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-100 text-slate-700';
};

const getTxTypeClass = (type) => {
  if (type === 'Sale Invoice' || type === 'Sale') return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800';
  if (type === 'Payment') return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
  if (type === 'Sale Return' || type === 'Refund') return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800';
  return 'px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-100 text-slate-700';
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.15s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.4);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.6);
}
</style>
