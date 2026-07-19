<template>
  <div class="w-full max-w-full py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950 min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">Customer Management</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Manage your customer database and relationships</p>
      </div>
      <button
        @click="handleCreateCustomer"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-1.5 active:scale-95 cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
        <span>Add Customer</span>
      </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Total Customers</p>
            <p class="text-2xl font-extrabold text-slate-800 dark:text-zinc-100 mt-1">{{ formatNumber(statistics.total_customers || 0) }}</p>
          </div>
          <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Active Customers</p>
            <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ formatNumber(statistics.active_customers || 0) }}</p>
          </div>
          <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Inactive Customers</p>
            <p class="text-2xl font-extrabold text-rose-500 dark:text-rose-400 mt-1">{{ formatNumber(statistics.inactive_customers || 0) }}</p>
          </div>
          <div class="w-10 h-10 bg-rose-50 dark:bg-rose-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-soft">
      <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-zinc-800">
        <!-- Search & Toggle Group -->
        <div class="flex items-center space-x-3">
          <!-- Search -->
          <div class="relative w-96">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, email, phone or location"
              class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-0 focus:bg-white dark:focus:bg-zinc-800 transition-all text-slate-700 dark:text-zinc-200 dark:placeholder-zinc-500"
              @input="debouncedSearch"
            />
          </div>
          <!-- Status Switch/Toggle -->
          <div class="flex items-center bg-slate-100 dark:bg-zinc-800 p-0.5 rounded-lg text-[11px] font-semibold">
            <button
              type="button"
              @click="statusFilter = ''; loadCustomers(1);"
              :class="statusFilter === '' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-sm' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-3 py-1 rounded-md transition-all cursor-pointer"
            >
              All
            </button>
            <button
              type="button"
              @click="statusFilter = '1'; loadCustomers(1);"
              :class="statusFilter === '1' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-sm' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-3 py-1 rounded-md transition-all cursor-pointer"
            >
              Active
            </button>
            <button
              type="button"
              @click="statusFilter = '0'; loadCustomers(1);"
              :class="statusFilter === '0' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-sm' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-3 py-1 rounded-md transition-all cursor-pointer"
            >
              Inactive
            </button>
          </div>
        </div>
        <!-- Showing -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-zinc-400">
          <span>Showing</span>
          <select
            v-model="perPage"
            @change="loadCustomers(1)"
            class="border border-slate-200 dark:border-zinc-700 rounded px-1.5 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer bg-white dark:bg-zinc-800 dark:text-zinc-200"
          >
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <span>of {{ customers.total || 0 }} results</span>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 uppercase font-bold tracking-wider">
              <th class="py-3 px-4 bg-slate-50 dark:bg-zinc-800/50">Customer</th>
              <th class="py-3 px-4 bg-slate-50 dark:bg-zinc-800/50">Contact</th>
              <th class="py-3 px-4 bg-slate-50 dark:bg-zinc-800/50">Location</th>
              <th class="py-3 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Credit Limit</th>
              <th class="py-3 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Wallet</th>
              <th class="py-3 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Due Amount</th>
              <th class="py-3 px-4 text-center bg-slate-50 dark:bg-zinc-800/50">Status</th>
              <th class="py-3 px-4 text-center bg-slate-50 dark:bg-zinc-800/50 w-[80px]">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/70 dark:divide-zinc-800">
            <tr v-if="loading" class="bg-white dark:bg-zinc-900">
              <td colspan="8" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <div class="animate-spin rounded-full h-7 w-7 border-2 border-slate-300 dark:border-zinc-600 border-t-blue-600"></div>
                  <span class="text-xs font-semibold">Loading customers...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="!customers.data || customers.data.length === 0" class="bg-white dark:bg-zinc-900">
              <td colspan="8" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span>No customers found. Get started by adding your first customer.</span>
              </td>
            </tr>
            <tr v-else v-for="item in customers.data" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/50 transition-colors">
              <!-- Customer -->
              <td class="py-3.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">{{ getInitials(item.name) }}</span>
                  </div>
                  <div>
                    <button @click="viewLedger(item)" class="font-bold text-slate-800 dark:text-zinc-100 text-sm hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer">{{ item.name }}</button>
                    <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">ID: #{{ item.id }}</div>
                  </div>
                </div>
              </td>
              <!-- Contact -->
              <td class="py-3.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="text-slate-700 dark:text-zinc-200 text-xs">{{ item.email || '-' }}</div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ item.phone || '-' }}</div>
              </td>
              <!-- Location -->
              <td class="py-3.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="text-slate-700 dark:text-zinc-200 text-xs">{{ item.city || '-' }}</div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ item.state || '-' }}</div>
              </td>
              <!-- Credit Limit -->
              <td class="py-3.5 px-4 text-right font-semibold text-slate-700 dark:text-zinc-200 text-sm align-middle bg-white dark:bg-zinc-900">
                ${{ formatNumber(item.credit_limit || 0) }}
              </td>
              <!-- Wallet -->
              <td class="py-3.5 px-4 text-right font-bold text-sm align-middle bg-white dark:bg-zinc-900">
                <span
                  v-if="parseFloat(item.wallet_balance || 0) > 0"
                  class="text-amber-600 dark:text-amber-400"
                >
                  ${{ formatNumber(item.wallet_balance) }}
                </span>
                <span
                  v-else
                  class="text-slate-400 dark:text-zinc-500 font-medium"
                >
                  0.00
                </span>
              </td>
              <!-- Due Amount -->
              <td class="py-3.5 px-4 text-right font-bold text-sm align-middle bg-white dark:bg-zinc-900">
                <span
                  v-if="parseFloat(item.due_amount || 0) > 0"
                  class="text-rose-600 dark:text-rose-450"
                >
                  ${{ formatNumber(item.due_amount) }}
                </span>
                <span
                  v-else
                  class="text-slate-400 dark:text-zinc-500 font-medium"
                >
                  0.00
                </span>
              </td>
              <!-- Status -->
              <td class="py-3.5 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <span
                  :class="item.is_active ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <!-- Action -->
              <td class="py-3.5 px-4 text-center relative align-middle bg-white dark:bg-zinc-900">
                <button
                  @click.stop="toggleActionDropdown(item.id)"
                  class="text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all focus:outline-none cursor-pointer"
                >
                  <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 5a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                </button>
                <div
                  v-if="openActionDropdown === item.id"
                  class="absolute right-4 mt-1 w-32 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg py-1 z-50 animate-fade-in"
                >
                  <button @click="viewCustomer(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>View</span>
                  </button>
                  <button @click="editCustomer(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                  </button>
                  <button @click="viewLedger(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ledger</span>
                  </button>
                  <div class="border-t border-slate-100 dark:border-zinc-800 my-1"></div>
                  <button @click="deleteCustomer(item)" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between p-4 border-t border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <div class="text-xs text-slate-500 dark:text-zinc-400">
          Page {{ customers.current_page || 1 }} of {{ customers.last_page || 1 }}
        </div>
        <div class="flex items-center space-x-1">
          <button
            @click="changePage(customers.current_page - 1)"
            :disabled="customers.current_page === 1"
            class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50 cursor-pointer"
          >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <template v-for="page in visiblePages" :key="page">
            <button
              @click="changePage(page)"
              class="relative inline-flex items-center px-3 py-1.5 border text-xs font-bold transition-all cursor-pointer rounded-lg"
              :class="page === customers.current_page ? 'z-10 bg-slate-100 dark:bg-zinc-800 border-slate-300 dark:border-zinc-600 text-slate-800 dark:text-zinc-100' : 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800'"
            >
              {{ page }}
            </button>
          </template>
          <button
            @click="changePage(customers.current_page + 1)"
            :disabled="customers.current_page === customers.last_page"
            class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50 cursor-pointer"
          >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <CustomerModalSimple
      :show="showCreateModal || showEditModal"
      :customer="selectedCustomer"
      :is-edit="showEditModal"
      @close="closeModal"
      @saved="handleCustomerSaved"
    />

    <CustomerViewModalSimple
      :show="showViewModal"
      :customer="selectedCustomer"
      @close="closeModal"
    />

    <CustomerLedger
      :show="showLedgerModal"
      :customer="selectedCustomer"
      @close="closeModal"
    />
  </div>
