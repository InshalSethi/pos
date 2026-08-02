<template>
  <div class="w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950">
    <!-- Header / Top Action Bar -->
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
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">Create Sales Return</h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400">Process customer return, restore inventory stock, and post reversal accounting entry</p>
        </div>
      </div>
      
      <div class="flex items-center space-x-3">
        <button
          @click="submitReturn(false)"
          :disabled="isSubmitting"
          class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-2 active:scale-95 cursor-pointer"
        >
          <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Save Return</span>
        </button>

        <button
          @click="submitReturn(true)"
          :disabled="isSubmitting"
          class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-2 active:scale-95 cursor-pointer"
        >
          <span>Save & Print</span>
        </button>
      </div>
    </div>

    <!-- Alert / Message Banner -->
    <div v-if="errorMessage" class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-600 dark:text-rose-400 text-sm flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ errorMessage }}</span>
      </div>
      <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content (Left 2 cols) -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Return Settings Card -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
          <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Return Details</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Load from Original Invoice -->
            <div>
              <CustomFloatingSelect
                label="Original Invoice (Optional)"
                v-model="selectedOriginalSaleId"
                :options="completedInvoiceOptions"
                placeholder="-- Select Original Invoice --"
                searchable
              />
            </div>

            <!-- Customer Picker -->
            <div>
              <CustomFloatingSelect
                label="Customer *"
                v-model="form.customer_id"
                :options="customerOptions"
                placeholder="Walk-in Customer"
                searchable
              />
            </div>

            <!-- Return Date -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Return Date *</label>
              <input
                v-model="form.return_date"
                type="date"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 text-slate-800 dark:text-zinc-200"
              />
            </div>

            <!-- Return Reason -->
            <div>
              <CustomFloatingSelect
                label="Return Reason *"
                v-model="form.return_reason"
                :options="returnReasonOptions"
              />
            </div>

            <!-- Refund Method -->
            <div>
              <CustomFloatingSelect
                label="Refund Payment Method *"
                v-model="form.refund_method"
                :options="refundMethodOptions"
              />
            </div>

            <!-- Default Warehouse -->
            <div>
              <CustomFloatingSelect
                label="Destination Warehouse *"
                v-model="form.warehouse_id"
                :options="warehouseOptions"
                searchable
              />
            </div>
          </div>
        </div>

        <!-- Product Search & Add to Return -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Returned Items</h2>
            <span class="text-xs text-slate-400">{{ form.items.length }} line items</span>
          </div>

          <!-- Product Picker / Search Dropdown -->
          <div class="relative">
            <input
              v-model="productSearch"
              type="text"
              placeholder="Search product by name, SKU or scan barcode..."
              @focus="showProductDropdown = true"
              @input="onProductSearchInput"
              class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>

            <!-- Search Results Dropdown -->
            <div
              v-if="showProductDropdown && filteredProducts.length > 0"
              class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-xl z-50 divide-y divide-slate-100 dark:divide-zinc-800"
            >
              <button
                v-for="prod in filteredProducts"
                :key="prod.key"
                @click="addProductToReturn(prod)"
                class="w-full p-2.5 text-left hover:bg-blue-50 dark:hover:bg-zinc-800 flex items-center justify-between transition-colors cursor-pointer"
              >
                <div>
                  <div class="font-semibold text-xs text-slate-800 dark:text-zinc-200">{{ prod.name }}</div>
                  <div class="text-[10px] text-slate-400">SKU: {{ prod.sku || 'N/A' }} | Price: {{ formatMoney(prod.price) }}</div>
                </div>
                <div class="text-xs font-bold text-blue-600 dark:text-blue-400">+ Add</div>
              </button>
            </div>
          </div>

          <!-- Items Table -->
          <div class="overflow-x-auto border border-slate-100 dark:border-zinc-800 rounded-lg">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-50 dark:bg-zinc-800/60 text-slate-500 dark:text-zinc-400 font-bold uppercase border-b border-slate-200 dark:border-zinc-700">
                  <th class="py-3 px-3">Item</th>
                  <th class="py-3 px-3 w-28 text-center">Return Qty</th>
                  <th class="py-3 px-3 w-28 text-right">Unit Price</th>
                  <th class="py-3 px-3 w-28 text-right">Tax Amount</th>
                  <th class="py-3 px-3 w-28 text-right">Total Refund</th>
                  <th class="py-3 px-3 w-10 text-center"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                <tr v-if="form.items.length === 0">
                  <td colspan="6" class="py-8 text-center text-slate-400">
                    No items selected. Select an invoice above or search products to add returned items.
                  </td>
                </tr>
                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30">
                  <td class="py-3 px-3">
                    <div class="font-semibold text-slate-800 dark:text-zinc-200">{{ item.name }}</div>
                    <div class="text-[10px] text-slate-400" v-if="item.original_qty">Original Sold Qty: {{ item.original_qty }}</div>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      :max="item.original_qty || 9999"
                      @input="calculateRowTotal(item)"
                      class="w-20 text-center px-2 py-1 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500 font-bold"
                    />
                  </td>
                  <td class="py-3 px-3 text-right">
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      step="0.01"
                      min="0"
                      @input="calculateRowTotal(item)"
                      class="w-24 text-right px-2 py-1 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500 font-medium"
                    />
                  </td>
                  <td class="py-3 px-3 text-right font-medium text-slate-600 dark:text-zinc-400">
                    {{ formatMoney(item.tax_amount || 0) }}
                  </td>
                  <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-zinc-100">
                    {{ formatMoney(item.total_amount || 0) }}
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

        <!-- Return Notes -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
          <label class="block text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">Return Notes / Reason Remarks</label>
          <textarea
            v-model="form.return_notes"
            rows="3"
            placeholder="Add detailed customer return remarks or condition notes..."
            class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
          ></textarea>
        </div>
      </div>

      <!-- Right Column: Summary Card -->
      <div class="space-y-6">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4 sticky top-6">
          <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Refund Summary</h2>

          <div class="space-y-2.5 text-xs">
            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Subtotal (Items)</span>
              <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(totals.subtotal) }}</span>
            </div>

            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Tax Reversal</span>
              <span class="font-semibold text-slate-900 dark:text-zinc-100">{{ formatMoney(totals.tax_amount) }}</span>
            </div>

            <div class="border-t border-slate-200 dark:border-zinc-800 pt-3 flex justify-between items-center text-sm font-extrabold">
              <span class="text-slate-800 dark:text-zinc-200">Total Refund</span>
              <span class="text-emerald-600 dark:text-emerald-400 text-lg">{{ formatMoney(totals.total_amount) }}</span>
            </div>
          </div>

          <div class="bg-blue-50/50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-lg p-3 text-[11px] text-blue-700 dark:text-blue-300 space-y-1">
            <div class="font-bold flex items-center space-x-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Automated Postings</span>
            </div>
            <p>• Restores stock quantity into selected warehouse.</p>
            <p>• Debits Sales Returns (Contra Revenue) & Credits Liquid Asset / Accounts Receivable.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCurrencyStore } from '@/stores/currency'
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()
const currencyStore = useCurrencyStore()

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$'
})

