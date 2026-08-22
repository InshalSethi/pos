<template>
  <div class="space-y-6">
    
    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Total Logs Today -->
      <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-slate-200/80 dark:border-zinc-800 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Activities Today</p>
          <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stats.total_today || 0 }}</h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <!-- Active Users / Employees Today -->
      <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-slate-200/80 dark:border-zinc-800 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Active Actors Today</p>
          <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stats.active_users_today || 0 }}</h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-100 dark:border-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
      </div>

      <!-- Security & Auth Events -->
      <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-slate-200/80 dark:border-zinc-800 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Security Events</p>
          <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stats.security_events_today || 0 }}</h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-100 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
      </div>

    </div>

    <!-- Filters & Search Section -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-4">
      
      <!-- Category Filter Pills & Download PDF Button -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-none">
          <button
            v-for="cat in categories"
            :key="cat.key"
            @click="selectCategory(cat.key)"
            :class="[
              'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap',
              filters.log_type === cat.key
                ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs'
                : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700'
            ]"
          >
            {{ cat.label }}
          </button>
        </div>

        <!-- Download PDF Button -->
        <button
          @click="downloadPdf"
          :disabled="downloadingPdf"
          class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center space-x-1.5 shrink-0 cursor-pointer disabled:opacity-50"
        >
          <svg v-if="!downloadingPdf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <svg v-else class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span>{{ downloadingPdf ? 'Exporting PDF...' : 'Download PDF' }}</span>
        </button>
      </div>

      <!-- Control Bar: Search, Single Floating Date Range Picker, Page Limiter, Reset -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-2 border-t border-slate-100 dark:border-zinc-800 items-end">
        
        <!-- Search Input (4 Cols) -->
        <div class="md:col-span-4 relative">
          <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Search Keywords</label>
          <div class="relative">
            <input
              v-model="filters.search"
              @input="debouncedFetch"
              type="text"
              placeholder="Search by description, target, IP..."
              class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-zinc-700"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Single Floating Date Range Picker (4 Cols) -->
        <div class="md:col-span-4">
          <FloatingDateRangePicker
            v-model:startDate="filters.date_from"
            v-model:endDate="filters.date_to"
            label="DATE RANGE FILTER"
            placeholder="All Time"
            @change="fetchLogs(1)"
            @clear="fetchLogs(1)"
          />
        </div>

        <!-- Rows Per Page Limiter (2 Cols) -->
        <div class="md:col-span-2">
          <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Per Page</label>
          <select
            v-model="filters.per_page"
            @change="fetchLogs(1)"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer"
          >
            <option :value="10">10 per page</option>
            <option :value="15">15 per page</option>
            <option :value="25">25 per page</option>
            <option :value="50">50 per page</option>
            <option :value="100">100 per page</option>
          </select>
        </div>

        <!-- Reset Button (2 Cols) -->
        <div class="md:col-span-2">
          <button
            @click="resetFilters"
            class="w-full py-2 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
          >
            Reset Filters
          </button>
        </div>

      </div>
    </div>

    <!-- Logs DataTable -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
      
      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center text-slate-400">
        <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-slate-900 dark:text-white" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <span class="text-xs font-semibold">Loading activity logs...</span>
      </div>

      <!-- Data Table -->
      <div v-else-if="logs.data && logs.data.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50/70 dark:bg-zinc-800/50 border-b border-slate-200/80 dark:border-zinc-800 text-slate-400 dark:text-zinc-500 uppercase tracking-wider font-bold text-[10px]">
              
              <!-- Sortable Header: Timestamp -->
              <th @click="sortBy('created_at')" class="py-3.5 px-4 cursor-pointer hover:text-slate-900 dark:hover:text-white select-none transition-colors">
                <div class="flex items-center space-x-1">
                  <span>Timestamp</span>
                  <span class="text-xs">
                    <template v-if="filters.sort_by === 'created_at'">
                      {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
                    </template>
                    <template v-else><span class="opacity-30">↕</span></template>
                  </span>
                </div>
              </th>

              <!-- Sortable Header: Actor -->
              <th @click="sortBy('user_id')" class="py-3.5 px-4 cursor-pointer hover:text-slate-900 dark:hover:text-white select-none transition-colors">
                <div class="flex items-center space-x-1">
                  <span>Actor</span>
                  <span class="text-xs">
                    <template v-if="filters.sort_by === 'user_id'">
                      {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
                    </template>
                    <template v-else><span class="opacity-30">↕</span></template>
                  </span>
                </div>
              </th>

              <!-- Sortable Header: Category -->
              <th @click="sortBy('log_type')" class="py-3.5 px-4 cursor-pointer hover:text-slate-900 dark:hover:text-white select-none transition-colors">
                <div class="flex items-center space-x-1">
                  <span>Category</span>
                  <span class="text-xs">
                    <template v-if="filters.sort_by === 'log_type'">
                      {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
                    </template>
                    <template v-else><span class="opacity-30">↕</span></template>
                  </span>
                </div>
              </th>

              <!-- Sortable Header: Description -->
              <th @click="sortBy('description')" class="py-3.5 px-4 cursor-pointer hover:text-slate-900 dark:hover:text-white select-none transition-colors">
                <div class="flex items-center space-x-1">
                  <span>Activity Description</span>
                  <span class="text-xs">
                    <template v-if="filters.sort_by === 'description'">
                      {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
                    </template>
                    <template v-else><span class="opacity-30">↕</span></template>
                  </span>
                </div>
              </th>

              <!-- Sortable Header: IP -->
              <th @click="sortBy('ip_address')" class="py-3.5 px-4 cursor-pointer hover:text-slate-900 dark:hover:text-white select-none transition-colors">
                <div class="flex items-center space-x-1">
                  <span>IP Address</span>
                  <span class="text-xs">
                    <template v-if="filters.sort_by === 'ip_address'">
                      {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
                    </template>
                    <template v-else><span class="opacity-30">↕</span></template>
                  </span>
                </div>
              </th>

              <th class="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 font-medium">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors">
              
              <!-- Timestamp -->
              <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                <div class="font-bold text-slate-900 dark:text-white">{{ formatDate(log.created_at) }}</div>
                <div class="text-[10px] text-slate-400">{{ formatRelativeTime(log.created_at) }}</div>
              </td>

              <!-- Actor Name, Email & Role -->
              <td class="py-3.5 px-4 whitespace-nowrap">
                <div class="flex items-center space-x-2.5">
                  <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-slate-200 overflow-hidden shrink-0">
                    <img v-if="log.actor_avatar" :src="log.actor_avatar" class="w-full h-full object-cover" />
                    <span v-else>{{ (log.actor_name || 'U').charAt(0).toUpperCase() }}</span>
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-white leading-tight">{{ log.actor_name || 'System / Guest' }}</div>
                    <!-- Email displayed directly below name -->
                    <div v-if="log.actor_email || log.user?.email || log.employee?.email" class="text-[11px] font-medium text-slate-500 dark:text-slate-400 font-mono leading-tight">
                      {{ log.actor_email || log.user?.email || log.employee?.email }}
                    </div>
                    <div class="text-[10px] text-slate-400 capitalize mt-0.5">{{ log.actor_role }} • {{ log.actor_type }}</div>
                  </div>
                </div>
              </td>

              <!-- Category Badge -->
              <td class="py-3.5 px-4 whitespace-nowrap">
                <span :class="getCategoryBadgeClass(log.log_type)">
                  {{ log.log_type }}
                </span>
              </td>

              <!-- Description & Target Subject -->
              <td class="py-3.5 px-4">
                <div class="text-slate-900 dark:text-slate-100 font-semibold">{{ log.description }}</div>
                <div v-if="log.subject_title" class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">
                  Target: {{ log.subject_title }}
                </div>
              </td>

              <!-- IP Address -->
              <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                {{ log.ip_address || '127.0.0.1' }}
              </td>

              <!-- Diff Viewer Action Button -->
              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <button
                  v-if="log.properties"
                  @click="openDiffModal(log)"
                  class="px-2.5 py-1 bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-lg text-[11px] font-bold transition-colors cursor-pointer"
                >
                  View Details
                </button>
                <span v-else class="text-[11px] text-slate-400 italic">No diff</span>
              </td>

            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center">
        <svg class="w-12 h-12 text-slate-300 dark:text-zinc-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h4 class="text-sm font-bold text-slate-900 dark:text-white">No activity logs recorded</h4>
        <p class="text-xs text-slate-400 mt-1">Activities will automatically appear here as users perform actions.</p>
      </div>

      <!-- AJAX Pagination Footer with Limiter -->
      <div v-if="logs.total > 0" class="px-6 py-4 border-t border-slate-200/80 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-zinc-900/50">
        <div class="flex items-center space-x-3 text-xs text-slate-500 font-medium">
          <span>
            Showing <span class="font-bold text-slate-900 dark:text-white">{{ logs.from || 0 }}</span> to <span class="font-bold text-slate-900 dark:text-white">{{ logs.to || 0 }}</span> of <span class="font-bold text-slate-900 dark:text-white">{{ logs.total }}</span> activities
          </span>
        </div>

        <div class="flex items-center space-x-2">
          <!-- Page Jump Buttons -->
          <button
            @click="fetchLogs(logs.current_page - 1)"
            :disabled="logs.current_page === 1"
            class="px-3 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
          >
            Previous
          </button>
          
          <button
            v-for="page in getPageNumbers()"
            :key="page"
            @click="page !== '...' && fetchLogs(page)"
            :disabled="page === '...'"
            :class="[
              'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
              page === logs.current_page
                ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs'
                : page === '...'
                  ? 'bg-transparent text-slate-400 cursor-default'
                  : 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700'
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="fetchLogs(logs.current_page + 1)"
            :disabled="logs.current_page === logs.last_page"
            class="px-3 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 disabled:opacity-40 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
          >
            Next
          </button>
        </div>
      </div>

    </div>

    <!-- Diff Viewer Modal -->
    <ActivityDiffModal :show="showModal" :log="selectedLog" @close="showModal = false" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ActivityDiffModal from './ActivityDiffModal.vue';
import FloatingDateRangePicker from '../common/FloatingDateRangePicker.vue';

const loading = ref(false);
const downloadingPdf = ref(false);
const logs = ref({ data: [], total: 0, current_page: 1, last_page: 1, per_page: 15 });
const stats = ref({});
const showModal = ref(false);
const selectedLog = ref(null);

const categories = [
  { key: 'all', label: 'All Activities' },
  { key: 'auth', label: 'Auth & Login' },
  { key: 'company', label: 'Company' },
  { key: 'team', label: 'Team & HR' },
  { key: 'sales', label: 'Sales & POS' },
  { key: 'inventory', label: 'Inventory' },
  { key: 'finance', label: 'Finance' },
  { key: 'security', label: 'Security' },
];

const filters = ref({
  log_type: 'all',
  search: '',
  date_from: '',
  date_to: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  per_page: 15,
});

let debounceTimer = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchLogs(1);
  }, 400);
};

const selectCategory = (type) => {
  filters.value.log_type = type;
  fetchLogs(1);
};

const sortBy = (column) => {
  if (filters.value.sort_by === column) {
    filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    filters.value.sort_by = column;
    filters.value.sort_order = 'desc';
  }
  fetchLogs(1);
};

const resetFilters = () => {
  filters.value = {
    log_type: 'all',
    search: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: 15,
  };
  fetchLogs(1);
};

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const params = { page, ...filters.value };
    const response = await axios.get('/api/activity-logs', { params });
    if (response.data.success) {
      logs.value = response.data.data;
      stats.value = response.data.stats || {};
    }
  } catch (error) {
    console.error('Failed to fetch activity logs:', error);
  } finally {
    loading.value = false;
  }
};

