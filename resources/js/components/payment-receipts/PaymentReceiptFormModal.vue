<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200"
    style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
  >
    <!-- Modal Card Container (White and Black High-Contrast Modern System Style) -->
    <div class="relative mx-auto border border-slate-200/90 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-3xl bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 p-6 sm:p-7 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto custom-scrollbar my-auto">
      
      <!-- Modal Header -->
      <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-zinc-800">
        <div>
          <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">
            {{ isEditing ? 'Edit Payment Receipt' : 'Create New Payment Receipt' }}
          </h3>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
            Fill in transaction details for incoming payment receipt record
          </p>
        </div>

        <button
          type="button"
          @click="$emit('close')"
          class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all cursor-pointer"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="submitForm" class="mt-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          
          <!-- Receipt Type Floating Dropdown -->
          <div class="md:col-span-2">
            <FloatingSelect
              v-model="form.receipt_type"
              label="Receipt Type"
              placeholder="Select Receipt Type"
              :options="formattedReceiptTypes"
              :required="true"
              :error="errors.receipt_type ? errors.receipt_type[0] : ''"
              @change="onReceiptTypeChange"
            />
          </div>

          <!-- Amount Input -->
          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Amount <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 pointer-events-none">$</span>
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0.01"
                required
                class="w-full pl-8 pr-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
                placeholder="0.00"
              />
            </div>
            <span v-if="errors.amount" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.amount[0] }}</span>
          </div>

          <!-- Receipt Date Input -->
          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Receipt Date <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.receipt_date"
              type="date"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs cursor-pointer"
            />
            <span v-if="errors.receipt_date" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.receipt_date[0] }}</span>
          </div>

          <!-- Payment Method Floating Dropdown -->
          <div>
            <FloatingSelect
              v-model="form.payment_method"
              label="Payment Method"
              placeholder="Select Payment Method"
              :options="formattedPaymentMethods"
              :required="true"
              :error="errors.payment_method ? errors.payment_method[0] : ''"
              @change="onPaymentMethodChange"
            />
          </div>

          <!-- Bank Account Floating Dropdown -->
          <div>
            <FloatingSelect
              v-model="form.bank_account_id"
              label="Bank Account"
              placeholder="Select Bank Account"
              :options="formattedBankAccounts"
              :required="true"
              :error="errors.bank_account_id ? errors.bank_account_id[0] : ''"
            />
          </div>

          <!-- Payer Type Floating Dropdown -->
          <div>
            <FloatingSelect
              v-model="form.payer_type"
              label="Payer Type"
              placeholder="Select Payer Type"
              :options="payerTypeOptions"
              :error="errors.payer_type ? errors.payer_type[0] : ''"
              @change="onPayerTypeChange"
            />
          </div>

          <!-- Payer Selection Floating Dropdown -->
          <div v-if="form.payer_type && form.payer_type !== 'other'">
            <FloatingSelect
              v-model="form.payer_id"
              :label="getPayerLabel()"
              :placeholder="'Select ' + getPayerLabel()"
              :options="formattedPayerOptions"
              :error="errors.payer_id ? errors.payer_id[0] : ''"
              @change="onPayerChange"
            />
          </div>

          <!-- Payer Name Input -->
          <div :class="form.payer_type && form.payer_type !== 'other' ? '' : 'md:col-span-2'">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Payer Name <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.payer_name"
              type="text"
              required
              :readonly="form.payer_type && form.payer_type !== 'other' && form.payer_id"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              :class="{ 'opacity-70 cursor-not-allowed bg-slate-100 dark:bg-zinc-900': form.payer_type && form.payer_type !== 'other' && form.payer_id }"
              placeholder="Enter payer name"
            />
            <span v-if="errors.payer_name" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.payer_name[0] }}</span>
          </div>

          <!-- Transaction Reference -->
          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Transaction Reference
            </label>
            <input
              v-model="form.transaction_reference"
              type="text"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="Bank ref, check number, etc."
            />
            <span v-if="errors.transaction_reference" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.transaction_reference[0] }}</span>
          </div>

          <!-- Reference Number -->
          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Reference Number
            </label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="External reference number"
            />
            <span v-if="errors.reference_number" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.reference_number[0] }}</span>
          </div>

          <!-- Status Floating Dropdown (Editing Mode) -->
          <div v-if="isEditing">
            <FloatingSelect
              v-model="form.status"
              label="Status"
              placeholder="Select Status"
              :options="statusOptions"
              :error="errors.status ? errors.status[0] : ''"
            />
          </div>

          <!-- Description Textarea -->
          <div class="md:col-span-2">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Description <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="form.description"
              required
              rows="3"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="Enter receipt description"
            ></textarea>
            <span v-if="errors.description" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.description[0] }}</span>
          </div>

          <!-- Notes Textarea -->
          <div class="md:col-span-2">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Notes
            </label>
            <textarea
              v-model="form.notes"
              rows="2"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="Enter additional notes (optional)"
            ></textarea>
            <span v-if="errors.notes" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.notes[0] }}</span>
          </div>
        </div>

        <!-- Invoice Allocation Section (for customer payments) -->
        <div v-if="form.receipt_type === 'customer_payment' && form.payer_id" class="mt-6">
          <div class="border-t border-slate-100 dark:border-zinc-800 pt-5">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-3">Invoice Allocation</h4>
            <div v-if="customerInvoices.length > 0" class="space-y-2.5">
              <div v-for="invoice in customerInvoices" :key="invoice.id" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-zinc-800/50 border border-slate-200 dark:border-zinc-700/80 rounded-xl">
                <div class="flex-1">
                  <div class="text-xs font-bold text-slate-900 dark:text-white">{{ invoice.sale_number }}</div>
                  <div class="text-[11px] text-slate-500 dark:text-zinc-400">{{ formatDate(invoice.sale_date) }}</div>
                  <div class="text-[11px] font-semibold text-slate-700 dark:text-zinc-300">Outstanding: ${{ formatAmount(invoice.outstanding_amount) }}</div>
                </div>
                <div class="w-36">
                  <input
                    v-model="invoiceAllocations[invoice.id]"
                    type="number"
                    step="0.01"
                    min="0"
                    :max="invoice.outstanding_amount"
                    class="w-full bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                    placeholder="0.00"
                  />
                </div>
              </div>
              <div class="text-xs font-semibold text-slate-600 dark:text-zinc-400 pt-1">
                Total Allocated: ${{ formatAmount(getTotalAllocated()) }} / ${{ formatAmount(form.amount || 0) }}
              </div>
            </div>
            <div v-else class="text-xs text-slate-500 dark:text-zinc-400">
              No outstanding invoices found for this customer.
            </div>
          </div>
        </div>

        <!-- Form Action Buttons -->
        <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-5 py-2.5 bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-bold rounded-xl text-xs shadow-sm transition-all disabled:opacity-50 inline-flex items-center gap-2 cursor-pointer"
          >
            <div v-if="loading" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-current"></div>
            <span>{{ isEditing ? 'Update Receipt' : 'Create Receipt' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import FloatingSelect from '@/components/common/FloatingSelect.vue';

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

const formattedReceiptTypes = computed(() => {
  return receiptTypes.value.map(t => ({
    value: t.value,
    label: t.label
  }));
});

const isCashAccount = (acc) => {
  if (!acc) return false;
  const type = String(acc.account_type || '').toLowerCase();
  const name = String(acc.account_name || '').toLowerCase();
  const bank = String(acc.bank_name || '').toLowerCase();
  const num = String(acc.account_number || '').toLowerCase();
  return type === 'cash' || bank === 'cash' || name.includes('cash') || num.startsWith('cash');
};

const isCardAccount = (acc) => {
  if (!acc) return false;
  const type = String(acc.account_type || '').toLowerCase();
  const name = String(acc.account_name || '').toLowerCase();
  const bank = String(acc.bank_name || '').toLowerCase();
  return type === 'credit_card' || type === 'card' || type === 'debit_card' || name.includes('card') || bank.includes('card');
};

const formattedBankAccounts = computed(() => {
  const method = form.payment_method;
  let filtered = bankAccounts.value;

  if (method === 'cash') {
    filtered = bankAccounts.value.filter(acc => isCashAccount(acc));
  } else if (method === 'bank_transfer') {
    filtered = bankAccounts.value.filter(acc => !isCashAccount(acc));
  } else if (method === 'card') {
    filtered = bankAccounts.value.filter(acc => isCardAccount(acc));
  } else if (method === 'check' || method === 'cheque') {
    filtered = bankAccounts.value.filter(acc => !isCashAccount(acc));
  }

  return filtered.map(acc => ({
    value: acc.id,
    label: acc.bank_name ? `${acc.bank_name} (${acc.account_name})` : acc.account_name,
    sublabel: acc.account_number ? (acc.masked_account_number || ('****' + String(acc.account_number).slice(-4))) : ''
  }));
});

const onPaymentMethodChange = () => {
  const available = formattedBankAccounts.value;
  const exists = available.some(acc => String(acc.value) === String(form.bank_account_id));
  if (!exists) {
    form.bank_account_id = available.length > 0 ? available[0].value : '';
  }
};

const formattedPaymentMethods = computed(() => {
  return paymentMethods.value.map(m => ({
    value: m.value,
    label: m.label
  }));
});

const payerTypeOptions = [
  { value: 'customer', label: 'Customer' },
  { value: 'supplier', label: 'Supplier' },
  { value: 'other', label: 'Other' },
];

const formattedPayerOptions = computed(() => {
  return getPayerOptions().map(p => ({
    value: p.id,
    label: p.name
  }));
});

const statusOptions = [
  { value: 'draft', label: 'Draft' },
  { value: 'pending', label: 'Pending' },
  { value: 'verified', label: 'Verified' },
];

// Methods
const loadReceiptOptions = async () => {
  try {
    const response = await axios.get('/api/payment-receipt-options');

    receiptOptions.value = {
      bankAccounts: response.data.bank_accounts || [],
      customers: response.data.customers || [],
      suppliers: response.data.suppliers || [],
      receiptTypes: response.data.receipt_types || [],
      paymentMethods: response.data.payment_methods || [],
    };
  } catch (error) {
    console.error('Error loading receipt options:', error);
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
  form.payer_type = '';
  form.payer_id = '';
  form.payer_name = '';
  customerInvoices.value = [];
  invoiceAllocations.value = {};

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
    const formData = { ...form };

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

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
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
