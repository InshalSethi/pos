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
              'inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full border',
              getStatusBadgeClass(item.status)
            ]"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(item.status)"></span>
            {{ getStatusText(item.status) }}
          </span>
        </template>

        <!-- Custom Actions Column -->
        <template #column-actions="{ item }">
          <div class="flex items-center justify-center gap-1.5">
            <!-- 1. DRAFT STATUS -->
            <template v-if="item.status === 'draft'">
              <!-- Eye Icon (View) -->
              <button
                @click="$emit('view-expense', item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Expense Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>

              <!-- Edit Icon (Pencil) -->
              <button
                v-if="canEdit(item)"
                @click="$emit('edit-expense', item)"
                class="p-1.5 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="Edit Expense"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>

              <!-- Cancel Icon (Circle with Cross) -->
              <button
                @click="openTransitionModal(item, 'cancelled')"
                class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer"
                title="Cancel Expense"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                </svg>
              </button>

              <!-- Processing Icon (Hourglass with circular arrows) -->
              <button
                @click="openTransitionModal(item, 'process')"
                class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-lg transition-all cursor-pointer"
                title="Move to Processing"
              >
                <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18.5 7A9 9 0 0 0 7 4.5" />
                  <polyline points="18.5 3.5 18.5 7.5 14.5 7.5" />
                  <path d="M5.5 17A9 9 0 0 0 17 19.5" />
                  <polyline points="5.5 20.5 5.5 16.5 9.5 16.5" />
                  <path d="M9 8h6" />
                  <path d="M9 16h6" />
                  <path d="M9.5 8v2.2l2.5 1.8-2.5 1.8V16" />
                  <path d="M14.5 8v2.2l-2.5 1.8 2.5 1.8V16" />
                </svg>
              </button>
            </template>

            <!-- 2. PROCESS / PROCESSING STATUS -->
            <template v-else-if="item.status === 'process' || item.status === 'processing'">
              <!-- Eye Icon (View) -->
              <button
                @click="$emit('view-expense', item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Expense Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>

              <!-- Pending Icon (Clock Timer with Arrow) -->
              <button
                @click="openTransitionModal(item, 'pending')"
                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-lg transition-all cursor-pointer"
                title="Move to Pending Review"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.5a9.5 9.5 0 1 0 9.5 9.5" stroke-dasharray="4 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5.5l3.5 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.5 8l-2-2.5 3-1" />
                </svg>
              </button>
            </template>

            <!-- 3. PENDING / SUBMITTED REVIEW STATUS -->
            <template v-else-if="item.status === 'pending' || item.status === 'submitted' || item.status === 'approved'">
              <!-- Eye Icon (View) -->
              <button
                @click="$emit('view-expense', item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Expense Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>

              <!-- Rejected Icon (Slash Circle) -->
              <button
                @click="openTransitionModal(item, 'rejected')"
                class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer"
                title="Reject Expense"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5.6 5.6l12.8 12.8" />
                </svg>
              </button>

              <!-- Completed Icon (Checkmark Circle) -->
              <button
                @click="openTransitionModal(item, 'completed')"
                class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-all cursor-pointer"
                title="Mark as Paid"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.5 2.5 5-5" />
                </svg>
              </button>
            </template>

            <!-- 4. FINAL STATES (REJECTED, COMPLETED, CANCELLED, PAID) -->
            <template v-else>
              <!-- Eye Icon (View) -->
              <button
                @click="$emit('view-expense', item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Expense Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </template>

            <!-- Delete Icon for Draft -->
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

    <!-- State Machine Transition Confirmation Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showConfirmModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showConfirmModal = false"></div>

          <!-- Dialog Box -->
          <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-zinc-800 overflow-hidden p-6 z-10 space-y-4">
            <div class="flex items-start gap-4">
              <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-xs', confirmModalData.iconBgClass]">
                <!-- Process Icon -->
                <svg v-if="confirmModalData.type === 'process'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18.5 7A9 9 0 0 0 7 4.5" />
                  <polyline points="18.5 3.5 18.5 7.5 14.5 7.5" />
                  <path d="M5.5 17A9 9 0 0 0 17 19.5" />
                  <polyline points="5.5 20.5 5.5 16.5 9.5 16.5" />
                  <path d="M9 8h6" />
                  <path d="M9 16h6" />
                  <path d="M9.5 8v2.2l2.5 1.8-2.5 1.8V16" />
                  <path d="M14.5 8v2.2l-2.5 1.8 2.5 1.8V16" />
                </svg>

                <!-- Pending Icon -->
                <svg v-else-if="confirmModalData.type === 'pending'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.5a9.5 9.5 0 1 0 9.5 9.5" stroke-dasharray="4 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5.5l3.5 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.5 8l-2-2.5 3-1" />
                </svg>

                <!-- Rejected Icon -->
                <svg v-else-if="confirmModalData.type === 'rejected'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5.6 5.6l12.8 12.8" />
                </svg>

                <!-- Completed Icon -->
                <svg v-else-if="confirmModalData.type === 'completed'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.5 2.5 5-5" />
                </svg>

                <!-- Cancelled Icon -->
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                </svg>
              </div>

              <div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ confirmModalData.title }}</h4>
                <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400 mt-0.5">{{ confirmModalData.message }}</p>
                <p v-if="confirmModalData.subtext" class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1 font-medium">{{ confirmModalData.subtext }}</p>
              </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
              <button
                type="button"
                @click="showConfirmModal = false"
                class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
              >
                Cancel
              </button>

              <button
                type="button"
                @click="confirmStatusTransition"
                :disabled="isUpdatingStatus"
                :class="['px-4 py-2 rounded-xl text-xs font-extrabold shadow-sm transition-all cursor-pointer disabled:opacity-50 flex items-center gap-1.5', confirmModalData.confirmButtonClass]"
              >
                <span v-if="isUpdatingStatus">Updating...</span>
                <span v-else>{{ confirmModalData.confirmButtonText }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

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
import { ref, reactive, onMounted, computed } from 'vue';
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
  { value: 'pending', label: 'Pending' },
  { value: 'process', label: 'Process' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'completed', label: 'Paid' }
];