</template>

<script>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { debounce } from '@/utils/debounce';
import CustomerModalSimple from './CustomerModalSimple.vue';
import CustomerViewModalSimple from './CustomerViewModalSimple.vue';
import CustomerLedger from './CustomerLedger.vue';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

export default {
  name: 'Customers',
  components: {
    CustomerModalSimple,
    CustomerViewModalSimple,
    CustomerLedger
  },
  setup() {
    const { showToast } = useToast();

    const loading = ref(false);
    const customers = ref({ data: [], current_page: 1, last_page: 1, total: 0 });
    const statistics = ref({});
    const searchQuery = ref('');
    const perPage = ref(15);
    const statusFilter = ref('');
    const openActionDropdown = ref(null);

    const selectedCustomer = ref(null);
    const showCreateModal = ref(false);
    const showEditModal = ref(false);
    const showViewModal = ref(false);
    const showLedgerModal = ref(false);

    const loadCustomers = async (page = 1) => {
      loading.value = true;
      try {
        const params = { page, per_page: perPage.value };
        if (searchQuery.value) params.search = searchQuery.value;
        if (statusFilter.value !== '') params.is_active = statusFilter.value;

        const response = await api.get('/customers', { params });
        customers.value = response.data;
      } catch (error) {
        showToast('Error loading customers: ' + (error.response?.data?.message || error.message), 'error');
      } finally {
        loading.value = false;
      }
    };

    const loadStatistics = async () => {
      try {
        const response = await api.get('/customers/statistics');
        statistics.value = response.data;
      } catch (error) {
        console.error('Error loading statistics:', error);
      }
    };

    const debouncedSearch = debounce(() => { loadCustomers(1); }, 300);

    const changePage = (page) => {
      if (page >= 1 && page <= customers.value.last_page) {
        loadCustomers(page);
      }
    };

    const visiblePages = computed(() => {
      const current = customers.value.current_page;
      const last = customers.value.last_page;
      const pages = [];
      for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
      }
      return pages;
    });

    const toggleActionDropdown = (id) => {
      openActionDropdown.value = openActionDropdown.value === id ? null : id;
    };

    const closeAllDropdowns = () => { openActionDropdown.value = null; };

    const handleCreateCustomer = () => {
      selectedCustomer.value = null;
      showCreateModal.value = true;
    };

    const viewCustomer = (customer) => {
      selectedCustomer.value = customer;
      showViewModal.value = true;
      openActionDropdown.value = null;
    };

    const editCustomer = (customer) => {
      selectedCustomer.value = { ...customer };
      showEditModal.value = true;
      openActionDropdown.value = null;
    };

    const viewLedger = (customer) => {
      selectedCustomer.value = customer;
      showLedgerModal.value = true;
      openActionDropdown.value = null;
    };

    const closeModal = () => {
      showCreateModal.value = false;
      showEditModal.value = false;
      showViewModal.value = false;
      showLedgerModal.value = false;
      selectedCustomer.value = null;
    };

    const handleCustomerSaved = () => {
      loadCustomers(customers.value.current_page);
      loadStatistics();
      closeModal();
    };

    const deleteCustomer = async (customer) => {
      openActionDropdown.value = null;
      if (confirm(`Are you sure you want to delete customer "${customer.name}"?`)) {
        try {
          await api.delete(`/customers/${customer.id}`);
          showToast('Customer deleted successfully', 'success');
          loadCustomers(customers.value.current_page);
          loadStatistics();
        } catch (error) {
          showToast(error.response?.data?.message || 'Error deleting customer', 'error');
        }
      }
    };

    const formatNumber = (value) => new Intl.NumberFormat().format(value || 0);

    const getInitials = (name) => {
      if (!name) return '?';
      return name.split(' ').map(w => w.charAt(0).toUpperCase()).slice(0, 2).join('');
    };

    onMounted(() => {
      loadCustomers();
      loadStatistics();
      document.addEventListener('click', closeAllDropdowns);
    });

    onUnmounted(() => {
      document.removeEventListener('click', closeAllDropdowns);
    });

    return {
      loading, customers, statistics, searchQuery, perPage, statusFilter, openActionDropdown,
      selectedCustomer, showCreateModal, showEditModal, showViewModal, showLedgerModal,
      visiblePages, loadCustomers, debouncedSearch, changePage, toggleActionDropdown,
      handleCreateCustomer, viewCustomer, editCustomer, viewLedger, deleteCustomer,
      closeModal, handleCustomerSaved, formatNumber, getInitials
    };
  }
};
</script>

<style scoped>
.shadow-soft {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
}
.animate-fade-in {
  animation: fadeIn 0.15s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
