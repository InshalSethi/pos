<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h3 class="text-lg font-medium text-gray-900">Expense Details</h3>
          <p class="text-sm text-gray-500">{{ expense.expense_number }}</p>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Expense Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Basic Info -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <p class="mt-1 text-sm text-gray-900">{{ expense.title }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Category</label>
            <p class="mt-1 text-sm text-gray-900">{{ expense.category?.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Amount</label>
            <p class="mt-1 text-lg font-semibold text-gray-900">${{ parseFloat(expense.amount).toFixed(2) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Expense Date</label>
            <p class="mt-1 text-sm text-gray-900">{{ formatDate(expense.expense_date) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <span :class="getStatusClass(expense.status)" class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
              {{ getStatusText(expense.status) }}
            </span>
          </div>
        </div>

        <!-- Additional Info -->
        <div class="space-y-4">
          <div v-if="expense.employee">
            <label class="block text-sm font-medium text-gray-700">Employee</label>
            <p class="mt-1 text-sm text-gray-900">{{ expense.employee.full_name }}</p>
          </div>
          <div v-if="expense.vendor_name">
            <label class="block text-sm font-medium text-gray-700">Vendor</label>
            <p class="mt-1 text-sm text-gray-900">{{ expense.vendor_name }}</p>
          </div>
          <div v-if="expense.reference_number">
            <label class="block text-sm font-medium text-gray-700">Reference Number</label>
            <p class="mt-1 text-sm text-gray-900">{{ expense.reference_number }}</p>
          </div>
          <div v-if="expense.payment_method">
            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
            <p class="mt-1 text-sm text-gray-900">{{ getPaymentMethodText(expense.payment_method) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Created By</label>
            <p class="mt-1 text-sm text-gray-900">{{ expense.user?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div v-if="expense.description" class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ expense.description }}</p>
      </div>

      <!-- Notes -->
      <div v-if="expense.notes" class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ expense.notes }}</p>
      </div>

      <!-- Workflow Information -->
      <div v-if="expense.status !== 'draft'" class="mb-6">
        <h4 class="text-md font-medium text-gray-900 mb-3">Workflow History</h4>
        <div class="space-y-3">
          <div v-if="expense.submitted_at" class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Submitted for approval</p>
              <p class="text-sm text-gray-500">{{ formatDateTime(expense.submitted_at) }} by {{ expense.submitted_by?.name }}</p>
            </div>
          </div>

          <div v-if="expense.approved_at" class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Approved</p>
              <p class="text-sm text-gray-500">{{ formatDateTime(expense.approved_at) }} by {{ expense.approved_by?.name }}</p>
              <p v-if="expense.approval_notes" class="text-sm text-gray-600 mt-1">{{ expense.approval_notes }}</p>
            </div>
          </div>

          <div v-if="expense.rejected_at" class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Rejected</p>
              <p class="text-sm text-gray-500">{{ formatDateTime(expense.rejected_at) }} by {{ expense.rejected_by?.name }}</p>
              <p v-if="expense.rejection_reason" class="text-sm text-gray-600 mt-1">{{ expense.rejection_reason }}</p>
            </div>
          </div>

          <div v-if="expense.paid_at" class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                </svg>
              </div>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Paid</p>
              <p class="text-sm text-gray-500">{{ formatDateTime(expense.paid_at) }} by {{ expense.paid_by?.name }}</p>
              <p v-if="expense.payment_reference" class="text-sm text-gray-600 mt-1">Reference: {{ expense.payment_reference }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-between items-center pt-6 border-t">
        <div class="flex space-x-3">
          <button
            v-if="canEdit"
            @click="$emit('edit')"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Edit
          </button>
          <button
            v-if="canSubmit"
            @click="submitExpense"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
          >
            Submit for Approval
          </button>
        </div>

        <div class="flex space-x-3">
          <button
            v-if="canApprove"
            @click="showApprovalModal = true"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
          >
            Approve
          </button>
          <button
            v-if="canReject"
            @click="showRejectionModal = true"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Reject
          </button>
          <button
            v-if="canPay"
            @click="showPaymentModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Mark as Paid
          </button>
          <button
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-60">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Approve Expense</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Approval Notes (Optional)</label>
          <textarea
            v-model="approvalNotes"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Add approval notes..."
          ></textarea>
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showApprovalModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="approveExpense"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
          >
            Approve
          </button>
        </div>
      </div>
    </div>

    <!-- Rejection Modal -->
    <div v-if="showRejectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-60">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Expense</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason *</label>
          <textarea
            v-model="rejectionReason"
            rows="3"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Please provide a reason for rejection..."
          ></textarea>
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showRejectionModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="rejectExpense"
            :disabled="!rejectionReason.trim()"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
          >
            Reject
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-60">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Mark as Paid</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Payment Reference (Optional)</label>
          <input
            v-model="paymentReference"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Transaction ID, check number, etc."
          />
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showPaymentModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="markAsPaid"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Mark as Paid
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

// Props and Emits
const props = defineProps({
  expense: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'edit', 'approve', 'reject', 'pay']);

// Reactive data
const showApprovalModal = ref(false);
const showRejectionModal = ref(false);
const showPaymentModal = ref(false);
const approvalNotes = ref('');
const rejectionReason = ref('');
const paymentReference = ref('');

// Computed
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

// Methods
const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString();
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    paid: 'bg-blue-100 text-blue-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
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
    petty_cash: 'Petty Cash'
  };
  return methods[method] || method;
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

const markAsPaid = async () => {
  try {
    await axios.post(`/api/expenses/${props.expense.id}/mark-as-paid`, {
      payment_reference: paymentReference.value
    });
    showPaymentModal.value = false;
    emit('pay');
  } catch (error) {
    console.error('Error marking expense as paid:', error);
  }
};
</script>
