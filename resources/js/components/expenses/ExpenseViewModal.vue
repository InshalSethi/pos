<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
      
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 mb-5 border-b border-slate-100 dark:border-zinc-800">
        <div>
          <div class="flex items-center gap-2">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Expense Details</h3>
            <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
              {{ expense.expense_number }}
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Created by {{ expense.user?.name || 'System' }}</p>
        </div>
        <button @click="$emit('close')" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Expense Summary Card -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-200/80 dark:border-zinc-700/80">
        <div class="space-y-3">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Title</span>
            <p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ expense.title }}</p>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Category</span>
            <p class="text-xs font-bold text-slate-800 dark:text-zinc-200">{{ expense.category?.name || 'N/A' }}</p>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Expense Date</span>
            <p class="text-xs font-semibold text-slate-700 dark:text-zinc-300">{{ formatDate(expense.expense_date) }}</p>
          </div>
        </div>

        <div class="space-y-3">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Amount</span>
            <p class="text-xl font-black text-slate-900 dark:text-white">${{ parseFloat(expense.amount || 0).toFixed(2) }}</p>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Status</span>
            <div>
              <span
                :class="[
                  'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider mt-0.5',
                  getStatusBadgeClass(expense.status)
                ]"
              >
                {{ getStatusText(expense.status) }}
              </span>
            </div>
          </div>
          <div v-if="expense.employee">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Employee</span>
            <p class="text-xs font-semibold text-slate-700 dark:text-zinc-300">{{ expense.employee.full_name }}</p>
          </div>
        </div>
      </div>

      <!-- Vendor & Payment Method Info -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div v-if="expense.vendor_name" class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Vendor</span>
          <p class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ expense.vendor_name }}</p>
        </div>

        <div v-if="expense.reference_number" class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Reference #</span>
          <p class="text-xs font-mono font-bold text-slate-900 dark:text-white mt-0.5">{{ expense.reference_number }}</p>
        </div>

        <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Payment Method</span>
          <p class="text-xs font-bold text-slate-900 dark:text-white mt-0.5 capitalize">{{ getPaymentMethodText(expense.payment_method) }}</p>
        </div>
      </div>

      <!-- Multi Payment Methods Breakdown (If present) -->
      <div v-if="expense.payments && Array.isArray(expense.payments) && expense.payments.length > 0" class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-200/80 dark:border-zinc-700/80">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white mb-2.5">Multi-Payment Breakdown</h4>
        <div class="space-y-2">
          <div v-for="(pm, idx) in expense.payments" :key="idx" class="flex items-center justify-between p-2.5 rounded-lg bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800 text-xs">
            <span class="font-bold text-slate-900 dark:text-white capitalize">{{ getPaymentMethodText(pm.method) }}</span>
            <span class="font-black text-slate-900 dark:text-white">${{ parseFloat(pm.amount || 0).toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <!-- Description & Notes -->
      <div v-if="expense.description" class="mb-5">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Description</label>
        <p class="text-xs font-medium text-slate-800 dark:text-zinc-200 bg-slate-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-slate-200/60 dark:border-zinc-700/60 leading-relaxed">{{ expense.description }}</p>
      </div>

      <div v-if="expense.notes" class="mb-5">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Additional Notes</label>
        <p class="text-xs font-medium text-slate-800 dark:text-zinc-200 bg-slate-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-slate-200/60 dark:border-zinc-700/60 leading-relaxed">{{ expense.notes }}</p>
      </div>

      <!-- Workflow Information -->
      <div v-if="expense.status !== 'draft'" class="mb-6 border-t border-slate-100 dark:border-zinc-800 pt-4">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white mb-3">Workflow History</h4>
        <div class="space-y-3 text-xs">
          <div v-if="expense.submitted_at" class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 flex items-center justify-center font-bold">✓</div>
            <div>
              <p class="font-bold text-slate-900 dark:text-white">Submitted for approval</p>
              <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ formatDateTime(expense.submitted_at) }} by {{ expense.submitted_by?.name || 'User' }}</p>
            </div>
          </div>

          <div v-if="expense.approved_at" class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 flex items-center justify-center font-bold">✓</div>
            <div>
              <p class="font-bold text-slate-900 dark:text-white">Approved</p>
              <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ formatDateTime(expense.approved_at) }} by {{ expense.approved_by?.name || 'User' }}</p>
              <p v-if="expense.approval_notes" class="text-xs text-slate-600 dark:text-zinc-300 mt-0.5">Notes: {{ expense.approval_notes }}</p>
            </div>
          </div>

          <div v-if="expense.rejected_at" class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 flex items-center justify-center font-bold">✕</div>
            <div>
              <p class="font-bold text-rose-600 dark:text-rose-400">Rejected</p>
              <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ formatDateTime(expense.rejected_at) }} by {{ expense.rejected_by?.name || 'User' }}</p>
              <p v-if="expense.rejection_reason" class="text-xs text-rose-600 dark:text-rose-300 mt-0.5">Reason: {{ expense.rejection_reason }}</p>
            </div>
          </div>

          <div v-if="expense.paid_at" class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 flex items-center justify-center font-bold">✓</div>
            <div>
              <p class="font-bold text-emerald-600 dark:text-emerald-400">Paid</p>
              <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ formatDateTime(expense.paid_at) }} by {{ expense.paid_by?.name || 'User' }}</p>
              <p v-if="expense.payment_reference" class="text-xs text-slate-600 dark:text-zinc-300 mt-0.5">Reference: {{ expense.payment_reference }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
        <div class="flex items-center gap-2">
          <button
            v-if="canEdit"
            @click="$emit('edit')"
            class="px-4 py-2 bg-slate-900 hover:bg-black text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 rounded-xl text-xs font-extrabold transition-all cursor-pointer"
          >
            Edit
          </button>
          <button
            v-if="canSubmit"
            @click="submitExpense"
            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-extrabold transition-all cursor-pointer"
          >
            Submit for Approval
          </button>
        </div>

        <div class="flex items-center gap-2">
          <button
            v-if="canApprove"
            @click="showApprovalModal = true"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-extrabold transition-all cursor-pointer"
          >
            Approve
          </button>
          <button
            v-if="canReject"
            @click="showRejectionModal = true"
            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold transition-all cursor-pointer"
          >
            Reject
          </button>
          <button
            v-if="canPay"
            @click="showPaymentModal = true"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold transition-all cursor-pointer"
          >
            Mark as Paid
          </button>
          <button
            @click="$emit('close')"
            class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Approval Modal -->
  <div v-if="showApprovalModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md" style="backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 p-5">
      <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-2">Approve Expense</h3>
      <div class="mb-4">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Approval Notes (Optional)</label>
        <textarea
          v-model="approvalNotes"
          rows="3"
          class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
          placeholder="Add approval notes..."
        ></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button
          @click="showApprovalModal = false"
          class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
        >
          Cancel
        </button>
        <button
          @click="approveExpense"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-extrabold transition-all cursor-pointer"
        >
          Approve
        </button>
      </div>
    </div>
  </div>

  <!-- Rejection Modal -->
  <div v-if="showRejectionModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md" style="backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 p-5">
      <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-2">Reject Expense</h3>
      <div class="mb-4">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Rejection Reason *</label>
        <textarea
          v-model="rejectionReason"
          rows="3"
          required
          class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
          placeholder="Please provide a reason for rejection..."
        ></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button
          @click="showRejectionModal = false"
          class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
        >
          Cancel
        </button>
        <button
          @click="rejectExpense"
          :disabled="!rejectionReason.trim()"
          class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold transition-all cursor-pointer disabled:opacity-50"
        >
          Reject
        </button>
      </div>
    </div>
  </div>

  <!-- Mark as Paid Modal -->
  <div v-if="showPaymentModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md" style="backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 p-5">
      <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-3">Mark Expense as Paid</h3>

      <div class="mb-4">
        <CustomFloatingSelect
          v-model="selectedBankAccount"
          label="Bank Account *"
          placeholder="Select Bank Account"
          :options="bankAccountOptions"
          :searchable="true"
        />
        <p v-if="!selectedBankAccount && paymentError" class="text-rose-500 text-[11px] font-semibold mt-1">
          Please select a bank account
        </p>
      </div>

      <div class="mb-4">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Payment Reference (Optional)</label>
        <input
          v-model="paymentReference"
          type="text"
          class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
          placeholder="Transaction ID, check number, etc."
        />
      </div>

      <div class="flex justify-end gap-2">
        <button
          @click="cancelPayment"
          class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
        >
          Cancel
        </button>
        <button
          @click="markAsPaid"
          :disabled="!selectedBankAccount"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold transition-all cursor-pointer disabled:opacity-50"
        >
          Mark as Paid
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';
import axios from 'axios';

const authStore = useAuthStore();

// Props and Emits
const props = defineProps({
  expense: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'edit', 'approve', 'reject', 'pay', 'create-payment']);

// Reactive data
const showApprovalModal = ref(false);
const showRejectionModal = ref(false);
const showPaymentModal = ref(false);
const approvalNotes = ref('');
const rejectionReason = ref('');
const paymentReference = ref('');
const selectedBankAccount = ref('');
const bankAccounts = ref([]);
const paymentError = ref(false);

const canEdit = computed(() => {
  return authStore.hasPermission('expenses.edit') && ['draft', 'rejected'].includes(props.expense.status);
});

const canSubmit = computed(() => {
  return props.expense.status === 'draft';
});

const canApprove = computed(() => {
  return authStore.hasPermission('expenses.approve') && props.expense.status === 'submitted';
});

const canReject = computed(() => {
  return authStore.hasPermission('expenses.approve') && props.expense.status === 'submitted';
});

const canPay = computed(() => {
  return authStore.hasPermission('expenses.pay') && props.expense.status === 'approved';
});

const bankAccountOptions = computed(() => {
  return bankAccounts.value.map(account => ({
    value: account.id,
    label: `${account.account_name} - ${account.bank_name || 'Bank'} (${account.formatted_account_number || account.account_number || ''})`,
    disabled: account.is_active === false || account.is_active === 0
  }));
});

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString();
};

