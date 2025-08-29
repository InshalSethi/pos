<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Bank Account Transactions</h1>
        <p class="text-gray-600">View all bank account transactions and journal entries</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex flex-col space-y-4">
        <!-- Filter Controls -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Account Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
            <select
              v-model="filters.account_id"
              @change="handleFilterChange"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">All Accounts</option>
              <option v-for="account in bankAccounts" :key="account.id" :value="account.id">
                {{ account.account_name }} ({{ account.account_code }})
              </option>
            </select>
          </div>

          <!-- Date Range -->
          <div>
            <DateRangePicker
              v-model="dateRange"
              label="Date Range"
              placeholder="Select date range"
              @change="handleDateRangeChange"
            />
          </div>

          <!-- Transaction Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
            <select
              v-model="filters.transaction_type"
              @change="handleFilterChange"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">All Types</option>
              <option value="debit">Debit</option>
              <option value="credit">Credit</option>
            </select>
          </div>

          <!-- Clear Filters Button -->
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Clear Filters
            </button>
          </div>
        </div>

        <!-- Active Filters Display -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
          <span class="text-sm text-gray-600">Active filters:</span>
          <span v-if="filters.account_id" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            Account: {{ getAccountName(filters.account_id) }}
            <button @click="clearFilter('account_id')" class="ml-1 text-blue-600 hover:text-blue-800">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
            </button>
          </span>
          <span v-if="dateRange.start_date || dateRange.end_date" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            Date: {{ formatDateRange() }}
            <button @click="clearFilter('dateRange')" class="ml-1 text-green-600 hover:text-green-800">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
            </button>
          </span>
          <span v-if="filters.transaction_type" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
            Type: {{ filters.transaction_type }}
            <button @click="clearFilter('transaction_type')" class="ml-1 text-purple-600 hover:text-purple-800">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
            </button>
          </span>
        </div>
      </div>
    </div>

    <!-- DataTable -->
    <DataTable
      title="Bank Account Transactions"
      subtitle="View all bank account transactions and journal entries"
      :columns="tableColumns"
      :data="transactions"
      :loading="loading"
      :pagination="pagination"
      :initial-search="filters.search"
      :initial-per-page="pagination.per_page"
      :default-per-page="10"
      storage-key="transactions-table-state"
      empty-message="No transactions found"
      empty-sub-message="Try adjusting your search or filter criteria."
      @search="handleSearch"
      @sort="handleSort"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    >
      <!-- Export Buttons -->
      <template #actions>
        <div class="flex space-x-2">
          <!-- CSV Export -->
          <button
            @click="exportToCSV"
            :disabled="loading || exportingCSV"
            class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
          >
            <svg v-if="exportingCSV" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>{{ exportingCSV ? 'Exporting...' : 'CSV' }}</span>
          </button>

          <!-- PDF Export -->
          <button
            @click="exportToPDF"
            :disabled="loading || exportingPDF"
            class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
          >
            <svg v-if="exportingPDF" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>{{ exportingPDF ? 'Generating...' : 'PDF' }}</span>
          </button>
        </div>
      </template>
      <!-- Custom column content -->
      <template #column-type="{ item }">
        <span :class="[
          'px-2 py-1 text-xs font-medium rounded-full',
          item.debit_amount > 0
            ? 'bg-red-100 text-red-800'
            : 'bg-green-100 text-green-800'
        ]">
          {{ item.debit_amount > 0 ? 'Debit' : 'Credit' }}
        </span>
      </template>

      <template #column-debit_amount="{ item }">
        <span v-if="item.debit_amount > 0" class="text-red-600">
          ${{ formatCurrency(item.debit_amount) }}
        </span>
      </template>

      <template #column-credit_amount="{ item }">
        <span v-if="item.credit_amount > 0" class="text-green-600">
          ${{ formatCurrency(item.credit_amount) }}
        </span>
      </template>

      <template #column-running_balance="{ item }">
        <span :class="item.running_balance >= 0 ? 'text-green-600' : 'text-red-600'">
          ${{ formatCurrency(Math.abs(item.running_balance)) }}
        </span>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import DateRangePicker from '@/components/common/DateRangePicker.vue';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

