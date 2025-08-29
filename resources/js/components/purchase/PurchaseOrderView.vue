<template>
  <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
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
          <h3 class="text-sm font-medium text-red-800">Error loading purchase order</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Purchase Order Content -->
    <div v-else-if="purchaseOrder" class="bg-white shadow-lg rounded-lg overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 text-white">
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold">Purchase Order</h1>
            <p class="text-blue-100 mt-2">{{ purchaseOrder.po_number }}</p>
          </div>
          <div class="text-right">
            <div class="text-sm text-blue-100">Order Date</div>
            <div class="text-lg font-semibold">{{ formatDate(purchaseOrder.order_date) }}</div>
          </div>
        </div>
      </div>

      <div class="p-6">
        <!-- Purchase Order Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- Supplier Information -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Supplier Information</h3>
            <div class="space-y-2">
              <div><span class="font-medium">Name:</span> {{ purchaseOrder.supplier?.name }}</div>
              <div v-if="purchaseOrder.supplier?.company_name"><span class="font-medium">Company:</span> {{ purchaseOrder.supplier.company_name }}</div>
              <div v-if="purchaseOrder.supplier?.email"><span class="font-medium">Email:</span> {{ purchaseOrder.supplier.email }}</div>
              <div v-if="purchaseOrder.supplier?.phone"><span class="font-medium">Phone:</span> {{ purchaseOrder.supplier.phone }}</div>
              <div v-if="purchaseOrder.supplier?.address"><span class="font-medium">Address:</span> {{ purchaseOrder.supplier.address }}</div>
            </div>
          </div>

          <!-- Order Information -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Order Details</h3>
            <div class="space-y-2">
              <div><span class="font-medium">Order Date:</span> {{ formatDate(purchaseOrder.order_date) }}</div>
              <div v-if="purchaseOrder.expected_delivery_date"><span class="font-medium">Expected Delivery:</span> {{ formatDate(purchaseOrder.expected_delivery_date) }}</div>
              <div><span class="font-medium">Status:</span> 
                <span :class="getStatusClass(purchaseOrder.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2">
                  {{ formatStatus(purchaseOrder.status) }}
                </span>
              </div>
              <div><span class="font-medium">Created By:</span> {{ purchaseOrder.user?.name }}</div>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Ordered</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Received</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in purchaseOrder.purchase_order_items" :key="item.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ item.product?.name }}</div>
                    <div class="text-sm text-gray-500">{{ item.product?.sku }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.quantity_ordered }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.quantity_received || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.unit_cost) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.total_cost) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Order Totals -->
        <div class="flex justify-end mb-8">
          <div class="w-full max-w-sm">
            <div class="bg-gray-50 p-4 rounded-lg space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Subtotal:</span>
                <span class="text-sm font-medium">{{ formatCurrency(purchaseOrder.subtotal) }}</span>
              </div>
              <div v-if="purchaseOrder.tax_amount > 0" class="flex justify-between">
                <span class="text-sm text-gray-600">Tax:</span>
                <span class="text-sm font-medium">{{ formatCurrency(purchaseOrder.tax_amount) }}</span>
              </div>
              <div v-if="purchaseOrder.shipping_cost > 0" class="flex justify-between">
                <span class="text-sm text-gray-600">Shipping:</span>
                <span class="text-sm font-medium">{{ formatCurrency(purchaseOrder.shipping_cost) }}</span>
              </div>
              <div class="border-t pt-2 flex justify-between">
                <span class="text-base font-semibold">Total:</span>
                <span class="text-base font-semibold">{{ formatCurrency(purchaseOrder.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="purchaseOrder.notes" class="mt-8 p-4 bg-gray-50 rounded-lg">
          <h4 class="text-sm font-medium text-gray-900 mb-2">Notes</h4>
          <p class="text-sm text-gray-700">{{ purchaseOrder.notes }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
        <button @click="goBack" class="btn btn-secondary">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Orders
        </button>
        <div class="flex space-x-3">
          <button @click="editOrder" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Order
          </button>
          <button v-if="purchaseOrder.status === 'confirmed'" @click="receiveOrder" class="btn btn-success">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Receive Order
          </button>
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

const purchaseOrder = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchPurchaseOrder = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/purchase-orders/${route.params.id}`);
    purchaseOrder.value = response.data;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load purchase order';
    showToast('Error loading purchase order', 'error');
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

const formatStatus = (status) => {
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-yellow-100 text-yellow-800',
    partially_received: 'bg-orange-100 text-orange-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const goBack = () => {
  router.push('/purchase/orders');
};

const editOrder = () => {
  router.push(`/purchase/orders/${route.params.id}/edit`);
};

const receiveOrder = () => {
  // Navigate to receive order functionality
  showToast('Receive order functionality coming soon', 'info');
};

onMounted(() => {
  fetchPurchaseOrder();
});
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  text-decoration: none;
}

.btn-primary {
  background: #6366f1;
  color: white;
}

.btn-primary:hover {
  background: #5855eb;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}
</style>
