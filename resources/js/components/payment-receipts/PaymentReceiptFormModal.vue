<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">
          {{ isEditing ? 'Edit Payment Receipt' : 'Create New Payment Receipt' }}
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
          <!-- Receipt Type -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Type *</label>
            <select
              v-model="form.receipt_type"
              @change="onReceiptTypeChange"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select Receipt Type</option>
              <option v-for="type in receiptTypes" :key="type.value" :value="type.value">
                {{ type.label }}
              </option>
            </select>
            <span v-if="errors.receipt_type" class="text-red-500 text-sm">{{ errors.receipt_type[0] }}</span>
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

          <!-- Receipt Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Date *</label>
            <input
              v-model="form.receipt_date"
              type="date"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
            <span v-if="errors.receipt_date" class="text-red-500 text-sm">{{ errors.receipt_date[0] }}</span>
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

          <!-- Payer Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payer Type</label>
            <select
              v-model="form.payer_type"
              @change="onPayerTypeChange"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select Payer Type</option>
              <option value="customer">Customer</option>
              <option value="supplier">Supplier</option>
              <option value="other">Other</option>
            </select>
            <span v-if="errors.payer_type" class="text-red-500 text-sm">{{ errors.payer_type[0] }}</span>
          </div>

          <!-- Payer Selection -->
          <div v-if="form.payer_type && form.payer_type !== 'other'">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ getPayerLabel() }}</label>
            <select
              v-model="form.payer_id"
              @change="onPayerChange"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select {{ getPayerLabel() }}</option>
              <option v-for="payer in getPayerOptions()" :key="payer.id" :value="payer.id">
                {{ payer.name }}
              </option>
            </select>
            <span v-if="errors.payer_id" class="text-red-500 text-sm">{{ errors.payer_id[0] }}</span>
          </div>

          <!-- Payer Name -->
          <div :class="form.payer_type && form.payer_type !== 'other' ? '' : 'md:col-span-2'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payer Name *</label>
            <input
              v-model="form.payer_name"
              type="text"
              required
              :readonly="form.payer_type && form.payer_type !== 'other' && form.payer_id"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter payer name"
            />
            <span v-if="errors.payer_name" class="text-red-500 text-sm">{{ errors.payer_name[0] }}</span>
          </div>

          <!-- Transaction Reference -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference</label>
            <input
              v-model="form.transaction_reference"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Bank ref, check number, etc."
            />
            <span v-if="errors.transaction_reference" class="text-red-500 text-sm">{{ errors.transaction_reference[0] }}</span>
          </div>

          <!-- Reference Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="External reference number"
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
              <option value="verified">Verified</option>
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
              placeholder="Enter receipt description"
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

        <!-- Invoice Allocation Section (for customer payments) -->
        <div v-if="form.receipt_type === 'customer_payment' && form.payer_id" class="mt-6">
          <div class="border-t border-gray-200 pt-6">
            <h4 class="text-md font-medium text-gray-900 mb-4">Invoice Allocation</h4>
            <div v-if="customerInvoices.length > 0" class="space-y-3">
              <div v-for="invoice in customerInvoices" :key="invoice.id" class="flex items-center justify-between p-3 border border-gray-200 rounded-md">
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900">{{ invoice.sale_number }}</div>
                  <div class="text-sm text-gray-600">{{ formatDate(invoice.sale_date) }}</div>
                  <div class="text-sm text-gray-600">Outstanding: ${{ formatAmount(invoice.outstanding_amount) }}</div>
                </div>
                <div class="w-32">
                  <input
                    v-model="invoiceAllocations[invoice.id]"
                    type="number"
                    step="0.01"
                    min="0"
                    :max="invoice.outstanding_amount"
                    class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="0.00"
                  />
                </div>
              </div>
              <div class="text-sm text-gray-600">
                Total Allocated: ${{ formatAmount(getTotalAllocated()) }} / ${{ formatAmount(form.amount || 0) }}
              </div>
            </div>
            <div v-else class="text-sm text-gray-500">
              No outstanding invoices found for this customer.
            </div>
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
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
          >
            <span v-if="loading" class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              {{ isEditing ? 'Updating...' : 'Creating...' }}
            </span>
            <span v-else>
              {{ isEditing ? 'Update Receipt' : 'Create Receipt' }}
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
  receipt: {
    type: Object,
    default: null
  }
});

