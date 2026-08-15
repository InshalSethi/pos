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
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 pointer-events-none">{{ currencySymbol }}</span>
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

          <!-- Status Floating Dropdown -->
          <div>
            <FloatingSelect
              v-model="form.status"
              label="Status"
              placeholder="Select Status"
              :options="statusOptions"
              :required="true"
              :error="errors.status ? errors.status[0] : ''"
            />
          </div>

          <!-- Expense Category Floating Dropdown (Shown only when Payment Type is Expense Payment) -->
          <div v-if="form.payment_type === 'expense_payment'">
            <FloatingSelect
              v-model="form.expense_category_id"
              label="Expense Category"
              placeholder="Select Expense Category"
              :options="formattedExpenseCategoryOptions"
              :required="true"
              :error="errors.expense_category_id ? errors.expense_category_id[0] : ''"
            />
          </div>

          <!-- Payee Selection Floating Dropdown -->
          <div v-if="form.payment_type !== 'expense_payment' && form.payee_type && form.payee_type !== 'other'">
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
          <div :class="[
            (form.payment_type === 'expense_payment' || (form.payee_type && form.payee_type !== 'other'))
              ? ''
              : 'md:col-span-2'
          ]">
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
          <div class="md:col-span-2">
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

          <!-- Attachment Field -->
          <div class="md:col-span-2">
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                Attachments <span class="text-slate-400 font-normal lowercase">(images or PDF, max 5MB each, max 5 files)</span>
              </label>
              <span v-if="attachmentFiles.length > 0 || existingAttachments.length > 0" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-900/50">
                {{ attachmentFiles.length + existingAttachments.length }} / 5 file(s) selected
              </span>
            </div>

            <!-- Drag & Drop Container Box -->
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleFileDrop"
              @click="triggerFileInput"
              :class="[
                'relative border-2 border-dashed rounded-2xl p-4 transition-all duration-200 cursor-pointer text-center group flex flex-col items-center justify-center gap-1.5',
                isDragging
                  ? 'border-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/30 scale-[1.01]'
                  : 'border-slate-200 dark:border-zinc-700/80 bg-slate-50/60 hover:bg-slate-100/70 dark:bg-zinc-800/40 dark:hover:bg-zinc-800/80 hover:border-indigo-300 dark:hover:border-indigo-700'
              ]"
            >
              <input
                ref="attachmentInputRef"
                type="file"
                accept="image/*,.pdf"
                multiple
                @change="handleFileChange"
                class="hidden"
              />

              <div class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 shadow-xs flex items-center justify-center group-hover:scale-105 transition-transform text-indigo-600 dark:text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
              </div>

              <div class="space-y-0.5">
                <p class="text-xs font-semibold text-slate-700 dark:text-zinc-200">
                  <span class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Click to upload</span> or drag and drop
                </p>
                <p class="text-[10px] font-medium text-slate-400 dark:text-zinc-500">
                  PNG, JPG, WEBP or PDF (max 5MB each, max 5 files)
                </p>
              </div>
            </div>

            <!-- List of Attachments -->
            <div v-if="existingAttachments.length > 0 || attachmentFiles.length > 0" class="flex flex-wrap gap-2 pt-2.5">
              <!-- Existing Attachments -->
              <div
                v-for="(item, index) in existingAttachments"
                :key="'existing-' + index"
                class="flex items-center gap-2 bg-indigo-50/80 dark:bg-zinc-800 px-3 py-1.5 rounded-xl border border-indigo-100 dark:border-zinc-700 text-xs shadow-2xs"
              >
                <button
                  type="button"
                  @click.stop="downloadFile(props.payment.id, item.index, item.filename, item.url)"
                  class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1 cursor-pointer"
                  title="Download File"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  <span class="truncate max-w-[150px]">{{ item.filename }}</span>
                </button>
                <button type="button" @click.stop="removeExistingFile(index)" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-md transition-all cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Newly Selected Attachments -->
              <div
                v-for="(file, index) in attachmentFiles"
                :key="'new-' + index"
                class="flex items-center gap-2 bg-slate-100/90 dark:bg-zinc-800 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-zinc-700 text-xs shadow-2xs"
              >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="truncate font-semibold text-slate-800 dark:text-slate-200 max-w-[150px]">{{ file.name }}</span>
                <span class="text-[10px] text-slate-400 font-medium">({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                <button type="button" @click.stop="removeNewFile(index)" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-md transition-all cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            <span v-if="attachmentError || errors.attachments" class="text-rose-500 text-[11px] font-semibold mt-1 block">
              {{ attachmentError || (errors.attachments ? errors.attachments[0] : '') }}
            </span>
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
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import { downloadAttachmentFile } from '@/utils/downloadAttachment';

const downloadFile = (paymentId, index = 0, fileName = 'attachment', directUrl = '') => {
  const url = directUrl || `/api/payments/${paymentId}/download-attachment?index=${index}`;
  downloadAttachmentFile(url, fileName);
};

const authStore = useAuthStore();
const currencyStore = useCurrencyStore();

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$';
});

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
const isDragging = ref(false);
const attachmentInputRef = ref(null);
const attachmentFiles = ref([]);
const existingAttachments = ref([]);
const attachmentError = ref('');
const paymentOptions = ref({
  bankAccounts: [],
  suppliers: [],
  employees: [],
  customers: [],
  expenseCategories: [],
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
  expense_category_id: '',
  status: '',
});

