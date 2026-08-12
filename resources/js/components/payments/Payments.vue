<template>
  <div class="space-y-4 max-w-full">
    <!-- Top Header & Metrics Bar (Black & White Theme) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <div class="flex items-center gap-3">
          <div class="p-2 bg-slate-900 text-white rounded-xl shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Payments Out</h1>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage all outgoing supplier and operational payment transactions</p>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Summary Cards -->
      <div class="flex flex-wrap items-center gap-2.5">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-3.5 py-2 shadow-xs flex items-center gap-2.5">
          <div class="p-1.5 bg-slate-900 text-white rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Payments</div>
            <div class="text-xs font-bold text-slate-900 dark:text-slate-100 font-mono">{{ pagination.total || 0 }}</div>
          </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-3.5 py-2 shadow-xs flex items-center gap-2.5">
          <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Completed Paid</div>
            <div class="text-xs font-bold text-slate-900 dark:text-slate-100 font-mono">{{ paidCount }}</div>
          </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-3.5 py-2 shadow-xs flex items-center gap-2.5">
          <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pending / Draft</div>
            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">{{ pendingCount }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar Card (White and Black High-Contrast Style) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs p-4 sm:p-5">
      <div class="flex items-center justify-between mb-3.5">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-900 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">Filter Payments</h3>
        </div>
        <button
          v-if="hasActiveFilters"
          @click="resetFilters"
          class="text-xs font-semibold text-slate-600 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 px-2.5 py-1 rounded-lg transition-all inline-flex items-center gap-1.5 cursor-pointer"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Reset Filters
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Payment Type Filter -->
        <div class="space-y-1">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Payment Type</label>
          <div class="relative">
            <select
              v-model="filters.payment_type"
              @change="fetchPayments(1)"
              class="w-full appearance-none bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl px-3.5 py-2 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-sm cursor-pointer"
            >
              <option value="" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">All Payment Types</option>
              <option value="supplier_payment" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Supplier Payment</option>
              <option value="expense_payment" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Expense Payment</option>
              <option value="salary_payment" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Salary Payment</option>
              <option value="sale_return_payment" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Sale Return Payment</option>
              <option value="purchase_invoice_payment" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Purchase Invoice Payment</option>
              <option value="other_payment" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Other Payment</option>
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
              @change="fetchPayments(1)"
              class="w-full appearance-none bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl px-3.5 py-2 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-sm cursor-pointer"
            >
              <option value="" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">All Statuses</option>
              <option value="draft" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Draft</option>
              <option value="pending" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Pending</option>
              <option value="approved" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Approved</option>
              <option value="paid" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">Paid</option>
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
            @change="fetchPayments(1)"
          />
        </div>
      </div>
    </div>

    <!-- DataTable Container (Black and White System Theme) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden">
      <DataTable
        title="Payment Transactions"
        subtitle="Manage all outgoing supplier and operational payment entries"
        :columns="tableColumns"
        :data="payments"
        :loading="loading"
        :pagination="pagination"
        :initial-search="searchQuery"
        :initial-per-page="perPage"
        :default-per-page="15"
        storage-key="payments-table-state"
        empty-message="No payments found"
        empty-sub-message="Get started by creating your first outgoing payment."
        @search="handleTableSearch"
        @sort="handleSort"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      >
        <!-- Primary Action Button (+ New Payment) -->
        <template #actions>
          <div class="flex items-center space-x-2">
            <button
              v-if="authStore.hasPermission('payments.create')"
              @click="showCreateModal = true"
              class="bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-semibold rounded-xl text-xs px-4 py-2.5 transition-all shadow-sm cursor-pointer"
            >
              <span>+ New Payment</span>
            </button>
          </div>
        </template>

        <!-- Column: Payment Number -->
        <template #column-payment_number="{ item }">
          <button
            @click="viewPayment(item)"
            class="font-mono text-xs font-bold text-slate-900 dark:text-slate-100 hover:text-black dark:hover:text-white hover:underline transition-colors inline-flex items-center gap-1.5 text-left cursor-pointer"
          >
            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>{{ item.payment_number }}</span>
          </button>
        </template>

        <!-- Column: Payment Date -->
        <template #column-payment_date="{ item }">
          <div class="text-xs text-slate-700 dark:text-slate-300 font-medium inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(item.payment_date) }}</span>
          </div>
        </template>

        <!-- Column: Payment Type (Black & White Monochromatic Badges) -->
        <template #column-payment_type="{ item }">
          <span :class="getPaymentTypeBadgeClass(item.payment_type)" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-lg border">
            <span class="w-1.5 h-1.5 rounded-full" :class="getPaymentTypeDotClass(item.payment_type)"></span>
            {{ getPaymentTypeDisplay(item.payment_type) }}
          </span>
        </template>

        <!-- Column: Payee -->
        <template #column-payee_name="{ item }">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold shrink-0 shadow-xs">
              {{ (item.payee_name || 'P').charAt(0).toUpperCase() }}
            </div>
            <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate max-w-[200px]">{{ item.payee_name || 'N/A' }}</span>
          </div>
        </template>

        <!-- Column: Amount -->
        <template #column-amount="{ item }">
          <span class="text-xs font-bold font-mono text-slate-900 dark:text-slate-100 text-right block">
            ${{ formatAmount(item.amount) }}
          </span>
        </template>

        <!-- Column: Status (Black & White High-Contrast Status Badges) -->
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
              @click="viewPayment(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="View Payment Details"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <button
              v-if="authStore.hasPermission('payments.edit') && item.can_be_edited"
              @click="editPayment(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="Edit Payment"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button
              v-if="authStore.hasPermission('payments.approve') && item.can_be_approved"
              @click="approvePayment(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="Approve Payment"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </button>
            <button
              v-if="authStore.hasPermission('payments.pay') && item.can_be_paid"
              @click="markAsPaid(item)"
              class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
              title="Mark as Paid"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </button>
            <button
              v-if="authStore.hasPermission('payments.delete') && item.can_be_deleted"
              @click="deletePayment(item)"
              class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer"
              title="Delete Payment"
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
    <PaymentFormModal
      v-if="showCreateModal"
      :show="showCreateModal"
      @close="showCreateModal = false"
      @saved="handlePaymentSaved"
    />

    <PaymentFormModal
      v-if="showEditModal && selectedPayment"
      :show="showEditModal"
      :payment="selectedPayment"
      @close="showEditModal = false"
      @saved="handlePaymentSaved"
    />

    <PaymentViewModal
      v-if="showViewModal && selectedPayment"
      :show="showViewModal"
      :payment="selectedPayment"
      @close="showViewModal = false"
      @edit="editPayment"
      @approve="approvePayment"
      @mark-as-paid="markAsPaid"
      @delete="deletePayment"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import DataTable from '@/components/common/DataTable.vue';
