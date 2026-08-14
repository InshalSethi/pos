<template>
  <div class="expense-list space-y-6">
    <!-- Filters Bar -->
    <div class="bg-white dark:bg-zinc-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm transition-all">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Status Filter (Custom Floating Select) -->
        <div>
          <CustomFloatingSelect
            v-model="filters.status"
            label="Status"
            placeholder="All Statuses"
            :options="statusOptions"
            @update:modelValue="handleFilterChange"
          />
        </div>

        <!-- Category Filter (Custom Floating Select) -->
        <div>
          <CustomFloatingSelect
            v-model="filters.category_id"
            label="Category"
            placeholder="All Categories"
            :options="categoryOptions"
            :searchable="true"
            @update:modelValue="handleFilterChange"
          />
        </div>

        <!-- Date Range Filter -->
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
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden">
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
        <!-- Custom Title Column -->
        <template #column-title="{ item }">
          <div class="py-0.5">
            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ item.title }}</div>
            <div v-if="item.vendor_name" class="text-[11px] font-medium text-slate-500 dark:text-zinc-400 mt-0.5 flex items-center gap-1">
              <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span>{{ item.vendor_name }}</span>
            </div>
          </div>
        </template>

        <!-- Custom Status Column (System UI Badges) -->
        <template #column-status="{ item }">
          <span
            :class="[
              'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider',
              getStatusBadgeClass(item.status)
            ]"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(item.status)"></span>
            {{ getStatusText(item.status) }}
          </span>
        </template>

        <!-- Custom Actions Column -->
        <template #column-actions="{ item }">
          <div class="flex items-center justify-end gap-1.5">
            <button
              @click="$emit('view-expense', item)"
              class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-800 transition-all cursor-pointer"
              title="View Details"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button>

            <button
              v-if="canEdit(item)"
              @click="$emit('edit-expense', item)"
              class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40 transition-all cursor-pointer"
              title="Edit Expense"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>

            <!-- Submit (Tick Icon) Button for Draft Expenses -->
            <button
              v-if="item.status === 'draft'"
              @click="submitExpense(item)"
              :disabled="submittingId === item.id"
              class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/40 transition-all cursor-pointer disabled:opacity-50"
              title="Submit Expense & Deduct Payment"
            >
              <svg v-if="submittingId === item.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
            </button>

            <button
              v-if="canDelete(item)"
              @click="deleteExpense(item)"
              class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition-all cursor-pointer"
              title="Delete Expense"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>
    </div>

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
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

// Stores and composables
const authStore = useAuthStore();
const { success, error } = useToast();

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

const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'draft', label: 'Draft' },
  { value: 'submitted', label: 'Submitted' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'paid', label: 'Paid' }
];

const categoryOptions = computed(() => {
  return [
    { value: '', label: 'All Categories' },
    ...categories.value.map(cat => ({ value: cat.id, label: cat.name }))
  ];
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'expense_number',
    label: 'Expense #',
    sortable: true,
    align: 'left',
    class: 'text-slate-600 dark:text-zinc-400 font-mono text-xs font-bold'
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

const showDeleteConfirmation = ref(false);
const submittingId = ref(null);
const deleteConfirmation = ref({
  title: '',
  message: '',
  details: '',
  loading: false,
  expenseToDelete: null
});

const submitExpense = async (expense) => {
  try {
    submittingId.value = expense.id;
    const response = await axios.post(`/api/expenses/${expense.id}/submit`);
    success(response.data.message || 'Expense submitted and payment deducted successfully');
    fetchExpenses(pagination.value.current_page);
    emit('refresh');
  } catch (err) {
    console.error('Error submitting expense:', err);
    error(err.response?.data?.message || 'Failed to submit expense');
  } finally {
    submittingId.value = null;
  }
};

const canEdit = computed(() => (expense) => {
  return authStore.hasPermission('expenses.edit');
});

const canDelete = computed(() => (expense) => {
  return authStore.hasPermission('expenses.delete');
});

const fetchExpenses = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/expenses', { params });
    expenses.value = response.data;

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
    details += `<br><span class="text-rose-600 dark:text-rose-400 font-bold">Warning: This expense has been paid. Deleting it will also remove all associated accounting records and bank transactions.</span>`;
  } else if (expense.status === 'approved') {
    details += `<br><span class="text-amber-600 dark:text-amber-400 font-bold">Note: This expense has been approved. Deleting it will remove associated accounting records.</span>`;
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
    success(`Expense "${expense.title}" deleted successfully.`);
  } catch (err) {
    console.error('Error deleting expense:', err);
    error('Failed to delete expense. Please try again.');
  } finally {
    deleteConfirmation.value.loading = false;
  }
};

const cancelDelete = () => {
  showDeleteConfirmation.value = false;
};

const handleDateRangeChange = (range) => {
  filters.value.start_date = range.start_date;
  filters.value.end_date = range.end_date;
  fetchExpenses(1);
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-slate-200 border border-slate-200 dark:border-zinc-700',
    submitted: 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-900/50',
    approved: 'bg-indigo-50 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-900/50',
    rejected: 'bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-900/50',
    paid: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-900/50'
  };
  return classes[status] || 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-slate-200';
};

const getStatusDotClass = (status) => {
  const classes = {
    draft: 'bg-slate-500',
    submitted: 'bg-amber-500',
    approved: 'bg-indigo-500',
    rejected: 'bg-rose-500',
    paid: 'bg-emerald-500'
  };
  return classes[status] || 'bg-slate-500';
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

defineExpose({
  fetchExpenses
});

onMounted(() => {
  fetchExpenses();
  fetchCategories();
});
</script>