// Reactive data
const loading = ref(false);
const exportingCSV = ref(false);
const exportingPDF = ref(false);
const transactions = ref([]);
const bankAccounts = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
});

const filters = ref({
  account_id: '',
  date_from: '',
  date_to: '',
  transaction_type: '',
  search: '',
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
    key: 'id',
    label: 'ID',
    sortable: true,
    align: 'left',
    class: 'text-gray-500 font-mono text-xs'
  },
  {
    key: 'entry_date',
    label: 'Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'reference',
    label: 'Reference',
    sortable: true,
    align: 'left'
  },
  {
    key: 'description',
    label: 'Description',
    sortable: false,
    align: 'left'
  },
  {
    key: 'account_name',
    label: 'Account',
    sortable: true,
    align: 'left'
  },
  {
    key: 'type',
    label: 'Type',
    sortable: false,
    align: 'center'
  },
  {
    key: 'debit_amount',
    label: 'Debit',
    sortable: true,
    align: 'right'
  },
  {
    key: 'credit_amount',
    label: 'Credit',
    sortable: true,
    align: 'right'
  },
  {
    key: 'running_balance',
    label: 'Balance',
    sortable: false,
    align: 'right'
  }
]);

// Computed properties
const hasActiveFilters = computed(() => {
  return filters.value.account_id ||
         filters.value.date_from ||
         filters.value.date_to ||
         filters.value.transaction_type ||
         dateRange.value.start_date ||
         dateRange.value.end_date;
});

// Methods
const fetchBankAccounts = async () => {
  try {
    const response = await axios.get('/api/accounts', {
      params: { account_type: 'asset' }
    });
    // Filter for cash and bank accounts
    const allAccounts = response.data.data || response.data;
    bankAccounts.value = allAccounts.filter(account =>
      ['Cash', 'Bank Account', 'Petty Cash'].includes(account.account_name)
    );
  } catch (error) {
    console.error('Error fetching bank accounts:', error);
  }
};

const fetchTransactions = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    };
    
    const response = await axios.get('/api/transactions', { params });
    transactions.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching transactions:', error);
  } finally {
    loading.value = false;
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatCurrency = (amount) => {
  return parseFloat(amount).toFixed(2);
};

const getAccountName = (accountId) => {
  const account = bankAccounts.value.find(acc => acc.id == accountId);
  return account ? account.account_name : '';
};

const formatDateRange = () => {
  if (dateRange.value.start_date && dateRange.value.end_date) {
    return `${formatDate(dateRange.value.start_date)} - ${formatDate(dateRange.value.end_date)}`;
  } else if (dateRange.value.start_date) {
    return `From ${formatDate(dateRange.value.start_date)}`;
  } else if (dateRange.value.end_date) {
    return `Until ${formatDate(dateRange.value.end_date)}`;
  }
  return '';
};



const handleFilterChange = () => {
  saveFiltersState();
  fetchTransactions(1); // Reset to first page when filters change
};

const handleDateRangeChange = (range) => {
  filters.value.date_from = range.start_date;
  filters.value.date_to = range.end_date;
  handleFilterChange();
};

const handleSearch = (searchQuery) => {
  filters.value.search = searchQuery;
  saveFiltersState();
  fetchTransactions(1); // Reset to first page when searching
};

const handleSort = (sortData) => {
  filters.value.sort_field = sortData.field;
  filters.value.sort_order = sortData.order;
  saveFiltersState();
  fetchTransactions();
};

const handlePageChange = (page) => {
  fetchTransactions(page);
};

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage;
  saveFiltersState();
  fetchTransactions(1); // Reset to first page when changing per page
};

