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
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">Edit Return {{ form.sale_number }}</h1>
          <p class="text-xs text-slate-500 dark:text-zinc-400">Update customer return items, quantities, or refund payment details</p>
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

    <!-- Alert / Message Banner -->
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
      <!-- Main Content (Left 2 cols) -->
      <div class="lg:col-span-2 space-y-6">
        <!-- 1. Returned Items Table Container -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-2">
            <div>
              <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Returned Items</h2>
              <p class="text-[10px] text-slate-400">Select items from original invoice or search products to return</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 dark:bg-zinc-800 rounded-lg text-slate-600 dark:text-zinc-300">
              {{ form.items.length }} line items
            </span>
          </div>

          <!-- Product Picker / Search Dropdown -->
          <div class="relative">
            <input
              v-model="productSearch"
              type="text"
              placeholder="Search product by name, SKU or scan barcode..."
              @focus="showProductDropdown = true"
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
                :key="prod.id"
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
                    No items selected. Select an original invoice on the right or search products above to add returned items.
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
                      class="w-20 text-center px-2 py-1 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500 font-bold text-slate-900 dark:text-zinc-100"
                    />
                  </td>
                  <td class="py-3 px-3 text-right">
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      step="0.01"
                      min="0"
                      @input="calculateRowTotal(item)"
                      class="w-24 text-right px-2 py-1 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500 font-medium text-slate-900 dark:text-zinc-100"
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

        <!-- 2. Refund Payment Accounts & Splits Container -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-2">
            <div>
              <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Refund Payment Accounts & Splits</h3>
              <p class="text-[10px] text-slate-400 dark:text-zinc-500">Allocate cash/bank payout or route unpaid balance to Customer Ledger</p>
            </div>
            <button
              type="button"
              @click="addPaymentSplit"
              class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1 shadow-xs"
            >
              + Add Refund Account
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(split, idx) in paymentSplits"
              :key="idx"
              class="p-4 bg-slate-50 dark:bg-zinc-950/60 border border-slate-200/80 dark:border-zinc-800 rounded-xl space-y-3"
            >
              <div class="flex flex-col sm:flex-row items-start sm:items-start justify-start gap-4">
                <!-- Dynamic Unified Account Selector -->
                <div class="w-full sm:w-80 md:w-96 shrink-0 space-y-1">
                  <CustomFloatingSelect
                    label="Refund Account / Payment Source *"
                    v-model="split.selected_account_key"
                    :options="refundAccountOptions"
                    @change="onAccountKeyChange(split)"
                    searchable
                  />
                  <!-- Live Available Balance Pill -->
                  <div v-if="getSplitAccountInfo(split).availableBalance !== null" class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-zinc-400 pl-1">
                    <span>Live Account Balance:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="getSplitAccountInfo(split).badgeClass">
                      {{ formatMoney(getSplitAccountInfo(split).availableBalance) }}
                    </span>
                  </div>
                </div>

                <!-- Refund Amount -->
                <div class="w-full sm:w-44 space-y-1">
                  <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Refund Amount *</label>
                  <input
                    v-model.number="split.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg font-bold text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-right"
                    :class="{ 'border-rose-500 ring-1 ring-rose-500': getSplitBalanceError(split) }"
                  />
                </div>

                <!-- Remove Split Button -->
                <button
                  v-if="paymentSplits.length > 1"
                  type="button"
                  @click="removePaymentSplit(idx)"
                  class="p-2 text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 cursor-pointer shrink-0 mt-1 sm:mt-6"
                  title="Remove split"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>

              <!-- Insufficient Funds Alert Banner -->
              <div v-if="getSplitBalanceError(split)" class="p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-lg text-xs font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ getSplitBalanceError(split) }}</span>
              </div>
            </div>
          </div>

          <!-- Customer Ledger Breakdown Summary -->
          <div class="p-3 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl space-y-1.5 text-xs">
            <div class="flex justify-between text-slate-600 dark:text-zinc-400">
              <span>Total Refund Paid Out (Cash/Banks/Wallet):</span>
              <span class="font-bold text-slate-900 dark:text-zinc-100">{{ formatMoney(totalPaidOutSplits) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs pt-1.5 border-t border-slate-200 dark:border-zinc-800">
              <span class="font-bold text-indigo-600 dark:text-indigo-400">Remaining Unpaid / Customer Ledger Credit:</span>
              <span class="font-extrabold text-indigo-600 dark:text-indigo-400 text-sm">{{ formatMoney(remainingUnpaidLedger) }}</span>
            </div>
            <p class="text-[9.5px] text-slate-400 dark:text-zinc-500 italic">
              Note: Any return balance not paid out in cash or bank transfer will be automatically credited to the Customer's Account Payable / Store Credit Ledger.
            </p>
          </div>
        </div>

        <!-- 3. Return Notes -->
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

      <!-- Right Column: Return Details & Refund Summary -->
      <div class="space-y-6">
        <div class="space-y-6 sticky top-6">
          <!-- 1. Return Details -->
          <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
            <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Return Details</h2>

            <div class="space-y-4">
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

              <!-- Destination Warehouse -->
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

          <!-- 2. Refund Summary Card -->
          <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft space-y-4">
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
          </div>
        </div>
      </div>
    </div>

    </div>
    <!-- Floating Grand Total Badge -->
    <div class="fixed bottom-[10px] right-6 z-50 animate-fade-in-down">
      <div class="relative bg-slate-900 dark:bg-zinc-800 text-white pl-4 pr-5 py-1.5 min-w-[300px] rounded-xl shadow-xl flex flex-col items-end border border-slate-700 dark:border-zinc-700 cursor-default">
        <span class="absolute top-1.5 left-4 text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-400">Grand Total</span>
        <span class="text-2xl font-black leading-tight text-emerald-400 pt-3">{{ formatMoney(totals.total_amount) }}</span>
      </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCurrencyStore } from '@/stores/currency'
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue'
import axios from 'axios'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const currencyStore = useCurrencyStore()

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$'
})