// Emits
const emit = defineEmits(['close', 'saved']);

// Reactive data
const loading = ref(false);
const errors = ref({});
const receiptOptions = ref({
  bankAccounts: [],
  customers: [],
  suppliers: [],
  receiptTypes: [],
  paymentMethods: [],
});
const customerInvoices = ref([]);
const invoiceAllocations = ref({});

// Form data
const form = reactive({
  receipt_type: '',
  amount: '',
  receipt_date: new Date().toISOString().split('T')[0],
  payment_method: 'bank_transfer',
  transaction_reference: '',
  reference_number: '',
  description: '',
  notes: '',
  bank_account_id: '',
  payer_type: '',
  payer_id: '',
  payer_name: '',
  status: 'draft',
});

// Computed
const isEditing = computed(() => !!props.receipt);

const receiptTypes = computed(() => receiptOptions.value.receiptTypes);
const paymentMethods = computed(() => receiptOptions.value.paymentMethods);
const bankAccounts = computed(() => receiptOptions.value.bankAccounts);

// Methods
const loadReceiptOptions = async () => {
  try {
    const response = await axios.get('/api/payment-receipt-options');

    // Map the API response to match our component structure
    receiptOptions.value = {
      bankAccounts: response.data.bank_accounts || [],
      customers: response.data.customers || [],
      suppliers: response.data.suppliers || [],
      receiptTypes: response.data.receipt_types || [],
      paymentMethods: response.data.payment_methods || [],
    };
  } catch (error) {
    console.error('Error loading receipt options:', error);
    // Set fallback data
    receiptOptions.value = {
      bankAccounts: [],
      customers: [],
      suppliers: [],
      receiptTypes: [
        { value: 'customer_payment', label: 'Customer Payment' },
        { value: 'customer_advance', label: 'Customer Advance' },
        { value: 'supplier_refund', label: 'Supplier Refund' },
        { value: 'supplier_rebate', label: 'Supplier Rebate' },
        { value: 'interest_income', label: 'Interest Income' },
        { value: 'rental_income', label: 'Rental Income' },
        { value: 'commission_income', label: 'Commission Income' },
        { value: 'asset_sale', label: 'Asset Sale' },
        { value: 'bank_transfer_in', label: 'Bank Transfer In' },
        { value: 'cash_deposit', label: 'Cash Deposit' },
        { value: 'miscellaneous_income', label: 'Miscellaneous Income' },
        { value: 'other_receipt', label: 'Other Receipt' },
      ],
      paymentMethods: [
        { value: 'cash', label: 'Cash' },
        { value: 'bank_transfer', label: 'Bank Transfer' },
        { value: 'check', label: 'Check' },
        { value: 'card', label: 'Card' },
        { value: 'online', label: 'Online Payment' },
      ],
    };
  }
};

const onReceiptTypeChange = () => {
  // Reset payer fields when receipt type changes
  form.payer_type = '';
  form.payer_id = '';
  form.payer_name = '';
  customerInvoices.value = [];
  invoiceAllocations.value = {};

  // Set default payer type based on receipt type
  if (form.receipt_type === 'customer_payment' || form.receipt_type === 'customer_advance') {
    form.payer_type = 'customer';
  } else if (form.receipt_type === 'supplier_refund' || form.receipt_type === 'supplier_rebate') {
    form.payer_type = 'supplier';
  }
};

const onPayerTypeChange = () => {
  form.payer_id = '';
  form.payer_name = '';
  customerInvoices.value = [];
  invoiceAllocations.value = {};
};

const onPayerChange = async () => {
  if (form.payer_id) {
    const payerOptions = getPayerOptions();
    const selectedPayer = payerOptions.find(p => p.id == form.payer_id);
    if (selectedPayer) {
      form.payer_name = selectedPayer.name;
    }

    // Load customer invoices if this is a customer payment
    if (form.receipt_type === 'customer_payment' && form.payer_type === 'customer') {
      await loadCustomerInvoices();
    }
  } else {
    form.payer_name = '';
    customerInvoices.value = [];
    invoiceAllocations.value = {};
  }
};

