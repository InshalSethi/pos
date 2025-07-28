<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">
          {{ isEditing ? 'Edit Expense' : 'Create New Expense' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveExpense" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Title -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter expense title"
            />
            <span v-if="errors.title" class="text-red-500 text-sm">{{ errors.title[0] }}</span>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <select
              v-model="form.category_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <span v-if="errors.category_id" class="text-red-500 text-sm">{{ errors.category_id[0] }}</span>
          </div>

          <!-- Employee -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
            <select
              v-model="form.employee_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Employee</option>
              <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                {{ employee.full_name }}
              </option>
            </select>
            <span v-if="errors.employee_id" class="text-red-500 text-sm">{{ errors.employee_id[0] }}</span>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
            <input
              v-model="form.amount"
              type="number"
              step="0.01"
              min="0.01"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="0.00"
            />
            <span v-if="errors.amount" class="text-red-500 text-sm">{{ errors.amount[0] }}</span>
          </div>

          <!-- Expense Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date *</label>
            <input
              v-model="form.expense_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <span v-if="errors.expense_date" class="text-red-500 text-sm">{{ errors.expense_date[0] }}</span>
          </div>

          <!-- Vendor Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Name</label>
            <input
              v-model="form.vendor_name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter vendor name"
            />
            <span v-if="errors.vendor_name" class="text-red-500 text-sm">{{ errors.vendor_name[0] }}</span>
          </div>

          <!-- Reference Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Invoice/Receipt number"
            />
            <span v-if="errors.reference_number" class="text-red-500 text-sm">{{ errors.reference_number[0] }}</span>
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
            <select
              v-model="form.payment_method"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Payment Method</option>
              <option value="cash">Cash</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="credit_card">Credit Card</option>
              <option value="check">Check</option>
              <option value="petty_cash">Petty Cash</option>
            </select>
            <span v-if="errors.payment_method" class="text-red-500 text-sm">{{ errors.payment_method[0] }}</span>
          </div>

          <!-- Description -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter expense description"
            ></textarea>
            <span v-if="errors.description" class="text-red-500 text-sm">{{ errors.description[0] }}</span>
          </div>

          <!-- Receipt Images -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Images</label>
            <input
              ref="fileInput"
              type="file"
              multiple
              accept="image/*"
              @change="handleFileUpload"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p class="text-sm text-gray-500 mt-1">Upload receipt images (JPEG, PNG, GIF, max 2MB each)</p>
            <span v-if="errors.receipt_images" class="text-red-500 text-sm">{{ errors.receipt_images[0] }}</span>
          </div>

          <!-- Notes -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Additional notes"
            ></textarea>
            <span v-if="errors.notes" class="text-red-500 text-sm">{{ errors.notes[0] }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
          </button>
          <button
            v-if="!isEditing"
            type="button"
            @click="saveAndSubmit"
            :disabled="saving"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : 'Create & Submit' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Props and Emits
const props = defineProps({
  expense: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

// Reactive data
const form = ref({
  title: '',
  category_id: '',
  employee_id: '',
  amount: '',
  expense_date: new Date().toISOString().split('T')[0],
  vendor_name: '',
  reference_number: '',
  payment_method: '',
  description: '',
  notes: ''
});

const categories = ref([]);
const employees = ref([]);
const errors = ref({});
const saving = ref(false);
const selectedFiles = ref([]);

// Computed
const isEditing = computed(() => !!props.expense);

// Methods
const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/expense-categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const fetchEmployees = async () => {
  try {
    const response = await axios.get('/api/employees');
    employees.value = response.data;
  } catch (error) {
    console.error('Error fetching employees:', error);
  }
};

const handleFileUpload = (event) => {
  selectedFiles.value = Array.from(event.target.files);
};

const saveExpense = async (submitAfterSave = false) => {
  saving.value = true;
  errors.value = {};

  try {
    const formData = new FormData();
    
    // Append form data
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key]);
      }
    });

    // Append files
    selectedFiles.value.forEach((file, index) => {
      formData.append(`receipt_images[${index}]`, file);
    });

    let response;
    if (isEditing.value) {
      formData.append('_method', 'PUT');
      response = await axios.post(`/api/expenses/${props.expense.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      response = await axios.post('/api/expenses', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }

    if (submitAfterSave && !isEditing.value) {
      await axios.post(`/api/expenses/${response.data.expense.id}/submit`);
    }

    emit('saved');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error saving expense:', error);
    }
  } finally {
    saving.value = false;
  }
};

const saveAndSubmit = () => {
  saveExpense(true);
};

// Initialize form if editing
const initializeForm = () => {
  if (props.expense) {
    Object.keys(form.value).forEach(key => {
      if (props.expense[key] !== undefined) {
        form.value[key] = props.expense[key];
      }
    });
  }
};

// Lifecycle
onMounted(() => {
  fetchCategories();
  fetchEmployees();
  initializeForm();
});
</script>