const isSubmitting = ref(false)
const errorMessage = ref('')
const selectedOriginalSaleId = ref(null)

const completedInvoices = ref([])
const customers = ref([])
const warehouses = ref([])
const availableProducts = ref([])

const productSearch = ref('')
const showProductDropdown = ref(false)

const form = reactive({
  original_sale_id: null,
  customer_id: null,
  return_date: new Date().toISOString().substring(0, 10),
  return_reason: 'customer_change_mind',
  refund_method: 'cash',
  warehouse_id: null,
  return_notes: '',
  items: []
})

const completedInvoiceOptions = computed(() => [
  { value: '', label: '-- Select Original Invoice --' },
  ...completedInvoices.value.map(inv => ({
    value: inv.id,
    label: `${inv.sale_number} - ${inv.customer?.name || 'Walk-in Customer'} (${formatDate(inv.sale_date)}) - ${formatMoney(inv.total_amount)}`
  }))
])

const customerOptions = computed(() => [
  { value: '', label: 'Walk-in Customer' },
  ...customers.value.map(cust => ({
    value: cust.id,
    label: `${cust.name} ${cust.phone ? '(' + cust.phone + ')' : ''}`
  }))
])

const returnReasonOptions = [
  { value: 'customer_change_mind', label: 'Customer Changed Mind (Clean Inventory)' },
  { value: 'wrong_item', label: 'Wrong Item Issued (Clean Inventory)' },
  { value: 'not_as_described', label: 'Not As Described (Clean Inventory)' },
  { value: 'damaged', label: 'Damaged / Opened (Quarantine Buffer Warehouse)' },
  { value: 'defective', label: 'Defective / Faulty (Quarantine Buffer Warehouse)' }
]

const refundMethodOptions = [
  { value: 'cash', label: 'Cash Refund' },
  { value: 'card', label: 'Card / Bank Refund' },
  { value: 'store_credit', label: 'Store Credit (Customer Wallet)' },
  { value: 'exchange', label: 'Exchange Item' }
]

