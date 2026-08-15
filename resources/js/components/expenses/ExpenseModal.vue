<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[92vh] overflow-y-auto my-auto">
      
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 mb-5 border-b border-slate-100 dark:border-zinc-800">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
            {{ isEditing ? 'Edit Expense' : 'Create New Expense' }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Fill in the details below to {{ isEditing ? 'update this expense record' : 'log a new expense' }}
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveExpense('save_pending')" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          
          <!-- Title -->
          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Title <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="e.g., Office Supplies Purchase, Internet Bill"
            />
            <span v-if="errors.title" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.title[0] }}</span>
          </div>

          <!-- Category (Floating Dropdown) -->
          <div>
            <CustomFloatingSelect
              v-model="form.category_id"
              label="Category *"
              placeholder="Select Category"
              :options="categoryOptions"
              :searchable="true"
            />
            <span v-if="errors.category_id" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.category_id[0] }}</span>
          </div>

          <!-- Status (Floating Dropdown) -->
          <div>
            <CustomFloatingSelect
              v-model="form.status"
              label="STATUS *"
              placeholder="Select Status"
              :options="statusOptions"
            />
            <span v-if="errors.status" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.status[0] }}</span>
          </div>

          <!-- Employee (Floating Dropdown) -->
          <div>
            <CustomFloatingSelect
              v-model="form.employee_id"
              label="Employee"
              placeholder="Select Employee (Optional)"
              :options="employeeOptions"
              :searchable="true"
            />
            <span v-if="errors.employee_id" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.employee_id[0] }}</span>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Total Amount ($) <span class="text-rose-500">*</span>
            </label>
            <input
              v-model.number="form.amount"
              type="number"
              step="0.01"
              min="0.01"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="0.00"
            />
            <span v-if="errors.amount" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.amount[0] }}</span>
          </div>

          <!-- Expense Date -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Expense Date <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.expense_date"
              type="date"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
            />
            <span v-if="errors.expense_date" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.expense_date[0] }}</span>
          </div>

          <!-- Vendor Name -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Vendor Name
            </label>
            <input
              v-model="form.vendor_name"
              type="text"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="e.g., Staples, Amazon, Utilities Co."
            />
            <span v-if="errors.vendor_name" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.vendor_name[0] }}</span>
          </div>

          <!-- Reference Number -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Reference / Invoice #
            </label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="Receipt / Reference number"
            />
            <span v-if="errors.reference_number" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.reference_number[0] }}</span>
          </div>

          <!-- Side-by-Side Payment Fields (Left: Payment Method, Right: Select Payment) -->
          <div class="md:col-span-2 border-t border-b border-slate-100 dark:border-zinc-800 py-4 my-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
              <!-- Left Field: Payment Method -->
              <div>
                <CustomFloatingSelect
                  v-model="singlePaymentMethod"
                  label="PAYMENT METHOD *"
                  placeholder="Select Payment Method"
                  :options="paymentMethodOptions"
                />
                <span v-if="errors.payment_method" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.payment_method[0] }}</span>
              </div>

              <!-- Right Field: Select Payment (Dynamic based on selected payment method) -->
              <div>
                <!-- If Cash is selected -->
                <div v-if="singlePaymentMethod === 'cash'">
                  <CustomFloatingSelect
                    v-model="bankAccountIds['cash']"
                    label="SELECT PAYMENT *"
                    placeholder="Select Cash Vault Account"
                    :options="cashAccountOptions"
                  />
                </div>

                <!-- If Bank Transfer is selected -->
                <div v-else-if="singlePaymentMethod === 'bank_transfer'">
                  <CustomFloatingSelect
                    v-model="bankAccountIds['bank_transfer']"
                    label="BANK ACCOUNT *"
                    placeholder="Select Bank Account"
                    :options="bankAccountOptions"
                    :searchable="true"
                  />
                </div>

                <!-- If Card is selected -->
                <div v-else-if="singlePaymentMethod === 'credit_card'">
                  <CustomFloatingSelect
                    v-model="bankAccountIds['credit_card']"
                    label="CARD ACCOUNT *"
                    placeholder="Select Card Account"
                    :options="cardAccountOptions"
                    :searchable="true"
                  />
                </div>

                <!-- Fallback if nothing selected -->
                <div v-else>
                  <CustomFloatingSelect
                    model-value=""
                    label="SELECT PAYMENT *"
                    placeholder="Select Payment Account"
                    :options="[]"
                    :disabled="true"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Description
            </label>
            <textarea
              v-model="form.description"
              rows="2"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="Enter detailed description"
            ></textarea>
            <span v-if="errors.description" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.description[0] }}</span>
          </div>

          <!-- Receipt Images / Attachments -->
          <div class="md:col-span-2">
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                Receipt Attachments <span class="text-slate-400 font-normal lowercase">(images or PDF, max 5MB each, max 5 files)</span>
              </label>
            </div>

            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleFileDrop"
              @click="triggerFileInput"
              :class="[
                'relative border-2 border-dashed rounded-xl p-4 transition-all duration-200 cursor-pointer text-center group flex flex-col items-center justify-center gap-1.5',
                isDragging
                  ? 'border-slate-900 dark:border-white bg-slate-100 dark:bg-zinc-800'
                  : 'border-slate-200 dark:border-zinc-700/80 bg-slate-50/50 hover:bg-slate-100/70 dark:bg-zinc-950/40 dark:hover:bg-zinc-900/80'
              ]"
            >
              <input
                ref="fileInputRef"
                type="file"
                multiple
                accept=".png,.jpg,.jpeg,.webp,.pdf,image/png,image/jpeg,image/webp,application/pdf"
                @change="handleFileUpload"
                class="hidden"
              />

              <div class="w-8 h-8 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shadow-xs flex items-center justify-center group-hover:scale-105 transition-transform text-slate-700 dark:text-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
              </div>

              <div class="space-y-0.5">
                <p class="text-xs font-semibold text-slate-700 dark:text-zinc-200">
                  <span class="text-slate-900 dark:text-white font-bold hover:underline">Click to upload</span> or drag and drop
                </p>
                <p class="text-[10px] font-medium text-slate-400 dark:text-zinc-500">
                  PNG, JPG, WEBP, PDF (max 5MB each, max 5 files)
                </p>
              </div>
            </div>

            <!-- Existing Uploaded Attachments -->
            <div v-if="existingAttachments.length > 0" class="space-y-1.5 pt-2">
              <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Current Attachments</p>
              <div class="flex flex-wrap gap-2">
                <div
                  v-for="(att, idx) in existingAttachments"
                  :key="'exist-' + idx"
                  class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800 text-xs shadow-2xs"
                >
                  <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  <span class="truncate font-semibold text-slate-800 dark:text-slate-200 max-w-[150px]">{{ getFileName(att) }}</span>
                  
                  <!-- Download Icon Button -->
                  <a
                    :href="getFileUrl(att)"
                    target="_blank"
                    download
                    class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200 p-0.5 rounded-md transition-all"
                    title="Download attachment"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </a>

                  <!-- Remove Existing Attachment -->
                  <button type="button" @click="removeExistingAttachment(idx)" class="text-rose-400 hover:text-rose-600 p-0.5 rounded-md transition-all cursor-pointer" title="Remove attachment">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Newly Selected Files Badges -->
            <div v-if="selectedFiles.length > 0" class="flex flex-wrap gap-2 pt-2">
              <div
                v-for="(file, index) in selectedFiles"
                :key="index"
                class="flex items-center gap-2 bg-slate-100 dark:bg-zinc-800 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-zinc-700 text-xs shadow-2xs"
              >
                <span class="truncate font-semibold text-slate-800 dark:text-slate-200 max-w-[150px]">{{ file.name }}</span>
                <span class="text-[10px] text-slate-400 font-medium">({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                <button type="button" @click.stop="removeSelectedFile(index)" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-md transition-all cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            <span v-if="errors.receipt_images" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.receipt_images[0] }}</span>
          </div>

          <!-- Notes -->
          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
              Additional Notes
            </label>
            <textarea
              v-model="form.notes"
              rows="2"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
              placeholder="Any additional notes or comments"
            ></textarea>
            <span v-if="errors.notes" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.notes[0] }}</span>
          </div>

        </div>

        <!-- Actions Bar -->
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="$emit('close')"
            class="w-full sm:w-auto px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          >
            Cancel
          </button>
          
          <button
            type="submit"
            :disabled="saving"
            class="w-full sm:w-auto px-5 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 rounded-xl text-xs font-extrabold shadow-sm transition-all cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>{{ isEditing ? 'Update Expense' : 'Create Expense' }}</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

