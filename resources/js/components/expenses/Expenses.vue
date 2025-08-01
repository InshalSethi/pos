<template>
  <div class="expenses-container">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Expense Management</h1>
        <p class="text-gray-600">Manage and track company expenses</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="showCategoryModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Category
        </button>
        <button
          @click="showExpenseModal = true"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Expense
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          @click="activeTab = 'expenses'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'expenses'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Expenses
        </button>
        <button
          @click="activeTab = 'categories'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'categories'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Categories
        </button>
        <button
          @click="activeTab = 'reports'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'reports'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Reports
        </button>
      </nav>
    </div>

    <!-- Tab Content -->
    <div v-if="activeTab === 'expenses'">
      <ExpenseList 
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
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import ExpenseList from './ExpenseList.vue';
import ExpenseCategoryList from './ExpenseCategoryList.vue';
import ExpenseReports from './ExpenseReports.vue';
import ExpenseModal from './ExpenseModal.vue';
import ExpenseCategoryModal from './ExpenseCategoryModal.vue';
import ExpenseViewModal from './ExpenseViewModal.vue';

// Reactive data
const activeTab = ref('expenses');
const showExpenseModal = ref(false);
const showCategoryModal = ref(false);
const showExpenseViewModal = ref(false);
const selectedExpense = ref(null);
const selectedCategory = ref(null);

// Methods
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

const fetchExpenses = () => {
  // This will be handled by the ExpenseList component
};

const fetchCategories = () => {
  // This will be handled by the ExpenseCategoryList component
};

onMounted(() => {
  // Component initialization
});
</script>

<style scoped>
.expenses-container {
  padding: 1.5rem;
}
</style>