const warehouseOptions = computed(() => 
  warehouses.value.map(wh => ({
    value: wh.id,
    label: `${wh.name} ${wh.is_default ? '(Default)' : ''}`
  }))
)

watch(selectedOriginalSaleId, (newVal) => {
  if (newVal) {
    onOriginalSaleSelect()
  } else {
    form.original_sale_id = null
  }
})

const filteredProducts = computed(() => {
  if (!productSearch.value) return []
  const query = productSearch.value.toLowerCase()
  return availableProducts.value.filter(p =>
    p.name.toLowerCase().includes(query) ||
    (p.sku && p.sku.toLowerCase().includes(query)) ||
    (p.barcode && p.barcode.toLowerCase().includes(query))
  ).slice(0, 8)
})

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

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString()
}

const goBack = () => {
  router.push('/sales/returns')
}

const fetchInitialData = async () => {
  try {
    if (!currencyStore.currencies.length) {
      currencyStore.fetchCurrencies()
    }
    const [invRes, custRes, whRes, prodRes] = await Promise.all([
      axios.get('/api/sales', { params: { is_refund: false, per_page: 50 } }),
      axios.get('/api/customers'),
      axios.get('/api/warehouses'),
      axios.get('/api/sales/products-with-stock')
    ])

    completedInvoices.value = invRes.data.data || invRes.data || []
    customers.value = custRes.data.data || custRes.data || []
    warehouses.value = whRes.data.data || whRes.data || []
    availableProducts.value = prodRes.data.items || []

    const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0]
    if (defaultWh) {
      form.warehouse_id = defaultWh.id
    }
  } catch (err) {
    console.error('Failed fetching data for sales return:', err)
  }
}

const onOriginalSaleSelect = async () => {
  if (!selectedOriginalSaleId.value) {
    form.original_sale_id = null
    return
  }

  try {
    const res = await axios.get(`/api/sales/${selectedOriginalSaleId.value}`)
    const sale = res.data
    form.original_sale_id = sale.id
    form.customer_id = sale.customer_id

    if (sale.warehouse_id) {
      form.warehouse_id = sale.warehouse_id
    }

    // Populate returned items from original sale
    form.items = (sale.sale_items || sale.saleItems || []).map(item => {
      const unitPrice = parseFloat(item.unit_price || 0)
      const qty = parseInt(item.quantity || 1)
      const tax = parseFloat(item.tax_amount || 0)

      return {
        original_item_id: item.id,
        product_id: item.product_id,
        product_variation_id: item.product_variation_id,
        name: item.product?.name || item.description || 'Product Item',
        quantity: qty,
        original_qty: qty,
        unit_price: unitPrice,
        tax_amount: tax,
        total_amount: (qty * unitPrice) + tax
      }
    })
  } catch (err) {
    errorMessage.value = 'Failed to load details for selected invoice.'
  }
}

const onProductSearchInput = () => {
  showProductDropdown.value = true
}

const addProductToReturn = (prod) => {
  form.items.push({
    original_item_id: null,
    product_id: prod.product_id,
    product_variation_id: prod.product_variation_id,
    name: prod.name,
    quantity: 1,
    original_qty: null,
    unit_price: prod.price || 0,
    tax_amount: 0,
    total_amount: prod.price || 0
  })

  productSearch.value = ''
  showProductDropdown.value = false
}

const calculateRowTotal = (item) => {
  const qty = parseInt(item.quantity || 0)
  const price = parseFloat(item.unit_price || 0)
  const rowSubtotal = qty * price
  item.tax_amount = item.tax_amount || 0
  item.total_amount = rowSubtotal + parseFloat(item.tax_amount)
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const submitReturn = async (andPrint = false) => {
  if (form.items.length === 0) {
    errorMessage.value = 'Please select at least one item to return.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      original_sale_id: form.original_sale_id,
      customer_id: form.customer_id,
      return_date: form.return_date,
      return_reason: form.return_reason,
      refund_method: form.refund_method,
      warehouse_id: form.warehouse_id,
      return_notes: form.return_notes,
      return_items: form.items.map(item => ({
        original_item_id: item.original_item_id,
        product_id: item.product_id,
        quantity: item.quantity,
        return_amount: item.total_amount
      }))
    }

    const res = await axios.post('/api/sales/returns', payload)
    const returnSale = res.data.return_sale || res.data.sale || res.data

    if (andPrint && returnSale.id) {
      router.push(`/sales/returns/${returnSale.id}/print`)
    } else {
      router.push('/sales/returns')
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || err.response?.data?.error || 'Failed to process sales return.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchInitialData()
})
</script>