// Props and Emits
const props = defineProps({
  expense: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

// Form Reactive Data
const form = ref({
  title: '',
  category_id: '',
  employee_id: '',
  amount: 0,
  expense_date: new Date().toISOString().split('T')[0],
  vendor_name: '',
  reference_number: '',
  payment_method: '',
  description: '',
  notes: '',
  status: 'draft'
});

// Status options for expense lifecycle matching Payment Out
const statusOptions = [
  { value: 'draft', label: 'Draft' },
  { value: 'pending', label: 'Pending' },
  { value: 'process', label: 'Process' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'completed', label: 'Completed' }
];

// Single payment method reactive state (left field = Payment Method, right field = Select Payment)
const singlePaymentMethod = ref('cash');
const isMultiPayMode = ref(false);

// Multi payment method reactive state
const selectedPaymentMethods = ref(['cash']);
const paymentAmounts = ref({});
const bankAccountIds = ref({});

const categories = ref([]);
const employees = ref([]);
const bankAccounts = ref([]);
const errors = ref({});
const saving = ref(false);
const selectedFiles = ref([]);
const existingAttachments = ref([]);
const fileInputRef = ref(null);
const isDragging = ref(false);

const getFileName = (path) => {
  if (!path) return '';
  return path.split('/').pop();
};

const getFileUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  if (path.startsWith('/storage/')) return path;
  return '/storage/' + path;
};

const removeExistingAttachment = (index) => {
  existingAttachments.value.splice(index, 1);
};

const isEditing = computed(() => !!props.expense);

// Payment method select options - strictly Cash, Bank Transfer, and Card
const paymentMethodOptions = [
  { value: 'cash', label: 'Cash' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
  { value: 'credit_card', label: 'Card' }
];

// Computed dropdown options
const categoryOptions = computed(() => {
  return categories.value.map(cat => ({
    value: cat.id,
    label: cat.code ? `${cat.code}-${cat.name}` : cat.name
  }));
});

const employeeOptions = computed(() => {
  return employees.value.map(emp => ({
    value: emp.id,
    label: emp.full_name || `${emp.first_name} ${emp.last_name}`
  }));
});

// Helper to format Bank Account label displaying Account Name (Bank Name - Credit Card / Bank Name) — ****-Last4 • Balance: $Balance
const formatAccountOptionLabel = (bank, isCard = false) => {
  const accName = bank.account_name || 'Account';
  const bName = bank.bank_name && bank.bank_name !== accName ? bank.bank_name : '';
  
  let typeOrBank = '';
  if (isCard || bank.account_type === 'credit_card' || bank.account_type === 'card') {
    typeOrBank = bName ? ` (${bName} - Credit Card)` : ' (Credit Card)';
  } else if (bName) {
    typeOrBank = ` (${bName})`;
  }

  const accNum = String(bank.account_number || '').trim();
  const last4 = accNum.length >= 4 ? accNum.slice(-4) : (accNum || '001');
  const balance = parseFloat(bank.current_balance ?? bank.opening_balance ?? 0).toFixed(2);

  let label = `${accName}${typeOrBank}`;
  if (last4) {
    label += ` — ****-${last4}`;
  }
  label += ` • Balance: $${balance}`;
  return label;
};

// Cash Account Options (strictly cash accounts)
const cashAccountOptions = computed(() => {
  const activeBanks = bankAccounts.value.filter(b => (b.is_active !== false && b.is_active !== 0));
  const cashBanks = activeBanks.filter(b =>
    b.account_type === 'cash' ||
    (b.bank_name && b.bank_name.toLowerCase().includes('cash')) ||
    (b.account_name && b.account_name.toLowerCase().includes('cash'))
  );
  const listToUse = cashBanks.length > 0 ? cashBanks : activeBanks;
  return listToUse.map(bank => ({
    value: bank.id,
    label: formatAccountOptionLabel(bank, false)
  }));
});

// Bank Account options for Bank Transfer (checking, savings, or general bank)
const bankAccountOptions = computed(() => {
  const activeBanks = bankAccounts.value.filter(b => (b.is_active !== false && b.is_active !== 0));
  const bankBanks = activeBanks.filter(b => b.account_type !== 'cash' && b.account_type !== 'credit_card');
  const listToUse = bankBanks.length > 0 ? bankBanks : activeBanks;
  return listToUse.map(bank => ({
    value: bank.id,
    label: formatAccountOptionLabel(bank, false)
  }));
});

// Card Account options for Card payment method
const cardAccountOptions = computed(() => {
  const activeBanks = bankAccounts.value.filter(b => (b.is_active !== false && b.is_active !== 0));
  const cardBanks = activeBanks.filter(b =>
    b.account_type === 'credit_card' ||
    b.account_type === 'card' ||
    (b.account_name && b.account_name.toLowerCase().includes('card')) ||
    (b.bank_name && b.bank_name.toLowerCase().includes('card'))
  );
  const listToUse = cardBanks.length > 0 ? cardBanks : activeBanks;
  return listToUse.map(bank => ({
    value: bank.id,
    label: formatAccountOptionLabel(bank, true)
  }));
});

const getMethodLabel = (val) => {
  const match = paymentMethodOptions.find(o => o.value === val);
  return match ? match.label : val;
};

// Helper to select default cash account if Cash is selected (ignores non-cash default bank accounts)
const selectDefaultCashAccount = () => {
  if (bankAccounts.value.length === 0) return;
  const activeBanks = bankAccounts.value.filter(b => (b.is_active !== false && b.is_active !== 0));
  
  // Find explicit cash account first
  const defaultCash = activeBanks.find(b =>
    b.account_type === 'cash' ||
    (b.bank_name && b.bank_name.toLowerCase().includes('cash')) ||
    (b.account_name && b.account_name.toLowerCase().includes('cash'))
  ) || activeBanks.find(b => b.is_default) || activeBanks[0];

  if (defaultCash) {
    bankAccountIds.value['cash'] = defaultCash.id;
  }
};

// Helper to select default bank or card accounts when method changes
const selectDefaultBankOrCardAccount = (methodKey) => {
  if (bankAccounts.value.length === 0) return;
  const activeBanks = bankAccounts.value.filter(b => (b.is_active !== false && b.is_active !== 0));
  if (activeBanks.length === 0) return;

  if (methodKey === 'bank_transfer') {
    const bankOnly = activeBanks.filter(b => b.account_type !== 'cash' && b.account_type !== 'credit_card');
    const listToSearch = bankOnly.length > 0 ? bankOnly : activeBanks;
    const defaultBank = listToSearch.find(b => b.is_default) || listToSearch[0];
    if (defaultBank) {
      bankAccountIds.value['bank_transfer'] = defaultBank.id;
    }
  } else if (methodKey === 'credit_card') {
    const cardOnly = activeBanks.filter(b =>
      b.account_type === 'credit_card' ||
      b.account_type === 'card' ||
      (b.account_name && b.account_name.toLowerCase().includes('card'))
    );
    const listToSearch = cardOnly.length > 0 ? cardOnly : activeBanks;
    const defaultCard = listToSearch.find(b => b.is_default) || listToSearch[0];
    if (defaultCard) {
      bankAccountIds.value['credit_card'] = defaultCard.id;
    }
  }
};

// Watch singlePaymentMethod to auto-populate default account for selected method
watch(singlePaymentMethod, (newMethod) => {
  if (newMethod === 'cash') {
    selectDefaultCashAccount();
  } else if (newMethod === 'bank_transfer') {
    selectDefaultBankOrCardAccount('bank_transfer');
  } else if (newMethod === 'credit_card') {
    selectDefaultBankOrCardAccount('credit_card');
  }

  // Update selected payment methods list in single mode
  if (!isMultiPayMode.value) {
    selectedPaymentMethods.value = [newMethod];
    paymentAmounts.value[newMethod] = form.value.amount || 0;
  }
}, { immediate: true });

// Calculate total allocated payment amount
const totalAllocatedPayment = computed(() => {
  if (!isMultiPayMode.value) {
    return parseFloat(form.value.amount) || 0;
  }
  let sum = 0;
  selectedPaymentMethods.value.forEach(method => {
    sum += parseFloat(paymentAmounts.value[method]) || 0;
  });
  return sum;
});

// Calculate remaining balance
const remainingUnallocated = computed(() => {
  const total = parseFloat(form.value.amount) || 0;
  const rem = total - totalAllocatedPayment.value;
  return rem > 0 ? rem : 0;
});

// Watch total amount change to update single method allocation
watch(() => form.value.amount, (newAmount) => {
  if (!isMultiPayMode.value && singlePaymentMethod.value) {
    paymentAmounts.value[singlePaymentMethod.value] = newAmount || 0;
  }
});

// File upload handlers
const triggerFileInput = () => {
  if (fileInputRef.value) fileInputRef.value.click();
};

const handleFileUpload = (event) => {
  addFiles(Array.from(event.target.files));
};

const handleFileDrop = (event) => {
  isDragging.value = false;
  addFiles(Array.from(event.dataTransfer.files));
};

const addFiles = (files) => {
  if (selectedFiles.value.length + files.length > 5) {
    alert('Maximum 5 files allowed.');
    return;
  }
  const allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'pdf'];
  for (const file of files) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!allowedExts.includes(ext)) {
      alert(`File "${file.name}" format is not allowed. Only PNG, JPG, WEBP, PDF allowed.`);
      continue;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert(`File "${file.name}" exceeds 5MB size limit.`);
      continue;
    }
    selectedFiles.value.push(file);
  }
};

const removeSelectedFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

// API Fetching
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
    employees.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching employees:', error);
  }
};

const fetchBankAccounts = async () => {
  try {
    const response = await axios.get('/api/bank-accounts');
    bankAccounts.value = response.data.data || response.data;
    selectDefaultCashAccount();
    if (singlePaymentMethod.value === 'bank_transfer') selectDefaultBankOrCardAccount('bank_transfer');
    if (singlePaymentMethod.value === 'credit_card') selectDefaultBankOrCardAccount('credit_card');
  } catch (error) {
    console.error('Error fetching bank accounts:', error);
  }
};

// Save logic
const saveExpense = async () => {
  saving.value = true;
  errors.value = {};

  try {
    const formData = new FormData();

    // Primary form attributes
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key]);
      }
    });

    // Payment method & payments payload
    if (isMultiPayMode.value && selectedPaymentMethods.value.length > 0) {
      const pmString = selectedPaymentMethods.value.length === 1 ? selectedPaymentMethods.value[0] : 'mixed';
      formData.append('payment_method', pmString);

      const paymentsList = selectedPaymentMethods.value.map(method => ({
        method,
        amount: parseFloat(paymentAmounts.value[method]) || 0,
        bank_id: bankAccountIds.value[method] || null
      }));
      formData.append('payments', JSON.stringify(paymentsList));
    } else {
      formData.append('payment_method', singlePaymentMethod.value);
      const paymentsList = [{
        method: singlePaymentMethod.value,
        amount: parseFloat(form.value.amount) || 0,
        bank_id: bankAccountIds.value[singlePaymentMethod.value] || null
      }];
      formData.append('payments', JSON.stringify(paymentsList));
    }

    // Attachments
    formData.append('existing_attachments', JSON.stringify(existingAttachments.value));
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

    if (response.data?.message) {
      toast.success(response.data.message);
    } else {
      toast.success(isEditing.value ? 'Expense updated successfully' : 'Expense saved successfully');
    }

    emit('saved');
  } catch (error) {
    if (error.response?.status === 422) {
      if (error.response.data?.errors) {
        errors.value = error.response.data.errors;
      }
      if (error.response.data?.message) {
        toast.error(error.response.data.message);
      }
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message);
    } else {
      toast.error('Failed to save expense');
      console.error('Error saving expense:', error);
    }
  } finally {
    saving.value = false;
  }
};

