<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200"
    style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
  >
    <!-- Modal Container Card (White and Black High-Contrast Modern System Style) -->
    <div class="relative mx-auto border border-slate-200/90 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-3xl bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 p-6 sm:p-7 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto custom-scrollbar my-auto">
      
      <!-- Modal Header -->
      <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-zinc-800">
        <div>
          <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">
            {{ isEditing ? 'Edit Payment' : 'Create New Payment' }}
          </h3>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
            Fill in transaction details for outgoing payment record
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
          
          <!-- Payment Type Floating Dropdown -->
          <div class="md:col-span-2">
            <FloatingSelect
              v-model="form.payment_type"
              label="Payment Type"
              placeholder="Select Payment Type"
              :options="formattedPaymentTypes"
              :required="true"
              :error="errors.payment_type ? errors.payment_type[0] : ''"
              @change="onPaymentTypeChange"
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

          <!-- Payment Date Input -->
          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Payment Date <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.payment_date"
              type="date"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs cursor-pointer"
            />
            <span v-if="errors.payment_date" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.payment_date[0] }}</span>
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

          <!-- Payment Method Floating Dropdown -->
          <div>
            <FloatingSelect
              v-model="form.payment_method"
              label="Payment Method"
              placeholder="Select Payment Method"
              :options="formattedPaymentMethods"
              :required="true"
              :error="errors.payment_method ? errors.payment_method[0] : ''"
            />
          </div>

          <!-- Payee Type Floating Dropdown -->
          <div>
            <FloatingSelect
              v-model="form.payee_type"
              label="Payee Type"
              placeholder="Select Payee Type"
              :options="payeeTypeOptions"
              :error="errors.payee_type ? errors.payee_type[0] : ''"
              @change="onPayeeTypeChange"
            />
          </div>

          <!-- Payee Selection Floating Dropdown -->
          <div v-if="form.payee_type && form.payee_type !== 'other'">
            <FloatingSelect
              v-model="form.payee_id"
              :label="getPayeeLabel()"
              :placeholder="'Select ' + getPayeeLabel()"
              :options="formattedPayeeOptions"
              :error="errors.payee_id ? errors.payee_id[0] : ''"
              @change="onPayeeChange"
            />
          </div>

          <!-- Payee Name Input -->
          <div :class="form.payee_type && form.payee_type !== 'other' ? '' : 'md:col-span-2'">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Payee Name <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.payee_name"
              type="text"
              required
              :readonly="form.payee_type && form.payee_type !== 'other' && form.payee_id"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              :class="{ 'opacity-70 cursor-not-allowed bg-slate-100 dark:bg-zinc-900': form.payee_type && form.payee_type !== 'other' && form.payee_id }"
              placeholder="Enter payee name"
            />
            <span v-if="errors.payee_name" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.payee_name[0] }}</span>
          </div>

          <!-- Reference Number Input -->
          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Reference Number
            </label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="Enter reference number"
            />
            <span v-if="errors.reference_number" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.reference_number[0] }}</span>
          </div>

          <!-- Status Floating Dropdown -->
          <div>
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
              placeholder="Enter payment description"
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

        <!-- Form Action Buttons (High Contrast Black Theme) -->
        <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          >
            Cancel
          </button>

          <button
            type="button"
            @click="saveAsDraft"
            :disabled="loading"
            class="px-4 py-2.5 border border-slate-300 dark:border-zinc-700 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-800 dark:text-zinc-200 font-bold rounded-xl text-xs shadow-xs transition-all disabled:opacity-50 inline-flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            <span>Save as Draft</span>
          </button>

          <button
            type="submit"
            :disabled="loading"
            class="px-5 py-2.5 bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-bold rounded-xl text-xs shadow-sm transition-all disabled:opacity-50 inline-flex items-center gap-2 cursor-pointer"
          >
            <div v-if="loading" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-current"></div>
            <span>{{ isEditing ? 'Update Payment' : 'Create Payment' }}</span>
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
  statuses: [],
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

const formattedPaymentTypes = computed(() => {
  return paymentTypes.value.map(t => ({
    value: t.value,
    label: t.label
  }));
});

const formattedBankAccounts = computed(() => {
  return bankAccounts.value.map(acc => ({
    value: acc.id,
    label: acc.bank_name ? `${acc.bank_name} (${acc.account_name})` : acc.account_name,
    sublabel: acc.account_number ? (acc.masked_account_number || ('****' + String(acc.account_number).slice(-4))) : ''
  }));
});

const formattedPaymentMethods = computed(() => {
  return paymentMethods.value.map(m => ({
    value: m.value,
    label: m.label
  }));
});

const payeeTypeOptions = [
  { value: 'supplier', label: 'Supplier' },
  { value: 'employee', label: 'Employee' },
  { value: 'customer', label: 'Customer' },
  { value: 'other', label: 'Other' },
];

const formattedPayeeOptions = computed(() => {
  return getPayeeOptions().map(p => ({
    value: p.id,
    label: p.name
  }));
});

const statusOptions = [
  { value: 'draft', label: 'Draft' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'paid', label: 'Paid' },
];

// Methods
const loadPaymentOptions = async () => {
  try {
    const response = await axios.get('/api/payment-options');

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
  form.payee_type = '';
  form.payee_id = '';
  form.payee_name = '';

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

const saveAsDraft = () => {
  form.status = 'draft';
  submitForm();
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
    loadPaymentOptions();

    if (isEditing.value) {
      populateForm();
    } else {
      resetForm();
      prefillFromUrlParams();
    }
  }
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
};

// Initialize
onMounted(() => {
  loadPaymentOptions();
});
</script>
