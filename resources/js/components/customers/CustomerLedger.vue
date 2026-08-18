<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm animate-fade-in overflow-y-auto"
      @click.self="$emit('close')"
    >
      <div
        class="relative w-full max-w-7xl max-h-[92vh] bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-zinc-800 flex flex-col overflow-hidden transition-all duration-200 my-auto"
        @click.stop
      >
        <!-- Modal Header with IAS 1 Metadata -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shrink-0">
          <div class="flex items-center space-x-3">
            <div class="p-2.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <div class="flex items-center space-x-2">
                <h3 class="text-lg font-black text-slate-900 dark:text-zinc-100 tracking-tight">Customer General Ledger</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                  {{ accountCode }} • Official Account Statement
                </span>
              </div>
              <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">
                Accounts Receivable Ledger for {{ activeCustomer?.name || customer?.name }}
              </p>
            </div>
          </div>

          <!-- Top Right Controls & Date Filter -->
          <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <FloatingDateRangePicker
              v-model="dateRange"
              :show-presets="true"
              placeholder="All Time"
              @update:modelValue="fetchLedgerData"
              @clear="fetchLedgerData"
            />

            <!-- Download PDF Button -->
            <button
              type="button"
              @click="downloadPDF"
              :disabled="downloadingPdf"
              class="px-3.5 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl text-xs font-extrabold shadow-xs transition-all flex items-center space-x-1.5 cursor-pointer disabled:opacity-50 shrink-0"
              title="Export Customer General Ledger PDF Statement"
            >
              <svg v-if="!downloadingPdf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <div v-else class="animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent"></div>
              <span>{{ downloadingPdf ? 'Exporting...' : 'PDF Statement' }}</span>
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

        <!-- Modal Body (Customer Account Info Sidebar + General Ledger Table Grid) -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 bg-slate-50/50 dark:bg-black/20 space-y-4">
          
          <!-- EXECUTIVE FINANCIAL STATUS BANNER (CRYSTAL CLEAR ACCOUNT SUMMARY) -->
          <div
            v-if="closingBalance > 0"
            class="p-4 rounded-2xl bg-rose-500/10 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs"
          >
            <div class="flex items-start space-x-3">
              <div class="p-2 bg-rose-600 text-white rounded-xl font-black text-sm shrink-0">
                ⚠
              </div>
              <div>
                <h5 class="text-xs font-black uppercase tracking-wider text-rose-700 dark:text-rose-300">
                  Payment Due Action Required
                </h5>
                <p class="text-xs font-medium text-rose-700 dark:text-rose-300 mt-0.5">
                  You have to receive <strong class="text-sm font-black text-rose-800 dark:text-rose-100 underline underline-offset-2">{{ formatCurrency(closingBalance) }}</strong> in outstanding payment from {{ activeCustomer?.name || customer?.name }}.
                </p>
              </div>
            </div>
            <div class="shrink-0 self-end sm:self-center">
              <span class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-rose-600 text-white shadow-xs">
                NET DUE TO RECEIVE: {{ formatCurrency(closingBalance) }}
              </span>
            </div>
          </div>

          <div
            v-else-if="closingBalance < 0"
            class="p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs"
          >
            <div class="flex items-start space-x-3">
              <div class="p-2 bg-emerald-600 text-white rounded-xl font-black text-sm shrink-0">
                ✓
              </div>
              <div>
                <h5 class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                  Customer Advance Wallet Credit
                </h5>
                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300 mt-0.5">
                  This client has overpaid / holds an advance wallet credit of <strong class="text-sm font-black text-emerald-800 dark:text-emerald-100">{{ formatCurrency(Math.abs(closingBalance)) }}</strong>. No payment is currently due.
                </p>
              </div>
            </div>
          </div>

          <div
            v-else
            class="p-4 rounded-2xl bg-emerald-50 dark:bg-zinc-800/80 border border-emerald-200 dark:border-zinc-700 flex items-center space-x-3"
          >
            <div class="p-2 bg-emerald-600 text-white rounded-xl font-black text-sm shrink-0">
              ✓
            </div>
            <div>
              <h5 class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                Account Fully Cleared ($0.00 Outstanding)
              </h5>
              <p class="text-xs font-medium text-slate-600 dark:text-zinc-400 mt-0.5">
                All sales invoices for {{ activeCustomer?.name || customer?.name }} have been paid in full. No payment is due.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT SIDE: Customer Account Information Sidebar (col-span-3) -->
            <div class="lg:col-span-3 space-y-4">
              <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-5">
                
                <!-- Profile & Account Code Header -->
                <div class="text-center pb-4 border-b border-slate-100 dark:border-zinc-800">
                  <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center font-black text-xl shadow-md mx-auto mb-3">
                    {{ getInitials(activeCustomer?.name || customer?.name) }}
                  </div>
                  <h4 class="font-extrabold text-slate-900 dark:text-zinc-100 text-base leading-tight">
                    {{ activeCustomer?.name || customer?.name || 'Customer Ledger' }}
                  </h4>
                  <p class="text-xs font-black text-indigo-600 dark:text-indigo-400 mt-1">
                    Account Code: {{ accountCode }}
                  </p>
                  <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase mt-0.5">
                    {{ accountType }}
                  </p>
                </div>

                <!-- Info Fields -->
                <div class="space-y-3 text-xs">
                  
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

            <!-- RIGHT SIDE: General Ledger Main Section (col-span-9) -->
            <div class="lg:col-span-9 space-y-5">
              
              <!-- 4-Card Summary Header (CLEAR ACTIONABLE FINANCIAL CARDS) -->
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <!-- Opening Balance B/F -->
                <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-2xs space-y-1">
                  <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Opening Balance B/F</span>
                  <p class="text-base font-black text-slate-700 dark:text-zinc-300">
                    {{ formatCurrency(openingBalance) }}
                  </p>
                </div>

                <!-- Total Sales Invoiced (Debit - RED) -->
                <div class="bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-transparent dark:from-rose-500/15 dark:via-rose-500/5 p-4 rounded-2xl border border-rose-200 dark:border-rose-900/60 shadow-2xs space-y-1">
                  <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400">Total Billed / Sales (Debit)</span>
                  <p class="text-base font-black text-rose-600 dark:text-rose-400">
                    {{ formatCurrency(totalDebits) }}
                  </p>
                </div>

                <!-- Total Payments Received (Credit - GREEN) -->
                <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent dark:from-emerald-500/15 dark:via-emerald-500/5 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-900/60 shadow-2xs space-y-1">
                  <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total Received / Paid (Credit)</span>
                  <p class="text-base font-black text-emerald-600 dark:text-emerald-400">
                    {{ formatCurrency(totalCredits) }}
                  </p>
                </div>

                <!-- NET AMOUNT OUTSTANDING TO RECEIVE (DUE) -->
                <div
                  :class="[
                    'p-4 rounded-2xl border shadow-2xs space-y-1 transition-all',
                    closingBalance > 0
                      ? 'bg-rose-500/15 border-rose-400 dark:border-rose-800'
                      : 'bg-emerald-500/15 border-emerald-400 dark:border-emerald-800'
                  ]"
                >
                  <span
                    :class="[
                      'text-[10px] font-black uppercase tracking-wider',
                      closingBalance > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300'
                    ]"
                  >
                    {{ closingBalance > 0 ? 'Net Due to Receive' : (closingBalance < 0 ? 'Customer Advance' : 'Account Settled') }}
                  </span>
                  <p
                    :class="[
                      'text-base font-black',
                      closingBalance > 0 ? 'text-rose-700 dark:text-rose-200' : 'text-emerald-700 dark:text-emerald-200'
                    ]"
                  >
                    {{ formatCurrency(Math.abs(closingBalance)) }}
                    <span class="text-xs font-extrabold">({{ closingBalanceType }})</span>
                  </p>
                </div>
              </div>

              <!-- Datatable Card Container -->
              <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs flex flex-col overflow-hidden">
                
                <!-- Table Controls (Search, Sort Order Toggle & Per Page) -->
                <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-900/50">
                  <div class="relative w-full sm:w-80">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                      v-model="searchQuery"
                      type="text"
                      placeholder="Search ref # (e.g. INV-1347), particulars..."
                      class="w-full pl-9 pr-4 py-1.5 text-xs bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-slate-800 dark:text-zinc-200 placeholder-slate-400 transition-all"
                    />
                  </div>

                  <!-- Quick Controls: Sort Order & Page Size -->
                  <div class="flex items-center space-x-3 text-xs text-slate-500 dark:text-zinc-400 shrink-0">
                    <!-- Sort Order Toggle -->
                    <button
                      type="button"
                      @click="toggleSortDirection"
                      class="px-3 py-1.5 rounded-xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 font-extrabold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all flex items-center space-x-1 cursor-pointer"
                      title="Toggle Chronological Order"
                    >
                      <span>Order:</span>
                      <span class="text-indigo-600 dark:text-indigo-400">{{ sortOrder === 'desc' ? 'Newest First' : 'Oldest First' }}</span>
                      <span>{{ sortOrder === 'desc' ? '▼' : '▲' }}</span>
                    </button>

                    <!-- Entries Per Page -->
                    <div class="flex items-center space-x-1.5">
                      <span>Show</span>
                      <select
                        v-model="perPage"
                        class="px-2 py-1 text-xs bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg focus:outline-none text-slate-800 dark:text-zinc-200 font-semibold"
                      >
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                        <option value="all">All</option>
                      </select>
                      <span>entries</span>
                    </div>
                  </div>
                </div>

                <!-- QUICKBOOKS / IAS GENERAL LEDGER TABLE (RED FOR DEBIT, GREEN FOR CREDIT) -->
                <div class="overflow-x-auto custom-scrollbar">
                  <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-slate-900 text-white dark:bg-zinc-950 dark:text-zinc-100 font-black uppercase text-[10px] tracking-wider border-b border-slate-800">
                      <!-- Group Header Row -->
                      <tr>
                        <th rowspan="2" @click="toggleSort('date')" class="py-3 px-4 cursor-pointer select-none hover:text-indigo-300 transition-colors w-28 border-r border-slate-800">
                          <div class="flex items-center space-x-1">
                            <span>Date</span>
                            <span class="text-[10px] text-indigo-400">
                              {{ sortKey === 'date' ? (sortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                            </span>
                          </div>
                        </th>
                        <th rowspan="2" class="py-3 px-4 border-r border-slate-800">Description / Transaction Details</th>
                        <th rowspan="2" @click="toggleSort('reference')" class="py-3 px-3 cursor-pointer select-none hover:text-indigo-300 transition-colors w-32 border-r border-slate-800">
                          <div class="flex items-center space-x-1">
                            <span>Journal Ref</span>
                            <span class="text-[10px] text-indigo-400">
                              {{ sortKey === 'reference' ? (sortOrder === 'asc' ? '▲' : '▼') : '↕' }}
                            </span>
                          </div>
                        </th>
                        <th colspan="2" class="py-2 px-4 text-center border-r border-slate-800 bg-slate-800/90 dark:bg-zinc-900">
                          Transactions
                        </th>
                        <th colspan="2" class="py-2 px-4 text-center bg-slate-800/90 dark:bg-zinc-900">
                          Running Balance
                        </th>
                      </tr>
                      <!-- Sub Header Row (RED FOR DEBIT, GREEN FOR CREDIT) -->
                      <tr class="bg-slate-800/70 dark:bg-zinc-900/70 text-[9px]">
                        <th @click="toggleSort('debit')" class="py-2 px-3 text-right cursor-pointer select-none text-rose-400 border-r border-slate-700/80 w-28">
                          Debit (Sales +)
                        </th>
                        <th @click="toggleSort('credit')" class="py-2 px-3 text-right cursor-pointer select-none text-emerald-400 border-r border-slate-800 w-28">
                          Credit (Paid -)
                        </th>
                        <th class="py-2 px-3 text-right border-r border-slate-700/80 w-28 text-rose-300">Debit (Dr)</th>
                        <th class="py-2 px-3 text-right w-28 text-emerald-300">Credit (Cr)</th>
                      </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                      
                      <!-- Opening Balance Row -->
                      <tr v-if="dateRange?.start" class="bg-slate-50/80 dark:bg-zinc-800/40 font-bold">
                        <td class="py-2.5 px-4 text-slate-600 dark:text-zinc-400 border-r border-slate-100 dark:border-zinc-800">{{ formatDate(dateRange.start) }}</td>
                        <td class="py-2.5 px-4 text-slate-800 dark:text-zinc-200 border-r border-slate-100 dark:border-zinc-800">Balance B/F (Opening Balance)</td>
                        <td class="py-2.5 px-3 text-slate-500 dark:text-zinc-400 uppercase text-[11px] border-r border-slate-100 dark:border-zinc-800">OPENING</td>
                        <td class="py-2.5 px-3 text-right text-slate-400 border-r border-slate-100 dark:border-zinc-800">-</td>
                        <td class="py-2.5 px-3 text-right text-slate-400 border-r border-slate-100 dark:border-zinc-800">-</td>
                        <!-- Balance Debit vs Credit -->
                        <td class="py-2.5 px-3 text-right font-black text-rose-600 dark:text-rose-400 border-r border-slate-100 dark:border-zinc-800">
                          {{ openingBalance > 0 ? `${formatCurrency(openingBalance)} Dr` : '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                          {{ openingBalance < 0 ? `${formatCurrency(Math.abs(openingBalance))} Cr` : '-' }}
                        </td>
                      </tr>

                      <!-- Loading Skeleton State -->
                      <tr v-if="loading">
                        <td colspan="7" class="py-12 text-center">
                          <div class="inline-flex items-center space-x-2 text-indigo-600 dark:text-indigo-400">
                            <div class="animate-spin rounded-full h-5 w-5 border-2 border-current border-t-transparent"></div>
                            <span class="font-extrabold text-xs">Loading general ledger entries...</span>
                          </div>
                        </td>
                      </tr>

                      <!-- Empty State -->
                      <tr v-else-if="paginatedTransactions.length === 0">
                        <td colspan="7" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                          <p class="font-bold text-xs">No ledger transactions recorded for this customer in selected period.</p>
                        </td>
                      </tr>

                      <!-- Ledger Transaction Rows -->
                      <template v-else v-for="tx in paginatedTransactions" :key="tx.id">
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition-colors">
                          
                          <!-- Date -->
                          <td class="py-3 px-4 font-semibold text-slate-600 dark:text-zinc-400 whitespace-nowrap border-r border-slate-100 dark:border-zinc-800">
                            {{ formatDate(tx.date) }}
                          </td>

                          <!-- Description / Particulars & Status Badges -->
                          <td class="py-3 px-4 border-r border-slate-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between gap-2">
                              <div>
                                <div class="flex items-center space-x-2">
                                  <span class="font-bold text-slate-900 dark:text-zinc-100">
                                    {{ tx.particulars || tx.description }}
                                  </span>

                                  <!-- Payment Status Badge -->
                                  <span
                                    :class="[
                                      'px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider',
                                      tx.status === 'Paid' || tx.status === 'Received & Cleared' || tx.status === 'Received & Posted'
                                        ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                                        : (tx.status === 'Partially Paid'
                                          ? 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800'
                                          : 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800')
                                    ]"
                                  >
                                    {{ tx.status }}
                                  </span>
                                </div>

                                <div class="flex items-center space-x-2 text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">
                                  <span v-if="tx.due_amount > 0" class="text-rose-600 dark:text-rose-400 font-bold">
                                    Outstanding Due: {{ formatCurrency(tx.due_amount) }}
                                  </span>
                                  <span v-else-if="tx.paid_amount > 0" class="text-emerald-600 dark:text-emerald-400 font-bold">
                                    Paid in Full: {{ formatCurrency(tx.paid_amount) }}
                                  </span>
                                  <span v-if="tx.salesman && tx.salesman !== '-'">• Salesman: {{ tx.salesman }}</span>
                                </div>
                              </div>

                              <!-- Line Items Expand Toggle Button -->
                              <button
                                v-if="tx.items && tx.items.length > 0"
                                type="button"
                                @click="toggleExpand(tx.id)"
                                class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900 hover:bg-indigo-100 transition-all cursor-pointer flex items-center space-x-1 shrink-0"
                              >
                                <span>{{ expandedRows[tx.id] ? 'Hide Items' : `${tx.items.length} Item(s)` }}</span>
                                <span>{{ expandedRows[tx.id] ? '▲' : '▼' }}</span>
                              </button>
                            </div>
                          </td>

                          <!-- Journal Reference -->
                          <td class="py-3 px-3 font-black text-indigo-600 dark:text-indigo-400 whitespace-nowrap border-r border-slate-100 dark:border-zinc-800">
                            {{ tx.reference }}
                          </td>

                          <!-- Transactions Debit ($ RED) -->
                          <td class="py-3 px-3 text-right font-black whitespace-nowrap border-r border-slate-100 dark:border-zinc-800">
                            <span v-if="tx.debit > 0" class="text-rose-600 dark:text-rose-400">
                              {{ formatCurrency(tx.debit) }}
                            </span>
                            <span v-else class="text-slate-300 dark:text-zinc-700">-</span>
                          </td>

                          <!-- Transactions Credit ($ GREEN) -->
                          <td class="py-3 px-3 text-right font-black whitespace-nowrap border-r border-slate-100 dark:border-zinc-800">
                            <span v-if="tx.credit > 0" class="text-emerald-600 dark:text-emerald-400">
                              {{ formatCurrency(tx.credit) }}
                            </span>
                            <span v-else class="text-slate-300 dark:text-zinc-700">-</span>
                          </td>

                          <!-- Balance Debit ($ RED Dr) -->
                          <td class="py-3 px-3 text-right font-black text-rose-600 dark:text-rose-400 whitespace-nowrap border-r border-slate-100 dark:border-zinc-800">
                            <span v-if="tx.balance > 0">{{ formatCurrency(tx.balance) }} <span class="text-[9px] text-rose-500">Dr</span></span>
                            <span v-else class="text-slate-300 dark:text-zinc-700">-</span>
                          </td>

                          <!-- Balance Credit ($ GREEN Cr) -->
                          <td class="py-3 px-3 text-right font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            <span v-if="tx.balance < 0">{{ formatCurrency(Math.abs(tx.balance)) }} <span class="text-[9px] text-emerald-500">Cr</span></span>
                            <span v-else class="text-slate-300 dark:text-zinc-700">-</span>
                          </td>
                        </tr>

                        <!-- Expandable Item Breakdown Drawer -->
                        <tr v-if="expandedRows[tx.id] && tx.items && tx.items.length > 0" class="bg-indigo-50/40 dark:bg-indigo-950/20">
                          <td colspan="7" class="py-2.5 px-6">
                            <div class="pl-6 border-l-2 border-indigo-400 dark:border-indigo-600 space-y-1 text-[11px]">
                              <p class="text-[10px] font-black uppercase text-indigo-600 dark:text-indigo-400 tracking-wider mb-1">
                                Line Items Breakdown:
                              </p>
                              <div
                                v-for="(item, idx) in tx.items"
                                :key="idx"
                                class="flex items-center justify-between text-slate-700 dark:text-zinc-300 py-0.5 border-b border-indigo-100/50 dark:border-indigo-900/30 last:border-0"
                              >
                                <span class="font-semibold">{{ item.name }}</span>
                                <div class="space-x-3 text-xs">
                                  <span class="text-slate-500 dark:text-zinc-400">{{ item.qty }} x {{ formatCurrency(item.price) }}</span>
                                  <strong class="font-bold text-slate-900 dark:text-zinc-100">{{ formatCurrency(item.total) }}</strong>
                                </div>
                              </div>
                            </div>
                          </td>
                        </tr>
                      </template>

                    </tbody>

                    <!-- Summary Footer Row (QuickBooks Style Total Row) -->
                    <tfoot v-if="filteredTransactions.length > 0" class="bg-slate-900 text-white dark:bg-zinc-950 dark:text-zinc-100 font-black border-t-2 border-slate-800">
                      <tr>
                        <td colspan="3" class="py-3 px-4 text-center uppercase text-[11px] tracking-wider">
                          Total Summary
                        </td>
                        <td class="py-3 px-3 text-right text-rose-400 text-xs border-r border-slate-800">
                          {{ formatCurrency(totalDebits) }}
                        </td>
                        <td class="py-3 px-3 text-right text-emerald-400 text-xs border-r border-slate-800">
                          {{ formatCurrency(totalCredits) }}
                        </td>
                        <td class="py-3 px-3 text-right text-rose-400 text-xs border-r border-slate-800">
                          {{ closingBalance > 0 ? `${formatCurrency(closingBalance)} Dr` : '-' }}
                        </td>
                        <td class="py-3 px-3 text-right text-emerald-400 text-xs">
                          {{ closingBalance < 0 ? `${formatCurrency(Math.abs(closingBalance))} Cr` : '-' }}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>

                <!-- Pagination Controls -->
                <div v-if="filteredTransactions.length > 0" class="px-4 py-3 bg-slate-50/80 dark:bg-zinc-900 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                  <span class="text-slate-500 dark:text-zinc-400 font-medium">
                    Showing <strong class="text-slate-700 dark:text-zinc-200">{{ paginationInfo.from }}</strong> to <strong class="text-slate-700 dark:text-zinc-200">{{ paginationInfo.to }}</strong> of <strong class="text-slate-700 dark:text-zinc-200">{{ paginationInfo.total }}</strong> entries
                  </span>

                  <div v-if="perPage !== 'all' && totalPages > 1" class="flex items-center space-x-1">
                    <button
                      @click="currentPage = 1"
                      :disabled="currentPage <= 1"
                      class="px-2 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                      title="First Page"
                    >
                      «
                    </button>
                    <button
                      @click="currentPage--"
                      :disabled="currentPage <= 1"
                      class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    >
                      Prev
                    </button>
                    <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-extrabold rounded-lg border border-indigo-200 dark:border-indigo-800">
                      {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button
                      @click="currentPage++"
                      :disabled="currentPage >= totalPages"
                      class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-600 dark:text-zinc-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                    >
                      Next
                    </button>
                    <button
                      @click="currentPage = totalPages"
                      :disabled="currentPage >= totalPages"
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
            IAS 1 Compliant General Ledger Statement — <strong class="text-slate-700 dark:text-zinc-300">{{ transactions.length }} Total Ledger Entries</strong>
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
  </Teleport>
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
const activeCustomer = ref(null);

const loading = ref(false);
const downloadingPdf = ref(false);

const dateRange = ref({ start: '', end: '' });
const searchQuery = ref('');

const accountCode = ref('');
const accountType = ref('Asset (Debtors / Receivable)');
const openingBalance = ref(0);
const closingBalance = ref(0);
const closingBalanceType = ref('Dr');
const totalDebits = ref(0);
const totalCredits = ref(0);
const transactions = ref([]);

const expandedRows = ref({});
const toggleExpand = (id) => {
  expandedRows.value[id] = !expandedRows.value[id];
};

const perPage = ref(25);
const currentPage = ref(1);

const sortKey = ref('date');
const sortOrder = ref('desc'); // Default to DESC (Newest First) so new invoices like INV-1347 appear immediately!

const toggleSortDirection = () => {
  sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc';
};

const toggleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'desc';
  }
};

const fetchLedgerData = async () => {
  const custId = props.customer?.id;
  if (!custId) return;

  loading.value = true;
  try {
    const params = {};
    if (dateRange.value?.start) params.start_date = dateRange.value.start;
    if (dateRange.value?.end) params.end_date = dateRange.value.end;

    const res = await api.get(`/customers/${custId}/ledger`, { params });
    const data = res.data;

    activeCustomer.value = data.customer || props.customer;
    accountCode.value = data.customer?.account_code || `AR-${String(custId).padStart(5, '0')}`;
    accountType.value = data.customer?.account_type || 'Asset (Debtors / Receivable)';
    openingBalance.value = data.opening_balance || 0;
    closingBalance.value = data.closing_balance || 0;
    closingBalanceType.value = data.closing_balance_type || 'Dr';
    totalDebits.value = data.total_debits || 0;
    totalCredits.value = data.total_credits || 0;
    transactions.value = data.transactions || [];
    currentPage.value = 1;
  } catch (err) {
    console.error('Failed to fetch customer ledger:', err);
  } finally {
    loading.value = false;
  }
};

const filteredTransactions = computed(() => {
  let list = [...transactions.value];

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(tx => 
      (tx.reference && tx.reference.toLowerCase().includes(q)) ||
      (tx.particulars && tx.particulars.toLowerCase().includes(q)) ||
      (tx.description && tx.description.toLowerCase().includes(q)) ||
      (tx.date && tx.date.includes(q)) ||
      (tx.salesman && tx.salesman.toLowerCase().includes(q)) ||
      (tx.items && tx.items.some(i => i.name.toLowerCase().includes(q)))
    );
  }

  list.sort((a, b) => {
    let valA = a[sortKey.value];
    let valB = b[sortKey.value];

    if (valA === undefined || valA === null) valA = '';
    if (valB === undefined || valB === null) valB = '';

    if (typeof valA === 'string') valA = valA.toLowerCase();
    if (typeof valB === 'string') valB = valB.toLowerCase();

    if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1;
    if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });

  return list;
});

const totalPages = computed(() => {
  if (perPage.value === 'all') return 1;
  return Math.ceil(filteredTransactions.value.length / perPage.value) || 1;
});

const paginatedTransactions = computed(() => {
  if (perPage.value === 'all') return filteredTransactions.value;
  const start = (currentPage.value - 1) * perPage.value;
  return filteredTransactions.value.slice(start, start + perPage.value);
});

const paginationInfo = computed(() => {
  const total = filteredTransactions.value.length;
  if (total === 0) return { from: 0, to: 0, total: 0 };
  if (perPage.value === 'all') return { from: 1, to: total, total };

  const from = (currentPage.value - 1) * perPage.value + 1;
  const to = Math.min(currentPage.value * perPage.value, total);
  return { from, to, total };
});

const downloadPDF = async () => {
  const custId = activeCustomer.value?.id || props.customer?.id;
  if (!custId) return;

  downloadingPdf.value = true;
  try {
    const params = {};
    if (dateRange.value?.start) params.start_date = dateRange.value.start;
    if (dateRange.value?.end) params.end_date = dateRange.value.end;

    const response = await api.get(`/customers/${custId}/ledger/pdf`, {
      params,
      responseType: 'blob'
    });

    if (response.data.type === 'application/json' || response.data.type === 'text/html') {
      const text = await response.data.text();
      console.error('PDF export failed:', text);
      alert('Failed to generate PDF statement.');
      return;
    }

    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    const custName = (activeCustomer.value?.name || props.customer?.name || 'customer').replace(/[^a-zA-Z0-9_-]/g, '_');
    link.setAttribute('download', `general_ledger_${custName}_${new Date().toISOString().slice(0,10)}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error('Failed to export customer ledger PDF:', err);
    alert('An error occurred while exporting PDF statement.');
  } finally {
    downloadingPdf.value = false;
  }
};

const formatCurrency = (val) => {
  if (currencyStore && typeof currencyStore.formatPrice === 'function') {
    return currencyStore.formatPrice(val || 0);
  }
  const symbol = currencyStore?.symbol || '$';
  return `${symbol} ${parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const getInitials = (name) => {
  if (!name) return 'C';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatAddress = (c) => {
  if (!c) return 'N/A';
  const parts = [c.address, c.city, c.state, c.country].filter(Boolean);
  return parts.length ? parts.join(', ') : 'N/A';
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    activeCustomer.value = props.customer;
    fetchLedgerData();
  }
}, { immediate: true });

onMounted(() => {
  if (props.show) {
    fetchLedgerData();
  }
});
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