// Form Initialization
const initializeForm = () => {
  if (props.expense) {
    Object.keys(form.value).forEach(key => {
      if (props.expense[key] !== undefined && props.expense[key] !== null) {
        form.value[key] = props.expense[key];
      }
    });

    // Format expense_date to YYYY-MM-DD for date input
    if (props.expense.expense_date) {
      form.value.expense_date = String(props.expense.expense_date).split('T')[0].split(' ')[0];
    }

    // Populate existing attachments
    existingAttachments.value = [];
    if (props.expense.receipt_images && Array.isArray(props.expense.receipt_images)) {
      existingAttachments.value = [...props.expense.receipt_images];
    } else if (props.expense.receipt_image) {
      existingAttachments.value = [props.expense.receipt_image];
    }

    if (props.expense.payments && Array.isArray(props.expense.payments) && props.expense.payments.length > 1) {
      isMultiPayMode.value = true;
      selectedPaymentMethods.value = props.expense.payments.map(p => p.method);
      props.expense.payments.forEach(p => {
        paymentAmounts.value[p.method] = p.amount;
        if (p.bank_id) bankAccountIds.value[p.method] = p.bank_id;
      });
    } else if (props.expense.payment_method) {
      const mappedPm = props.expense.payment_method === 'bank' ? 'bank_transfer' : (props.expense.payment_method === 'card' ? 'credit_card' : props.expense.payment_method);
      singlePaymentMethod.value = mappedPm;
      paymentAmounts.value[mappedPm] = props.expense.amount;
      if (props.expense.payments && props.expense.payments[0] && props.expense.payments[0].bank_id) {
        bankAccountIds.value[mappedPm] = props.expense.payments[0].bank_id;
      }
    }
  } else {
    singlePaymentMethod.value = 'cash';
    paymentAmounts.value['cash'] = form.value.amount || 0;
    existingAttachments.value = [];
  }
};

onMounted(() => {
  fetchCategories();
  fetchEmployees();
  fetchBankAccounts();
  initializeForm();
});
</script>