import FloatingDateRangePicker from '@/components/common/FloatingDateRangePicker.vue';
import PaymentFormModal from './PaymentFormModal.vue';
import PaymentViewModal from './PaymentViewModal.vue';

const authStore = useAuthStore();

// Reactive data
const loading = ref(false);
const payments = ref([]);
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
const selectedPayment = ref(null);

// Filters
const filters = reactive({
  payment_type: '',
  status: '',
  start_date: '',
  end_date: '',
});

// Computed Helper Flags
const hasActiveFilters = computed(() => {
  return filters.payment_type !== '' || filters.status !== '' || filters.start_date !== '' || filters.end_date !== '';
});

const paidCount = computed(() => {
  return payments.value.filter(p => p.status === 'paid').length;
});

const pendingCount = computed(() => {
  return payments.value.filter(p => p.status === 'pending' || p.status === 'draft').length;
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'payment_number',
    label: 'Payment Number',
    sortable: true,
    align: 'left',
  },
  {
    key: 'payment_date',
    label: 'Date',
    sortable: true,
    align: 'left'
  },
  {
    key: 'payment_type',
    label: 'Payment Type',
    sortable: true,
    align: 'left'
  },
  {
    key: 'payee_name',
    label: 'Payee / Recipient',
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
const fetchPayments = async (page = 1) => {
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

    const response = await axios.get('/api/payments', { params });
    payments.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
    };
  } catch (error) {
    console.error('Error loading payments:', error);
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.payment_type = '';
  filters.status = '';
  filters.start_date = '';
  filters.end_date = '';
  fetchPayments(1);
};

// DataTable event handlers
const handleTableSearch = (query) => {
  searchQuery.value = query;
  fetchPayments(1);
};

const handleSort = () => {
  fetchPayments(1);
};

const handlePageChange = (page) => {
  fetchPayments(page);
};

const handlePerPageChange = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  perPage.value = newPerPage;
  fetchPayments(1);
};