const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const selectedOriginalSaleId = ref(null)

const completedInvoices = ref([])
const customers = ref([])
const warehouses = ref([])
const availableProducts = ref([])
const bankAccounts = ref([])
const cashAccountBalance = ref(0)
const refundAccountOptions = computed(() => {
  const options = []
  
  options.push({
    value: 'cash',
    label: `Default Cash Vault — Avail: ${formatMoney(cashAccountBalance.value)}`,
    type: 'cash',
    bank_id: null,
    availableBalance: cashAccountBalance.value,
    badgeClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
  })

  bankAccounts.value.forEach(bAcc => {
    const isCashAccount = (bAcc.account_type && bAcc.account_type.toLowerCase().includes('cash')) ||
      (bAcc.bank_name && bAcc.bank_name.toLowerCase().includes('cash')) ||
      (bAcc.account_name && bAcc.account_name.toLowerCase().includes('cash'))

    if (isCashAccount) return

    const isInactive = bAcc.is_active === false || bAcc.is_active === 0
    const bal = (bAcc.current_balance !== undefined && bAcc.current_balance !== null)
      ? parseFloat(bAcc.current_balance)
      : 0
    options.push({
      value: `bank_${bAcc.id}`,
      label: `${bAcc.bank_name || 'Bank'} (${bAcc.account_name})${isInactive ? ' (Inactive)' : ''} — Avail: ${formatMoney(bal)}`,
      type: 'bank',
      bank_id: bAcc.id,
      is_active: bAcc.is_active,
      disabled: isInactive,
      availableBalance: bal,
      badgeClass: isInactive ? 'bg-slate-200 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
    })
  })

  options.push({
    value: 'store_credit',
    label: 'Store Credit (Customer Wallet / Store Ledger)',
    type: 'store_credit',
    bank_id: null,
    availableBalance: null,
    badgeClass: 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300'
  })

  return options
})

const paymentSplits = ref([
  { selected_account_key: 'cash', type: 'cash', bank_id: null, amount: 0 }
])

