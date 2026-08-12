<template>
  <div class="p-6 space-y-6 max-w-[1600px] mx-auto">
    <!-- Top Header & Metrics Bar (Black & White System Theme) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-xl shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Payment Receipts</h1>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage and track all incoming customer and operational payment receipts</p>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Summary Cards -->
      <div class="flex flex-wrap items-center gap-3">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-2.5 shadow-xs flex items-center gap-3">
          <div class="p-2 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Total Receipts</div>
            <div class="text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">{{ pagination.total || 0 }}</div>
          </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-2.5 shadow-xs flex items-center gap-3">
          <div class="p-2 bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Deposited / Verified</div>
            <div class="text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">{{ depositedCount }}</div>
          </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-4 py-2.5 shadow-xs flex items-center gap-3">
          <div class="p-2 bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Pending / Draft</div>
            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 font-mono">{{ pendingCount }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar Card (White and Black High-Contrast Style) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs p-5">
      <div class="flex items-center justify-between mb-3.5">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-900 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">Filter Payment Receipts</h3>
        </div>
        <button
          v-if="hasActiveFilters"
          @click="resetFilters"
          class="text-xs font-semibold text-slate-600 hover:text-black dark:text-zinc-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 px-2.5 py-1 rounded-lg transition-all inline-flex items-center gap-1.5 cursor-pointer"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Reset Filters
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Receipt Type Filter -->
        <div class="space-y-1">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Receipt Type</label>
          <div class="relative">
            <select
              v-model="filters.receipt_type"
              @change="fetchReceipts(1)"
              class="w-full appearance-none bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl px-3.5 py-2 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs cursor-pointer"
            >
              <option value="" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">All Receipt Types</option>
              <option value="customer_payment" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Customer Payment</option>
              <option value="customer_advance" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Customer Advance</option>
              <option value="supplier_refund" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Supplier Refund</option>
              <option value="supplier_rebate" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Supplier Rebate</option>
              <option value="interest_income" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Interest Income</option>
              <option value="rental_income" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Rental Income</option>
              <option value="commission_income" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Commission Income</option>
              <option value="asset_sale" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Asset Sale</option>
              <option value="bank_transfer_in" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Bank Transfer In</option>
              <option value="cash_deposit" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Cash Deposit</option>
              <option value="miscellaneous_income" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Miscellaneous Income</option>
              <option value="other_receipt" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Other Receipt</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Status Filter -->
        <div class="space-y-1">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Status</label>
          <div class="relative">
            <select
              v-model="filters.status"
              @change="fetchReceipts(1)"
              class="w-full appearance-none bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl px-3.5 py-2 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs cursor-pointer"
            >
              <option value="" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">All Statuses</option>
              <option value="draft" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Draft</option>
              <option value="pending" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Pending</option>
              <option value="verified" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Verified</option>
              <option value="deposited" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Deposited</option>
              <option value="cancelled" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Cancelled</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Floating Date Range Picker Component -->
        <div>
          <FloatingDateRangePicker
            v-model:start-date="filters.start_date"
            v-model:end-date="filters.end_date"
            @change="fetchReceipts(1)"
          />
        </div>
      </div>
    </div>

    <!-- DataTable Container -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
      <DataTable
        title="Payment Receipts Transactions"
        subtitle="Track and manage all incoming payment receipts with comprehensive workflow"
        :columns="tableColumns"
        :data="receipts"
        :loading="loading"
        :pagination="pagination"
        :initial-search="searchQuery"
        :initial-per-page="perPage"
        :default-per-page="15"
        storage-key="payment-receipts-table-state"
        empty-message="No payment receipts found"
        empty-sub-message="Get started by creating your first payment receipt."
        @search="handleTableSearch"
        @sort="handleSort"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      >
        <!-- Primary Action Button (+ New Receipt) -->
        <template #actions>
          <div class="flex items-center space-x-2">
            <button
              v-if="authStore.hasPermission('payment_receipts.create')"
              @click="showCreateModal = true"
              class="bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-semibold rounded-xl text-xs px-4 py-2.5 transition-all shadow-xs inline-flex items-center gap-2 cursor-pointer"
            >
              <span>+ New Receipt</span>
            </button>
          </div>
        </template>

        <!-- Column: Receipt Number -->
        <template #column-receipt_number="{ item }">
          <button
            @click="viewReceipt(item)"
            class="font-mono text-xs font-bold text-slate-900 dark:text-slate-100 hover:text-black dark:hover:text-white hover:underline transition-colors inline-flex items-center gap-1.5 text-left cursor-pointer"
          >
            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>{{ item.receipt_number }}</span>
          </button>
        </template>

        <!-- Column: Receipt Date -->
        <template #column-receipt_date="{ item }">
          <div class="text-xs text-slate-700 dark:text-slate-300 font-medium inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(item.receipt_date) }}</span>
          </div>
        </template>

        <!-- Column: Receipt Type -->
        <template #column-receipt_type="{ item }">
          <span :class="getReceiptTypeBadgeClass(item.receipt_type)" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-lg border">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            {{ getReceiptTypeDisplay(item.receipt_type) }}
          </span>
        </template>

        <!-- Column: Payer -->
        <template #column-payer_name="{ item }">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 flex items-center justify-center text-[10px] font-bold shrink-0 shadow-xs">
              {{ (item.payer_name || 'R').charAt(0).toUpperCase() }}
            </div>
            <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate max-w-[200px]">{{ item.payer_name || 'N/A' }}</span>
          </div>
        </template>

        <!-- Column: Amount -->
        <template #column-amount="{ item }">
          <span class="text-xs font-bold font-mono text-slate-900 dark:text-slate-100 text-right block">
            ${{ formatAmount(item.amount) }}
          </span>
        </template>

        <!-- Column: Status -->
        <template #column-status="{ item }">
          <span :class="getStatusBadgeClass(item.status)" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full border">
            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(item.status)"></span>
            {{ item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : 'Unknown' }}
          </span>
        </template>

        <!-- Column: Actions -->
        <template #column-actions="{ item }">
          <div class="flex items-center justify-center gap-1">
            <button
              @click="viewReceipt(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="View Receipt Details"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>

            <button
              v-if="authStore.hasPermission('payment_receipts.edit') && item.can_be_edited"
              @click="editReceipt(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="Edit Receipt"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>

            <button
              v-if="authStore.hasPermission('payment_receipts.verify') && item.can_be_verified"
              @click="verifyReceipt(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="Verify Receipt"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </button>

            <button
              v-if="authStore.hasPermission('payment_receipts.deposit') && item.can_be_deposited"
              @click="markAsDeposited(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="Mark as Deposited"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </button>

            <button
              v-if="authStore.hasPermission('payment_receipts.delete') && item.can_be_deleted"
              @click="deleteReceipt(item)"
              class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer"
              title="Delete Receipt"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Modals -->
    <PaymentReceiptFormModal
      v-if="showCreateModal"
      :show="showCreateModal"
      @close="showCreateModal = false"
      @saved="handleReceiptSaved"
    />

    <PaymentReceiptFormModal
      v-if="showEditModal && selectedReceipt"
      :show="showEditModal"
      :receipt="selectedReceipt"
      @close="showEditModal = false"
      @saved="handleReceiptSaved"
    />

    <PaymentReceiptViewModal
      v-if="showViewModal && selectedReceipt"
      :show="showViewModal"
      :receipt="selectedReceipt"
      @close="showViewModal = false"
      @edit="editReceipt"
      @verify="verifyReceipt"
      @mark-as-deposited="markAsDeposited"
      @delete="deleteReceipt"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import DataTable from '@/components/common/DataTable.vue';
