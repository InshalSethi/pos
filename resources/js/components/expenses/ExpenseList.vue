<template>
  <div class="expense-list">
    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="handleFilterChange"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="paid">Paid</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="filters.category_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="handleFilterChange"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
        <div>
          <DateRangePicker
            v-model="dateRange"
            label="Date Range"
            placeholder="Select date range"
            @change="handleDateRangeChange"
          />
        </div>
      </div>
    </div>

    <!-- DataTable -->
    <DataTable
      title="Expenses"
      subtitle="Manage and track all expense records"
      :columns="tableColumns"
      :data="expenses.data || []"
      :loading="loading"
      :pagination="pagination"
      :initial-search="filters.search"
      :initial-per-page="pagination.per_page"
      :default-per-page="25"
      storage-key="expenses-table-state"
      empty-message="No expenses found"
      empty-sub-message="Try adjusting your search or filter criteria."
      @search="handleSearch"
      @sort="handleSort"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    >
      <!-- Custom column content -->
      <template #column-title="{ item }">
        <div>
          <div class="text-sm font-medium text-gray-900">{{ item.title }}</div>
          <div class="text-sm text-gray-500" v-if="item.vendor_name">{{ item.vendor_name }}</div>
        </div>
      </template>

      <template #column-status="{ item }">
        <span :class="getStatusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
          {{ getStatusText(item.status) }}
        </span>
      </template>

      <template #column-actions="{ item }">
        <div class="flex justify-end space-x-2">
          <button
            @click="$emit('view-expense', item)"
            class="text-blue-600 hover:text-blue-900"
            title="View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
          </button>
          <button
            v-if="canEdit(item)"
            @click="$emit('edit-expense', item)"
            class="text-indigo-600 hover:text-indigo-900"
            title="Edit"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </button>
          <button
            v-if="canDelete(item)"
            @click="deleteExpense(item)"
            class="text-red-600 hover:text-red-900"
            title="Delete"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Confirmation Modal -->
    <ConfirmationModal
      v-model:show="showDeleteConfirmation"
      :title="deleteConfirmation.title"
      :message="deleteConfirmation.message"
      :details="deleteConfirmation.details"
      type="danger"
      confirm-text="Delete Expense"
      cancel-text="Cancel"
      :loading="deleteConfirmation.loading"
      loading-text="Deleting..."
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import DateRangePicker from '@/components/common/DateRangePicker.vue';
import ConfirmationModal from '@/components/common/ConfirmationModal.vue';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

// Stores and composables
const authStore = useAuthStore();
const { success, error } = useToast();

// Props and Emits
const emit = defineEmits(['edit-expense', 'view-expense', 'refresh']);

// Reactive data
const expenses = ref({ data: [] });
const categories = ref([]);
const loading = ref(false);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
  from: 0,
  to: 0
});

const filters = ref({
  search: '',
  status: '',
  category_id: '',
  start_date: '',
  end_date: '',
  sort_field: '',
  sort_order: ''
});

const dateRange = ref({
  start_date: '',
  end_date: ''
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'expense_number',
    label: 'Expense #',
    sortable: true,
    align: 'left',
    class: 'text-gray-500 font-mono text-xs'
  },
  {
    key: 'title',
    label: 'Title',
    sortable: true,
    align: 'left'
  },
  {
    key: 'category.name',
    label: 'Category',
    sortable: true,
    align: 'left'
  },
  {
    key: 'amount',
    label: 'Amount',
    sortable: true,
    type: 'currency',
    align: 'right'
  },
  {
    key: 'expense_date',
    label: 'Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'employee.full_name',
    label: 'Employee',
    sortable: true,
    align: 'left'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'right'
  }
]);

// Confirmation modal data
const showDeleteConfirmation = ref(false);
const deleteConfirmation = ref({
  title: '',
  message: '',
  details: '',
  loading: false,
  expenseToDelete: null
});

// Computed
const canEdit = computed(() => (expense) => {
  return authStore.hasPermission('expenses.edit');
});

const canDelete = computed(() => (expense) => {
  return authStore.hasPermission('expenses.delete');
});

// Methods
const fetchExpenses = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    };

    // Remove empty parameters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/expenses', { params });
    expenses.value = response.data;

    // Update pagination
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching expenses:', error);
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/expense-categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

// DataTable event handlers
const handleSearch = (searchQuery) => {
  filters.value.search = searchQuery;
  fetchExpenses(1);
};

const handleSort = (sortData) => {
  filters.value.sort_field = sortData.field;
  filters.value.sort_order = sortData.order;
  fetchExpenses(1);
};

const handlePageChange = (page) => {
  fetchExpenses(page);
};

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage;
  fetchExpenses(1);
};

const handleFilterChange = () => {
  fetchExpenses(1);
};

const deleteExpense = (expense) => {
  // Set up confirmation modal data
  deleteConfirmation.value = {
    title: 'Delete Expense',
    message: `Are you sure you want to delete the expense "<strong>${expense.title}</strong>"?`,
    details: getDeleteDetails(expense),
    loading: false,
    expenseToDelete: expense
  };

  showDeleteConfirmation.value = true;
};

const getDeleteDetails = (expense) => {
  let details = `<strong>Expense Details:</strong><br>`;
  details += `• Amount: $${parseFloat(expense.amount).toFixed(2)}<br>`;
  details += `• Status: ${expense.status}<br>`;
  details += `• Date: ${new Date(expense.expense_date).toLocaleDateString()}<br>`;

  if (expense.status === 'paid') {
    details += `<br><span class="text-red-600"><strong>Warning:</strong> This expense has been paid. Deleting it will also remove all associated accounting records and bank transactions.</span>`;
  } else if (expense.status === 'approved') {
    details += `<br><span class="text-yellow-600"><strong>Note:</strong> This expense has been approved. Deleting it will remove associated accounting records.</span>`;
  }

  return details;
};

const confirmDelete = async () => {
  const expense = deleteConfirmation.value.expenseToDelete;
  if (!expense) return;

  deleteConfirmation.value.loading = true;

  try {
    await axios.delete(`/api/expenses/${expense.id}`);
    showDeleteConfirmation.value = false;
    await fetchExpenses();

    success(`Expense "${expense.title}" has been deleted successfully.`);
  } catch (error) {
    console.error('Error deleting expense:', error);
    error('Failed to delete expense. Please try again.');
  } finally {
    deleteConfirmation.value.loading = false;
  }
};

const cancelDelete = () => {
  deleteConfirmation.value = {
    title: '',
    message: '',
    details: '',
    loading: false,
    expenseToDelete: null
  };
  showDeleteConfirmation.value = false;
};

const handleDateRangeChange = (range) => {
  filters.value.start_date = range.start_date;
  filters.value.end_date = range.end_date;
  fetchExpenses(1);
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    paid: 'bg-blue-100 text-blue-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
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

// Lifecycle
// Expose methods to parent component
defineExpose({
  fetchExpenses
});

onMounted(() => {
  fetchExpenses();
  fetchCategories();
});
</script>
