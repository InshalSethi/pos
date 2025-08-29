<template>
  <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error loading invoice</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Invoice Content -->
      <div v-else-if="invoice" class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8 text-white">
          <div class="flex justify-between items-start">
            <div>
              <h1 class="text-3xl font-bold">Sales Invoice</h1>
              <p class="text-indigo-100 mt-2">{{ invoice.sale_number }}</p>
            </div>
            <div class="text-right">
              <div class="text-sm text-indigo-100">Date</div>
              <div class="text-lg font-semibold">{{ formatDate(invoice.sale_date) }}</div>
            </div>
          </div>
        </div>

        <!-- Invoice Details -->
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Customer Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">Customer Information</h3>
              <div v-if="invoice.customer">
                <p class="font-medium text-gray-900">{{ invoice.customer.name }}</p>
                <p class="text-gray-600" v-if="invoice.customer.email">{{ invoice.customer.email }}</p>
                <p class="text-gray-600" v-if="invoice.customer.phone">{{ invoice.customer.phone }}</p>
                <div v-if="invoice.customer.address" class="mt-2 text-gray-600">
                  <p>{{ invoice.customer.address }}</p>
                  <p v-if="invoice.customer.city || invoice.customer.state">
                    {{ invoice.customer.city }}{{ invoice.customer.city && invoice.customer.state ? ', ' : '' }}{{ invoice.customer.state }}
                  </p>
                </div>
              </div>
              <div v-else>
                <p class="text-gray-600">Walk-in Customer</p>
              </div>
            </div>

            <!-- Invoice Summary -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">Invoice Summary</h3>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-600">Status:</span>
                  <span :class="getStatusClass(invoice.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ invoice.status }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Payment Method:</span>
                  <span class="font-medium">{{ formatPaymentMethod(invoice.payment_method) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Cashier:</span>
                  <span class="font-medium">{{ invoice.user?.name || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Items</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in invoice.sale_items" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ item.product?.name || 'Unknown Product' }}</div>
                      <div class="text-sm text-gray-500" v-if="item.product?.sku">SKU: {{ item.product.sku }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.unit_price) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.discount_amount || 0) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(item.total_price) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Totals -->
          <div class="border-t border-gray-200 pt-6">
            <div class="flex justify-end">
              <div class="w-full max-w-sm space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium">{{ formatCurrency(invoice.subtotal) }}</span>
                </div>
                <div v-if="invoice.discount_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Discount:</span>
                  <span class="font-medium text-red-600">-{{ formatCurrency(invoice.discount_amount) }}</span>
                </div>
                <div v-if="invoice.tax_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Tax:</span>
                  <span class="font-medium">{{ formatCurrency(invoice.tax_amount) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3">
                  <span>Total:</span>
                  <span>{{ formatCurrency(invoice.total_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Paid:</span>
                  <span class="font-medium text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
                </div>
                <div v-if="invoice.change_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Change:</span>
                  <span class="font-medium">{{ formatCurrency(invoice.change_amount) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="invoice.notes" class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Notes</h4>
            <p class="text-sm text-gray-700">{{ invoice.notes }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
          <button @click="goBack" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Invoices
          </button>
          <div class="flex space-x-3">
            <button @click="printInvoice" class="btn btn-primary">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print
            </button>
            <button v-if="invoice.status === 'completed'" @click="processReturn" class="btn btn-warning">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
              </svg>
              Process Return
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const { showToast } = useToast();

const invoice = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchInvoice = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/sales/${route.params.id}`);
    invoice.value = response.data;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load invoice';
    showToast('Error loading invoice', 'error');
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0);
};

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    card: 'Card',
    bank_transfer: 'Bank Transfer',
    mobile_payment: 'Mobile Payment',
    mixed: 'Mixed Payment'
  };
  return methods[method] || method;
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const goBack = () => {
  router.push('/sales/invoices');
};

const printInvoice = () => {
  const printUrl = router.resolve(`/sales/invoices/${route.params.id}/print`).href;
  window.open(printUrl, '_blank');
};

const processReturn = () => {
  router.push(`/sales/returns?original_invoice=${invoice.value.id}`);
};

onMounted(() => {
  fetchInvoice();
});
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #4f46e5;
  color: white;
}

.btn-primary:hover {
  background: #4338ca;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover {
  background: #d97706;
}

@media print {
  .btn, .bg-gray-50:last-child {
    display: none !important;
  }
  
  .shadow-lg {
    box-shadow: none !important;
  }
  
  .bg-gradient-to-r {
    background: #4f46e5 !important;
    -webkit-print-color-adjust: exact;
  }
}
</style>