import FloatingDateRangePicker from '@/components/common/FloatingDateRangePicker.vue';
import PaymentReceiptFormModal from './PaymentReceiptFormModal.vue';
import PaymentReceiptViewModal from './PaymentReceiptViewModal.vue';

const authStore = useAuthStore();

// Reactive data
const loading = ref(false);
const receipts = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});
const searchQuery = ref('');
const perPage = ref(15);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const selectedReceipt = ref(null);

// Filters
const filters = reactive({
  receipt_type: '',
  status: '',
  start_date: '',
  end_date: '',
});

// Computed Helper Flags
const hasActiveFilters = computed(() => {
  return filters.receipt_type !== '' || filters.status !== '' || filters.start_date !== '' || filters.end_date !== '';
});

const depositedCount = computed(() => {
  return receipts.value.filter(r => r.status === 'deposited' || r.status === 'verified').length;
});

const pendingCount = computed(() => {
  return receipts.value.filter(r => r.status === 'pending' || r.status === 'draft').length;
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'receipt_number',
    label: 'Receipt Number',
    sortable: true,
    align: 'left',
  },
  {
    key: 'receipt_date',
    label: 'Date',
    sortable: true,
    align: 'left'
  },
  {
    key: 'receipt_type',
    label: 'Receipt Type',
    sortable: true,
    align: 'left'
  },
  {
    key: 'payer_name',
    label: 'Payer / Customer',
    sortable: true,
    align: 'left'
  },
  {
    key: 'amount',
    label: 'Amount ($)',
    sortable: true,
    align: 'right'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'center'
  }
]);

