<template>
  <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950 min-h-screen">
    <!-- Header Bar / Back Navigation -->
    <div class="flex justify-between items-center mb-6 print:hidden">
      <router-link
        to="/purchase/returns"
        class="inline-flex items-center text-xs font-bold text-slate-600 dark:text-zinc-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
      >
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Purchase Returns
      </router-link>

      <div class="flex items-center space-x-2">
        <router-link
          v-if="purchaseReturn && ['draft', 'pending'].includes(purchaseReturn.status)"
          :to="`/purchase/returns/${purchaseReturn.id}/edit`"
          class="px-3.5 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 text-xs font-bold rounded-lg transition-all"
        >
          Edit Return
        </router-link>
        <button
          @click="printDebitNote"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all flex items-center space-x-1.5 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          <span>Print Debit Note</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white dark:bg-zinc-900 shadow-lg rounded-2xl p-12 text-center text-slate-400">
      <svg class="animate-spin h-8 w-8 mx-auto text-blue-600 mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
      <span>Loading purchase return details...</span>
    </div>

    <!-- Main Debit Note Invoice Card -->
    <div v-else-if="purchaseReturn" class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xl rounded-2xl overflow-hidden print:shadow-none print:border-none">
      
      <!-- Top Banner Header -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 px-8 py-8 text-white flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div>
          <span class="px-2.5 py-1 bg-blue-500/20 text-blue-300 text-[10px] font-bold uppercase tracking-wider rounded-md border border-blue-400/30 inline-block mb-2">
            Debit Note / Vendor Return
          </span>
          <h1 class="text-3xl font-extrabold tracking-tight">{{ purchaseReturn.return_number }}</h1>
          <p class="text-xs text-slate-300 mt-1">Issued Date: {{ formatDate(purchaseReturn.return_date) }}</p>
        </div>
        <div class="mt-4 sm:mt-0 text-left sm:text-right">
          <div class="text-xs text-slate-400 font-medium">Grand Return Total</div>
          <div class="text-3xl font-black text-blue-400">{{ currencySymbol }}{{ formatCurrency(purchaseReturn.total_amount) }}</div>
        </div>
      </div>

      <div class="p-8">
        
        <!-- Reason for Return Banner -->
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-xl p-4 mb-8 flex items-center space-x-3 text-xs text-amber-800 dark:text-amber-300">
          <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <div>
            <span class="font-bold uppercase tracking-wider text-[10px]">Reason for Return:</span>
            <span class="ml-1.5 font-bold">{{ purchaseReturn.reason }}</span>
          </div>
        </div>

        <!-- Supplier & Return Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          
          <!-- Supplier Information -->
          <div class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-xl border border-slate-100 dark:border-zinc-800">
            <h3 class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-3">Supplier Details</h3>
            <div class="space-y-1.5 text-xs">
              <div class="font-extrabold text-slate-900 dark:text-zinc-100 text-sm">{{ purchaseReturn.supplier?.name || 'N/A' }}</div>
              <div v-if="purchaseReturn.supplier?.company_name" class="text-slate-600 dark:text-zinc-400">
                <span class="font-medium">Company:</span> {{ purchaseReturn.supplier.company_name }}
              </div>
              <div v-if="purchaseReturn.supplier?.email" class="text-slate-600 dark:text-zinc-400">
                <span class="font-medium">Email:</span> {{ purchaseReturn.supplier.email }}
              </div>
              <div v-if="purchaseReturn.supplier?.phone" class="text-slate-600 dark:text-zinc-400">
                <span class="font-medium">Phone:</span> {{ purchaseReturn.supplier.phone }}
              </div>
              <div v-if="purchaseReturn.supplier?.address" class="text-slate-600 dark:text-zinc-400">
                <span class="font-medium">Address:</span> {{ purchaseReturn.supplier.address }}
              </div>
            </div>
          </div>

          <!-- Return Metadata -->
          <div class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-xl border border-slate-100 dark:border-zinc-800">
            <h3 class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-3">Reference Information</h3>
            <div class="space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-slate-500">PO Reference:</span>
                <span class="font-mono font-bold text-slate-800 dark:text-zinc-200">
                  {{ purchaseReturn.original_purchase_order?.po_number || purchaseReturn.purchase_order?.po_number || 'Standalone Return' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Warehouse:</span>
                <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ purchaseReturn.warehouse?.name || 'Main Warehouse' }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-500">Return Status:</span>
                <span :class="getStatusBadgeClass(purchaseReturn.status)" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize">
                  {{ purchaseReturn.status }}
                </span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-500">Refund Status:</span>
                <span :class="getRefundBadgeClass(purchaseReturn.refund_status)" class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider">
                  {{ purchaseReturn.refund_status || 'pending' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Processed By:</span>
                <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ purchaseReturn.user?.name || 'System Admin' }}</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Returned Items Table -->
        <div class="mb-8 overflow-hidden rounded-xl border border-slate-200 dark:border-zinc-800">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-100 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 uppercase font-bold tracking-wider border-b border-slate-200 dark:border-zinc-800">
                <th class="py-3.5 px-4 w-12 text-center">#</th>
                <th class="py-3.5 px-4">Product Description</th>
                <th class="py-3.5 px-4 text-center">Unit Cost</th>
                <th class="py-3.5 px-4 text-center">Qty Returned</th>
                <th class="py-3.5 px-4 text-center">Tax</th>
                <th class="py-3.5 px-4 text-center">Discount</th>
                <th class="py-3.5 px-4 text-right">Line Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
              <tr v-for="(item, idx) in purchaseReturn.items" :key="item.id || idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30">
                <td class="py-3.5 px-4 text-center text-slate-400 font-bold">{{ idx + 1 }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-800 dark:text-zinc-200">{{ item.product?.name || 'Product' }}</div>
                  <div class="text-[10px] text-slate-400 font-mono" v-if="item.product?.sku">SKU: {{ item.product.sku }}</div>
                </td>
                <td class="py-3.5 px-4 text-center text-slate-700 dark:text-zinc-300">
                  {{ currencySymbol }}{{ formatCurrency(item.unit_cost) }}
                </td>
                <td class="py-3.5 px-4 text-center font-bold text-slate-900 dark:text-zinc-100">
                  {{ item.quantity }}
                </td>
                <td class="py-3.5 px-4 text-center text-amber-600">
                  +{{ currencySymbol }}{{ formatCurrency(item.tax_amount) }}
                </td>
                <td class="py-3.5 px-4 text-center text-emerald-600">
                  -{{ currencySymbol }}{{ formatCurrency(item.discount_amount) }}
                </td>
                <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-zinc-100">
                  {{ currencySymbol }}{{ formatCurrency(item.total_cost || ((item.unit_cost * item.quantity) + (item.tax_amount || 0) - (item.discount_amount || 0))) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Summary Totals Card -->
        <div class="flex justify-end mb-8">
          <div class="w-full sm:w-80 bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-xl border border-slate-200 dark:border-zinc-800 space-y-2.5 text-xs">
            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Subtotal</span>
              <span class="font-bold">{{ currencySymbol }}{{ formatCurrency(purchaseReturn.subtotal || calculateSubtotal(purchaseReturn.items)) }}</span>
            </div>
            <div class="flex justify-between text-amber-600">
              <span>Tax Total</span>
              <span class="font-bold">+{{ currencySymbol }}{{ formatCurrency(purchaseReturn.tax_amount) }}</span>
            </div>
            <div class="flex justify-between text-emerald-600">
              <span>Discount Total</span>
              <span class="font-bold">-{{ currencySymbol }}{{ formatCurrency(purchaseReturn.discount_amount) }}</span>
            </div>
            <div class="border-t border-slate-200 dark:border-zinc-700 pt-2.5 flex justify-between text-sm font-black text-slate-900 dark:text-zinc-100">
              <span>Grand Total</span>
              <span class="text-blue-600 dark:text-blue-400">{{ currencySymbol }}{{ formatCurrency(purchaseReturn.total_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Notes / Terms -->
        <div v-if="purchaseReturn.notes" class="bg-slate-50 dark:bg-zinc-800/30 p-4 rounded-xl border border-slate-100 dark:border-zinc-800 text-xs">
          <div class="font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider text-[10px] mb-1">Notes &amp; Remarks</div>
          <div class="text-slate-700 dark:text-zinc-300 whitespace-pre-line">{{ purchaseReturn.notes }}</div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const route = useRoute();
const authStore = useAuthStore();
const currencySymbol = computed(() => authStore.currencySymbol || '$');

const purchaseReturn = ref(null);
const loading = ref(true);

const fetchReturnDetails = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/purchase-returns/${route.params.id}`);
    purchaseReturn.value = res.data;
  } catch (err) {
    console.error('Error loading purchase return details:', err);
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatCurrency = (val) => {
  return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const calculateSubtotal = (items) => {
  if (!items) return 0;
  return items.reduce((sum, item) => sum + ((parseFloat(item.unit_cost) || 0) * (parseFloat(item.quantity) || 0)), 0);
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
    case 'pending':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50';
    case 'approved':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50';
    case 'completed':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50';
    case 'cancelled':
      return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50';
    default:
      return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
  }
};

const getRefundBadgeClass = (status) => {
  switch (status) {
    case 'refunded':
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
    case 'partial':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
    default:
      return 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400';
  }
};

const printDebitNote = () => {
  window.print();
};

onMounted(() => {
  fetchReturnDetails();
});
</script>
