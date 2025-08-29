<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Payment Receipts</h1>
          <p class="text-gray-600">Manage all incoming payment receipts</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Receipt Type Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Type</label>
          <select
            v-model="filters.receipt_type"
            @change="fetchReceipts"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="">All Types</option>
            <option value="customer_payment">Customer Payment</option>
            <option value="customer_advance">Customer Advance</option>
            <option value="supplier_refund">Supplier Refund</option>
            <option value="supplier_rebate">Supplier Rebate</option>
            <option value="interest_income">Interest Income</option>
            <option value="rental_income">Rental Income</option>
            <option value="commission_income">Commission Income</option>
            <option value="asset_sale">Asset Sale</option>
            <option value="bank_transfer_in">Bank Transfer In</option>
            <option value="cash_deposit">Cash Deposit</option>
            <option value="miscellaneous_income">Miscellaneous Income</option>
            <option value="other_receipt">Other Receipt</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            @change="fetchReceipts"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="deposited">Deposited</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="filters.start_date"
            @change="fetchReceipts"
            type="date"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="filters.end_date"
            @change="fetchReceipts"
            type="date"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>
      </div>
    </div>

    <!-- DataTable -->
    <DataTable
      title="Payment Receipts"
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
      <!-- Action Buttons -->
      <template #actions>
        <div class="flex space-x-2">
          <button
            v-if="authStore.hasPermission('payment_receipts.create')"
            @click="showCreateModal = true"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>New Receipt</span>
          </button>
        </div>
      </template>

      <!-- Custom column content -->
      <template #column-receipt_type="{ item }">
        <span class="text-sm text-gray-900">{{ getReceiptTypeDisplay(item.receipt_type) }}</span>
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
            @click="viewReceipt(item)"
            class="text-indigo-600 hover:text-indigo-900"
            title="View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payment_receipts.edit') && item.can_be_edited"
            @click="editReceipt(item)"
            class="text-blue-600 hover:text-blue-900"
            title="Edit"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payment_receipts.verify') && item.can_be_verified"
            @click="verifyReceipt(item)"
            class="text-green-600 hover:text-green-900"
            title="Verify"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payment_receipts.deposit') && item.can_be_deposited"
            @click="markAsDeposited(item)"
            class="text-purple-600 hover:text-purple-900"
            title="Mark as Deposited"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </button>
          <button
            v-if="authStore.hasPermission('payment_receipts.delete') && item.can_be_deleted"
            @click="deleteReceipt(item)"
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
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import DataTable from '@/components/common/DataTable.vue';
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

// Table columns configuration
const tableColumns = ref([
  {
    key: 'receipt_number',
    label: 'Receipt Number',
    sortable: true,
    align: 'left',
    class: 'font-medium text-gray-900'
  },
  {
    key: 'receipt_date',
    label: 'Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'receipt_type',
    label: 'Type',
    sortable: true,
    align: 'left'
  },
  {
    key: 'payer_name',
    label: 'Payer',
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
    // Handle error (show notification, etc.)
  } finally {
    loading.value = false;
  }
};

// DataTable event handlers
const handleTableSearch = (query) => {
  searchQuery.value = query;
  fetchReceipts();
};

const handleSort = (sortConfig) => {
  // The DataTable component handles sorting internally
  fetchReceipts();
};

const handlePageChange = (page) => {
  fetchReceipts(page);
};

const handlePerPageChange = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  perPage.value = newPerPage;
  fetchReceipts();
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
    // Update the receipt in the list
    const index = receipts.value.findIndex(r => r.id === receipt.id);
    if (index !== -1) {
      receipts.value[index] = response.data.receipt;
    }
    // Show success notification
    alert('Payment receipt verified successfully');
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
    // Update the receipt in the list
    const index = receipts.value.findIndex(r => r.id === receipt.id);
    if (index !== -1) {
      receipts.value[index] = response.data.receipt;
    }
    // Show success notification
    alert('Payment receipt marked as deposited successfully');
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
    // Remove the receipt from the list
    receipts.value = receipts.value.filter(r => r.id !== receipt.id);
    // Show success notification
    alert('Payment receipt deleted successfully');
  } catch (error) {
    console.error('Error deleting payment receipt:', error);
    alert('Failed to delete payment receipt');
  }
};

const handleReceiptSaved = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  fetchReceipts();
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
  return types[type] || type;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    verified: 'bg-blue-100 text-blue-800',
    deposited: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

// Initialize
onMounted(() => {
  fetchReceipts();

  // Check for URL parameters to set filters
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('status')) {
    filters.status = urlParams.get('status');
  }
  if (urlParams.get('create') === 'true') {
    showCreateModal.value = true;
  }
});
</script>
