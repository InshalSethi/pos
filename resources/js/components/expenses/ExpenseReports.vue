<template>
  <div class="expense-reports space-y-6">
    <!-- Date Range Filter Card -->
    <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Start Date</label>
          <input
            v-model="dateRange.start_date"
            type="date"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
            @change="fetchStatistics"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">End Date</label>
          <input
            v-model="dateRange.end_date"
            type="date"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
            @change="fetchStatistics"
          />
        </div>
      </div>

      <!-- Quick Range Buttons -->
      <div class="flex flex-wrap gap-2 pt-1 border-t border-slate-100 dark:border-zinc-800">
        <button
          @click="setQuickRange('thisMonth')"
          class="px-3.5 py-1.5 text-xs font-bold border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-all cursor-pointer"
        >
          This Month
        </button>
        <button
          @click="setQuickRange('lastMonth')"
          class="px-3.5 py-1.5 text-xs font-bold border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-all cursor-pointer"
        >
          Last Month
        </button>
        <button
          @click="setQuickRange('thisYear')"
          class="px-3.5 py-1.5 text-xs font-bold border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-all cursor-pointer"
        >
          This Year
        </button>
      </div>
    </div>

    <!-- Statistics KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-900 dark:text-white shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Total Expenses</span>
          <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ statistics.total_expenses || 0 }}</p>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Total Amount</span>
          <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">${{ formatAmount(statistics.total_amount) }}</p>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Pending Approval</span>
          <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ statistics.pending_approval || 0 }}</p>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Paid Amount</span>
          <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">${{ formatAmount(statistics.paid_amount) }}</p>
        </div>
      </div>
    </div>

    <!-- Charts / Breakdown Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- By Category -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Expenses by Category</h3>
        <div v-if="statistics.by_category && statistics.by_category.length > 0" class="space-y-3">
          <div v-for="item in statistics.by_category" :key="item.category_id" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 bg-slate-900 dark:bg-white rounded-full"></div>
              <span class="text-xs font-bold text-slate-900 dark:text-white">{{ item.category?.name || 'Unknown' }}</span>
            </div>
            <div class="text-right">
              <div class="text-xs font-black text-slate-900 dark:text-white">${{ formatAmount(item.total_amount) }}</div>
              <div class="text-[10px] font-semibold text-slate-400 dark:text-zinc-400">{{ item.count }} expenses</div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-xs text-slate-400 dark:text-zinc-500 font-semibold">
          No category breakdown available
        </div>
      </div>

      <!-- By Status -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Expenses by Status</h3>
        <div v-if="statistics.by_status && statistics.by_status.length > 0" class="space-y-3">
          <div v-for="item in statistics.by_status" :key="item.status" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div :class="getStatusColor(item.status)" class="w-3 h-3 rounded-full"></div>
              <span class="text-xs font-bold text-slate-900 dark:text-white">{{ getStatusText(item.status) }}</span>
            </div>
            <div class="text-right">
              <div class="text-xs font-black text-slate-900 dark:text-white">${{ formatAmount(item.total_amount) }}</div>
              <div class="text-[10px] font-semibold text-slate-400 dark:text-zinc-400">{{ item.count }} expenses</div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-xs text-slate-400 dark:text-zinc-500 font-semibold">
          No status breakdown available
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const statistics = ref({});
const loading = ref(false);
const dateRange = ref({
  start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  end_date: new Date().toISOString().split('T')[0]
});

const fetchStatistics = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/expenses/statistics/summary', {
      params: dateRange.value
    });
    statistics.value = response.data;
  } catch (error) {
    console.error('Error fetching statistics:', error);
  } finally {
    loading.value = false;
  }
};

const setQuickRange = (rangeType) => {
  const now = new Date();

  if (rangeType === 'thisMonth') {
    dateRange.value = {
      start_date: new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0],
      end_date: now.toISOString().split('T')[0]
    };
  } else if (rangeType === 'lastMonth') {
    dateRange.value = {
      start_date: new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0],
      end_date: new Date(now.getFullYear(), now.getMonth(), 0).toISOString().split('T')[0]
    };
  } else if (rangeType === 'thisYear') {
    dateRange.value = {
      start_date: new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0],
      end_date: now.toISOString().split('T')[0]
    };
  }

  fetchStatistics();
};

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toFixed(2);
};

const getStatusColor = (status) => {
  const colors = {
    draft: 'bg-slate-400',
    submitted: 'bg-amber-500',
    approved: 'bg-indigo-500',
    rejected: 'bg-rose-500',
    paid: 'bg-emerald-500'
  };
  return colors[status] || 'bg-slate-400';
};

const getStatusText = (status) => {
  const texts = {
    draft: 'Draft',
    submitted: 'Submitted',
    approved: 'Approved',
    rejected: 'Rejected',
    paid: 'Paid'
  };
  return texts[status] || status;
};

onMounted(() => {
  fetchStatistics();
});
</script>
