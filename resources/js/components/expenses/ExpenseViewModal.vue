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

      <!-- Action Buttons -->
      <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
        <div>
          <button
            v-if="canEdit"
            @click="$emit('edit')"
            class="px-4 py-2 bg-slate-900 hover:bg-black text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 rounded-xl text-xs font-extrabold transition-all cursor-pointer"
          >
            Edit
          </button>
        </div>

        <div>
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
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Props and Emits
const props = defineProps({
  expense: {
    type: Object,
    required: true
  }
});

defineEmits(['close', 'edit']);

const canEdit = computed(() => {
  return authStore.hasPermission('expenses.edit') && ['draft', 'submitted'].includes(props.expense.status);
});

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString();
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-slate-200 border border-slate-200 dark:border-zinc-700',
    submitted: 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-900/50',
    completed: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-900/50',
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
    completed: 'Completed',
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
</script>
