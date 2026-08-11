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
                    No items selected. Select an original invoice on the right or search products above to add returned items.
                  </td>
                </tr>
                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30">
                  <td class="py-3 px-3">
                    <div class="font-semibold text-slate-800 dark:text-zinc-200">{{ item.name }}</div>
                    <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                      <span v-if="item.original_qty">Original Sold Qty: {{ item.original_qty }}</span>
                      <span v-if="item.source_warehouse_name" class="inline-flex items-center gap-1 text-slate-500 dark:text-zinc-400 font-medium bg-slate-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">
                        Dispatched from: <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ item.source_warehouse_name }}</span>
                      </span>
                    </div>
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

        <!-- 2. Refund Payment Accounts & Splits Container (Unified Selector) -->
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
          <!-- 1. Return Details (Right Sidebar) -->
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
              <div class="relative">
                <CustomFloatingSelect
                  label="Customer *"
                  v-model="form.customer_id"
                  :options="customerOptions"
                  placeholder="Walk-in Customer"
                  searchable
                  :disabled="!!form.original_sale_id"
                  :buttonClass="form.original_sale_id ? 'bg-blue-50/50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' : ''"
                />
                <span v-if="form.original_sale_id" class="absolute top-0 right-0 mt-0 text-[9px] font-bold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40 px-1.5 py-0.5 rounded">Auto-filled</span>
              </div>

              <!-- Sales Representative / Salesman -->
              <div class="relative">
                <CustomFloatingSelect
                  label="Sales Representative / Salesman"
                  v-model="form.salesman_id"
                  :options="salesmanOptions"
                  placeholder="-- Select Sales Representative --"
                  searchable
                  :disabled="!!form.original_sale_id"
                  :buttonClass="form.original_sale_id ? 'bg-blue-50/50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' : ''"
                />
                <span v-if="form.original_sale_id" class="absolute top-0 right-0 mt-0 text-[9px] font-bold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40 px-1.5 py-0.5 rounded">Auto-filled</span>
              </div>

              <!-- POS Counter -->
              <div class="relative">
                <CustomFloatingSelect
                  label="POS Counter"
                  v-model="form.counter_id"
                  :options="counterOptions"
                  placeholder="-- Select POS Counter --"
                  searchable
                  :disabled="!!form.original_sale_id"
                  :buttonClass="form.original_sale_id ? 'bg-blue-50/50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' : ''"
                />
                <span v-if="form.original_sale_id" class="absolute top-0 right-0 mt-0 text-[9px] font-bold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40 px-1.5 py-0.5 rounded">Auto-filled</span>
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

              <!-- Destination Return Warehouse -->
              <div>
                <CustomFloatingSelect
                  label="Destination Return Warehouse *"
                  v-model="form.warehouse_id"
                  :options="warehouseOptions"
                  searchable
                />
                <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Warehouse where returned stock will be restored.</p>
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

            <div class="bg-blue-50/50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-lg p-3 text-[11px] text-blue-700 dark:text-blue-300 space-y-1">
              <div class="font-bold flex items-center space-x-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Automated Postings</span>
              </div>
              <p>• Restores stock quantity into designated return warehouse.</p>
              <p>• Debits Sales Returns (Contra Revenue) & Credits Liquid Asset / Accounts Receivable.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  
  <!-- Floating Grand Total Badge -->
  <div class="fixed bottom-[10px] right-6 z-50 animate-fade-in-down">
    <div class="bg-slate-900 dark:bg-zinc-800 text-white px-10 py-2.5 min-w-[300px] rounded-xl shadow-xl flex items-center justify-between border border-slate-700 dark:border-zinc-700 cursor-default">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-400">Grand Total</span>
      <span class="text-2xl font-black leading-tight text-emerald-400">{{ formatMoney(totals.total_amount) }}</span>
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
const bankAccounts = ref([])
const salesmen = ref([])
const counters = ref([])
const cashAccountBalance = ref(0)

const form = reactive({
  original_sale_id: null,
  customer_id: null,
  salesman_id: null,
  counter_id: null,
  return_date: new Date().toISOString().substring(0, 10),
  return_reason: 'customer_change_mind',
  refund_method: 'cash',
  warehouse_id: null,
  return_notes: '',
  items: []
})

