<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" v-if="show">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h3 class="text-2xl font-bold text-gray-900">Supplier Ledger</h3>
          <p class="text-gray-600">{{ supplier?.name || supplier?.company_name }}</p>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Date Range Filter -->
      <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <div class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input
              type="date"
              v-model="dateRange.start"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input
              type="date"
              v-model="dateRange.end"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <button
            @click="loadLedgerData"
            :disabled="loading"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 disabled:opacity-50"
          >
            {{ loading ? 'Loading...' : 'Apply Filter' }}
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" v-if="ledgerData">
        <div class="bg-blue-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-blue-800">Opening Balance</h4>
          <p class="text-2xl font-bold text-blue-900">${{ formatAmount(ledgerData.opening_balance) }}</p>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-orange-800">Total Purchases</h4>
          <p class="text-2xl font-bold text-orange-900">${{ formatAmount(ledgerData.summary?.total_purchases || 0) }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-purple-800">Total Payments</h4>
          <p class="text-2xl font-bold text-purple-900">${{ formatAmount(ledgerData.summary?.total_payments || 0) }}</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-gray-800">Closing Balance</h4>
          <p class="text-2xl font-bold text-gray-900">${{ formatAmount(ledgerData.closing_balance) }}</p>
        </div>
      </div>

      <!-- Ledger Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
          <h4 class="text-lg font-medium text-gray-900">Transaction History</h4>
        </div>
        
        <div v-if="loading" class="p-8 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-600">Loading ledger data...</p>
        </div>

        <div v-else-if="!ledgerData?.transactions?.length" class="p-8 text-center text-gray-500">
          No transactions found for the selected period.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="transaction in ledgerData.transactions" :key="transaction.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(transaction.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ transaction.reference }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ transaction.description }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTypeClass(transaction.type)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ transaction.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                  {{ transaction.debit ? '$' + formatAmount(transaction.debit) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                  {{ transaction.credit ? '$' + formatAmount(transaction.credit) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                  ${{ formatAmount(transaction.running_balance) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-6 flex justify-end space-x-3">
        <button
          @click="exportLedger"
          class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700"
        >
          Export PDF
        </button>
        <button
          @click="$emit('close')"
          class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  supplier: Object
});

const emit = defineEmits(['close']);

// Reactive data
const loading = ref(false);
const ledgerData = ref(null);
const dateRange = ref({
  start: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0]
});

// Methods
const loadLedgerData = async () => {
  if (!props.supplier?.id) return;
  
  loading.value = true;
  try {
    const response = await axios.get(`/api/suppliers/${props.supplier.id}/ledger`, {
      params: {
        start_date: dateRange.value.start,
        end_date: dateRange.value.end
      }
    });
    ledgerData.value = response.data;
  } catch (error) {
    console.error('Error loading supplier ledger data:', error);
  } finally {
    loading.value = false;
  }
};

const formatAmount = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const getTypeClass = (type) => {
  const classes = {
    'Purchase': 'bg-orange-100 text-orange-800',
    'Payment': 'bg-blue-100 text-blue-800',
    'Return': 'bg-red-100 text-red-800',
    'Adjustment': 'bg-yellow-100 text-yellow-800'
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const exportLedger = () => {
  // TODO: Implement PDF export
  console.log('Export supplier ledger functionality to be implemented');
};

// Watchers
watch(() => props.show, (newVal) => {
  if (newVal && props.supplier) {
    loadLedgerData();
  }
});

watch(() => props.supplier, (newSupplier) => {
  if (newSupplier && props.show) {
    loadLedgerData();
  }
});
</script>
