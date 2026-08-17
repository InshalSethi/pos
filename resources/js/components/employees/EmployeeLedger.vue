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
              <h2 class="text-lg font-black text-slate-800 dark:text-zinc-100 tracking-tight">Employee General Ledger</h2>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 uppercase tracking-wider">
                Statement
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-zinc-400 font-medium">
              Real-time statement for <span class="font-bold text-slate-700 dark:text-zinc-200">{{ activeEmployee?.name || employee?.full_name || employee?.first_name }}</span>
            </p>
          </div>
        </div>

        <!-- Right Side Header Controls -->
        <div class="flex items-center space-x-3">
          <!-- Download PDF Button -->
          <button 
            @click="downloadPdf" 
            :disabled="pdfLoading"
            class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all flex items-center space-x-1.5 disabled:opacity-50"
          >
            <svg v-if="!pdfLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <svg v-else class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ pdfLoading ? 'Generating...' : 'PDF' }}</span>
          </button>

          <!-- Floating Date Range Picker -->
          <FloatingDateRangePicker
            :model-value="dateRange"
            @update:model-value="onDateRangeChange"
          />

          <!-- Close Modal Cross -->
          <button 
            @click="$emit('close')"
            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-500 dark:text-zinc-400 flex items-center justify-center transition-colors"
          >
            ✕
          </button>
        </div>
      </div>

      <!-- Main Body Container: Left 25% (col-3) & Right 75% (col-9) -->
      <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50 dark:bg-zinc-950">
        <div class="grid grid-cols-12 gap-6">
          
          <!-- LEFT SIDE: 25% Width (col-span-12 lg:col-span-3) - Employee Info -->
          <div class="col-span-12 lg:col-span-3 space-y-4">
            <!-- Profile Info Card -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl p-5 shadow-xs relative overflow-hidden">
              <div class="absolute -top-12 -right-12 w-28 h-28 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>
              
              <div class="flex flex-col items-center text-center pb-4 border-b border-slate-100 dark:border-zinc-800">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-indigo-500/20 mb-3">
                  {{ getInitials(activeEmployee?.name || employee?.full_name || 'E') }}
                </div>
                <h3 class="font-black text-base text-slate-800 dark:text-zinc-100 tracking-tight">
                  {{ activeEmployee?.name || employee?.full_name }}
                </h3>
                <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400 mt-0.5">
                  {{ activeEmployee?.position || employee?.position?.name || 'Employee' }}
                </span>
                <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400 border border-slate-200/60 dark:border-zinc-700">
                  ID: {{ activeEmployee?.employee_number || employee?.employee_number || '#' + activeEmployee?.id }}
                </div>
              </div>

              <!-- Details List -->
              <div class="pt-4 space-y-3 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Department</span>
                  <span class="font-bold text-slate-700 dark:text-zinc-300">
                    {{ activeEmployee?.department || employee?.department?.name || 'N/A' }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Email</span>
                  <span class="font-semibold text-slate-700 dark:text-zinc-300 truncate max-w-[150px]">
                    {{ activeEmployee?.email || employee?.email || 'N/A' }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Phone</span>
                  <span class="font-semibold text-slate-700 dark:text-zinc-300">
                    {{ activeEmployee?.phone || employee?.phone || 'N/A' }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Basic Salary</span>
                  <span class="font-black text-indigo-600 dark:text-indigo-400">
                    ${{ formatNumber(activeEmployee?.basic_salary || employee?.basic_salary || 0) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 dark:text-zinc-500 font-medium">Status</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize" :class="activeEmployee?.status === 'active' || employee?.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'">
                    {{ activeEmployee?.status || (employee?.is_active ? 'Active' : 'Inactive') }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Balance Summary Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-zinc-900 dark:to-zinc-900 border border-indigo-100 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
              <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block mb-1">
                Net Account Balance
              </span>
              <div class="text-2xl font-black text-indigo-950 dark:text-zinc-100">
                ${{ formatNumber(closingBalance) }}
              </div>
              <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">
                Accumulated disbursements net receipts for selected period.
              </p>
            </div>
          </div>

          <!-- RIGHT SIDE: 75% Width (col-span-12 lg:col-span-9) -->
          <div class="col-span-12 lg:col-span-9 space-y-6">
            
            <!-- Top Summary Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Total Disbursements Card -->
              <div class="bg-white dark:bg-zinc-900 border-l-4 border-l-rose-500 border border-slate-200/80 dark:border-zinc-800/80 rounded-xl p-4 shadow-xs flex items-center justify-between">
                <div>
                  <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Disbursements</span>
                  <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                    ${{ formatNumber(stats.total_debits) }}
                  </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                  </svg>
                </div>
              </div>

              <!-- Total Receipts Card -->
              <div class="bg-white dark:bg-zinc-900 border-l-4 border-l-emerald-500 border border-slate-200/80 dark:border-zinc-800/80 rounded-xl p-4 shadow-xs flex items-center justify-between">
                <div>
                  <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Receipts</span>
                  <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                    ${{ formatNumber(stats.total_credits) }}
                  </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                  </svg>
                </div>
              </div>

              <!-- Closing Balance Card -->
              <div class="bg-white dark:bg-zinc-900 border-l-4 border-l-indigo-500 border border-slate-200/80 dark:border-zinc-800/80 rounded-xl p-4 shadow-xs flex items-center justify-between">
                <div>
                  <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Closing Balance</span>
                  <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                    ${{ formatNumber(closingBalance) }}
                  </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- TRANSACTIONS TABLE SECTION -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl shadow-xs overflow-hidden">
              <div class="px-5 py-4 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                  <h3 class="font-black text-sm text-slate-800 dark:text-zinc-100 uppercase tracking-wider">
                    Transactions
                  </h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                    {{ txPagination.total }} total
                  </span>
                </div>

                <!-- Table Controls: Search & Per Page -->
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                  <div class="relative flex-1 sm:w-64">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                      v-model="txSearch" 
                      @input="debouncedFetchTx"
                      type="text" 
                      placeholder="Search ref, desc..." 
                      class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700/80 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500"
                    />
                  </div>
                  <div class="flex items-center space-x-1.5 shrink-0">
                    <span class="text-xs font-semibold text-slate-400 dark:text-zinc-500">Show</span>
                    <select 
                      v-model="txPerPage" 
                      @change="onTxPerPageChange"
                      class="py-1.5 px-2 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700/80 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-slate-800 dark:text-zinc-100 font-bold"
                    >
                      <option :value="5">5</option>
                      <option :value="10">10</option>
                      <option :value="25">25</option>
                      <option :value="50">50</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Table Content -->
              <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                  <thead class="bg-slate-50/70 dark:bg-zinc-800/50 text-slate-400 dark:text-zinc-500 uppercase tracking-wider font-bold text-[10px] border-b border-slate-100 dark:border-zinc-800">
                    <tr>
                      <th @click="sortTable('date')" class="py-3 px-4 cursor-pointer hover:text-slate-700 dark:hover:text-zinc-300">
                        Date <span v-if="sortKey === 'date'">{{ sortAsc ? '↑' : '↓' }}</span>
                      </th>
                      <th class="py-3 px-4">Reference</th>
                      <th class="py-3 px-4">Description</th>
                      <th class="py-3 px-4 text-center">Type</th>
                      <th @click="sortTable('debit')" class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 dark:hover:text-zinc-300">
                        Debit ($) <span v-if="sortKey === 'debit'">{{ sortAsc ? '↑' : '↓' }}</span>
                      </th>
                      <th @click="sortTable('credit')" class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 dark:hover:text-zinc-300">
                        Credit ($) <span v-if="sortKey === 'credit'">{{ sortAsc ? '↑' : '↓' }}</span>
                      </th>
                      <th class="py-3 px-4 text-right">Balance ($)</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 font-medium">
                    <tr v-if="txLoading">
                      <td colspan="7" class="py-8 text-center text-slate-400 dark:text-zinc-500">
                        <div class="inline-flex items-center space-x-2">
                          <svg class="w-4 h-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                          <span>Loading employee transactions...</span>
                        </div>
                      </td>
                    </tr>
                    <tr v-else-if="txData.length === 0">
                      <td colspan="7" class="py-8 text-center text-slate-400 dark:text-zinc-500 italic">
                        No transactions found for this employee.
                      </td>
                    </tr>
                    <tr v-for="(tx, idx) in txData" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                      <td class="py-3 px-4 font-bold text-slate-700 dark:text-zinc-300 whitespace-nowrap">
                        {{ formatDate(tx.date) }}
                      </td>
                      <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                        {{ tx.reference }}
                      </td>
                      <td class="py-3 px-4 text-slate-600 dark:text-zinc-400 max-w-xs truncate">
                        {{ tx.description }}
                      </td>
                      <td class="py-3 px-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide" :class="tx.debit > 0 ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'">
                          {{ tx.type }}
                        </span>
                      </td>
                      <td class="py-3 px-4 text-right font-black" :class="tx.debit > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-300 dark:text-zinc-700'">
                        {{ tx.debit > 0 ? '$' + formatNumber(tx.debit) : '-' }}
                      </td>
                      <td class="py-3 px-4 text-right font-black" :class="tx.credit > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-300 dark:text-zinc-700'">
                        {{ tx.credit > 0 ? '$' + formatNumber(tx.credit) : '-' }}
                      </td>
                      <td class="py-3 px-4 text-right font-black text-indigo-950 dark:text-zinc-100">
                        ${{ formatNumber(tx.running_balance) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Datatable Pagination Footer -->
              <div v-if="txPagination.total > 0" class="px-5 py-3 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <span class="text-slate-500 dark:text-zinc-400 font-medium">
                  Showing <span class="font-bold text-slate-700 dark:text-zinc-200">{{ txPagination.from }}</span> to <span class="font-bold text-slate-700 dark:text-zinc-200">{{ txPagination.to }}</span> of <span class="font-bold text-slate-700 dark:text-zinc-200">{{ txPagination.total }}</span> transactions
                </span>

                <div class="flex items-center space-x-1">
                  <button 
                    @click="changeTxPage(txPagination.current_page - 1)" 
                    :disabled="txPagination.current_page <= 1"
                    class="px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-zinc-700/80 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold hover:bg-slate-100 dark:hover:bg-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                  >
                    Prev
                  </button>
                  <span class="px-3 py-1 font-bold text-indigo-600 dark:text-indigo-400">
                    {{ txPagination.current_page }} / {{ txPagination.last_page }}
                  </span>
                  <button 
                    @click="changeTxPage(txPagination.current_page + 1)" 
                    :disabled="txPagination.current_page >= txPagination.last_page"
                    class="px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-zinc-700/80 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold hover:bg-slate-100 dark:hover:bg-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-3 bg-slate-50 dark:bg-zinc-900/80 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-between shrink-0 text-xs">
        <span class="text-slate-400 dark:text-zinc-500 font-medium">
          Default period: <strong class="text-slate-600 dark:text-zinc-300">This Month</strong>
        </span>
        <button 
          @click="$emit('close')"
          class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 font-bold transition-colors"
        >
          Close
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue';
import axios from 'axios';
import FloatingDateRangePicker from '../common/FloatingDateRangePicker.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  employee: { type: Object, default: () => null },
});

const emit = defineEmits(['close']);

// Date Range (default "This Month")
const dateRange = ref({
  startDate: null,
  endDate: null,
  preset: 'this_month',
});

// Ledger summary states
const activeEmployee = ref(null);
const openingBalance = ref(0);
const closingBalance = ref(0);
const stats = ref({
  total_debits: 0,
  total_credits: 0,
  net_balance: 0,
});

// Transactions Datatable state
const txData = ref([]);
const txLoading = ref(false);
const txSearch = ref('');
const txPerPage = ref(5);
const txPagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0,
  from: 0,
  to: 0,
});
const sortKey = ref('date');
const sortAsc = ref(false);

const pdfLoading = ref(false);
let debounceTimeout = null;

// Helper formatters
const formatNumber = (val) => {
  const num = parseFloat(val) || 0;
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dStr) => {
  if (!dStr) return 'N/A';
  const d = new Date(dStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getInitials = (name) => {
  if (!name) return 'E';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.slice(0, 2).toUpperCase();
};

// Fetch overall ledger stats
const fetchLedgerSummary = async () => {
  if (!props.employee?.id) return;
  try {
    const params = {};
    if (dateRange.value.startDate) params.start_date = dateRange.value.startDate;
    if (dateRange.value.endDate) params.end_date = dateRange.value.endDate;

    const res = await axios.get(`/api/employees/${props.employee.id}/ledger`, { params });
    if (res.data) {
      activeEmployee.value = res.data.employee;
      openingBalance.value = res.data.opening_balance;
      closingBalance.value = res.data.closing_balance;
      stats.value = res.data.stats;
    }
  } catch (err) {
    console.error('Error fetching employee ledger summary:', err);
  }
};

// Fetch Transactions datatable
const fetchTransactions = async (page = 1) => {
  if (!props.employee?.id) return;
  txLoading.value = true;
  try {
    const params = {
      page,
      per_page: txPerPage.value,
      search: txSearch.value,
    };
    if (dateRange.value.startDate) params.start_date = dateRange.value.startDate;
    if (dateRange.value.endDate) params.end_date = dateRange.value.endDate;

    const res = await axios.get(`/api/employees/${props.employee.id}/transactions`, { params });
    if (res.data) {
      let items = res.data.data || [];

      // Client side sort if needed
      if (sortKey.value) {
        items.sort((a, b) => {
          let valA = a[sortKey.value];
          let valB = b[sortKey.value];
          if (valA < valB) return sortAsc.value ? -1 : 1;
          if (valA > valB) return sortAsc.value ? 1 : -1;
          return 0;
        });
      }

      txData.value = items;
      txPagination.value = {
        current_page: res.data.current_page,
        last_page: res.data.last_page,
        per_page: res.data.per_page,
        total: res.data.total,
        from: res.data.from,
        to: res.data.to,
      };
    }
  } catch (err) {
    console.error('Error fetching employee transactions:', err);
  } finally {
    txLoading.value = false;
  }
};

const debouncedFetchTx = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchTransactions(1);
  }, 350);
};

const onTxPerPageChange = () => {
  fetchTransactions(1);
};

const changeTxPage = (page) => {
  if (page >= 1 && page <= txPagination.value.last_page) {
    fetchTransactions(page);
  }
};

const sortTable = (key) => {
  if (sortKey.value === key) {
    sortAsc.value = !sortAsc.value;
  } else {
    sortKey.value = key;
    sortAsc.value = true;
  }
  fetchTransactions(txPagination.value.current_page);
};

const onDateRangeChange = (newVal) => {
  dateRange.value = newVal;
  fetchLedgerSummary();
  fetchTransactions(1);
};

// Download PDF export
const downloadPdf = async () => {
  if (!props.employee?.id) return;
  pdfLoading.value = true;
  try {
    const params = {};
    if (dateRange.value.startDate) params.start_date = dateRange.value.startDate;
    if (dateRange.value.endDate) params.end_date = dateRange.value.endDate;

    const res = await axios.get(`/api/employees/${props.employee.id}/ledger/pdf`, {
      params,
      responseType: 'blob',
    });

    const blob = new Blob([res.data], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    const empName = (activeEmployee.value?.name || props.employee.full_name || 'employee').toLowerCase().replace(/[^a-z0-9]/g, '_');
    link.download = `employee_ledger_${empName}.pdf`;
    link.click();
    window.URL.revokeObjectURL(link.href);
  } catch (err) {
    console.error('Error downloading employee ledger PDF:', err);
  } finally {
    pdfLoading.value = false;
  }
};

// Watchers
watch(() => props.show, (newVal) => {
  if (newVal && props.employee) {
    fetchLedgerSummary();
    fetchTransactions(1);
  }
});

onUnmounted(() => {
  clearTimeout(debounceTimeout);
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
</style>
