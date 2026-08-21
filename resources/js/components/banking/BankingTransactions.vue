<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header with Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Banking Transactions</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
          View all income, expense, and transfer records across your bank &amp; cash accounts.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <!-- Date Range Picker (Header) -->
        <div class="w-56">
          <FloatingDateRangePicker
            v-model:start-date="headerStartDate"
            v-model:end-date="headerEndDate"
            label=""
            placeholder="All Time"
            :show-top-presets="false"
            @change="onHeaderDateRangeChange"
          />
        </div>

        <!-- Download PDF Button -->
        <button
          @click="downloadPDF"
          :disabled="exportingPDF"
          class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-xl text-xs font-semibold shadow-xs transition-all cursor-pointer shrink-0 active:scale-[0.98] gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
          title="Download PDF Report"
        >
          <svg v-if="!exportingPDF" class="w-3.5 h-3.5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <svg v-else class="w-3.5 h-3.5 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span>{{ exportingPDF ? 'Generating...' : 'Download PDF' }}</span>
        </button>

        <button
          @click="navigateToPaymentIn"
          class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-900 rounded-xl text-xs font-semibold shadow-xs transition-all cursor-pointer shrink-0 active:scale-[0.98] gap-1.5"
          title="Go to Payment In (Payment Receipts)"
        >
          <svg class="w-3.5 h-3.5 text-emerald-400 dark:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
          <span>Payment In</span>
        </button>

        <button
          @click="navigateToPaymentOut"
          class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-900 rounded-xl text-xs font-semibold shadow-xs transition-all cursor-pointer shrink-0 active:scale-[0.98] gap-1.5"
          title="Go to Payment Out (Payments)"
        >
          <svg class="w-3.5 h-3.5 text-rose-400 dark:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
          </svg>
          <span>Payment Out</span>
        </button>
      </div>
    </div>

    <!-- Active Filter Tags Bar (based on appliedFilters) -->
    <div v-if="activeFilterCount > 0" class="flex flex-wrap items-center gap-2 pt-1">
      <span class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Active Filters:</span>

      <!-- Date Range Tag -->
      <span
        v-if="appliedFilters.start_date || appliedFilters.end_date"
        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 rounded-lg text-xs font-medium border border-slate-200 dark:border-zinc-700"
      >
        <span>📅 {{ formatDateShort(appliedFilters.start_date) }} - {{ formatDateShort(appliedFilters.end_date) }}</span>
        <button @click="clearFilter('date')" class="hover:text-rose-500 text-slate-400 cursor-pointer font-bold">×</button>
      </span>

      <!-- Multi-select Types Tags -->
      <template v-if="appliedFilters.types && appliedFilters.types.length > 0">
        <span
          v-for="t in appliedFilters.types"
          :key="'applied-type-' + t"
          class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 rounded-lg text-xs font-medium border border-indigo-200 dark:border-indigo-800/60"
        >
          <span>🏷️ {{ getTypeLabel(t) }}</span>
          <button @click="removeAppliedType(t)" class="hover:text-rose-500 text-indigo-400 cursor-pointer font-bold">×</button>
        </span>
      </template>

      <!-- Multi-select Bank Accounts Tags -->
      <template v-if="appliedFilters.bank_account_ids && appliedFilters.bank_account_ids.length > 0">
        <span
          v-for="accId in appliedFilters.bank_account_ids"
          :key="'applied-acc-' + accId"
          class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 rounded-lg text-xs font-medium border border-emerald-200 dark:border-emerald-800/60"
        >
          <span>🏦 {{ getAccountLabel(accId) }}</span>
          <button @click="removeAppliedAccount(accId)" class="hover:text-rose-500 text-emerald-400 cursor-pointer font-bold">×</button>
        </span>
      </template>

      <!-- Amount Tag -->
      <span
        v-if="appliedFilters.amount"
        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 rounded-lg text-xs font-medium border border-slate-200 dark:border-zinc-700"
      >
        <span>💵 {{ companyCurrencySymbol }} {{ formatNumber(appliedFilters.amount) }}</span>
        <button @click="clearFilter('amount')" class="hover:text-rose-500 text-slate-400 cursor-pointer font-bold">×</button>
      </span>

      <button
        @click="resetFilters"
        class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer ml-1"
      >
        Clear All
      </button>
    </div>

    <!-- DataTable Container -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
      <DataTable
        title="Transactions Ledger"
        subtitle="Complete record of banking activities, invoice settlements, and account balance changes"
        :columns="tableColumns"
        :data="transactions"
        :loading="loading"
        :pagination="pagination"
        :initial-search="searchQuery"
        :initial-per-page="perPage"
        :default-per-page="15"
        table-height="400px"
        storage-key="banking-transactions-table-state"
        empty-message="No banking transactions found"
        empty-sub-message="Transactions recorded via Payment In, Payment Out, Sales, or Purchases will appear here."
        @search="handleTableSearch"
        @sort="handleSort"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      >
        <!-- Filter Drawer Button -->
        <template #actions>
          <div class="flex items-center space-x-2">
            <button
              @click="openFilterDrawer"
              type="button"
              class="relative inline-flex items-center gap-2 px-3.5 py-2 border border-gray-300 dark:border-zinc-700/80 rounded-xl bg-slate-50 dark:bg-zinc-800/80 hover:bg-white dark:hover:bg-zinc-800 text-xs font-bold text-gray-800 dark:text-zinc-200 transition-all shadow-xs cursor-pointer active:scale-95"
              title="Open Filter Drawer"
            >
              <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              <span>Filter</span>
              <span
                v-if="activeFilterCount > 0"
                class="px-1.5 py-0.5 text-[10px] font-black bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-full"
              >
                {{ activeFilterCount }}
              </span>
            </button>
          </div>
        </template>

        <!-- Custom Cell: Date -->
        <template #column-transaction_date="{ item }">
          <div class="flex items-center space-x-2 text-xs font-semibold text-slate-700 dark:text-zinc-300">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(item.transaction_date) }}</span>
          </div>
        </template>

        <!-- Custom Cell: Bank / Cash Account -->
        <template #column-bank_account="{ item }">
          <div class="flex flex-col">
            <span class="font-bold text-xs text-slate-900 dark:text-white">
              {{ item.bank_account?.account_name || 'Cash in Hand' }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-zinc-400 font-medium">
              {{ item.bank_account?.bank_name ? `${item.bank_account.bank_name} ${formatLastFour(item.bank_account?.account_number)}` : 'Cash in Hand' }}
            </span>
          </div>
        </template>

        <!-- Custom Cell: Reference Number -->
        <template #column-reference_number="{ item }">
          <span class="font-mono text-xs font-semibold text-slate-800 dark:text-zinc-200">
            {{ item.reference_number || '-' }}
          </span>
        </template>

        <!-- Custom Cell: Description -->
        <template #column-description="{ item }">
          <span class="text-xs text-slate-600 dark:text-zinc-400 line-clamp-1" :title="item.description">
            {{ item.description || '-' }}
          </span>
        </template>

        <!-- Custom Cell: Transaction Type Badge -->
        <template #column-type="{ item }">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase',
              getTransactionBadge(item).class
            ]"
          >
            {{ getTransactionBadge(item).label }}
          </span>
        </template>

        <!-- Custom Cell: Amount -->
        <template #column-amount="{ item }">
          <span
            :class="[
              'font-bold text-xs',
              isIncomeType(item.transaction_type)
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-rose-600 dark:text-rose-400'
            ]"
          >
            {{ isIncomeType(item.transaction_type) ? '+' : '-' }}
            {{ getCurrencySymbol(item.bank_account?.currency) }}
            {{ formatNumber(item.amount) }}
          </span>
        </template>

        <!-- Custom Cell: Running Balance -->
        <template #column-running_balance="{ item }">
          <span class="font-bold text-xs text-slate-900 dark:text-zinc-100">
            {{ getCurrencySymbol(item.bank_account?.currency) }}
            {{ formatNumber(item.running_balance) }}
          </span>
        </template>
      </DataTable>
    </div>

    <!-- Slide-over Filter Drawer (Manual Apply & Multi-select) -->
    <Teleport to="body">
      <div v-if="showFilterDrawer" class="fixed inset-0 z-[100] overflow-hidden">
        <!-- Backdrop (Discard unapplied changes on click) -->
        <div
          @click="closeFilterDrawer"
          class="fixed inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-xs transition-opacity duration-300"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
          <div class="w-screen max-w-md bg-white dark:bg-zinc-900 shadow-2xl flex flex-col justify-between border-l border-slate-200 dark:border-zinc-800 transform transition-all duration-300">
            <!-- Drawer Header -->
            <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
              <div class="flex items-center gap-2.5">
                <div class="p-2 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-xl shadow-xs">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Filter Transactions</h2>
                  <p class="text-[11px] font-medium text-slate-500 dark:text-zinc-400">Refine bank &amp; cash records</p>
                </div>
              </div>

              <button
                @click="closeFilterDrawer"
                class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all cursor-pointer"
                title="Close drawer"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Drawer Content (Draft Filters) -->
            <div class="p-6 space-y-5 overflow-y-auto flex-1 custom-scrollbar">
              <!-- Filter 1: Date Range -->
              <div class="space-y-1.5">
                <FloatingDateRangePicker
                  v-model:start-date="draftFilters.start_date"
                  v-model:end-date="draftFilters.end_date"
                  label="Date Range"
                />
              </div>

              <!-- Filter 2: Transaction Type (Multi-Select) -->
              <div class="space-y-1.5">
                <CustomFloatingSelect
                  v-model="draftFilters.types"
                  :options="transactionTypeOptions"
                  label="Transaction Types"
                  placeholder="Select transaction types..."
                  multiple
                  searchable
                />
              </div>

              <!-- Filter 3: Bank & Cash Accounts (Multi-Select with balance) -->
              <div class="space-y-1.5">
                <CustomFloatingSelect
                  v-model="draftFilters.bank_account_ids"
                  :options="bankAccountOptions"
                  label="Bank &amp; Cash Accounts"
                  placeholder="Select bank / cash accounts..."
                  multiple
                  searchable
                />
              </div>

              <!-- Filter 4: Amount Filter -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                  Transaction Amount
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-slate-400 dark:text-zinc-500">
                    {{ companyCurrencySymbol }}
                  </span>
                  <input
                    v-model="draftFilters.amount"
                    type="number"
                    step="0.01"
                    placeholder="Enter manual amount..."
                    class="w-full pl-12 pr-8 py-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs font-normal text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 hover:border-slate-400 dark:hover:border-zinc-600 focus:outline-none focus:ring-1 focus:ring-slate-400 transition-all shadow-xs"
                    @keyup.enter="applyAndCloseDrawer"
                  />
                  <button
                    v-if="draftFilters.amount"
                    type="button"
                    @click="draftFilters.amount = ''"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer font-bold"
                  >
                    ×
                  </button>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-zinc-500">Matches transactions with this exact amount.</p>
              </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between gap-3">
              <button
                type="button"
                @click="resetFilters"
                :disabled="activeFilterCount === 0 && draftFilterCount === 0"
                class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all disabled:opacity-50 cursor-pointer"
              >
                Reset Filters
              </button>
              <button
                type="button"
                @click="applyAndCloseDrawer"
                class="px-5 py-2.5 bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-bold rounded-xl text-xs shadow-xs transition-all cursor-pointer"
              >
                Apply &amp; Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import DataTable from '@/components/common/DataTable.vue';