// Methods
const fetchReceipts = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value,
      ...filters,
    };

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/payment-receipts', { params });
    receipts.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
    };
  } catch (error) {
    console.error('Error loading payment receipts:', error);
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.receipt_type = '';
  filters.status = '';
  filters.start_date = '';
  filters.end_date = '';
  fetchReceipts(1);
};

// DataTable event handlers
const handleTableSearch = (query) => {
  searchQuery.value = query;
  fetchReceipts(1);
};

const handleSort = () => {
  fetchReceipts(1);
};

const handlePageChange = (page) => {
  fetchReceipts(page);
};

const handlePerPageChange = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  perPage.value = newPerPage;
  fetchReceipts(1);
};

// Receipt actions
const viewReceipt = (receipt) => {
  selectedReceipt.value = receipt;
  showViewModal.value = true;
};

const editReceipt = (receipt) => {
  selectedReceipt.value = receipt;
  showEditModal.value = true;
  showViewModal.value = false;
};

const verifyReceipt = async (receipt) => {
  if (!confirm('Are you sure you want to verify this payment receipt?')) {
    return;
  }

  try {
    const response = await axios.post(`/api/payment-receipts/${receipt.id}/verify`);
    const index = receipts.value.findIndex(r => r.id === receipt.id);
    if (index !== -1) {
      receipts.value[index] = response.data.receipt;
    }
  } catch (error) {
    console.error('Error verifying payment receipt:', error);
    alert('Failed to verify payment receipt');
  }
};

const markAsDeposited = async (receipt) => {
  if (!confirm('Are you sure you want to mark this receipt as deposited?')) {
    return;
  }

  try {
    const response = await axios.post(`/api/payment-receipts/${receipt.id}/mark-as-deposited`);
    const index = receipts.value.findIndex(r => r.id === receipt.id);
    if (index !== -1) {
      receipts.value[index] = response.data.receipt;
    }
  } catch (error) {
    console.error('Error marking receipt as deposited:', error);
    alert('Failed to mark receipt as deposited');
  }
};

const deleteReceipt = async (receipt) => {
  if (!confirm('Are you sure you want to delete this payment receipt? This action cannot be undone.')) {
    return;
  }

  try {
    await axios.delete(`/api/payment-receipts/${receipt.id}`);
    receipts.value = receipts.value.filter(r => r.id !== receipt.id);
  } catch (error) {
    console.error('Error deleting payment receipt:', error);
    alert('Failed to delete payment receipt');
  }
};

const handleReceiptSaved = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  fetchReceipts(1);
};

// Utility functions
const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getReceiptTypeDisplay = (type) => {
  const types = {
    customer_payment: 'Customer Payment',
    customer_advance: 'Customer Advance',
    supplier_refund: 'Supplier Refund',
    supplier_rebate: 'Supplier Rebate',
    interest_income: 'Interest Income',
    rental_income: 'Rental Income',
    commission_income: 'Commission Income',
    asset_sale: 'Asset Sale',
    bank_transfer_in: 'Bank Transfer In',
    cash_deposit: 'Cash Deposit',
    miscellaneous_income: 'Miscellaneous Income',
    other_receipt: 'Other Receipt',
  };
  return types[type] || type || 'General Receipt';
};

const getReceiptTypeBadgeClass = (type) => {
  const classes = {
    customer_payment: 'bg-slate-900 text-white border-slate-900 dark:bg-zinc-100 dark:text-zinc-900 dark:border-zinc-100',
    customer_advance: 'bg-slate-800 text-white border-slate-800 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    supplier_refund: 'bg-slate-700 text-white border-slate-700 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    bank_transfer_in: 'bg-slate-200 text-slate-900 border-slate-300 dark:bg-zinc-700 dark:text-slate-100 dark:border-zinc-600',
  };
  return classes[type] || 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-zinc-800 dark:text-slate-200 dark:border-zinc-700';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    deposited: 'bg-slate-900 text-white border-slate-900 dark:bg-zinc-100 dark:text-zinc-900 dark:border-zinc-100',
    verified: 'bg-slate-800 text-white border-slate-800 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    pending: 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-zinc-800 dark:text-slate-200 dark:border-zinc-700',
    draft: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-zinc-800/60 dark:text-slate-400 dark:border-zinc-700/60',
    cancelled: 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
  };
  return classes[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};

const getStatusDotClass = (status) => {
  const dots = {
    deposited: 'bg-emerald-400',
    verified: 'bg-blue-400',
    pending: 'bg-amber-400',
    draft: 'bg-slate-400',
    cancelled: 'bg-rose-500',
  };
  return dots[status] || 'bg-slate-400';
};

// Initialize
onMounted(() => {
  fetchReceipts();

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('status')) {
    filters.status = urlParams.get('status');
  }
  if (urlParams.get('create') === 'true') {
    showCreateModal.value = true;
  }
});
</script>
