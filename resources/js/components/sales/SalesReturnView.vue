<template>
  <div class="w-full max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <div class="flex items-center space-x-3">
        <button
          @click="goBack"
          class="p-2 border border-slate-200 dark:border-zinc-800 rounded-lg text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-900 transition-colors cursor-pointer"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        <div>
          <div class="flex items-center space-x-2">
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">
              Return {{ saleReturn?.sale_number }}
            </h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 uppercase tracking-wider">
              Refunded
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-zinc-400">Processed on {{ formatDate(saleReturn?.sale_date) }}</p>
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <button
          @click="printReturn"
          class="inline-flex items-center px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 shadow-sm transition-all cursor-pointer"
        >
          <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          <span>Print Return</span>
        </button>

        <button
          @click="editReturn"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center space-x-1.5 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          <span>Edit Return</span>
        </button>
      </div>
    </div>

    <!-- Main Content Container -->
    <div v-if="isLoading" class="p-12 text-center text-slate-400">
      <svg class="animate-spin h-8 w-8 mx-auto text-blue-600 mb-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Loading return details...
    </div>

    <div v-else-if="saleReturn" class="space-y-6">
      <!-- Info Header Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Customer Info -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-2">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Customer Details</span>
          <div class="text-sm font-bold text-slate-900 dark:text-zinc-100">
            {{ saleReturn.customer?.name || 'Walk-in Customer' }}
          </div>
          <div class="text-xs text-slate-500 dark:text-zinc-400 space-y-1">
            <div v-if="saleReturn.customer?.phone">Phone: {{ saleReturn.customer.phone }}</div>
            <div v-if="saleReturn.customer?.email">Email: {{ saleReturn.customer.email }}</div>
          </div>
        </div>

        <!-- Return Overview -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-2">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Refund Method & Reason</span>
          <div class="flex items-center space-x-2">
            <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300">
              {{ saleReturn.payment_method }}
            </span>
          </div>
          <p class="text-xs text-slate-600 dark:text-zinc-400 capitalize">
            Reason: {{ formatReason(saleReturn.notes || 'Customer Return') }}
          </p>
        </div>

        <!-- Original Sale Reference -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-2">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Reference Invoice</span>
          <div v-if="saleReturn.original_sale || saleReturn.original_sale_id" class="text-sm font-bold text-blue-600 dark:text-blue-400">
            Invoice #{{ saleReturn.original_sale?.sale_number || saleReturn.original_sale_id }}
          </div>
          <div v-else class="text-xs text-slate-400 italic">
            Direct / Standalone Return
          </div>
          <div class="text-xs text-slate-500">
            Target Warehouse: <span class="font-semibold text-slate-700 dark:text-zinc-300">{{ saleReturn.warehouse?.name || 'Default Warehouse' }}</span>
          </div>
        </div>
      </div>

      <!-- Itemized Table -->
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-soft">
        <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center">
          <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Returned Line Items</h2>
          <span class="text-xs text-slate-400">{{ (saleReturn.sale_items || saleReturn.saleItems || []).length }} items</span>
        </div>

        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-zinc-800/60 text-slate-500 dark:text-zinc-400 font-bold uppercase border-b border-slate-200 dark:border-zinc-700">
              <th class="py-3 px-4">Item Name</th>
              <th class="py-3 px-4 text-center w-28">Returned Qty</th>
              <th class="py-3 px-4 text-right w-32">Unit Price</th>
              <th class="py-3 px-4 text-right w-32">Tax Amount</th>
              <th class="py-3 px-4 text-right w-36">Total Refund</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
            <tr v-for="item in (saleReturn.sale_items || saleReturn.saleItems || [])" :key="item.id">
              <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-zinc-200">
                {{ item.product?.name || item.description || 'Returned Item' }}
              </td>
              <td class="py-3.5 px-4 text-center font-bold text-slate-700 dark:text-zinc-300">
                {{ Math.abs(item.quantity) }}
              </td>
              <td class="py-3.5 px-4 text-right font-medium text-slate-600 dark:text-zinc-400">
                {{ formatMoney(item.unit_price) }}
              </td>
              <td class="py-3.5 px-4 text-right font-medium text-slate-600 dark:text-zinc-400">
                {{ formatMoney(item.tax_amount) }}
              </td>
              <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 dark:text-zinc-100">
                {{ formatMoney(Math.abs(item.total_amount)) }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Totals Card Footer -->
        <div class="p-6 bg-slate-50/50 dark:bg-zinc-900/50 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
          <div class="w-72 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Subtotal</span>
              <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(Math.abs(saleReturn.subtotal)) }}</span>
            </div>
            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Tax Reversal</span>
              <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(Math.abs(saleReturn.tax_amount)) }}</span>
            </div>
            <div class="border-t border-slate-200 dark:border-zinc-700 pt-2 flex justify-between items-center text-sm font-extrabold">
              <span class="text-slate-800 dark:text-zinc-200">Total Refund</span>
              <span class="text-rose-600 dark:text-rose-400 text-lg">{{ formatMoney(Math.abs(saleReturn.total_amount)) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCurrencyStore } from '@/stores/currency'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const currencyStore = useCurrencyStore()

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$'
})

const saleReturn = ref(null)
const isLoading = ref(true)

const formatMoney = (val) => {
  const num = Number(val || 0)
  return `${currencySymbol.value} ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatReason = (reason) => {
  if (!reason) return 'Customer Return'
  return reason.replace(/_/g, ' ')
}

const goBack = () => {
  router.push('/sales/returns')
}

const editReturn = () => {
  router.push(`/sales/returns/${route.params.id}/edit`)
}

const printReturn = () => {
  router.push(`/sales/returns/${route.params.id}/print`)
}

const fetchReturnDetails = async () => {
  isLoading.value = true
  try {
    if (!currencyStore.currencies.length) {
      currencyStore.fetchCurrencies()
    }
    const res = await axios.get(`/api/sales/returns/${route.params.id}`)
    saleReturn.value = res.data
  } catch (err) {
    console.error('Failed to fetch return details:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchReturnDetails()
})
</script>