const clearFilters = () => {
  filters.value.account_id = '';
  filters.value.date_from = '';
  filters.value.date_to = '';
  filters.value.transaction_type = '';
  filters.value.search = '';
  dateRange.value.start_date = '';
  dateRange.value.end_date = '';

  saveFiltersState();
  fetchTransactions(1);
};

const clearFilter = (filterType) => {
  switch (filterType) {
    case 'account_id':
      filters.value.account_id = '';
      break;
    case 'dateRange':
      filters.value.date_from = '';
      filters.value.date_to = '';
      dateRange.value.start_date = '';
      dateRange.value.end_date = '';
      break;
    case 'transaction_type':
      filters.value.transaction_type = '';
      break;
  }

  saveFiltersState();
  fetchTransactions(1);
};

// Export functionality
const exportToCSV = async () => {
  try {
    exportingCSV.value = true;

    // Fetch all transactions with current filters (no pagination)
    const exportParams = {
      ...filters.value,
      export: true, // Flag to indicate export request
      per_page: 'all' // Get all records
    };

    const response = await axios.get('/api/transactions', { params: exportParams });
    const allTransactions = response.data.data || response.data;

    if (!allTransactions || allTransactions.length === 0) {
      alert('No transactions to export');
      return;
    }

    // Prepare CSV data
    const csvHeaders = [
      'ID',
      'Date',
      'Reference',
      'Description',
      'Account',
      'Type',
      'Debit Amount',
      'Credit Amount',
      'Running Balance'
    ];

    const csvRows = allTransactions.map(transaction => [
      transaction.id,
      formatDate(transaction.entry_date),
      transaction.reference || '',
      transaction.description || '',
      transaction.account_name || '',
      transaction.debit_amount > 0 ? 'Debit' : 'Credit',
      transaction.debit_amount > 0 ? formatCurrency(transaction.debit_amount) : '',
      transaction.credit_amount > 0 ? formatCurrency(transaction.credit_amount) : '',
      formatCurrency(Math.abs(transaction.running_balance))
    ]);

    // Create CSV content
    const csvContent = [
      csvHeaders.join(','),
      ...csvRows.map(row => row.map(field => `"${field}"`).join(','))
    ].join('\n');

    // Create and download file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `transactions_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

  } catch (error) {
    console.error('Error exporting transactions:', error);
    alert('Failed to export transactions. Please try again.');
  } finally {
    exportingCSV.value = false;
  }
};

const exportToPDF = async () => {
  try {
    exportingPDF.value = true;

    // Use axios to make authenticated request and get blob
    const response = await axios.get('/api/transactions/export-pdf', {
      params: filters.value,
      responseType: 'blob'
    });

    // Create blob URL and download
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = `transactions_${new Date().toISOString().split('T')[0]}.pdf`;
    link.style.display = 'none';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Clean up
    window.URL.revokeObjectURL(url);

  } catch (error) {
    console.error('Error exporting PDF:', error);
    alert('Failed to export PDF. Please try again.');
  } finally {
    exportingPDF.value = false;
  }
};

// State management
const saveFiltersState = () => {
  const state = {
    filters: filters.value,
    dateRange: dateRange.value,
    timestamp: Date.now()
  };
  localStorage.setItem('transactions-filters-state', JSON.stringify(state));
};

const loadFiltersState = () => {
  try {
    const saved = localStorage.getItem('transactions-filters-state');
    if (saved) {
      const state = JSON.parse(saved);

      // Only load state if it's recent (within 24 hours)
      if (Date.now() - state.timestamp < 24 * 60 * 60 * 1000) {
        filters.value = { ...filters.value, ...state.filters };
        dateRange.value = { ...dateRange.value, ...state.dateRange };
      }
    }
  } catch (error) {
    console.warn('Failed to load filters state:', error);
  }
};

// Lifecycle
onMounted(() => {
  loadFiltersState();
  fetchBankAccounts();
  fetchTransactions();
});
</script>
