<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-medium text-gray-900">Payment Receipt Details</h3>
          <p class="text-sm text-gray-600">{{ receipt.receipt_number }}</p>
        </div>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Receipt Information -->
      <div class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Basic Information -->
          <div class="space-y-4">
            <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Basic Information</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Receipt Number</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.receipt_number }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Receipt Type</label>
              <p class="mt-1 text-sm text-gray-900">{{ getReceiptTypeDisplay(receipt.receipt_type) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Amount</label>
              <p class="mt-1 text-lg font-semibold text-gray-900">${{ formatAmount(receipt.amount) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Receipt Date</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(receipt.receipt_date) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Method</label>
              <p class="mt-1 text-sm text-gray-900">{{ getPaymentMethodDisplay(receipt.payment_method) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <span :class="getStatusBadgeClass(receipt.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ receipt.status.charAt(0).toUpperCase() + receipt.status.slice(1) }}
              </span>
            </div>
          </div>

          <!-- Payer Information -->
          <div class="space-y-4">
            <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Payer Information</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Payer Name</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.payer_name }}</p>
            </div>

            <div v-if="receipt.payer_type">
              <label class="block text-sm font-medium text-gray-700">Payer Type</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.payer_type.charAt(0).toUpperCase() + receipt.payer_type.slice(1) }}</p>
            </div>

            <div v-if="receipt.bank_account">
              <label class="block text-sm font-medium text-gray-700">Bank Account</label>
              <p class="mt-1 text-sm text-gray-900">
                {{ receipt.bank_account.account_name }} - {{ receipt.bank_account.bank_name }}
                <br>
                <span class="text-gray-600">{{ receipt.bank_account.account_number }}</span>
              </p>
            </div>

            <div v-if="receipt.transaction_reference">
              <label class="block text-sm font-medium text-gray-700">Transaction Reference</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.transaction_reference }}</p>
            </div>

            <div v-if="receipt.reference_number">
              <label class="block text-sm font-medium text-gray-700">Reference Number</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.reference_number }}</p>
            </div>
          </div>
        </div>

        <!-- Description and Notes -->
        <div class="mt-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <p class="mt-1 text-sm text-gray-900">{{ receipt.description }}</p>
          </div>

          <div v-if="receipt.notes">
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <p class="mt-1 text-sm text-gray-900">{{ receipt.notes }}</p>
          </div>
        </div>

        <!-- Invoice Allocations -->
        <div v-if="receipt.invoice_allocations && receipt.invoice_allocations.length > 0" class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Invoice Allocations</h4>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="allocation in receipt.invoice_allocations" :key="allocation.invoice_id">
                  <td class="px-4 py-2 text-sm text-gray-900">
                    Invoice #{{ allocation.invoice_id }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-900 text-right">
                    ${{ formatAmount(allocation.amount) }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td class="px-4 py-2 text-sm font-medium text-gray-900">Total Allocated</td>
                  <td class="px-4 py-2 text-sm font-medium text-gray-900 text-right">
                    ${{ formatAmount(getTotalAllocated()) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Verification Information -->
        <div v-if="receipt.verified_by || receipt.verified_at" class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Verification Information</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="receipt.verified_by">
              <label class="block text-sm font-medium text-gray-700">Verified By</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.verified_by.name }}</p>
            </div>

            <div v-if="receipt.verified_at">
              <label class="block text-sm font-medium text-gray-700">Verified At</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(receipt.verified_at) }}</p>
            </div>
          </div>

          <div v-if="receipt.verification_notes">
            <label class="block text-sm font-medium text-gray-700">Verification Notes</label>
            <p class="mt-1 text-sm text-gray-900">{{ receipt.verification_notes }}</p>
          </div>
        </div>

        <!-- Deposit Information -->
        <div v-if="receipt.deposited_by || receipt.deposited_at" class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Deposit Information</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="receipt.deposited_by">
              <label class="block text-sm font-medium text-gray-700">Deposited By</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.deposited_by.name }}</p>
            </div>

            <div v-if="receipt.deposited_at">
              <label class="block text-sm font-medium text-gray-700">Deposited At</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(receipt.deposited_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Journal Entry Information -->
        <div v-if="receipt.journal_entry" class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Accounting Information</h4>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Journal Entry</label>
            <p class="mt-1 text-sm text-gray-900">{{ receipt.journal_entry.entry_number }}</p>
          </div>

          <div v-if="receipt.journal_entry.journal_entry_lines && receipt.journal_entry.journal_entry_lines.length > 0">
            <label class="block text-sm font-medium text-gray-700 mb-2">Journal Entry Lines</label>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="line in receipt.journal_entry.journal_entry_lines" :key="line.id">
                    <td class="px-4 py-2 text-sm text-gray-900">
                      {{ line.account ? line.account.account_name : 'N/A' }}
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ line.description }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 text-right">
                      {{ line.debit_amount > 0 ? '$' + formatAmount(line.debit_amount) : '-' }}
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900 text-right">
                      {{ line.credit_amount > 0 ? '$' + formatAmount(line.credit_amount) : '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Created Information -->
        <div class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Created Information</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Created By</label>
              <p class="mt-1 text-sm text-gray-900">{{ receipt.created_by ? receipt.created_by.name : 'N/A' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Created At</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(receipt.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3 mt-8 pt-4 border-t border-gray-200">
        <button
          @click="$emit('close')"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Close
        </button>
        
        <button
          v-if="authStore.hasPermission('payment_receipts.edit') && receipt.can_be_edited"
          @click="$emit('edit', receipt)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Edit
        </button>

        <button
          v-if="authStore.hasPermission('payment_receipts.verify') && receipt.can_be_verified"
          @click="$emit('verify', receipt)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          Verify
        </button>

        <button
          v-if="authStore.hasPermission('payment_receipts.deposit') && receipt.can_be_deposited"
          @click="$emit('mark-as-deposited', receipt)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
        >
          Mark as Deposited
        </button>

        <button
          v-if="authStore.hasPermission('payment_receipts.delete') && receipt.can_be_deleted"
          @click="$emit('delete', receipt)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

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
  return new Date(date).toLocaleDateString();
};

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString();
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
  return types[type] || type;
};

const getPaymentMethodDisplay = (method) => {
  const methods = {
    cash: 'Cash',
    bank_transfer: 'Bank Transfer',
    check: 'Check',
    card: 'Card',
    online: 'Online Payment',
  };
  return methods[method] || method;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    verified: 'bg-blue-100 text-blue-800',
    deposited: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