const loadCustomerInvoices = async () => {
  if (!form.payer_id) return;

  try {
    const response = await axios.get('/api/customer-invoices', {
      params: { customer_id: form.payer_id }
    });
    customerInvoices.value = response.data.invoices || [];

    // Initialize allocations
    invoiceAllocations.value = {};
    customerInvoices.value.forEach(invoice => {
      invoiceAllocations.value[invoice.id] = 0;
    });
  } catch (error) {
    console.error('Error loading customer invoices:', error);
    customerInvoices.value = [];
  }
};

const getPayerLabel = () => {
  const labels = {
    customer: 'Customer',
    supplier: 'Supplier',
  };
  return labels[form.payer_type] || 'Payer';
};

const getPayerOptions = () => {
  const options = {
    customer: receiptOptions.value.customers,
    supplier: receiptOptions.value.suppliers,
  };
  return options[form.payer_type] || [];
};

const getTotalAllocated = () => {
  return Object.values(invoiceAllocations.value).reduce((sum, amount) => sum + parseFloat(amount || 0), 0);
};

const resetForm = () => {
  Object.assign(form, {
    receipt_type: '',
    amount: '',
    receipt_date: new Date().toISOString().split('T')[0],
    payment_method: 'bank_transfer',
    transaction_reference: '',
    reference_number: '',
    description: '',
    notes: '',
    bank_account_id: '',
    payer_type: '',
    payer_id: '',
    payer_name: '',
    status: 'draft',
  });
  errors.value = {};
  customerInvoices.value = [];
  invoiceAllocations.value = {};
};

const populateForm = () => {
  if (props.receipt) {
    Object.assign(form, {
      receipt_type: props.receipt.receipt_type || '',
      amount: props.receipt.amount || '',
      receipt_date: props.receipt.receipt_date || '',
      payment_method: props.receipt.payment_method || 'bank_transfer',
      transaction_reference: props.receipt.transaction_reference || '',
      reference_number: props.receipt.reference_number || '',
      description: props.receipt.description || '',
      notes: props.receipt.notes || '',
      bank_account_id: props.receipt.bank_account_id || '',
      payer_type: props.receipt.payer_type || '',
      payer_id: props.receipt.payer_id || '',
      payer_name: props.receipt.payer_name || '',
      status: props.receipt.status || 'draft',
    });

    // Load invoice allocations if editing customer payment
    if (props.receipt.invoice_allocations) {
      invoiceAllocations.value = {};
      props.receipt.invoice_allocations.forEach(allocation => {
        invoiceAllocations.value[allocation.invoice_id] = allocation.amount;
      });
    }
  }
};

const submitForm = async () => {
  loading.value = true;
  errors.value = {};

  try {
    // Prepare form data
    const formData = { ...form };

    // Add invoice allocations for customer payments
    if (form.receipt_type === 'customer_payment' && Object.keys(invoiceAllocations.value).length > 0) {
      formData.invoice_allocations = Object.entries(invoiceAllocations.value)
        .filter(([invoiceId, amount]) => parseFloat(amount) > 0)
        .map(([invoiceId, amount]) => ({
          invoice_id: parseInt(invoiceId),
          amount: parseFloat(amount)
        }));
    }

    const url = isEditing.value ? `/api/payment-receipts/${props.receipt.id}` : '/api/payment-receipts';
    const method = isEditing.value ? 'put' : 'post';

    const response = await axios[method](url, formData);

    emit('saved', response.data.receipt);
    resetForm();
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      console.error('Error saving payment receipt:', error);
      alert('Failed to save payment receipt. Please try again.');
    }
  } finally {
    loading.value = false;
  }
};

// Utility functions
const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

// Watchers
watch(() => props.show, (newValue) => {
  if (newValue) {
    loadReceiptOptions();

    if (isEditing.value) {
      populateForm();
      // Load customer invoices if editing customer payment
      if (props.receipt.receipt_type === 'customer_payment' && props.receipt.payer_id) {
        loadCustomerInvoices();
      }
    } else {
      resetForm();
    }
  }
});

// Initialize
onMounted(() => {
  loadReceiptOptions();
});
</script>