const onAccountKeyChange = (split) => {
  const option = refundAccountOptions.value.find(opt => String(opt.value) === String(split.selected_account_key))
  if (option) {
    split.type = option.type
    split.bank_id = option.bank_id
  } else {
    split.type = 'cash'
    split.bank_id = null
  }
}

const getSplitAccountInfo = (split) => {
  return refundAccountOptions.value.find(opt => String(opt.value) === String(split.selected_account_key)) || {
    label: 'Default Cash Vault',
    availableBalance: cashAccountBalance.value,
    badgeClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
  }
}

const addPaymentSplit = () => {
  const usedKeys = paymentSplits.value.map(s => s.selected_account_key)
  const unusedOpt = refundAccountOptions.value.find(opt => !usedKeys.includes(opt.value)) || refundAccountOptions.value[0]
  const defaultKey = unusedOpt ? unusedOpt.value : 'cash'
  const optInfo = refundAccountOptions.value.find(o => String(o.value) === String(defaultKey)) || {}

  const remaining = Math.max(0, remainingUnpaidLedger.value)
  paymentSplits.value.push({
    selected_account_key: defaultKey,
    type: optInfo.type || 'cash',
    bank_id: optInfo.bank_id || null,
    amount: remaining
  })
}

const removePaymentSplit = (idx) => {
  paymentSplits.value.splice(idx, 1)
}

const getSplitBalanceError = (split) => {
  if (!split.amount || split.amount <= 0) return null
  const info = getSplitAccountInfo(split)
  if (info && info.availableBalance !== null && info.availableBalance !== undefined) {
    if (split.amount > info.availableBalance) {
      return `Insufficient funds! Maximum available in ${info.label.split('—')[0].trim()} is ${formatMoney(info.availableBalance)}`
    }
  }
  return null
}

const hasBalanceErrors = computed(() => {
  return paymentSplits.value.some(s => getSplitBalanceError(s) !== null)
})

const totalPaidOutSplits = computed(() => {
  return paymentSplits.value.reduce((sum, s) => sum + (parseFloat(s.amount) || 0), 0)
})

const remainingUnpaidLedger = computed(() => {
  return Math.max(0, totals.value.total_amount - totalPaidOutSplits.value)
})

const productSearch = ref('')
const showProductDropdown = ref(false)

