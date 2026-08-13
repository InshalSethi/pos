<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200"
    style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
  >
    <!-- Modal Card Container -->
    <div class="relative mx-auto border border-slate-200/90 dark:border-zinc-800 w-full max-w-3xl shadow-2xl rounded-3xl bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 p-6 sm:p-7 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto custom-scrollbar my-auto">
      
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-zinc-800">
        <div>
          <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Payment Receipt Details</h3>
          <p class="text-xs font-mono font-bold text-slate-500 dark:text-zinc-400 mt-0.5">{{ receipt.receipt_number }}</p>
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

      <!-- Receipt Information -->
      <div class="mt-5 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Basic Information Card -->
          <div class="bg-slate-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-700/80 space-y-3">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-200 dark:border-zinc-700 pb-2">Basic Information</h4>
            
            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Receipt Number</span>
              <span class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ receipt.receipt_number }}</span>
            </div>

            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Receipt Type</span>
              <span class="font-semibold text-slate-900 dark:text-slate-100">{{ getReceiptTypeDisplay(receipt.receipt_type) }}</span>
            </div>

            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Amount</span>
              <span class="font-mono font-bold text-slate-900 dark:text-slate-100 text-sm">${{ formatAmount(receipt.amount) }}</span>
            </div>

            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Receipt Date</span>
              <span class="font-medium text-slate-900 dark:text-slate-100">{{ formatDate(receipt.receipt_date) }}</span>
            </div>

            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Payment Method</span>
              <span class="font-medium text-slate-900 dark:text-slate-100">{{ getPaymentMethodDisplay(receipt.payment_method) }}</span>
            </div>

            <div class="flex justify-between items-center text-xs pt-1">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Status</span>
              <span :class="getStatusBadgeClass(receipt.status)" class="inline-flex px-2.5 py-1 text-[11px] font-semibold rounded-full border">
                {{ receipt.status ? receipt.status.charAt(0).toUpperCase() + receipt.status.slice(1) : 'Unknown' }}
              </span>
            </div>
          </div>

          <!-- Payer Information Card -->
          <div class="bg-slate-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-700/80 space-y-3">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-200 dark:border-zinc-700 pb-2">Payer Information</h4>
            
            <div class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Payer Name</span>
              <span class="font-bold text-slate-900 dark:text-slate-100">{{ receipt.payer_name || 'N/A' }}</span>
            </div>

            <div v-if="receipt.payer_type" class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Payer Type</span>
              <span class="font-medium text-slate-900 dark:text-slate-100">{{ receipt.payer_type.charAt(0).toUpperCase() + receipt.payer_type.slice(1) }}</span>
            </div>

            <div v-if="receipt.bank_account" class="space-y-1 text-xs">
              <div class="flex justify-between items-center">
                <span class="text-slate-500 dark:text-zinc-400 font-semibold">Bank Account</span>
                <span class="font-bold text-slate-900 dark:text-slate-100">{{ receipt.bank_account.account_name }}</span>
              </div>
              <div class="text-right text-[11px] text-slate-500 dark:text-zinc-400">
                {{ receipt.bank_account.bank_name }} ({{ receipt.bank_account.account_number }})
              </div>
            </div>

            <div v-if="receipt.transaction_reference" class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Txn Reference</span>
              <span class="font-mono text-slate-900 dark:text-slate-100">{{ receipt.transaction_reference }}</span>
            </div>

            <div v-if="receipt.reference_number" class="flex justify-between items-center text-xs">
              <span class="text-slate-500 dark:text-zinc-400 font-semibold">Ref Number</span>
              <span class="font-mono text-slate-900 dark:text-slate-100">{{ receipt.reference_number }}</span>
            </div>
          </div>
        </div>

        <!-- Description and Notes -->
        <div class="space-y-3">
          <div class="bg-slate-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-700/80 space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Description</h4>
            <p class="text-xs text-slate-700 dark:text-zinc-300 font-medium">{{ receipt.description || 'No description provided.' }}</p>
          </div>

          <div v-if="receipt.notes" class="bg-slate-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-700/80 space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Notes</h4>
            <p class="text-xs text-slate-700 dark:text-zinc-300 font-medium">{{ receipt.notes }}</p>
          </div>

          <div v-if="(receipt.attachments_urls && receipt.attachments_urls.length > 0) || receipt.attachment" class="bg-slate-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-700/80 space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Attachments</h4>
            <div v-if="receipt.attachments_urls && receipt.attachments_urls.length > 0" class="flex flex-wrap gap-2 pt-1">
              <button
                v-for="att in receipt.attachments_urls"
                :key="att.index"
                type="button"
                @click="downloadFile(receipt.id, att.index, att.filename, att.url)"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-xs"
                :title="`Download Attachment: ${att.filename}`"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="truncate max-w-[200px]">{{ att.filename }}</span>
              </button>
            </div>
            <button
              v-else
              type="button"
              @click="downloadFile(receipt.id, 0, 'attachment')"
              class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-xs"
              title="Download Attachment"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              <span>Download Attachment</span>
            </button>
          </div>
        </div>

        <!-- Invoice Allocations -->
        <div v-if="receipt.invoice_allocations && receipt.invoice_allocations.length > 0" class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-200 dark:border-zinc-800 pb-2">Invoice Allocations</h4>
          
          <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-zinc-800">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
              <thead class="bg-slate-50 dark:bg-zinc-800/60 text-slate-500 dark:text-zinc-400">
                <tr>
                  <th class="px-4 py-2 text-left font-bold uppercase">Invoice</th>
                  <th class="px-4 py-2 text-right font-bold uppercase">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                <tr v-for="allocation in receipt.invoice_allocations" :key="allocation.invoice_id">
                  <td class="px-4 py-2.5 font-semibold text-slate-900 dark:text-slate-100">
                    Invoice #{{ allocation.invoice_id }}
                  </td>
                  <td class="px-4 py-2.5 font-bold font-mono text-slate-900 dark:text-slate-100 text-right">
                    ${{ formatAmount(allocation.amount) }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-slate-50 dark:bg-zinc-800/60 font-bold">
                <tr>
                  <td class="px-4 py-2 text-slate-900 dark:text-white">Total Allocated</td>
                  <td class="px-4 py-2 font-mono text-slate-900 dark:text-white text-right">
                    ${{ formatAmount(getTotalAllocated) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Audit & Actions Footer -->
        <div class="flex flex-wrap items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          >
            Close
          </button>
          
          <button
            v-if="authStore.hasPermission('payment_receipts.edit') && receipt.can_be_edited"
            @click="$emit('edit', receipt)"
            class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
          >
            Edit
          </button>

          <button
            v-if="authStore.hasPermission('payment_receipts.verify') && receipt.can_be_verified"
            @click="$emit('verify', receipt)"
            class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white dark:bg-zinc-200 dark:text-zinc-900 text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
          >
            Verify
          </button>

          <button
            v-if="authStore.hasPermission('payment_receipts.deposit') && receipt.can_be_deposited"
            @click="$emit('mark-as-deposited', receipt)"
            class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
          >
            Mark as Deposited
          </button>

          <button
            v-if="authStore.hasPermission('payment_receipts.delete') && receipt.can_be_deleted"
            @click="$emit('delete', receipt)"
            class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { downloadAttachmentFile } from '@/utils/downloadAttachment';

const downloadFile = (receiptId, index = 0, fileName = 'attachment', directUrl = '') => {
  const url = directUrl || `/api/payment-receipts/${receiptId}/download-attachment?index=${index}`;
  downloadAttachmentFile(url, fileName);
};

const authStore = useAuthStore();

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  receipt: {
    type: Object,
    required: true
  }
});

// Emits
const emit = defineEmits(['close', 'edit', 'verify', 'mark-as-deposited', 'delete']);

// Computed
const getTotalAllocated = computed(() => {
  if (!props.receipt.invoice_allocations) return 0;
  return props.receipt.invoice_allocations.reduce((sum, allocation) => sum + parseFloat(allocation.amount), 0);
});

// Utility functions
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
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

const getReceiptTypeDisplay = (type) => {
  const types = {
    customer_payment: 'Customer Payment',
    customer_advance: 'Customer Advance',
    supplier_refund: 'Supplier Refund',
    supplier_rebate: 'Supplier Rebate',
    interest_income: 'Interest Income',
    rental_income: 'Rental Income',
    commission_income: 'Commission Income',
    asset_sale: 'Asset Sale',
    bank_transfer_in: 'Bank Transfer In',
    cash_deposit: 'Cash Deposit',
    miscellaneous_income: 'Miscellaneous Income',
    other_receipt: 'Other Receipt',
  };
  return types[type] || type || 'General Receipt';
};

const getPaymentMethodDisplay = (method) => {
  const methods = {
    cash: 'Cash',
    bank_transfer: 'Bank Transfer',
    check: 'Check',
    card: 'Card',
    online: 'Online Payment',
  };
  return methods[method] || method || 'Bank Transfer';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    deposited: 'bg-slate-900 text-white border-slate-900 dark:bg-zinc-100 dark:text-zinc-900 dark:border-zinc-100',
    verified: 'bg-slate-800 text-white border-slate-800 dark:bg-zinc-800 dark:text-slate-100 dark:border-zinc-700',
    pending: 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-zinc-800 dark:text-slate-200 dark:border-zinc-700',
    draft: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-zinc-800/60 dark:text-slate-400 dark:border-zinc-700/60',
    cancelled: 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
  };
  return classes[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};
</script>
