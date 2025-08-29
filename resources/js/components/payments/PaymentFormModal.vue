<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">
          {{ isEditing ? 'Edit Payment' : 'Create New Payment' }}
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Payment Type -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type *</label>
            <select
              v-model="form.payment_type"
              @change="onPaymentTypeChange"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select Payment Type</option>
              <option v-for="type in paymentTypes" :key="type.value" :value="type.value">
                {{ type.label }}
              </option>
            </select>
            <span v-if="errors.payment_type" class="text-red-500 text-sm">{{ errors.payment_type[0] }}</span>
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
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="0.00"
            />
            <span v-if="errors.amount" class="text-red-500 text-sm">{{ errors.amount[0] }}</span>
          </div>

          <!-- Payment Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
            <input
              v-model="form.payment_date"
              type="date"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
            <span v-if="errors.payment_date" class="text-red-500 text-sm">{{ errors.payment_date[0] }}</span>
          </div>

          <!-- Bank Account -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
            <select
              v-model="form.bank_account_id"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select Bank Account</option>
              <option v-for="account in bankAccounts" :key="account.id" :value="account.id">
                {{ account.account_name }} - {{ account.bank_name }} ({{ account.account_number }})
              </option>
            </select>
            <span v-if="errors.bank_account_id" class="text-red-500 text-sm">{{ errors.bank_account_id[0] }}</span>
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
            <select
              v-model="form.payment_method"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select Payment Method</option>
              <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                {{ method.label }}
              </option>
            </select>
            <span v-if="errors.payment_method" class="text-red-500 text-sm">{{ errors.payment_method[0] }}</span>
          </div>

          <!-- Payee Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payee Type</label>
            <select
              v-model="form.payee_type"
              @change="onPayeeTypeChange"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select Payee Type</option>
              <option value="supplier">Supplier</option>
              <option value="employee">Employee</option>
              <option value="customer">Customer</option>
              <option value="other">Other</option>
            </select>
            <span v-if="errors.payee_type" class="text-red-500 text-sm">{{ errors.payee_type[0] }}</span>
          </div>

          <!-- Payee Selection -->
          <div v-if="form.payee_type && form.payee_type !== 'other'">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ getPayeeLabel() }}</label>
            <select
              v-model="form.payee_id"
              @change="onPayeeChange"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select {{ getPayeeLabel() }}</option>
              <option v-for="payee in getPayeeOptions()" :key="payee.id" :value="payee.id">
                {{ payee.name }}
              </option>
            </select>
            <span v-if="errors.payee_id" class="text-red-500 text-sm">{{ errors.payee_id[0] }}</span>
          </div>

          <!-- Payee Name -->
          <div :class="form.payee_type && form.payee_type !== 'other' ? '' : 'md:col-span-2'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payee Name *</label>
            <input
              v-model="form.payee_name"
              type="text"
              required
              :readonly="form.payee_type && form.payee_type !== 'other' && form.payee_id"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter payee name"
            />
            <span v-if="errors.payee_name" class="text-red-500 text-sm">{{ errors.payee_name[0] }}</span>
          </div>

          <!-- Reference Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter reference number"
            />
            <span v-if="errors.reference_number" class="text-red-500 text-sm">{{ errors.reference_number[0] }}</span>
          </div>

          <!-- Status -->
          <div v-if="isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="draft">Draft</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
            </select>
            <span v-if="errors.status" class="text-red-500 text-sm">{{ errors.status[0] }}</span>
          </div>

          <!-- Description -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <textarea
              v-model="form.description"
              required
              rows="3"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter payment description"
            ></textarea>
            <span v-if="errors.description" class="text-red-500 text-sm">{{ errors.description[0] }}</span>
          </div>

          <!-- Notes -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="2"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter additional notes (optional)"
            ></textarea>
            <span v-if="errors.notes" class="text-red-500 text-sm">{{ errors.notes[0] }}</span>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="loading" class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              {{ isEditing ? 'Updating...' : 'Creating...' }}
            </span>
            <span v-else>
              {{ isEditing ? 'Update Payment' : 'Create Payment' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    default: null
  }
});

// Emits
const emit = defineEmits(['close', 'saved']);

// Reactive data
const loading = ref(false);
const errors = ref({});
const paymentOptions = ref({
  bankAccounts: [],
  suppliers: [],
  employees: [],
  customers: [],
  paymentTypes: [],
  paymentMethods: [],
});

// Form data
const form = reactive({
  payment_type: '',
  amount: '',
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: 'bank_transfer',
  reference_number: '',
  description: '',
  notes: '',
  bank_account_id: '',
  payee_type: '',
  payee_id: '',
  payee_name: '',
  status: 'draft',
});

// Computed
const isEditing = computed(() => !!props.payment);

const paymentTypes = computed(() => paymentOptions.value.paymentTypes);
const paymentMethods = computed(() => paymentOptions.value.paymentMethods);
const bankAccounts = computed(() => paymentOptions.value.bankAccounts);

