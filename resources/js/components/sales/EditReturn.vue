<template>
  <div class="w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950">
    <!-- Header Bar -->
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
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">
            Edit Return {{ form.sale_number }}
          </h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400">Update sales return items, quantities, or refund payment details</p>
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <button
          @click="submitUpdate(false)"
          :disabled="isSubmitting"
          class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-2 active:scale-95 cursor-pointer"
        >
          <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Update Return</span>
        </button>

        <button
          @click="submitUpdate(true)"
          :disabled="isSubmitting"
          class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-2 active:scale-95 cursor-pointer"
        >
          <span>Update & Print</span>
        </button>
      </div>
    </div>

    <!-- Error Banner -->
    <div v-if="errorMessage" class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-600 dark:text-rose-400 text-sm flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ errorMessage }}</span>
      </div>
      <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>

    <div v-if="isLoading" class="p-12 text-center text-slate-400">
      <svg class="animate-spin h-8 w-8 mx-auto text-blue-600 mb-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Loading return data...
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Return Details Card -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
          <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Return Information</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <CustomFloatingSelect
                label="Customer"
                v-model="form.customer_id"
                :options="customerOptions"
                placeholder="Walk-in Customer"
                searchable
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Return Date</label>
              <input
                v-model="form.sale_date"
                type="date"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs focus:ring-1 focus:ring-indigo-500 text-slate-800 dark:text-zinc-200"
              />
            </div>

            <div>
              <CustomFloatingSelect
                label="Refund Payment Method"
                v-model="form.payment_method"
                :options="refundMethodOptions"
              />
            </div>

            <div>
              <CustomFloatingSelect
                label="Destination Warehouse"
                v-model="form.warehouse_id"
                :options="warehouseOptions"
                searchable
              />
            </div>
          </div>
        </div>

        <!-- Returned Items Table Card -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Returned Line Items</h2>
            <span class="text-xs text-slate-400">{{ form.items.length }} line items</span>
          </div>

          <div class="overflow-x-auto border border-slate-100 dark:border-zinc-800 rounded-lg">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-50 dark:bg-zinc-800/60 text-slate-500 dark:text-zinc-400 font-bold uppercase border-b border-slate-200 dark:border-zinc-700">
                  <th class="py-3 px-3">Item</th>
                  <th class="py-3 px-3 w-28 text-center">Return Qty</th>
                  <th class="py-3 px-3 w-28 text-right">Unit Price</th>
                  <th class="py-3 px-3 w-28 text-right">Total Refund</th>
                  <th class="py-3 px-3 w-10 text-center"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30">
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-zinc-200">
                    {{ item.name }}
                  </td>
                  <td class="py-3 px-3 text-center">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      @input="calculateRowTotal(item)"
                      class="w-20 text-center px-2 py-1 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded text-xs font-bold focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-3 text-right">
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      step="0.01"
                      min="0"
                      @input="calculateRowTotal(item)"
                      class="w-24 text-right px-2 py-1 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded text-xs font-medium focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-zinc-100">
                    {{ formatMoney(item.total_amount) }}
                  </td>
                  <td class="py-3 px-3 text-center">
                    <button @click="removeItem(idx)" class="text-rose-500 hover:text-rose-700 p-1 cursor-pointer">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Notes -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
          <label class="block text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">Return Notes / Reason Remarks</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
          ></textarea>
        </div>
      </div>

      <!-- Right Summary Column -->
      <div class="space-y-6">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4 sticky top-6">
          <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Refund Summary</h2>

          <div class="space-y-2 text-xs">
            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Subtotal</span>
              <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(totals.subtotal) }}</span>
            </div>
            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Tax Reversal</span>
              <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(totals.tax_amount) }}</span>
            </div>
            <div class="border-t border-slate-200 dark:border-zinc-800 pt-3 flex justify-between items-center text-sm font-extrabold">
              <span class="text-slate-800 dark:text-zinc-200">Total Refund</span>
              <span class="text-rose-600 dark:text-rose-400 text-lg">{{ formatMoney(totals.total_amount) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCurrencyStore } from '@/stores/currency'
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const currencyStore = useCurrencyStore()

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$'
})

const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')

const customers = ref([])
const warehouses = ref([])