// Unified / Consolidated Refund Account Selector Options
const refundAccountOptions = computed(() => {
  const options = []
  
  // 1. Single Clean Cash Entry ("Default Cash Vault")
  options.push({
    value: 'cash',
    label: `Default Cash Vault — Avail: ${formatMoney(cashAccountBalance.value)}`,
    type: 'cash',
    bank_id: null,
    availableBalance: cashAccountBalance.value,
    badgeClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
  })

  // 2. Bank Accounts (Filtering out duplicate cash entries)
  bankAccounts.value.forEach(bAcc => {
    const isCashAccount = (bAcc.account_type && bAcc.account_type.toLowerCase().includes('cash')) ||
      (bAcc.bank_name && bAcc.bank_name.toLowerCase().includes('cash')) ||
      (bAcc.account_name && bAcc.account_name.toLowerCase().includes('cash'))

    if (isCashAccount) {
      return // Deduplicate: Skip adding cash bank account entries
    }

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

  // 3. Store Credit / Customer Wallet
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
  const option = refundAccountOptions.value.find(opt => opt.value === split.selected_account_key)
  if (option) {
    split.type = option.type
    split.bank_id = option.bank_id
  } else {
    split.type = 'cash'
    split.bank_id = null
  }
}

const getSplitAccountInfo = (split) => {
  return refundAccountOptions.value.find(opt => opt.value === split.selected_account_key) || {
    label: 'Default Cash Vault',
    availableBalance: cashAccountBalance.value,
    badgeClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
  }
}

const addPaymentSplit = () => {
  // Find an unused account option or default to next option
  const usedKeys = paymentSplits.value.map(s => s.selected_account_key)
  const unusedOpt = refundAccountOptions.value.find(opt => !usedKeys.includes(opt.value)) || refundAccountOptions.value[0]
  const defaultKey = unusedOpt ? unusedOpt.value : 'cash'
  const optInfo = refundAccountOptions.value.find(o => o.value === defaultKey) || {}

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

const completedInvoiceOptions = computed(() => [
  { value: '', label: '-- Select Original Invoice --' },
  ...completedInvoices.value.map(inv => {
    const isVoid = !!(inv.is_void || ['void', 'voided', 'cancelled'].includes(String(inv.status).toLowerCase()))
    const isReturned = !isVoid && !!(inv.is_fully_returned || inv.is_returned || inv.return_status === 'full')
    let badge = ''
    if (isVoid) {
      badge = String(inv.status).toLowerCase() === 'cancelled' ? ' (Cancelled)' : ' (Void)'
    } else if (isReturned) {
      badge = ' (Returned)'
    }

    return {
      value: inv.id,
      label: `${inv.sale_number} - ${inv.customer?.name || 'Walk-in Customer'} (${formatDate(inv.sale_date)}) - ${formatMoney(inv.total_amount)}${badge}`,
      disabled: isVoid || isReturned
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

const salesmanOptions = computed(() => [
  { value: '', label: '-- Select Sales Representative --' },
  ...salesmen.value.map(emp => ({
    value: emp.id,
    label: `${emp.full_name || (emp.first_name + ' ' + (emp.last_name || ''))} ${emp.employee_number ? '(' + emp.employee_number + ')' : ''}`
  }))
])

const counterOptions = computed(() => {
  let list = counters.value
  if (form.warehouse_id) {
    const whIdStr = String(form.warehouse_id)
    list = list.filter(c => !c.warehouse_id || String(c.warehouse_id) === whIdStr)
  }
  return [
    { value: '', label: '-- Select POS Counter --' },
    ...list.map(cnt => ({
      value: cnt.id,
      label: cnt.name
    }))
  ]
})

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

watch(selectedOriginalSaleId, (newVal) => {
  if (newVal) {
    onOriginalSaleSelect()
  } else {
    form.original_sale_id = null
    form.customer_id = ''
    form.salesman_id = null
    form.counter_id = null
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

// Auto-sync refund amount if single split
watch(() => totals.value.total_amount, (newTotal) => {
  if (paymentSplits.value.length === 1) {
    paymentSplits.value[0].amount = newTotal
  }
}, { immediate: true })

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
    const [invRes, custRes, whRes, prodRes, bankRes, accRes, empRes, cntRes] = await Promise.all([
      axios.get('/api/sales', { params: { is_refund: false, per_page: 50 } }),
      axios.get('/api/customers'),
      axios.get('/api/warehouses'),
      axios.get('/api/sales/products-with-stock'),
      axios.get('/api/bank-accounts'),
      axios.get('/api/accounts').catch(() => ({ data: [] })),
      axios.get('/api/employees/for-dropdown').catch(() => ({ data: [] })),
      axios.get('/api/counters').catch(() => ({ data: [] }))
    ])

    completedInvoices.value = invRes.data.data || invRes.data || []
    customers.value = custRes.data.data || custRes.data || []
    warehouses.value = whRes.data.data || whRes.data || []
    availableProducts.value = prodRes.data.items || []
    bankAccounts.value = bankRes.data.data || bankRes.data || []
    salesmen.value = empRes.data.data || empRes.data || []
    counters.value = cntRes.data.data || cntRes.data || []

    const accountsList = accRes.data.data || accRes.data || []
    const cashAcc = accountsList.find(a => a.account_code === '1010' || (a.account_name && a.account_name.toLowerCase().includes('cash')))
    if (cashAcc) {
      cashAccountBalance.value = parseFloat(cashAcc.current_balance || cashAcc.calculated_balance || 0)
    } else {
      const defaultBank = bankAccounts.value.find(b => (b.bank_name && b.bank_name.toLowerCase().includes('cash')) || b.is_default)
      cashAccountBalance.value = defaultBank ? parseFloat(defaultBank.current_balance || 0) : 50000
    }

    const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0]
    if (defaultWh && !form.warehouse_id) {
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

  const existingInv = completedInvoices.value.find(i => String(i.id) === String(selectedOriginalSaleId.value))
  if (existingInv) {
    const isVoid = !!(existingInv.is_void || ['void', 'voided', 'cancelled'].includes(String(existingInv.status).toLowerCase()))
    const isReturned = !!(existingInv.is_fully_returned || existingInv.is_returned || existingInv.return_status === 'full')
    if (isVoid || isReturned) {
      selectedOriginalSaleId.value = ''
      form.original_sale_id = null
      return
    }
  }

  try {
    const res = await axios.get(`/api/sales/${selectedOriginalSaleId.value}`)
    const sale = res.data
    form.original_sale_id = sale.id
    form.customer_id = sale.customer_id || ''
    form.salesman_id = sale.salesman_id || sale.user_id || ''
    form.counter_id = sale.counter_id || ''

    if (sale.warehouse_id) {
      form.warehouse_id = sale.warehouse_id
    }

    // Populate returned items from original sale with original dispatch warehouse info
    const saleHeaderTax = Math.abs(parseFloat(sale.tax_amount || 0))
    const saleSubtotal = parseFloat(sale.subtotal || 0)

    form.items = (sale.sale_items || sale.saleItems || []).map(item => {
      const unitPrice = parseFloat(item.unit_price || 0)
      const qty = parseInt(item.quantity || 1)
      let tax = Math.abs(parseFloat(item.tax_amount || 0))

      // Fallback: if item has no tax but invoice has header-level tax, distribute proportionally
      if (tax === 0 && saleHeaderTax > 0 && saleSubtotal > 0) {
        const itemSubtotal = qty * unitPrice
        tax = (itemSubtotal / saleSubtotal) * saleHeaderTax
        tax = Math.round(tax * 100) / 100
      }

      return {
        original_item_id: item.id,
        product_id: item.product_id,
        product_variation_id: item.product_variation_id,
        name: item.product?.name || item.description || 'Product Item',
        quantity: qty,
        original_qty: qty,
        unit_price: unitPrice,
        tax_amount: tax,
        original_tax: tax,
        total_amount: (qty * unitPrice) + tax,
        source_warehouse_id: item.warehouse_id || sale.warehouse_id,
        source_warehouse_name: item.warehouse?.name || sale.warehouse?.name || 'Original Dispatch Warehouse'
      }
    })

    // Pre-populate refund splits with return total
    const returnTotal = form.items.reduce((s, i) => s + i.total_amount, 0)
    paymentSplits.value = [{ selected_account_key: 'cash', type: 'cash', bank_id: null, amount: returnTotal }]
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
    original_tax: 0,
    total_amount: prod.price || 0,
    source_warehouse_name: null
  })

  productSearch.value = ''
  showProductDropdown.value = false
}

const calculateRowTotal = (item) => {
  const qty = parseInt(item.quantity || 0)
  const price = parseFloat(item.unit_price || 0)
  const rowSubtotal = qty * price
  if (item.original_qty && item.original_tax !== undefined && item.original_tax > 0) {
    item.tax_amount = (qty / item.original_qty) * item.original_tax
  } else {
    item.tax_amount = parseFloat(item.tax_amount || 0)
  }
  item.total_amount = rowSubtotal + parseFloat(item.tax_amount || 0)
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const submitReturn = async (andPrint = false) => {
  if (form.items.length === 0) {
    errorMessage.value = 'Please select at least one item to return.'
    return
  }

  if (hasBalanceErrors.value) {
    errorMessage.value = 'Please resolve insufficient balance errors before proceeding.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      original_sale_id: form.original_sale_id,
      customer_id: form.customer_id,
      salesman_id: form.salesman_id,
      counter_id: form.counter_id,
      warehouse_id: form.warehouse_id,
      return_date: form.return_date,
      return_reason: form.return_reason,
      refund_method: paymentSplits.value.length > 1 ? 'mixed' : (paymentSplits.value[0]?.type || 'cash'),
      payments: paymentSplits.value.map(s => ({
        type: s.type,
        method: s.type === 'bank' ? 'bank_transfer' : s.type,
        bank_id: s.bank_id,
        amount: parseFloat(s.amount) || 0
      })),
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