// Methods
const loadPaymentOptions = async () => {
  try {
    const response = await axios.get('/api/payment-options');

    // Map the API response to match our component structure
    paymentOptions.value = {
      bankAccounts: response.data.bank_accounts || [],
      suppliers: response.data.suppliers || [],
      employees: response.data.employees || [],
      customers: response.data.customers || [],
      paymentTypes: response.data.payment_types || [],
      paymentMethods: response.data.payment_methods || [],
      statuses: response.data.statuses || [],
    };
  } catch (error) {
    console.error('Error loading payment options:', error);
    // Set empty arrays as fallback
    paymentOptions.value = {
      bankAccounts: [],
      suppliers: [],
      employees: [],
      customers: [],
      paymentTypes: [
        { value: 'supplier_payment', label: 'Supplier Payment' },
        { value: 'expense_payment', label: 'Expense Payment' },
        { value: 'salary_payment', label: 'Salary Payment' },
        { value: 'sale_return_payment', label: 'Sale Return Payment' },
        { value: 'purchase_invoice_payment', label: 'Purchase Invoice Payment' },
        { value: 'other_payment', label: 'Other Payment' },
      ],
      paymentMethods: [
        { value: 'cash', label: 'Cash' },
        { value: 'bank_transfer', label: 'Bank Transfer' },
        { value: 'check', label: 'Check' },
        { value: 'card', label: 'Card' },
      ],
      statuses: [
        { value: 'draft', label: 'Draft' },
        { value: 'pending', label: 'Pending' },
        { value: 'approved', label: 'Approved' },
        { value: 'paid', label: 'Paid' },
        { value: 'cancelled', label: 'Cancelled' },
      ],
    };
  }
};

const onPaymentTypeChange = () => {
  // Reset payee fields when payment type changes
  form.payee_type = '';
  form.payee_id = '';
  form.payee_name = '';

  // Set default payee type based on payment type
  if (form.payment_type === 'supplier_payment' || form.payment_type === 'purchase_invoice_payment') {
    form.payee_type = 'supplier';
  } else if (form.payment_type === 'salary_payment') {
    form.payee_type = 'employee';
  } else if (form.payment_type === 'sale_return_payment') {
    form.payee_type = 'customer';
  }
};

const onPayeeTypeChange = () => {
  form.payee_id = '';
  form.payee_name = '';
};

const onPayeeChange = () => {
  if (form.payee_id) {
    const payeeOptions = getPayeeOptions();
    const selectedPayee = payeeOptions.find(p => p.id == form.payee_id);
    if (selectedPayee) {
      form.payee_name = selectedPayee.name;
    }
  } else {
    form.payee_name = '';
  }
};

const getPayeeLabel = () => {
  const labels = {
    supplier: 'Supplier',
    employee: 'Employee',
    customer: 'Customer',
  };
  return labels[form.payee_type] || 'Payee';
};

const getPayeeOptions = () => {
  const options = {
    supplier: paymentOptions.value.suppliers,
    employee: paymentOptions.value.employees,
    customer: paymentOptions.value.customers,
  };
  return options[form.payee_type] || [];
};

const resetForm = () => {
  Object.assign(form, {
    payment_type: '',
    amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'bank_transfer',
    reference_number: '',
    description: '',
    notes: '',
    bank_account_id: '',
    payee_type: '',
    payee_id: '',
    payee_name: '',
    status: 'draft',
  });
  errors.value = {};
};

const populateForm = () => {
  if (props.payment) {
    Object.assign(form, {
      payment_type: props.payment.payment_type || '',
      amount: props.payment.amount || '',
      payment_date: props.payment.payment_date || '',
      payment_method: props.payment.payment_method || 'bank_transfer',
      reference_number: props.payment.reference_number || '',
      description: props.payment.description || '',
      notes: props.payment.notes || '',
      bank_account_id: props.payment.bank_account_id || '',
      payee_type: props.payment.payee_type || '',
      payee_id: props.payment.payee_id || '',
      payee_name: props.payment.payee_name || '',
      status: props.payment.status || 'draft',
    });
  }
};

const submitForm = async () => {
  loading.value = true;
  errors.value = {};

  try {
    const url = isEditing.value ? `/api/payments/${props.payment.id}` : '/api/payments';
    const method = isEditing.value ? 'put' : 'post';

    const response = await axios[method](url, form);

    emit('saved', response.data.payment);
    resetForm();
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      console.error('Error saving payment:', error);
      alert('Failed to save payment. Please try again.');
    }
  } finally {
    loading.value = false;
  }
};

// Watchers
watch(() => props.show, (newValue) => {
  if (newValue) {
    // Load payment options when modal is shown
    loadPaymentOptions();

    if (isEditing.value) {
      populateForm();
    } else {
      resetForm();
      // Pre-fill from URL parameters if creating new payment
      prefillFromUrlParams();
    }
  }
});

// Initialize
onMounted(() => {
  loadPaymentOptions();
});

// Pre-fill form from URL parameters
const prefillFromUrlParams = () => {
  const urlParams = new URLSearchParams(window.location.search);

  if (urlParams.get('type')) {
    form.payment_type = urlParams.get('type');
    onPaymentTypeChange();
  }

  if (urlParams.get('amount')) {
    form.amount = urlParams.get('amount');
  }

  if (urlParams.get('payee_name')) {
    form.payee_name = decodeURIComponent(urlParams.get('payee_name'));
  }

  if (urlParams.get('description')) {
    form.description = decodeURIComponent(urlParams.get('description'));
  }

  if (urlParams.get('reference_id')) {
    form.reference_id = urlParams.get('reference_id');
    form.reference_type = 'App\\Models\\Expense'; // Default for expense payments
  }
};

// Initialize
onMounted(() => {
  loadPaymentOptions();
});
</script>
