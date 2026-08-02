<template>
  <div class="min-h-screen bg-slate-100 dark:bg-zinc-950 p-4 sm:p-8 flex flex-col items-center">
    <!-- Non-printable Top Bar -->
    <div class="w-full max-w-3xl mb-6 flex justify-between items-center print:hidden">
      <button
        @click="goBack"
        class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-slate-50 shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Returns
      </button>

      <button
        @click="triggerPrint"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-md transition-all flex items-center space-x-1.5 cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Print Credit Note</span>
      </button>
    </div>

    <!-- Printable Receipt / Credit Note Sheet -->
    <div v-if="saleReturn" class="w-full max-w-3xl bg-white dark:bg-zinc-900 text-slate-900 dark:text-zinc-100 p-8 rounded-xl shadow-lg border border-slate-200 dark:border-zinc-800 print:shadow-none print:border-none print:p-0 print:m-0 print:w-full print:max-w-none">
      
      <!-- Document Header -->
      <div class="flex justify-between items-start border-b border-slate-200 dark:border-zinc-800 pb-6 mb-6">
        <div>
          <h2 class="text-xl font-extrabold tracking-tight uppercase text-blue-600 dark:text-blue-400">
            {{ companyName || 'POS Accounting' }}
          </h2>
          <p class="text-xs text-slate-500 mt-1">Official Sales Return Credit Note</p>
        </div>

        <div class="text-right">
          <span class="inline-block px-3 py-1 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-xs font-black rounded uppercase tracking-wider mb-1">
            SALES RETURN
          </span>
          <div class="text-base font-extrabold text-slate-800 dark:text-zinc-100">
            {{ saleReturn.sale_number }}
          </div>
          <div class="text-xs text-slate-500">
            Date: {{ formatDate(saleReturn.sale_date) }}
          </div>
        </div>
      </div>

      <!-- Info Grid -->
      <div class="grid grid-cols-2 gap-6 text-xs mb-8">
        <div class="bg-slate-50 dark:bg-zinc-800/40 p-4 rounded-lg border border-slate-100 dark:border-zinc-800">
          <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px] block mb-1">Returned By (Customer)</span>
          <div class="font-bold text-slate-800 dark:text-zinc-200 text-sm">
            {{ saleReturn.customer?.name || 'Walk-in Customer' }}
          </div>
          <div v-if="saleReturn.customer?.phone" class="text-slate-500">Phone: {{ saleReturn.customer.phone }}</div>
          <div v-if="saleReturn.customer?.email" class="text-slate-500">Email: {{ saleReturn.customer.email }}</div>
        </div>

        <div class="bg-slate-50 dark:bg-zinc-800/40 p-4 rounded-lg border border-slate-100 dark:border-zinc-800">
          <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px] block mb-1">Transaction Details</span>
          <div v-if="saleReturn.original_sale || saleReturn.original_sale_id" class="text-slate-700 dark:text-zinc-300 font-semibold">
            Original Sale: #{{ saleReturn.original_sale?.sale_number || saleReturn.original_sale_id }}
          </div>
          <div class="text-slate-700 dark:text-zinc-300 capitalize">
            Refund Method: <span class="font-bold">{{ saleReturn.payment_method }}</span>
          </div>
          <div class="text-slate-500">
            Warehouse: {{ saleReturn.warehouse?.name || 'Main Warehouse' }}
          </div>
        </div>
      </div>

      <!-- Itemized Table -->
      <div class="mb-8 overflow-hidden rounded-lg border border-slate-200 dark:border-zinc-800">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100 dark:bg-zinc-800/80 text-slate-600 dark:text-zinc-300 font-bold uppercase border-b border-slate-200 dark:border-zinc-700">
              <th class="py-3 px-4">Item Description</th>
              <th class="py-3 px-4 text-center w-24">Returned Qty</th>
              <th class="py-3 px-4 text-right w-28">Unit Price</th>
              <th class="py-3 px-4 text-right w-32">Total Refund</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
            <tr v-for="item in (saleReturn.sale_items || saleReturn.saleItems || [])" :key="item.id">
              <td class="py-3 px-4 font-semibold text-slate-800 dark:text-zinc-200">
                {{ item.product?.name || item.description || 'Returned Item' }}
              </td>
              <td class="py-3 px-4 text-center font-bold text-slate-700 dark:text-zinc-300">
                {{ Math.abs(item.quantity) }}
              </td>
              <td class="py-3 px-4 text-right text-slate-600 dark:text-zinc-400">
                {{ formatMoney(item.unit_price) }}
              </td>
              <td class="py-3 px-4 text-right font-extrabold text-slate-900 dark:text-zinc-100">
                {{ formatMoney(Math.abs(item.total_amount)) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Financial Totals Summary -->
      <div class="flex justify-end mb-8">
        <div class="w-64 space-y-2 text-xs">
          <div class="flex justify-between text-slate-600 dark:text-zinc-400">
            <span>Subtotal:</span>
            <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(Math.abs(saleReturn.subtotal)) }}</span>
          </div>
          <div class="flex justify-between text-slate-600 dark:text-zinc-400">
            <span>Tax Reversal:</span>
            <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(Math.abs(saleReturn.tax_amount)) }}</span>
          </div>
          <div class="border-t border-slate-300 dark:border-zinc-700 pt-2 flex justify-between items-center text-sm font-black">
            <span class="text-slate-800 dark:text-zinc-100">TOTAL REFUND:</span>
            <span class="text-rose-600 dark:text-rose-400 text-base">{{ formatMoney(Math.abs(saleReturn.total_amount)) }}</span>
          </div>
        </div>
      </div>

      <!-- Document Footer Notice -->
      <div class="border-t border-slate-200 dark:border-zinc-800 pt-6 text-center text-[11px] text-slate-400 space-y-1">
        <p>Thank you for your business. Stock has been credited back to inventory account.</p>
        <p>This is a system-generated credit note receipt.</p>
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
const companyName = ref('')

const formatMoney = (val) => {
  const num = Number(val || 0)
  return `${currencySymbol.value} ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const goBack = () => {
  router.push('/sales/returns')
}

const triggerPrint = () => {
  window.print()
}

const fetchReturnDetails = async () => {
  try {
    if (!currencyStore.currencies.length) {
      currencyStore.fetchCurrencies()
    }
    const res = await axios.get(`/api/sales/returns/${route.params.id}`)
    saleReturn.value = res.data

    const compRes = await axios.get('/api/companies')
    const activeCompany = (compRes.data || []).find(c => c.id === saleReturn.value.company_id)
    if (activeCompany) {
      companyName.value = activeCompany.name
    }
  } catch (err) {
    console.error('Failed to fetch return print data:', err)
  }
}

onMounted(() => {
  fetchReturnDetails()
})
</script>
