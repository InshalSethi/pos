<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-medium text-gray-900">Payment Details</h3>
          <p class="text-sm text-gray-600">{{ payment.payment_number }}</p>
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

      <!-- Payment Information -->
      <div class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Basic Information -->
          <div class="space-y-4">
            <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Basic Information</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Number</label>
              <p class="mt-1 text-sm text-gray-900">{{ payment.payment_number }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Type</label>
              <p class="mt-1 text-sm text-gray-900">{{ getPaymentTypeDisplay(payment.payment_type) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Amount</label>
              <p class="mt-1 text-lg font-semibold text-gray-900">${{ formatAmount(payment.amount) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Date</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(payment.payment_date) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Method</label>
              <p class="mt-1 text-sm text-gray-900">{{ getPaymentMethodDisplay(payment.payment_method) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <span :class="getStatusBadgeClass(payment.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
              </span>
            </div>
          </div>

          <!-- Payee Information -->
          <div class="space-y-4">
            <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Payee Information</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Payee Name</label>
              <p class="mt-1 text-sm text-gray-900">{{ payment.payee_name }}</p>
            </div>

            <div v-if="payment.payee_type">
              <label class="block text-sm font-medium text-gray-700">Payee Type</label>
              <p class="mt-1 text-sm text-gray-900">{{ payment.payee_type.charAt(0).toUpperCase() + payment.payee_type.slice(1) }}</p>
            </div>

            <div v-if="payment.bank_account">
              <label class="block text-sm font-medium text-gray-700">Bank Account</label>
              <p class="mt-1 text-sm text-gray-900">
                {{ payment.bank_account.account_name }} - {{ payment.bank_account.bank_name }}
                <br>
                <span class="text-gray-600">{{ payment.bank_account.account_number }}</span>
              </p>
            </div>

            <div v-if="payment.reference_number">
              <label class="block text-sm font-medium text-gray-700">Reference Number</label>
              <p class="mt-1 text-sm text-gray-900">{{ payment.reference_number }}</p>
            </div>
          </div>
        </div>

        <!-- Description and Notes -->
        <div class="mt-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <p class="mt-1 text-sm text-gray-900">{{ payment.description }}</p>
          </div>

          <div v-if="payment.notes">
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <p class="mt-1 text-sm text-gray-900">{{ payment.notes }}</p>
          </div>
        </div>

        <!-- Approval Information -->
        <div v-if="payment.approved_by || payment.approved_at" class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Approval Information</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="payment.approved_by">
              <label class="block text-sm font-medium text-gray-700">Approved By</label>
              <p class="mt-1 text-sm text-gray-900">{{ payment.approved_by.name }}</p>
            </div>

            <div v-if="payment.approved_at">
              <label class="block text-sm font-medium text-gray-700">Approved At</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(payment.approved_at) }}</p>
            </div>
          </div>

          <div v-if="payment.approval_notes">
            <label class="block text-sm font-medium text-gray-700">Approval Notes</label>
            <p class="mt-1 text-sm text-gray-900">{{ payment.approval_notes }}</p>
          </div>
        </div>

        <!-- Payment Information -->
        <div v-if="payment.paid_by || payment.paid_at" class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Payment Information</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="payment.paid_by">
              <label class="block text-sm font-medium text-gray-700">Paid By</label>
              <p class="mt-1 text-sm text-gray-900">{{ payment.paid_by.name }}</p>
            </div>

            <div v-if="payment.paid_at">
              <label class="block text-sm font-medium text-gray-700">Paid At</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(payment.paid_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Journal Entry Information -->
        <div v-if="payment.journal_entry" class="mt-6 space-y-4">
          <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Accounting Information</h4>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Journal Entry</label>
            <p class="mt-1 text-sm text-gray-900">{{ payment.journal_entry.entry_number }}</p>
          </div>

          <div v-if="payment.journal_entry.journal_entry_lines && payment.journal_entry.journal_entry_lines.length > 0">
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
                  <tr v-for="line in payment.journal_entry.journal_entry_lines" :key="line.id">
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
              <p class="mt-1 text-sm text-gray-900">{{ payment.created_by ? payment.created_by.name : 'N/A' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Created At</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(payment.created_at) }}</p>
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
          v-if="authStore.hasPermission('payments.edit') && payment.can_be_edited"
          @click="$emit('edit', payment)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Edit
        </button>

        <button
          v-if="authStore.hasPermission('payments.approve') && payment.can_be_approved"
          @click="$emit('approve', payment)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          Approve
        </button>

        <button
          v-if="authStore.hasPermission('payments.pay') && payment.can_be_paid"
          @click="$emit('mark-as-paid', payment)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
        >
          Mark as Paid
        </button>

        <button
          v-if="authStore.hasPermission('payments.delete') && payment.can_be_deleted"
          @click="$emit('delete', payment)"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    required: true
  }
});

// Emits
const emit = defineEmits(['close', 'edit', 'approve', 'mark-as-paid', 'delete']);

// Utility functions
const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString();
};

const formatAmount = (amount) => {
  return parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getPaymentTypeDisplay = (type) => {
  const types = {
    supplier_payment: 'Supplier Payment',
    expense_payment: 'Expense Payment',
    salary_payment: 'Salary Payment',
    sale_return_payment: 'Sale Return Payment',
    purchase_invoice_payment: 'Purchase Invoice Payment',
    other_payment: 'Other Payment',
  };
  return types[type] || type;
};

const getPaymentMethodDisplay = (method) => {
  const methods = {
    cash: 'Cash',
    bank_transfer: 'Bank Transfer',
    check: 'Check',
    card: 'Card',
  };
  return methods[method] || method;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