const categoryOptions = computed(() => {
  return [
    { value: '', label: 'All Categories' },
    ...categories.value.map(cat => ({
      value: cat.id,
      label: cat.code ? `${cat.code}-${cat.name}` : cat.name
    }))
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

const canEdit = (expense) => {
  return authStore.hasPermission('expenses.edit') && expense.status === 'draft';
};

const canDelete = (expense) => {
  return authStore.hasPermission('expenses.delete') && expense.status === 'draft';
};

const canComplete = (expense) => {
  return expense.status === 'draft';
};

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

// Transition modal reactive state
const showConfirmModal = ref(false);
const isUpdatingStatus = ref(false);
const confirmModalData = reactive({
  expenseId: null,
  targetStatus: '',
  title: '',
  message: 'Are you sure you want to do this?',
  subtext: '',
  confirmButtonText: 'Confirm',
  confirmButtonClass: 'bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white',
  iconBgClass: 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900',
  type: 'process',
});

const openTransitionModal = (item, targetStatus) => {
  confirmModalData.expenseId = item.id;
  confirmModalData.targetStatus = targetStatus;
  confirmModalData.message = 'Are you sure you want to do this?';
  confirmModalData.confirmButtonClass = 'bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white';
  confirmModalData.iconBgClass = 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900';

  if (targetStatus === 'process') {
    confirmModalData.title = 'Move to Processing';
    confirmModalData.subtext = 'Once moved to Processing, this expense can no longer be edited or cancelled.';
    confirmModalData.confirmButtonText = 'Move to Processing';
    confirmModalData.type = 'process';
  } else if (targetStatus === 'pending') {
    confirmModalData.title = 'Move to Pending Review';
    confirmModalData.subtext = 'Moving to Pending review. This action cannot be undone.';
    confirmModalData.confirmButtonText = 'Move to Pending';
    confirmModalData.type = 'pending';
  } else if (targetStatus === 'rejected') {
    confirmModalData.title = 'Reject Expense';
    confirmModalData.subtext = 'Rejecting this expense. Once rejected, status cannot be changed.';
    confirmModalData.confirmButtonText = 'Reject Expense';
    confirmModalData.type = 'rejected';
  } else if (targetStatus === 'completed') {
    confirmModalData.title = 'Mark as Paid';
    confirmModalData.subtext = 'Marking expense as Paid. Ledger entries will be posted and account balance deducted.';
    confirmModalData.confirmButtonText = 'Mark as Paid';
    confirmModalData.type = 'completed';
  } else if (targetStatus === 'cancelled') {
    confirmModalData.title = 'Cancel Expense Voucher';
    confirmModalData.subtext = 'Cancelling this expense voucher. Once cancelled, this action cannot be undone.';
    confirmModalData.confirmButtonText = 'Cancel Expense';
    confirmModalData.type = 'cancelled';
  }

  showConfirmModal.value = true;
};

const confirmStatusTransition = async () => {
  if (!confirmModalData.expenseId || !confirmModalData.targetStatus) return;
  isUpdatingStatus.value = true;
  try {
    const response = await axios.patch(`/api/expenses/${confirmModalData.expenseId}/status`, {
      status: confirmModalData.targetStatus
    });

    const successMessage = response.data.message || `Status updated to ${confirmModalData.targetStatus} successfully!`;
    showConfirmModal.value = false;
    success(successMessage);
    await fetchExpenses(pagination.value.current_page);
    emit('refresh');
  } catch (err) {
    console.error('Error updating expense status:', err);
    const errMessage = err.response?.data?.message || 'Failed to update expense status';
    error(errMessage);
  } finally {
    isUpdatingStatus.value = false;
  }
};

const getStatusBadgeClass = (status) => {
  const s = String(status || '').toLowerCase();
  switch (s) {
    case 'completed':
    case 'paid':
      return 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/50';
    case 'process':
    case 'processing':
      return 'bg-indigo-50 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-900/50';
    case 'pending':
    case 'submitted':
    case 'approved':
      return 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-900/50';
    case 'rejected':
      return 'bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-900/50';
    case 'cancelled':
    case 'void':
      return 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400 border-slate-200 dark:border-zinc-700';
    case 'draft':
    default:
      return 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-slate-200 border-slate-200 dark:border-zinc-700';
  }
};

const getStatusDotClass = (status) => {
  const s = String(status || '').toLowerCase();
  switch (s) {
    case 'completed':
    case 'paid':
      return 'bg-emerald-500';
    case 'process':
    case 'processing':
      return 'bg-indigo-500';
    case 'pending':
    case 'submitted':
    case 'approved':
      return 'bg-amber-500';
    case 'rejected':
      return 'bg-rose-500';
    case 'cancelled':
    case 'void':
      return 'bg-slate-400';
    case 'draft':
    default:
      return 'bg-slate-500';
  }
};

const getStatusText = (status) => {
  const s = String(status || '').toLowerCase();
  const texts = {
    draft: 'Draft',
    pending: 'Pending',
    process: 'Processing',
    processing: 'Processing',
    rejected: 'Rejected',
    completed: 'Paid',
    paid: 'Paid',
    cancelled: 'Cancelled'
  };
  return texts[s] || status;
};

defineExpose({
  fetchExpenses
});

onMounted(() => {
  fetchExpenses();
  fetchCategories();
});
</script>
