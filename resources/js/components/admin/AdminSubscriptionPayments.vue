<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">
          Subscription Payments History
        </h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
          Audit trail of all customer subscription transactions, plan purchases, and renewal payments.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="fetchPayments"
          :disabled="loading"
          class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-all flex items-center gap-2"
        >
          <svg :class="{'animate-spin': loading}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh History
        </button>
      </div>
    </div>

    <!-- Analytics Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Revenue -->
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Revenue</p>
          <h3 class="text-2xl font-black text-slate-900 dark:text-zinc-100 mt-1">${{ formatCurrency(stats.total_revenue) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <!-- Monthly Revenue -->
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">This Month</p>
          <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">${{ formatCurrency(stats.monthly_revenue) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
      </div>

      <!-- Total Transactions -->
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Transactions</p>
          <h3 class="text-2xl font-black text-slate-900 dark:text-zinc-100 mt-1">{{ stats.total_transactions || 0 }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
      </div>

      <!-- Successful Payments -->
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Paid Rate</p>
          <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
            {{ stats.total_transactions ? Math.round((stats.paid_count / stats.total_transactions) * 100) : 100 }}%
          </h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
      <!-- Search Input -->
      <div class="relative w-full md:w-80">
        <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="filters.search"
          @input="debounceFetch"
          type="text"
          placeholder="Search user, email or Txn ID..."
          class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        />
      </div>

      <!-- Filters Group -->
      <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
        <!-- Plan Filter -->
        <select
          v-model="filters.plan"
          @change="fetchPayments"
          class="px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-700 dark:text-zinc-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        >
          <option value="all">All Plans</option>
          <option value="standard">Standard</option>
          <option value="basic">Basic</option>
          <option value="advance">Advance</option>
          <option value="enterprise">Enterprise</option>
          <option value="custom">Custom</option>
        </select>

        <!-- Status Filter -->
        <select
          v-model="filters.status"
          @change="fetchPayments"
          class="px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-700 dark:text-zinc-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        >
          <option value="all">All Statuses</option>
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
          <option value="failed">Failed</option>
        </select>
      </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-zinc-950 border-b border-slate-200 dark:border-zinc-800 text-slate-500 dark:text-zinc-400 uppercase tracking-wider font-bold">
            <tr>
              <th class="px-6 py-3.5">Customer / Email</th>
              <th class="px-6 py-3.5">Plan</th>
              <th class="px-6 py-3.5">Billing Cycle</th>
              <th class="px-6 py-3.5">Amount</th>
              <th class="px-6 py-3.5">Coupon & Discount</th>
              <th class="px-6 py-3.5">Payment Method</th>
              <th class="px-6 py-3.5">Transaction ID</th>
              <th class="px-6 py-3.5">Date</th>
              <th class="px-6 py-3.5 text-right">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/80">
            <tr v-if="loading" class="text-center py-10">
              <td colspan="9" class="px-6 py-12 text-slate-400 dark:text-zinc-500">
                <div class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span>Loading payments history...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="payments.length === 0" class="text-center py-10">
              <td colspan="9" class="px-6 py-12 text-slate-400 dark:text-zinc-500">
                No subscription payments found matching parameters.
              </td>
            </tr>

            <tr
              v-for="item in payments"
              :key="item.id"
              class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <!-- Customer -->
              <td class="px-6 py-4">
                <div class="font-bold text-slate-900 dark:text-zinc-100">{{ item.user_name }}</div>
                <div class="text-[11px] text-slate-500 dark:text-zinc-400">{{ item.user_email }}</div>
              </td>

              <!-- Plan -->
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide"
                  :class="getPlanBadgeClass(item.plan_name)"
                >
                  {{ item.plan_name }}
                </span>
              </td>

              <!-- Billing Cycle -->
              <td class="px-6 py-4 capitalize font-semibold text-slate-700 dark:text-zinc-300">
                {{ item.billing_cycle }}
              </td>

              <!-- Amount -->
              <td class="px-6 py-4">
                <div class="font-black text-slate-900 dark:text-zinc-100">
                  ${{ formatCurrency(item.amount) }}
                </div>
                <div v-if="item.coupon_code && item.original_amount && Number(item.original_amount) > Number(item.amount)" class="text-[10px] text-slate-400 dark:text-zinc-500 line-through font-semibold">
                  ${{ formatCurrency(item.original_amount) }}
                </div>
              </td>

              <!-- Coupon Code & Discount -->
              <td class="px-6 py-4">
                <div v-if="item.coupon_code" class="flex flex-col gap-0.5">
                  <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase font-mono px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 w-max">
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    {{ item.coupon_code }}
                  </span>
                  <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    -${{ formatCurrency(item.discount_amount) }}
                  </span>
                </div>
                <span v-else class="text-slate-400 dark:text-zinc-600 text-[11px] font-medium">—</span>
              </td>

              <!-- Payment Method & Last 4 -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-1.5 font-medium text-slate-700 dark:text-zinc-300">
                  <span class="bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-[10px] font-bold text-slate-700 dark:text-zinc-300">
                    {{ item.payment_method || 'Card' }}
                  </span>
                  <span v-if="item.card_last_four" class="text-xs text-slate-500 dark:text-zinc-400 font-mono">•••• {{ item.card_last_four }}</span>
                </div>
              </td>

              <!-- Txn ID -->
              <td class="px-6 py-4 font-mono text-[11px] text-slate-600 dark:text-zinc-400 font-semibold">
                {{ item.transaction_id }}
              </td>

              <!-- Date -->
              <td class="px-6 py-4 text-slate-500 dark:text-zinc-400">
                {{ formatDate(item.paid_at || item.created_at) }}
              </td>

              <!-- Status -->
              <td class="px-6 py-4 text-right">
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold capitalize"
                  :class="item.status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'"
                >
                  ● {{ item.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 bg-slate-50 dark:bg-zinc-950 border-t border-slate-200 dark:border-zinc-800 flex justify-between items-center text-xs">
        <span class="text-slate-500 dark:text-zinc-400">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} payments
        </span>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 disabled:opacity-50 font-bold"
          >
            Prev
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 disabled:opacity-50 font-bold"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const payments = ref([]);
const stats = ref({
  total_revenue: 0,
  total_transactions: 0,
  paid_count: 0,
  monthly_revenue: 0
});

const filters = ref({
  search: '',
  plan: 'all',
  status: 'all',
  page: 1
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

let debounceTimer = null;
const debounceFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    filters.value.page = 1;
    fetchPayments();
  }, 300);
};

const fetchPayments = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/admin/api/subscription-payments', {
      params: {
        search: filters.value.search,
        plan: filters.value.plan,
        status: filters.value.status,
        page: filters.value.page
      }
    });

    if (res.data.success) {
      payments.value = res.data.payments.data || [];
      stats.value = res.data.stats || stats.value;
      pagination.value = {
        current_page: res.data.payments.current_page,
        last_page: res.data.payments.last_page,
        per_page: res.data.payments.per_page,
        total: res.data.payments.total,
        from: res.data.payments.from,
        to: res.data.payments.to
      };
    }
  } catch (err) {
    console.error('Failed to load subscription payments:', err);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.value.page = page;
    fetchPayments();
  }
};

const formatCurrency = (val) => {
  return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateStr).toLocaleDateString(undefined, options);
};

const getPlanBadgeClass = (plan) => {
  const p = (plan || '').toLowerCase();
  if (p === 'enterprise' || p === 'elite') return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
  if (p === 'advance' || p === 'master') return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300';
  if (p === 'basic') return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
  return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
};

onMounted(() => {
  fetchPayments();
});
</script>