import FloatingDateRangePicker from '@/components/common/FloatingDateRangePicker.vue';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';

export default {
  name: 'BankingTransactions',
  components: {
    DataTable,
    FloatingDateRangePicker,
    CustomFloatingSelect,
  },
  setup() {
    const router = useRouter();
    const { showToast } = useToast();
    const currencyStore = useCurrencyStore();

    // Table state
    const transactions = ref([]);
    const bankAccounts = ref([]);
    const loading = ref(true);
    const searchQuery = ref('');
    const perPage = ref(15);
    const currentPage = ref(1);
    const sortField = ref('transaction_date');
    const sortOrder = ref('desc');

    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    });

    // Drawer state
    const showFilterDrawer = ref(false);

    // Header date range picker state
    const headerStartDate = ref('');
    const headerEndDate = ref('');

    // PDF export state
    const exportingPDF = ref(false);

    // Applied Filters (committed, drives the API query)
    const appliedFilters = reactive({
      start_date: '',
      end_date: '',
      types: [],
      bank_account_ids: [],
      amount: '',
    });

    // Draft Filters (local to the drawer, decoupled from live query)
    const draftFilters = reactive({
      start_date: '',
      end_date: '',
      types: [],
      bank_account_ids: [],
      amount: '',
    });

    // Table Column Definitions
    const tableColumns = [
      { key: 'transaction_date', label: 'Date', sortable: true, align: 'left' },
      { key: 'bank_account', label: 'Bank / Cash Account', sortable: false, align: 'left' },
      { key: 'reference_number', label: 'Reference', sortable: true, align: 'left' },
      { key: 'description', label: 'Description', sortable: true, align: 'left' },
      { key: 'type', label: 'Type', sortable: false, align: 'center' },
      { key: 'amount', label: 'Amount', sortable: true, align: 'right' },
      { key: 'running_balance', label: 'Running Balance', sortable: true, align: 'right' },
    ];

    // Transaction Type Options (multi-select, no "All" default entry)
    const transactionTypeOptions = [
      // Payment In / Income
      { value: 'all_income', label: 'All Payment In (Income / Inflow)', sublabel: 'All money coming in' },
      { value: 'customer_payment', label: 'Customer Payment', sublabel: 'Invoice settlement receipt' },
      { value: 'customer_advance', label: 'Customer Advance', sublabel: 'Advance received from customer' },
      { value: 'supplier_refund', label: 'Supplier Refund', sublabel: 'Advance or return refund from supplier' },
      { value: 'supplier_rebate', label: 'Supplier Rebate', sublabel: 'Discount or rebate from supplier' },
      { value: 'sale_invoice', label: 'Sales Invoice / POS Payment', sublabel: 'Direct POS sale register receipt' },
      { value: 'interest_income', label: 'Interest Income', sublabel: 'Bank profit or interest' },
      { value: 'rental_income', label: 'Rental Income', sublabel: 'Property or asset rent' },
      { value: 'commission_income', label: 'Commission Income', sublabel: 'Earned commissions' },
      { value: 'asset_sale', label: 'Asset Sale', sublabel: 'Proceeds from equipment or asset disposal' },
      { value: 'cash_deposit', label: 'Cash Deposit', sublabel: 'Physical cash banked' },
      { value: 'miscellaneous_income', label: 'Miscellaneous Income', sublabel: 'Other miscellaneous income' },
      { value: 'other_receipt', label: 'Other Income Receipt', sublabel: 'General inflow' },

      // Payment Out / Expenses
      { value: 'all_expense', label: 'All Payment Out (Expense / Outflow)', sublabel: 'All money going out' },
      { value: 'supplier_payment', label: 'Supplier Payment', sublabel: 'PO / Bill payment to vendor' },
      { value: 'supplier_advance', label: 'Supplier Advance', sublabel: 'Advance paid to supplier' },
      { value: 'salary_payment', label: 'Salary / Payroll Payment', sublabel: 'Staff wage or salary' },
      { value: 'purchase_order', label: 'Purchase Order / Bill Payment', sublabel: 'Direct purchase invoice payment' },
      { value: 'sale_return', label: 'Sale Return Refund', sublabel: 'Refund given to customer' },
      { value: 'purchase_return', label: 'Purchase Return', sublabel: 'Purchase return adjustment' },
      { value: 'utility_expense', label: 'Utility Expense', sublabel: 'Electricity, water, gas, internet' },
      { value: 'rent_expense', label: 'Rent Expense', sublabel: 'Premises or equipment lease' },
      { value: 'office_expense', label: 'Office Expense', sublabel: 'Stationery, tea, consumables' },
      { value: 'tax_payment', label: 'Tax Payment', sublabel: 'Income tax, GST, SST payment' },
      { value: 'travel_expense', label: 'Travel Expense', sublabel: 'Transport, fuel, lodging' },
      { value: 'marketing_expense', label: 'Marketing Expense', sublabel: 'Advertising and promotion' },
      { value: 'maintenance_expense', label: 'Maintenance Expense', sublabel: 'Repairs and servicing' },
      { value: 'cash_withdrawal', label: 'Cash Withdrawal', sublabel: 'Cash drawn from bank' },
      { value: 'asset_purchase', label: 'Asset Purchase', sublabel: 'Fixed asset acquisition' },
      { value: 'loan_repayment', label: 'Loan Repayment', sublabel: 'Principal / finance installment' },
      { value: 'other_payment', label: 'Other Payment', sublabel: 'General outflow' },

      // Transfers
      { value: 'bank_transfer_in', label: 'Bank Transfer In', sublabel: 'Internal inter-account transfer' },
      { value: 'bank_transfer_out', label: 'Bank Transfer Out', sublabel: 'Internal inter-account transfer' },
    ];

    // Bank Account Options (multi-select, with live balances)
    const bankAccountOptions = computed(() => {
      return bankAccounts.value.map(acc => {
        const isCash = acc.account_type === 'cash' || String(acc.account_name).toLowerCase().includes('cash');
        const currency = acc.currency || companyCurrencySymbol.value;
        const balanceVal = formatNumber(acc.current_balance ?? acc.balance ?? 0);
        const currSymbol = getCurrencySymbol(currency);
        let label = acc.account_name;
        let sublabel = '';

        if (isCash) {
          sublabel = `Cash in Hand • Balance: ${currSymbol} ${balanceVal}`;
        } else {
          const bankName = acc.bank_name || 'Bank';
          const lastFour = formatLastFour(acc.account_number);
          label = `${acc.account_name} - ${bankName} ${lastFour}`.trim();
          const typeStr = acc.account_type ? acc.account_type.toUpperCase() : 'BANK';
          sublabel = `${typeStr} • ${currency} • Balance: ${currSymbol} ${balanceVal}`;
        }

        return { value: acc.id, label, sublabel };
      });
    });

    const activeFilterCount = computed(() => {
      let count = 0;
      if (appliedFilters.start_date || appliedFilters.end_date) count++;
      if (appliedFilters.types && appliedFilters.types.length > 0) count += appliedFilters.types.length;
      if (appliedFilters.bank_account_ids && appliedFilters.bank_account_ids.length > 0) count += appliedFilters.bank_account_ids.length;
      if (appliedFilters.amount && appliedFilters.amount.toString().trim() !== '') count++;
      return count;
    });

    const draftFilterCount = computed(() => {
      let count = 0;
      if (draftFilters.start_date || draftFilters.end_date) count++;
      if (draftFilters.types && draftFilters.types.length > 0) count += draftFilters.types.length;
      if (draftFilters.bank_account_ids && draftFilters.bank_account_ids.length > 0) count += draftFilters.bank_account_ids.length;
      if (draftFilters.amount && draftFilters.amount.toString().trim() !== '') count++;
      return count;
    });

    const companyCurrencySymbol = computed(() => {
      return currencyStore.symbol || currencyStore.tenantCurrencyCode || 'PKR';
    });

    const getCurrencySymbol = (code) => {
      if (!code) return companyCurrencySymbol.value;
      const upper = String(code).trim().toUpperCase();
      const map = { PKR: companyCurrencySymbol.value, USD: '$', EUR: '€', GBP: '£', AED: 'AED', SAR: 'SAR', CAD: 'CA$', AUD: 'A$', INR: '₹' };
      return map[upper] || upper || companyCurrencySymbol.value;
    };

    const isIncomeType = (type) => type === 'credit' || type === 'income';

    const formatLastFour = (num) => {
      if (!num) return '';
      const str = String(num).trim();
      return str.length > 4 ? `(••••${str.slice(-4)})` : `(${str})`;
    };

    const getTransactionBadge = (tx) => {
      if (isIncomeType(tx.transaction_type)) {
        let recType = tx.payment_receipt?.receipt_type || '';
        if (!recType && tx.description) {
          const d = tx.description.toLowerCase();
          if (d.includes('customer') || d.includes('payment in')) return { label: 'CUSTOMER PAYMENT', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-400 dark:border-emerald-800/40' };
          if (d.includes('refund')) return { label: 'SUPPLIER REFUND', class: 'bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-950/60 dark:text-teal-400 dark:border-teal-800/40' };
          if (d.includes('sale') || d.includes('pos')) return { label: 'SALE INVOICE', class: 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-400 dark:border-blue-800/40' };
        }
        if (recType === 'supplier_refund') return { label: 'SUPPLIER REFUND', class: 'bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-950/60 dark:text-teal-400 dark:border-teal-800/40' };
        if (recType === 'customer_advance') return { label: 'CUSTOMER ADVANCE', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-400 dark:border-emerald-800/40' };
        return { label: 'INCOME', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800/40' };
      }

      let typeKey = tx.payment?.payment_type || tx.payment_type || '';
      if (!typeKey && tx.description) {
        const desc = tx.description.toLowerCase();
        if (desc.includes('supplier')) typeKey = 'supplier_payment';
        else if (desc.includes('salary')) typeKey = 'salary_payment';
        else if (desc.includes('utility')) typeKey = 'utility_expense';
        else if (desc.includes('rent')) typeKey = 'rent_expense';
        else if (desc.includes('office')) typeKey = 'office_expense';
        else if (desc.includes('expense')) typeKey = 'expense_payment';
        else if (desc.includes('sale return') || desc.includes('return')) typeKey = 'sale_return_payment';
        else if (desc.includes('purchase') || desc.includes('bill')) typeKey = 'purchase_invoice_payment';
        else if (desc.includes('transfer')) typeKey = 'transfer_out';
      }

      const normalized = String(typeKey).trim().toLowerCase();
      if (normalized.includes('supplier')) return { label: 'SUPPLIER PAYMENT', class: 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-400 dark:border-rose-800/40' };
      if (normalized.includes('salary')) return { label: 'SALARY', class: 'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/60 dark:text-purple-400 dark:border-purple-800/40' };
      if (normalized.includes('sale_return') || normalized.includes('sale return')) return { label: 'SALE RETURN', class: 'bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800' };
      if (normalized.includes('purchase')) return { label: 'PURCHASE BILL', class: 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-400 dark:border-indigo-800/40' };
      if (normalized.includes('expense') || normalized.includes('utility') || normalized.includes('rent') || normalized.includes('office')) return { label: 'EXPENSE', class: 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-400 dark:border-amber-800/40' };
      if (normalized.includes('transfer')) return { label: 'TRANSFER', class: 'bg-violet-50 text-violet-700 border border-violet-200 dark:bg-violet-950/60 dark:text-violet-400 dark:border-violet-800/40' };
      return { label: 'PAYMENT OUT', class: 'bg-slate-100 text-slate-700 border border-slate-300 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700' };
    };

    // --- API ---
    const fetchTransactions = async (page = 1) => {
      loading.value = true;
      currentPage.value = page;

      try {
        const params = {
          page,
          per_page: perPage.value,
          search: searchQuery.value || undefined,
          start_date: appliedFilters.start_date || undefined,
          end_date: appliedFilters.end_date || undefined,
          types: appliedFilters.types && appliedFilters.types.length > 0 ? appliedFilters.types : undefined,
          bank_account_ids: appliedFilters.bank_account_ids && appliedFilters.bank_account_ids.length > 0 ? appliedFilters.bank_account_ids : undefined,
          amount: appliedFilters.amount || undefined,
          sort_by: sortField.value,
          sort_direction: sortOrder.value,
        };

        const response = await api.banking.transactions(params);

        if (response.data && response.data.data) {
          transactions.value = response.data.data;
          pagination.value = {
            current_page: response.data.current_page || 1,
            last_page: response.data.last_page || 1,
            per_page: response.data.per_page || perPage.value,
            total: response.data.total || 0,
          };
        } else {
          transactions.value = Array.isArray(response.data) ? response.data : [];
          pagination.value = { current_page: 1, last_page: 1, per_page: perPage.value, total: transactions.value.length };
        }
      } catch (error) {
        showToast('Failed to load banking transactions', 'error');
      } finally {
        loading.value = false;
      }
    };

    const fetchBankAccounts = async () => {
      try {
        const res = await api.banking.accounts();
        bankAccounts.value = res.data?.data || res.data || [];
      } catch (e) { /* ignore */ }
    };

    // Navigation
    const navigateToPaymentIn = () => router.push('/payment-receipts');
    const navigateToPaymentOut = () => router.push('/payments');

    // DataTable event handlers
    const handleTableSearch = (query) => { searchQuery.value = query; fetchTransactions(1); };
    const handleSort = ({ field, order }) => { sortField.value = field; sortOrder.value = order; fetchTransactions(1); };
    const handlePageChange = (page) => fetchTransactions(page);
    const handlePerPageChange = (newPerPage) => { perPage.value = newPerPage; fetchTransactions(1); };

    // Header Date Range Change
    const onHeaderDateRangeChange = () => {
      appliedFilters.start_date = headerStartDate.value;
      appliedFilters.end_date = headerEndDate.value;
      fetchTransactions(1);
    };

    // Download PDF
    const downloadPDF = async () => {
      try {
        exportingPDF.value = true;

        const params = {
          start_date: appliedFilters.start_date || undefined,
          end_date: appliedFilters.end_date || undefined,
          search: searchQuery.value || undefined,
          types: appliedFilters.types && appliedFilters.types.length > 0 ? appliedFilters.types : undefined,
          bank_account_ids: appliedFilters.bank_account_ids && appliedFilters.bank_account_ids.length > 0 ? appliedFilters.bank_account_ids : undefined,
          amount: appliedFilters.amount || undefined,
        };

        const response = await api.banking.exportPDF(params);

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = `banking-transactions-${new Date().toISOString().split('T')[0]}.pdf`;
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
        showToast('PDF downloaded successfully', 'success');
      } catch (error) {
        console.error('PDF export error:', error);
        showToast('Failed to generate PDF report', 'error');
      } finally {
        exportingPDF.value = false;
      }
    };

    // --- Drawer Manual Apply Logic ---
    const openFilterDrawer = () => {
      // Snapshot appliedFilters into draftFilters
      draftFilters.start_date = appliedFilters.start_date;
      draftFilters.end_date = appliedFilters.end_date;
      draftFilters.types = Array.isArray(appliedFilters.types) ? [...appliedFilters.types] : [];
      draftFilters.bank_account_ids = Array.isArray(appliedFilters.bank_account_ids) ? [...appliedFilters.bank_account_ids] : [];
      draftFilters.amount = appliedFilters.amount;
      showFilterDrawer.value = true;
    };

    const closeFilterDrawer = () => {
      // Discard unapplied drafts, revert to appliedFilters
      draftFilters.start_date = appliedFilters.start_date;
      draftFilters.end_date = appliedFilters.end_date;
      draftFilters.types = Array.isArray(appliedFilters.types) ? [...appliedFilters.types] : [];
      draftFilters.bank_account_ids = Array.isArray(appliedFilters.bank_account_ids) ? [...appliedFilters.bank_account_ids] : [];
      draftFilters.amount = appliedFilters.amount;
      showFilterDrawer.value = false;
    };

    const applyAndCloseDrawer = () => {
      // Commit draftFilters → appliedFilters, then fetch
      appliedFilters.start_date = draftFilters.start_date;
      appliedFilters.end_date = draftFilters.end_date;
      appliedFilters.types = Array.isArray(draftFilters.types) ? [...draftFilters.types] : [];
      appliedFilters.bank_account_ids = Array.isArray(draftFilters.bank_account_ids) ? [...draftFilters.bank_account_ids] : [];
      appliedFilters.amount = draftFilters.amount;
      // Sync header date picker with applied filters
      headerStartDate.value = appliedFilters.start_date;
      headerEndDate.value = appliedFilters.end_date;
      showFilterDrawer.value = false;
      fetchTransactions(1);
    };

    const resetFilters = () => {
      draftFilters.start_date = '';
      draftFilters.end_date = '';
      draftFilters.types = [];
      draftFilters.bank_account_ids = [];
      draftFilters.amount = '';
      appliedFilters.start_date = '';
      appliedFilters.end_date = '';
      appliedFilters.types = [];
      appliedFilters.bank_account_ids = [];
      appliedFilters.amount = '';
      headerStartDate.value = '';
      headerEndDate.value = '';
      fetchTransactions(1);
    };

    const removeAppliedType = (t) => {
      appliedFilters.types = appliedFilters.types.filter(item => item !== t);
      fetchTransactions(1);
    };

    const removeAppliedAccount = (id) => {
      appliedFilters.bank_account_ids = appliedFilters.bank_account_ids.filter(item => item != id);
      fetchTransactions(1);
    };

    const clearFilter = (key) => {
      if (key === 'date') { appliedFilters.start_date = ''; appliedFilters.end_date = ''; headerStartDate.value = ''; headerEndDate.value = ''; }
      else if (key === 'amount') { appliedFilters.amount = ''; }
      fetchTransactions(1);
    };

    const getTypeLabel = (typeKey) => {
      const match = transactionTypeOptions.find(o => o.value === typeKey);
      return match ? match.label : typeKey;
    };

    const getAccountLabel = (accId) => {
      const match = bankAccounts.value.find(o => o.id == accId);
      return match ? (match.bank_name ? `${match.account_name} (${match.bank_name})` : match.account_name) : 'Account';
    };

    const formatDate = (val) => {
      if (!val) return '-';
      try { const d = new Date(val); return isNaN(d.getTime()) ? String(val).split('T')[0] : d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); }
      catch { return val; }
    };

    const formatDateShort = (val) => {
      if (!val) return '';
      try { const d = new Date(val); return isNaN(d.getTime()) ? String(val) : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }); }
      catch { return val; }
    };

    const formatNumber = (val) => Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    onMounted(() => {
      currencyStore.fetchCurrencies();
      fetchTransactions(1);
      fetchBankAccounts();
    });

    return {
      transactions, bankAccounts, loading, searchQuery, perPage, pagination, tableColumns,
      showFilterDrawer, appliedFilters, draftFilters, transactionTypeOptions, bankAccountOptions,
      activeFilterCount, draftFilterCount, companyCurrencySymbol, getCurrencySymbol,
      isIncomeType, formatLastFour, getTransactionBadge,
      headerStartDate, headerEndDate, exportingPDF,
      navigateToPaymentIn, navigateToPaymentOut,
      handleTableSearch, handleSort, handlePageChange, handlePerPageChange,
      onHeaderDateRangeChange, downloadPDF,
      openFilterDrawer, closeFilterDrawer, applyAndCloseDrawer, resetFilters,
      removeAppliedType, removeAppliedAccount, clearFilter,
      getTypeLabel, getAccountLabel, formatDate, formatDateShort, formatNumber,
    };
  },
};
</script>