const downloadPdf = async () => {
  downloadingPdf.value = true;
  try {
    const params = new URLSearchParams({
      log_type: filters.value.log_type,
      search: filters.value.search,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      sort_by: filters.value.sort_by,
      sort_order: filters.value.sort_order,
    });

    const response = await axios.get(`/api/activity-logs/export-pdf?${params.toString()}`, {
      responseType: 'blob',
    });

    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `activity_audit_logs_${new Date().toISOString().slice(0, 10)}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Failed to download activity logs PDF:', error);
  } finally {
    downloadingPdf.value = false;
  }
};

const getPageNumbers = () => {
  const total = logs.value.last_page || 1;
  const current = logs.value.current_page || 1;
  const pages = [];

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 3) pages.push('...');
    
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    
    if (current < total - 2) pages.push('...');
    pages.push(total);
  }
  return pages;
};

const openDiffModal = (log) => {
  selectedLog.value = log;
  showModal.value = true;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleString();
};

const formatRelativeTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const diffSecs = Math.floor((now - date) / 1000);
  if (diffSecs < 60) return 'Just now';
  if (diffSecs < 3600) return `${Math.floor(diffSecs / 60)} mins ago`;
  if (diffSecs < 86400) return `${Math.floor(diffSecs / 3600)} hours ago`;
  return `${Math.floor(diffSecs / 86400)} days ago`;
};

const getCategoryBadgeClass = (type) => {
  const map = {
    auth: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800',
    sales: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
    team: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
    company: 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 border border-purple-200 dark:border-purple-800',
    security: 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
  };
  const base = 'px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-md tracking-wider inline-block ';
  return base + (map[type] || 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300 border border-slate-200 dark:border-zinc-700');
};

onMounted(() => {
  fetchLogs();
});
</script>