const formatDateTime = (datetime) => {
  if (!datetime) return 'N/A';
  return new Date(datetime).toLocaleString();
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-slate-200 border border-slate-200 dark:border-zinc-700',
    submitted: 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-900/50',
    approved: 'bg-indigo-50 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-900/50',
    rejected: 'bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-900/50',
    paid: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-900/50'
  };
  return classes[status] || 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-slate-200';
};

const getStatusText = (status) => {
  const texts = {
    draft: 'Draft',
    submitted: 'Submitted',
    approved: 'Approved',
    rejected: 'Rejected',
    paid: 'Paid'
  };
  return texts[status] || status;
};

const getPaymentMethodText = (method) => {
  const methods = {
    cash: 'Cash',
    bank_transfer: 'Bank Transfer',
    credit_card: 'Credit Card',
    check: 'Check',
    petty_cash: 'Petty Cash',
    mixed: 'Mixed (Multi-Payment)'
  };
  return methods[method] || method || 'Not specified';
};

const submitExpense = async () => {
  try {
    await axios.post(`/api/expenses/${props.expense.id}/submit`);
    emit('approve');
  } catch (error) {
    console.error('Error submitting expense:', error);
  }
};

const approveExpense = async () => {
  try {
    await axios.post(`/api/expenses/${props.expense.id}/approve`, {
      approval_notes: approvalNotes.value
    });
    showApprovalModal.value = false;
    emit('approve');
  } catch (error) {
    console.error('Error approving expense:', error);
  }
};