// Payment actions
const viewPayment = (payment) => {
  selectedPayment.value = payment;
  showViewModal.value = true;
};

const editPayment = (payment) => {
  selectedPayment.value = payment;
  showEditModal.value = true;
  showViewModal.value = false;
};

const approvePayment = async (payment) => {
  if (!confirm('Are you sure you want to approve this payment?')) {
    return;
  }

  try {
    const response = await axios.post(`/api/payments/${payment.id}/approve`);
    const index = payments.value.findIndex(p => p.id === payment.id);
    if (index !== -1) {
      payments.value[index] = response.data.payment;
    }
  } catch (error) {
    console.error('Error approving payment:', error);
    alert('Failed to approve payment');
  }
};

const markAsPaid = async (payment) => {
  if (!confirm('Are you sure you want to mark this payment as paid?')) {
    return;
  }

  try {
    const response = await axios.post(`/api/payments/${payment.id}/mark-as-paid`);
    const index = payments.value.findIndex(p => p.id === payment.id);
    if (index !== -1) {
      payments.value[index] = response.data.payment;
    }
  } catch (error) {
    console.error('Error marking payment as paid:', error);
    alert('Failed to mark payment as paid');
  }
};

const deletePayment = async (payment) => {
  if (!confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
    return;
  }

  try {
    await axios.delete(`/api/payments/${payment.id}`);
    payments.value = payments.value.filter(p => p.id !== payment.id);
  } catch (error) {
    console.error('Error deleting payment:', error);
    alert('Failed to delete payment');
  }
};

const handlePaymentSaved = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  fetchPayments(1);
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

const getPaymentTypeDisplay = (type) => {
  const types = {
    supplier_payment: 'Supplier Payment',
    expense_payment: 'Expense Payment',
    salary_payment: 'Salary Payment',
    sale_return_payment: 'Sale Return Payment',
    purchase_invoice_payment: 'Purchase Invoice Payment',
    other_payment: 'Other Payment',
  };
  return types[type] || type || 'General Payment';
};

const getPaymentTypeBadgeClass = (type) => {
  const classes = {
    supplier_payment: 'bg-slate-900 text-white border-slate-900 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    expense_payment: 'bg-slate-800 text-white border-slate-800 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    salary_payment: 'bg-zinc-900 text-white border-zinc-900 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    sale_return_payment: 'bg-slate-200 text-slate-900 border-slate-300 dark:bg-zinc-700 dark:text-slate-100 dark:border-zinc-600',
    purchase_invoice_payment: 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-zinc-800 dark:text-slate-200 dark:border-zinc-700',
  };
  return classes[type] || 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-zinc-800 dark:text-slate-300 dark:border-zinc-700';
};

const getPaymentTypeDotClass = (type) => {
  const dots = {
    supplier_payment: 'bg-emerald-400',
    expense_payment: 'bg-sky-400',
    salary_payment: 'bg-indigo-400',
    sale_return_payment: 'bg-rose-400',
    purchase_invoice_payment: 'bg-violet-400',
  };
  return dots[type] || 'bg-slate-400';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    paid: 'bg-slate-900 text-white border-slate-900 dark:bg-zinc-100 dark:text-zinc-900 dark:border-zinc-100',
    approved: 'bg-slate-800 text-white border-slate-800 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    pending: 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-zinc-800 dark:text-slate-200 dark:border-zinc-700',
    draft: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-zinc-800/60 dark:text-slate-400 dark:border-zinc-700/60',
    cancelled: 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
  };
  return classes[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};

const getStatusDotClass = (status) => {
  const dots = {
    paid: 'bg-emerald-400',
    approved: 'bg-blue-400',
    pending: 'bg-amber-400',
    draft: 'bg-slate-400',
    cancelled: 'bg-rose-500',
  };
  return dots[status] || 'bg-slate-400';
};

// Initialize
onMounted(() => {
  fetchPayments();

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('create') === 'true') {
    showCreateModal.value = true;
  }

  if (urlParams.get('status')) {
    filters.status = urlParams.get('status');
  }
});
</script>
