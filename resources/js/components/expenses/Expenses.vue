<template>
  <div class="expenses-container p-4 sm:p-6 max-w-[1600px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs">
      <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Expense Management</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-0.5">Track, categorize, and approve company expenses seamlessly</p>
      </div>
      <div class="flex items-center gap-2.5">
        <button
          @click="showCategoryModal = true"
          class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700/80 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold shadow-2xs transition-all flex items-center justify-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Category
        </button>
        <button
          @click="showExpenseModal = true"
          class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 rounded-xl text-xs font-extrabold shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Expense
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-zinc-800">
      <nav class="-mb-px flex space-x-6">
        <button
          @click="activeTab = 'expenses'"
          :class="[
            'py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer',
            activeTab === 'expenses'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white'
              : 'border-transparent text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Expenses
        </button>
        <button
          @click="activeTab = 'categories'"
          :class="[
            'py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer',
            activeTab === 'categories'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white'
              : 'border-transparent text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Categories
        </button>
        <button
          @click="activeTab = 'reports'"
          :class="[
            'py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer',
            activeTab === 'reports'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white'
              : 'border-transparent text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Reports
        </button>
      </nav>
    </div>

    <!-- Tab Content -->
    <div v-if="activeTab === 'expenses'">
      <ExpenseList
        ref="expenseListRef"
        @edit-expense="editExpense"
        @view-expense="viewExpense"
        @refresh="fetchExpenses"
      />
    </div>

    <div v-if="activeTab === 'categories'">
      <ExpenseCategoryList 
        @edit-category="editCategory"
        @refresh="fetchCategories"
      />
    </div>

    <div v-if="activeTab === 'reports'">
      <ExpenseReports />
    </div>

    <!-- Expense Modal -->
    <ExpenseModal
      v-if="showExpenseModal"
      :expense="selectedExpense"
      @close="closeExpenseModal"
      @saved="handleExpenseSaved"
    />

    <!-- Category Modal -->
    <ExpenseCategoryModal
      v-if="showCategoryModal"
      :category="selectedCategory"
      @close="closeCategoryModal"
      @saved="handleCategorySaved"
    />

    <!-- Expense View Modal -->
    <ExpenseViewModal
      v-if="showExpenseViewModal"
      :expense="selectedExpense"
      @close="showExpenseViewModal = false"
      @edit="editExpenseFromView"
      @approve="handleExpenseApprove"
      @reject="handleExpenseReject"
      @pay="handleExpensePay"
      @create-payment="handleCreatePayment"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ExpenseList from './ExpenseList.vue';
import ExpenseCategoryList from './ExpenseCategoryList.vue';
import ExpenseReports from './ExpenseReports.vue';
import ExpenseModal from './ExpenseModal.vue';
import ExpenseCategoryModal from './ExpenseCategoryModal.vue';
import ExpenseViewModal from './ExpenseViewModal.vue';

const route = useRoute();
const router = useRouter();

// Reactive data
const activeTab = ref('expenses');
const showExpenseModal = ref(false);
const showCategoryModal = ref(false);
const showExpenseViewModal = ref(false);
const selectedExpense = ref(null);
const selectedCategory = ref(null);
const expenseListRef = ref(null);

const checkAutoOpenCreate = () => {
  if (route.path.endsWith('/create') || route.query.create === 'true' || route.query.action === 'create') {
    selectedExpense.value = null;
    showExpenseModal.value = true;
  }
};

const editExpense = (expense) => {
  selectedExpense.value = expense;
  showExpenseModal.value = true;
};

const viewExpense = (expense) => {
  selectedExpense.value = expense;
  showExpenseViewModal.value = true;
};

const editCategory = (category) => {
  selectedCategory.value = category;
  showCategoryModal.value = true;
};

const closeExpenseModal = () => {
  showExpenseModal.value = false;
  selectedExpense.value = null;
  if (route.path.endsWith('/create')) {
    router.replace('/expenses');
  }
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
  selectedCategory.value = null;
};

const editExpenseFromView = () => {
  showExpenseViewModal.value = false;
  showExpenseModal.value = true;
};

const handleExpenseSaved = () => {
  closeExpenseModal();
  fetchExpenses();
};

const handleCategorySaved = () => {
  closeCategoryModal();
  fetchCategories();
};

const handleExpenseApprove = () => {
  showExpenseViewModal.value = false;
  fetchExpenses();
};

const handleExpenseReject = () => {
  showExpenseViewModal.value = false;
  fetchExpenses();
};

const handleExpensePay = () => {
  showExpenseViewModal.value = false;
  fetchExpenses();
};

const handleCreatePayment = (expense) => {
  showExpenseViewModal.value = false;
  window.open(`/payments?create=true&type=expense_payment&reference_id=${expense.id}&amount=${expense.amount}&payee_name=${encodeURIComponent(expense.vendor_name || 'Expense Payment')}&description=${encodeURIComponent('Payment for expense: ' + expense.title)}`, '_blank');
};

const fetchExpenses = () => {
  if (expenseListRef.value) {
    expenseListRef.value.fetchExpenses();
  }
};

const fetchCategories = () => {
};

onMounted(() => {
  checkAutoOpenCreate();
});

watch(() => route.path, () => {
  checkAutoOpenCreate();
});

watch(() => route.query, () => {
  checkAutoOpenCreate();
});
</script>