const rejectExpense = async () => {
  try {
    await axios.post(`/api/expenses/${props.expense.id}/reject`, {
      rejection_reason: rejectionReason.value
    });
    showRejectionModal.value = false;
    emit('reject');
  } catch (error) {
    console.error('Error rejecting expense:', error);
  }
};

const fetchBankAccounts = async () => {
  try {
    const response = await axios.get('/api/bank-accounts');
    bankAccounts.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
  } catch (error) {
    console.error('Error fetching bank accounts:', error);
  }
};

const cancelPayment = () => {
  showPaymentModal.value = false;
  selectedBankAccount.value = '';
  paymentReference.value = '';
  paymentError.value = false;
};

const markAsPaid = async () => {
  if (!selectedBankAccount.value) {
    paymentError.value = true;
    return;
  }

  try {
    await axios.post(`/api/expenses/${props.expense.id}/mark-as-paid`, {
      bank_account_id: selectedBankAccount.value,
      payment_reference: paymentReference.value
    });
    showPaymentModal.value = false;
    selectedBankAccount.value = '';
    paymentReference.value = '';
    paymentError.value = false;
    emit('pay');
  } catch (error) {
    console.error('Error marking expense as paid:', error);
  }
};

watch(showPaymentModal, (newValue) => {
  if (newValue) {
    fetchBankAccounts();
  }
});
</script>
