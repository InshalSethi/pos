<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
          <p class="text-gray-600">Manage all payment transactions</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Payment Type Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type</label>
          <select
            v-model="filters.payment_type"
            @change="fetchPayments"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="">All Types</option>
            <option value="supplier_payment">Supplier Payment</option>
            <option value="expense_payment">Expense Payment</option>
            <option value="salary_payment">Salary Payment</option>
            <option value="sale_return_payment">Sale Return Payment</option>
            <option value="purchase_invoice_payment">Purchase Invoice Payment</option>
            <option value="other_payment">Other Payment</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            @change="fetchPayments"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="filters.start_date"
            @change="fetchPayments"
            type="date"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="filters.end_date"
            @change="fetchPayments"
            type="date"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>
      </div>
    </div>

    <!-- DataTable -->
    <DataTable
      title="Payments"
      subtitle="Manage all payment transactions with comprehensive tracking"
      :columns="tableColumns"
      :data="payments"
      :loading="loading"
      :pagination="pagination"
      :initial-search="searchQuery"
      :initial-per-page="perPage"
      :default-per-page="15"
      storage-key="payments-table-state"
      empty-message="No payments found"
      empty-sub-message="Get started by creating your first payment."
      @search="handleTableSearch"
      @sort="handleSort"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    >
      <!-- Action Buttons -->
      <template #actions>
        <div class="flex space-x-2">
          <button
            v-if="authStore.hasPermission('payments.create')"
            @click="showCreateModal = true"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>New Payment</span>
          </button>
        </div>
      </template>

      <!-- Custom column content -->
      <template #column-payment_type="{ item }">
        <span class="text-sm text-gray-900">{{ getPaymentTypeDisplay(item.payment_type) }}</span>
      </template>

      <template #column-amount="{ item }">
        <span class="text-sm font-medium text-gray-900">${{ formatAmount(item.amount) }}</span>
      </template>

      <template #column-status="{ item }">
        <span :class="getStatusBadgeClass(item.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
          {{ item.status.charAt(0).toUpperCase() + item.status.slice(1) }}
        </span>
      </template>

      <template #column-actions="{ item }">
        <div class="flex space-x-2">
          <button
            @click="viewPayment(item)"
            class="text-indigo-600 hover:text-indigo-900"
            title="View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payments.edit') && item.can_be_edited"
            @click="editPayment(item)"
            class="text-blue-600 hover:text-blue-900"
            title="Edit"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payments.approve') && item.can_be_approved"
            @click="approvePayment(item)"
            class="text-green-600 hover:text-green-900"
            title="Approve"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payments.pay') && item.can_be_paid"
            @click="markAsPaid(item)"
            class="text-purple-600 hover:text-purple-900"
            title="Mark as Paid"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payments.delete') && item.can_be_deleted"
            @click="deletePayment(item)"
            class="text-red-600 hover:text-red-900"
            title="Delete"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </template>
    </DataTable>

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
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import DataTable from '@/components/common/DataTable.vue';
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

// Table columns configuration
const tableColumns = ref([
  {
    key: 'payment_number',
    label: 'Payment Number',
    sortable: true,
    align: 'left',
    class: 'font-medium text-gray-900'
  },
  {
    key: 'payment_date',
    label: 'Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'payment_type',
    label: 'Type',
    sortable: true,
    align: 'left'
  },
  {
    key: 'payee_name',
    label: 'Payee',
    sortable: true,
    align: 'left'
  },
  {
    key: 'amount',
    label: 'Amount',
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
    // Handle error (show notification, etc.)
  } finally {
    loading.value = false;
  }
};

// DataTable event handlers
const handleTableSearch = (query) => {
  searchQuery.value = query;
  fetchPayments();
};

const handleSort = (sortConfig) => {
  // The DataTable component handles sorting internally
  fetchPayments();
};

const handlePageChange = (page) => {
  fetchPayments(page);
};

const handlePerPageChange = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  perPage.value = newPerPage;
  fetchPayments();
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
    // Update the payment in the list
    const index = payments.value.findIndex(p => p.id === payment.id);
    if (index !== -1) {
      payments.value[index] = response.data.payment;
    }
    // Show success notification
    alert('Payment approved successfully');
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
    // Update the payment in the list
    const index = payments.value.findIndex(p => p.id === payment.id);
    if (index !== -1) {
      payments.value[index] = response.data.payment;
    }
    // Show success notification
    alert('Payment marked as paid successfully');
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
    // Remove the payment from the list
    payments.value = payments.value.filter(p => p.id !== payment.id);
    // Show success notification
    alert('Payment deleted successfully');
  } catch (error) {
    console.error('Error deleting payment:', error);
    alert('Failed to delete payment');
  }
};

const handlePaymentSaved = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  fetchPayments();
};

// Utility functions
const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatAmount = (amount) => {
  return parseFloat(amount).toLocaleString('en-US', {
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
  return types[type] || type;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

// Initialize
onMounted(() => {
  fetchPayments();

  // Check for URL parameters to auto-open create modal
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('create') === 'true') {
    showCreateModal.value = true;
  }

  // Check for URL parameters to set filters
  if (urlParams.get('status')) {
    filters.status = urlParams.get('status');
  }
});
</script>