const form = reactive({
  id: null,
  sale_number: '',
  customer_id: null,
  sale_date: '',
  payment_method: 'cash',
  warehouse_id: null,
  notes: '',
  items: []
})

const customerOptions = computed(() => [
  { value: '', label: 'Walk-in Customer' },
  ...customers.value.map(cust => ({
    value: cust.id,
    label: cust.name
  }))
])

const refundMethodOptions = [
  { value: 'cash', label: 'Cash Refund' },
  { value: 'card', label: 'Card / Bank Refund' },
  { value: 'store_credit', label: 'Store Credit' },
  { value: 'exchange', label: 'Exchange' }
]

const warehouseOptions = computed(() => 
  warehouses.value.map(wh => ({
    value: wh.id,
    label: wh.name
  }))
)

const totals = computed(() => {
  let subtotal = 0
  let tax_amount = 0
  let total_amount = 0

  form.items.forEach(item => {
    subtotal += (item.quantity * item.unit_price)
    tax_amount += (item.tax_amount || 0)
    total_amount += (item.total_amount || 0)
  })

  return { subtotal, tax_amount, total_amount }
})

const formatMoney = (val) => {
  const num = Number(val || 0)
  return `${currencySymbol.value} ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const goBack = () => {
  router.push('/sales/returns')
}

const calculateRowTotal = (item) => {
  const qty = Math.abs(parseInt(item.quantity || 0))
  const price = parseFloat(item.unit_price || 0)
  item.tax_amount = item.tax_amount || 0
  item.total_amount = (qty * price) + parseFloat(item.tax_amount)
}

const removeItem = (idx) => {
  form.items.splice(idx, 1)
}

const fetchReturnData = async () => {
  isLoading.value = true
  try {
    if (!currencyStore.currencies.length) {
      currencyStore.fetchCurrencies()
    }
    const [retRes, custRes, whRes] = await Promise.all([
      axios.get(`/api/sales/returns/${route.params.id}`),
      axios.get('/api/customers'),
      axios.get('/api/warehouses')
    ])

    const data = retRes.data
    customers.value = custRes.data.data || custRes.data || []
    warehouses.value = whRes.data.data || whRes.data || []

    form.id = data.id
    form.sale_number = data.sale_number
    form.customer_id = data.customer_id
    form.sale_date = data.sale_date ? data.sale_date.substring(0, 10) : ''
    form.payment_method = data.payment_method || 'cash'
    form.warehouse_id = data.warehouse_id
    form.notes = data.notes || ''

    form.items = (data.sale_items || data.saleItems || []).map(item => ({
      id: item.id,
      product_id: item.product_id,
      product_variation_id: item.product_variation_id,
      warehouse_id: item.warehouse_id || data.warehouse_id,
      name: item.product?.name || item.description || 'Returned Item',
      quantity: Math.abs(item.quantity),
      unit_price: parseFloat(item.unit_price || 0),
      tax_amount: Math.abs(parseFloat(item.tax_amount || 0)),
      total_amount: Math.abs(parseFloat(item.total_amount || 0))
    }))
  } catch (err) {
    errorMessage.value = 'Failed to load sales return details.'
  } finally {
    isLoading.value = false
  }
}

const submitUpdate = async (andPrint = false) => {
  if (form.items.length === 0) {
    errorMessage.value = 'Sales return must contain at least one item.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      is_refund: true,
      customer_id: form.customer_id,
      sale_date: form.sale_date,
      payment_method: form.payment_method,
      warehouse_id: form.warehouse_id,
      notes: form.notes,
      subtotal: -totals.value.subtotal,
      tax_amount: -totals.value.tax_amount,
      total_amount: -totals.value.total_amount,
      paid_amount: -totals.value.total_amount,
      items: form.items.map(item => ({
        product_id: item.product_id,
        product_variation_id: item.product_variation_id,
        warehouse_id: item.warehouse_id || form.warehouse_id,
        quantity: -Math.abs(item.quantity),
        unit_price: item.unit_price,
        tax_amount: item.tax_amount,
        total_amount: -Math.abs(item.total_amount)
      }))
    }

    await axios.put(`/api/sales/returns/${form.id}`, payload)

    if (andPrint) {
      router.push(`/sales/returns/${form.id}/print`)
    } else {
      router.push('/sales/returns')
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Failed to update sales return.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchReturnData()
})
</script>