const form = reactive({
  id: null,
  sale_number: '',
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
  ...completedInvoices.value.map(inv => {
    const isCurrentlyEditingThisInv = String(inv.id) === String(selectedOriginalSaleId.value)
    const isVoid = !!(inv.is_void || ['void', 'voided', 'cancelled'].includes(String(inv.status).toLowerCase()))
    const isReturned = !isVoid && !!(inv.is_fully_returned || inv.is_returned || inv.return_status === 'full')
    let badge = ''
    if (isVoid) {
      badge = String(inv.status).toLowerCase() === 'cancelled' ? ' (Cancelled)' : ' (Void)'
    } else if (isReturned && !isCurrentlyEditingThisInv) {
      badge = ' (Returned)'
    }

    return {
      value: inv.id,
      label: `${inv.sale_number} - ${inv.customer?.name || 'Walk-in Customer'} (${formatDate(inv.sale_date)}) - ${formatMoney(inv.total_amount)}${badge}`,
      disabled: isVoid || (isReturned && !isCurrentlyEditingThisInv)
    }
  })
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

const warehouseOptions = computed(() => 
  warehouses.value.map(wh => ({
    value: wh.id,
    label: `${wh.name} ${wh.is_default ? '(Default)' : ''}`
  }))
)

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

const calculateRowTotal = (item) => {
  const qty = Number(item.quantity || 0)
  const price = Number(item.unit_price || 0)
  const tax = Number(item.tax_amount || 0)
  item.total_amount = (qty * price) + tax
}

const addProductToReturn = (product) => {
  const existing = form.items.find(i => i.product_id === product.id)
  if (existing) {
    existing.quantity += 1
    calculateRowTotal(existing)
  } else {
    const item = {
      product_id: product.id,
      product_variation_id: product.variation_id || null,
      name: product.name,
      original_qty: null,
      quantity: 1,
      unit_price: product.price || 0,
      tax_amount: 0,
      total_amount: product.price || 0
    }
    form.items.push(item)
  }
  productSearch.value = ''
  showProductDropdown.value = false
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const goBack = () => {
  router.push('/sales/returns')
}

const loadReturnData = async () => {
  const returnId = route.params.id
  if (!returnId) return
  isLoading.value = true
  try {
    const res = await axios.get(`/api/sales/returns/${returnId}`)
    const sale = res.data?.sale || res.data

    form.id = sale.id
    form.sale_number = sale.sale_number || `RETURN-${sale.id}`
    form.customer_id = sale.customer_id
    form.return_date = sale.sale_date ? sale.sale_date.substring(0, 10) : new Date().toISOString().substring(0, 10)
    form.warehouse_id = sale.warehouse_id
    form.return_reason = sale.return_reason || 'customer_change_mind'
    form.return_notes = sale.notes || ''
    selectedOriginalSaleId.value = sale.original_sale_id || null

    const rawItems = sale.sale_items || sale.saleItems || sale.items || []
    const saleHeaderTax = Math.abs(parseFloat(sale.tax_amount || 0))
    const saleSubtotal = Math.abs(parseFloat(sale.subtotal || 0))

    // Try to fetch original sale for cross-referencing item-level taxes
    let originalSaleItems = []
    if (sale.original_sale_id) {
      try {
        const origRes = await axios.get(`/api/sales/${sale.original_sale_id}`)
        const origSale = origRes.data?.sale || origRes.data
        originalSaleItems = origSale?.sale_items || origSale?.saleItems || []
      } catch (e) {
        console.warn('Could not fetch original sale for tax cross-reference:', e)
      }
    }

    form.items = rawItems.map(item => {
      const qty = Math.abs(item.quantity || 1)
      const unitPrice = parseFloat(item.unit_price) || 0
      let taxAmt = Math.abs(parseFloat(item.tax_amount) || 0)

      // Fallback 1: Cross-reference original sale item tax
      if (taxAmt === 0 && originalSaleItems.length > 0) {
        const origItem = originalSaleItems.find(oi => oi.product_id === item.product_id)
        if (origItem) {
          const origTax = Math.abs(parseFloat(origItem.tax_amount) || 0)
          const origQty = parseInt(origItem.quantity || 1)
          if (origTax > 0 && origQty > 0) {
            taxAmt = (qty / origQty) * origTax
            taxAmt = Math.round(taxAmt * 100) / 100
          }
        }
      }

      // Fallback 2: Proportional from return sale header tax
      if (taxAmt === 0 && saleHeaderTax > 0 && saleSubtotal > 0) {
        const itemSubtotal = qty * unitPrice
        taxAmt = (itemSubtotal / saleSubtotal) * saleHeaderTax
        taxAmt = Math.round(taxAmt * 100) / 100
      }

      const totalAmt = (qty * unitPrice) + taxAmt
      return {
        product_id: item.product_id,
        product_variation_id: item.product_variation_id || null,
        name: item.product?.name || item.name || 'Product',
        original_qty: item.original_sold_qty || qty,
        quantity: qty,
        unit_price: unitPrice,
        tax_rate: parseFloat(item.tax_rate) || 0,
        tax_amount: taxAmt,
        original_tax: taxAmt,
        total_amount: totalAmt
      }
    })

    if (Array.isArray(sale.payment_details) && sale.payment_details.length > 0) {
      paymentSplits.value = sale.payment_details.map(p => {
        const type = p.type || (['card', 'bank_transfer'].includes(p.method) ? 'bank' : p.method)
        const bankId = p.bank_id ? Number(p.bank_id) : null
        let key = 'cash'
        if (type === 'bank' && bankId) key = `bank_${bankId}`
        else if (type === 'store_credit') key = 'store_credit'
        return {
          selected_account_key: key,
          type: type,
          bank_id: bankId,
          amount: Math.abs(parseFloat(p.amount)) || 0
        }
      })
    } else if (sale.payment_method) {
      const type = (sale.payment_method === 'bank_transfer' || sale.payment_method === 'card') ? 'bank' : sale.payment_method
      const bankId = sale.bank_id ? Number(sale.bank_id) : null
      let key = 'cash'
      if (type === 'bank' && bankId) key = `bank_${bankId}`
      else if (type === 'store_credit') key = 'store_credit'
      paymentSplits.value = [{
        selected_account_key: key,
        type: type,
        bank_id: bankId,
        amount: Math.abs(parseFloat(sale.paid_amount)) || Math.abs(parseFloat(sale.total_amount)) || 0
      }]
    } else {
      paymentSplits.value = [{ selected_account_key: 'cash', type: 'cash', bank_id: null, amount: totals.value.total_amount }]
    }
  } catch (err) {
    console.error('Failed to load return data:', err)
    errorMessage.value = 'Failed to load return data'
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  try {
    const [invRes, custRes, whRes, prodRes, bankRes, cashRes] = await Promise.all([
      axios.get('/api/sales?status=completed').catch(() => ({ data: [] })),
      axios.get('/api/customers').catch(() => ({ data: [] })),
      axios.get('/api/warehouses').catch(() => ({ data: [] })),
      axios.get('/api/products').catch(() => ({ data: [] })),
      axios.get('/api/bank-accounts').catch(() => ({ data: [] })),
      axios.get('/api/accounts').catch(() => ({ data: [] }))
    ])

    completedInvoices.value = Array.isArray(invRes.data) ? invRes.data : (invRes.data?.data || [])
    customers.value = Array.isArray(custRes.data) ? custRes.data : (custRes.data?.data || [])
    warehouses.value = Array.isArray(whRes.data) ? whRes.data : (whRes.data?.data || [])
    availableProducts.value = Array.isArray(prodRes.data) ? prodRes.data : (prodRes.data?.data || [])
    bankAccounts.value = Array.isArray(bankRes.data) ? bankRes.data : (bankRes.data?.data || [])

    const accountsList = Array.isArray(cashRes.data) ? cashRes.data : (cashRes.data?.data || [])
    const cashAcc = accountsList.find(a => a.account_code === '1010' || (a.account_name && a.account_name.toLowerCase().includes('cash')))
    cashAccountBalance.value = cashAcc ? (parseFloat(cashAcc.current_balance) || 0) : 0

    await loadReturnData()
  } catch (err) {
    console.error('Error fetching return form reference data:', err)
    errorMessage.value = 'Error initializing sales return form'
  }
})

const submitUpdate = async (andPrint = false) => {
  if (form.items.length === 0) {
    errorMessage.value = 'Please select at least one item to return.'
    return
  }

  if (hasBalanceErrors.value) {
    errorMessage.value = 'Cannot process return: One or more payment splits exceed available cash/bank balances.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      return_date: form.return_date,
      customer_id: form.customer_id,
      warehouse_id: form.warehouse_id,
      return_reason: form.return_reason,
      return_notes: form.return_notes,
      original_sale_id: selectedOriginalSaleId.value,
      items: form.items.map(item => ({
        product_id: item.product_id,
        product_variation_id: item.product_variation_id,
        warehouse_id: item.source_warehouse_id || form.warehouse_id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        tax_amount: item.tax_amount,
        total: item.total_amount
      })),
      payments: paymentSplits.value.filter(s => s.amount > 0).map(s => ({
        type: s.type,
        method: s.type === 'bank' ? 'bank_transfer' : s.type,
        bank_id: s.type === 'bank' ? s.bank_id : null,
        amount: s.amount
      })),
      refund_method: paymentSplits.value.length > 1 ? 'mixed' : (paymentSplits.value[0]?.type || 'cash')
    }

    const res = await axios.put(`/api/sales/returns/${form.id}`, payload)
    const returnSale = res.data?.sale || res.data?.return_sale || res.data

    if (andPrint && returnSale?.id) {
      router.push(`/sales/returns/${returnSale.id}/print`)
    } else {
      router.push('/sales/returns')
    }
  } catch (err) {
    console.error('Failed to update sales return:', err)
    errorMessage.value = err.response?.data?.message || err.message || 'Failed to update sales return.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