// Computed
const isEditing = computed(() => !!props.payment);

const paymentTypes = computed(() => paymentOptions.value.paymentTypes);
const paymentMethods = computed(() => paymentOptions.value.paymentMethods);
const bankAccounts = computed(() => paymentOptions.value.bankAccounts);

const formattedExpenseCategoryOptions = computed(() => {
  return (paymentOptions.value.expenseCategories || []).map(cat => ({
    value: cat.id,
    label: cat.code ? `${cat.code}-${cat.name}` : cat.name
  }));
});

const formattedPaymentTypes = computed(() => {
  return paymentTypes.value.map(t => ({
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
  } else if (method === 'bank_transfer' || method === 'check' || method === 'cheque') {
    filtered = bankAccounts.value.filter(acc => !isCashAccount(acc) && !isCardAccount(acc));
  } else if (method === 'card') {
    filtered = bankAccounts.value.filter(acc => isCardAccount(acc));
  }

  return filtered.map(acc => {
    const rawBal = acc.current_balance !== undefined && acc.current_balance !== null
      ? Number(acc.current_balance)
      : Number(acc.opening_balance || 0);
    const absBal = Math.abs(rawBal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const formattedBal = rawBal < 0 ? `-${currencySymbol.value}${absBal}` : `${currencySymbol.value}${absBal}`;

    const accNum = acc.account_number ? (acc.masked_account_number || ('****' + String(acc.account_number).slice(-4))) : '';

    const sublabelParts = [];
    if (accNum) sublabelParts.push(accNum);
    sublabelParts.push(`Balance: ${formattedBal}`);

    let labelText = '';
    if (acc.account_type === 'credit_card') {
      let bankTitle = acc.bank_name || 'Credit Card';
      if (!bankTitle.toLowerCase().includes('credit card')) {
        bankTitle += '-Credit Card';
      }
      labelText = `${acc.account_name || 'Card Holder'} (${bankTitle})`;
    } else {
      labelText = (acc.bank_name && acc.bank_name !== acc.account_name)
        ? `${acc.account_name} (${acc.bank_name})`
        : (acc.account_name || acc.bank_name || 'Bank Account');
    }

    return {
      value: acc.id,
      label: labelText,
      sublabel: sublabelParts.join(' • '),
      balance: formattedBal
    };
  });
});

const onPaymentMethodChange = () => {
  const available = formattedBankAccounts.value;
  if (available.length === 0) {
    form.bank_account_id = '';
    return;
  }

  const exists = available.some(acc => String(acc.value) === String(form.bank_account_id));

  if (!exists || !form.bank_account_id) {
    const defaultAccInAvailable = available.find(a => {
      const rawAcc = bankAccounts.value.find(b => String(b.id) === String(a.value));
      return rawAcc && (rawAcc.is_default || rawAcc.is_default === 1 || rawAcc.is_default === '1');
    });

    if (defaultAccInAvailable) {
      form.bank_account_id = defaultAccInAvailable.value;
    } else {
      form.bank_account_id = available[0].value;
    }
  }
};

const formattedPaymentMethods = computed(() => {
  return paymentMethods.value.map(m => ({
    value: m.value,
    label: (m.value === 'check' || m.label === 'Check') ? 'Cheque' : m.label
  }));
});

const payeeTypeOptions = [
  { value: 'supplier', label: 'Supplier' },
  { value: 'employee', label: 'Employee' },
  { value: 'customer', label: 'Customer' },
  { value: 'other', label: 'Other' },
];

const formattedPayeeOptions = computed(() => {
  return getPayeeOptions().map(p => {
    let label = p.name;
    if (form.payee_type === 'supplier' && p.company_name && p.name) {
      const name = (p.name || '').trim();
      const company = (p.company_name || '').trim();
      if (name && company && !name.toLowerCase().includes(company.toLowerCase())) {
        label = `${name} (${company})`;
      }
    }
    return {
      value: p.id,
      label: label
    };
  });
});

const statusOptions = computed(() => {
  return paymentOptions.value.statuses && paymentOptions.value.statuses.length > 0
    ? paymentOptions.value.statuses
    : [
        { value: 'draft', label: 'Draft' },
        { value: 'pending', label: 'Pending' },
        { value: 'process', label: 'Process' },
        { value: 'rejected', label: 'Rejected' },
        { value: 'paid', label: 'Paid' },
      ];
});

// Methods
const loadPaymentOptions = async () => {
  try {
    const response = await axios.get('/api/payment-options');

    paymentOptions.value = {
      bankAccounts: response.data.bank_accounts || [],
      suppliers: response.data.suppliers || [],
      employees: response.data.employees || [],
      customers: response.data.customers || [],
      expenseCategories: response.data.expense_categories || [],
      paymentTypes: response.data.payment_types || [],
      paymentMethods: response.data.payment_methods || [],
      statuses: response.data.statuses || [],
    };

    const defaultAcc = (response.data.bank_accounts || []).find(acc => acc.is_default || acc.is_default === 1 || acc.is_default === '1');
    const defaultId = defaultAcc ? defaultAcc.id : ((response.data.bank_accounts || []).length > 0 ? response.data.bank_accounts[0].id : '');
    if (!form.bank_account_id && defaultId) {
      form.bank_account_id = defaultId;
    }
  } catch (error) {
    console.error('Error loading payment options:', error);
    try {
      const catRes = await axios.get('/api/expense-categories');
      paymentOptions.value.expenseCategories = Array.isArray(catRes.data) ? catRes.data : (catRes.data.data || []);
    } catch (e) {
      console.error('Error loading fallback expense categories:', e);
    }
    paymentOptions.value = {
      ...paymentOptions.value,
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
        { value: 'check', label: 'Cheque' },
        { value: 'card', label: 'Card' },
      ],
      statuses: [
        { value: 'draft', label: 'Draft' },
        { value: 'pending', label: 'Pending' },
        { value: 'process', label: 'Process' },
        { value: 'rejected', label: 'Rejected' },
        { value: 'completed', label: 'Paid' },
      ],
    };
  }
};

const onPaymentTypeChange = () => {
  form.payee_type = '';
  form.payee_id = '';
  form.payee_name = '';
  if (form.payment_type !== 'expense_payment') {
    form.expense_category_id = '';
  }

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

const triggerFileInput = () => {
  if (attachmentInputRef.value) {
    attachmentInputRef.value.click();
  }
};

const handleFileDrop = (e) => {
  isDragging.value = false;
  const droppedFiles = e.dataTransfer ? Array.from(e.dataTransfer.files) : [];
  if (droppedFiles.length === 0) return;
  processFiles(droppedFiles);
};

const handleFileChange = (e) => {
  const files = e.target.files ? Array.from(e.target.files) : [];
  if (files.length === 0) return;
  processFiles(files);
  if (attachmentInputRef.value) attachmentInputRef.value.value = '';
};

const processFiles = (files) => {
  attachmentError.value = '';
  const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
  const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
  const maxSize = 5 * 1024 * 1024; // 5 MB
  const maxCount = 5;

  for (const file of files) {
    const currentTotal = existingAttachments.value.length + attachmentFiles.value.length;
    if (currentTotal >= maxCount) {
      alert(`Maximum ${maxCount} attachments allowed! You can only attach up to 5 files in total.`);
      break;
    }

    const extension = file.name.split('.').pop().toLowerCase();
    const isValidType = allowedMimeTypes.includes(file.type) || allowedExtensions.includes(extension);

    if (!isValidType) {
      alert(`Invalid file type for "${file.name}"! Only image files (JPG, PNG, GIF, WEBP) and PDF documents are allowed.`);
      continue;
    }

    if (file.size > maxSize) {
      alert(`File "${file.name}" exceeds the 5 MB limit! Please select a smaller file.`);
      continue;
    }

    attachmentFiles.value.push(file);
  }
};

const removeNewFile = (index) => {
  attachmentFiles.value.splice(index, 1);
};

const removeExistingFile = (index) => {
  existingAttachments.value.splice(index, 1);
};

const resetForm = () => {
  const defaultAcc = bankAccounts.value.find(acc => acc.is_default || acc.is_default === 1 || acc.is_default === '1');
  const defaultId = defaultAcc ? defaultAcc.id : (bankAccounts.value.length > 0 ? bankAccounts.value[0].id : '');

  Object.assign(form, {
    payment_type: '',
    amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'bank_transfer',
    reference_number: '',
    description: '',
    notes: '',
    bank_account_id: defaultId,
    payee_type: '',
    payee_id: '',
    payee_name: '',
    expense_category_id: '',
    status: '',
  });
  attachmentFiles.value = [];
  existingAttachments.value = [];
  attachmentError.value = '';
  if (attachmentInputRef.value) attachmentInputRef.value.value = '';
  errors.value = {};
};

const populateForm = () => {
  if (props.payment) {
    let formattedDate = props.payment.payment_date || '';
    if (formattedDate && typeof formattedDate === 'string' && formattedDate.includes('T')) {
      formattedDate = formattedDate.split('T')[0];
    }

    Object.assign(form, {
      payment_type: props.payment.payment_type || '',
      amount: props.payment.amount !== null && props.payment.amount !== undefined ? String(props.payment.amount) : '',
      payment_date: formattedDate || new Date().toISOString().split('T')[0],
      payment_method: props.payment.payment_method || 'bank_transfer',
      reference_number: props.payment.reference_number || '',
      description: props.payment.description || '',
      notes: props.payment.notes || '',
      bank_account_id: props.payment.bank_account_id || '',
      payee_type: props.payment.payee_type || '',
      payee_id: props.payment.payee_id || '',
      payee_name: props.payment.payee_name || '',
      expense_category_id: props.payment.expense_category_id || '',
      status: props.payment.status || '',
    });

    attachmentFiles.value = [];
    if (props.payment.attachments_urls && Array.isArray(props.payment.attachments_urls)) {
      existingAttachments.value = [...props.payment.attachments_urls];
    } else {
      existingAttachments.value = [];
    }
  }
};

const saveAsDraft = () => {
  form.status = 'draft';
  submitForm();
};

const submitForm = async () => {
  loading.value = true;
  errors.value = {};
  attachmentError.value = '';

  try {
    const url = isEditing.value ? `/api/payments/${props.payment.id}` : '/api/payments';
    
    const formData = new FormData();
    Object.keys(form).forEach(key => {
      if (form[key] !== null && form[key] !== undefined) {
        formData.append(key, form[key]);
      }
    });

    if (isEditing.value) {
      existingAttachments.value.forEach(item => {
        formData.append('existing_attachments[]', item.path);
      });
      formData.append('_method', 'PUT');
    }

    attachmentFiles.value.forEach(file => {
      formData.append('attachments[]', file);
    });

    const response = await axios.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    emit('saved', response.data.payment);
    resetForm();
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
      if (errors.value.attachments) {
        attachmentError.value = errors.value.attachments[0];
        alert(errors.value.attachments[0]);
      }
    } else {
      console.error('Error saving payment:', error);
      alert('Failed to save payment. Please try again.');
    }
  } finally {
    loading.value = false;
  }
};

// Watchers
watch(() => form.payment_method, () => {
  onPaymentMethodChange();
});

watch([() => props.show, () => props.payment], async ([newShow, newPayment]) => {
  if (newShow) {
    await loadPaymentOptions();

    if (newPayment) {
      populateForm();
    } else {
      resetForm();
      prefillFromUrlParams();
    }
  }
}, { immediate: true });

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
