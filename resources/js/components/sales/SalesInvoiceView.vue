<template>
  <div class="min-h-screen bg-slate-50 dark:bg-zinc-950 text-slate-800 dark:text-zinc-100 p-4 md:p-6 transition-colors duration-200">
    <div class="max-w-7xl mx-auto space-y-6">
      
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col justify-center items-center h-80 space-y-4">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-600 border-t-transparent"></div>
        <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Loading invoice details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-2xl p-6 shadow-xs">
        <div class="flex items-center space-x-3">
          <div class="p-2 bg-rose-100 dark:bg-rose-900/60 rounded-xl text-rose-600 dark:text-rose-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-rose-800 dark:text-rose-300">Error Loading Invoice</h3>
            <p class="text-xs text-rose-600 dark:text-rose-400 mt-0.5">{{ error }}</p>
          </div>
        </div>
        <div class="mt-4 flex space-x-3">
          <button @click="fetchInvoice" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition-all cursor-pointer">
            Retry
          </button>
          <button @click="goBack" class="px-4 py-2 bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 rounded-xl text-xs font-bold hover:bg-slate-300 dark:hover:bg-zinc-700 transition-all cursor-pointer">
            Back to Invoices
          </button>
        </div>
      </div>

      <!-- Main Invoice View Content -->
      <template v-else-if="invoice">
        
        <!-- Top Action Header & Breadcrumb -->
        <div class="bg-white dark:bg-zinc-900 p-4 md:p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-4">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            <!-- Invoice Meta & Badges -->
            <div class="space-y-2">
              <div class="flex items-center space-x-3">
                <button
                  @click="goBack"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
                  title="Back to Sales Invoices"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                  </svg>
                </button>
                <h1 class="text-2xl font-black text-slate-900 dark:text-zinc-100 tracking-tight">
                  Invoice <span class="text-indigo-600 dark:text-indigo-400">{{ invoice.sale_number }}</span>
                </h1>

                <!-- Status Badge -->
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-xs font-extrabold tracking-wide uppercase shadow-2xs border',
                    invoiceStatusStyle.bg,
                    invoiceStatusStyle.text,
                    invoiceStatusStyle.border
                  ]"
                >
                  {{ invoiceStatusLabel }}
                </span>

                <!-- Sales Pricing Mode Badge -->
                <span
                  :class="[
                    'px-2.5 py-1 rounded-lg text-xs font-bold tracking-wide flex items-center gap-1.5 border',
                    isWholesaleInvoice 
                      ? 'bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' 
                      : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 border-slate-200 dark:border-zinc-700'
                  ]"
                >
                  <span class="w-2 h-2 rounded-full" :class="isWholesaleInvoice ? 'bg-indigo-600 dark:bg-indigo-400' : 'bg-slate-400 dark:bg-zinc-500'"></span>
                  {{ isWholesaleInvoice ? 'Wholesale Mode' : 'Retail Mode' }}
                </span>
              </div>

              <!-- Dates Subtitle -->
              <p class="text-xs text-slate-500 dark:text-zinc-400 pl-9 font-medium flex items-center gap-3">
                <span>Created on: <strong class="text-slate-700 dark:text-zinc-200">{{ formatDate(invoice.sale_date) }}</strong></span>
                <span v-if="invoice.due_date">• Due date: <strong class="text-amber-600 dark:text-amber-400">{{ formatDate(invoice.due_date) }}</strong></span>
              </p>
            </div>

            <!-- Quick Action Buttons Bar -->
            <div class="flex items-center flex-wrap gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-zinc-800">
              
              <!-- WhatsApp Share Button -->
              <button
                @click="shareWhatsApp"
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center space-x-1.5 cursor-pointer"
                title="Send Invoice details via WhatsApp"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                </svg>
                <span>WhatsApp</span>
              </button>

              <!-- Print POS Receipt Button -->
              <button
                @click="printPOSReceipt"
                class="px-3.5 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5 border border-slate-200 dark:border-zinc-700 cursor-pointer"
              >
                <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>POS Receipt</span>
              </button>

              <!-- Print / Download PDF Button -->
              <button
                @click="printPDF"
                class="px-3.5 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5 border border-slate-200 dark:border-zinc-700 cursor-pointer"
              >
                <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>PDF / Print</span>
              </button>

              <!-- Edit Invoice Button -->
              <button
                @click="editInvoice"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center space-x-1.5 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Invoice</span>
              </button>
            </div>
          </div>
        </div>

        <!-- 2-Column Responsive Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- LEFT COLUMN (70% width / 2-span): Customer Info + Items Table + Notes & Terms -->
          <div class="lg:col-span-2 space-y-6">
            
            <!-- Customer Information Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-4">
              <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800/80 pb-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 flex items-center gap-2">
                  <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Customer Information
                </h3>
                <span v-if="invoice.customer" class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                  Registered Customer
                </span>
                <span v-else class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400">
                  Walk-in Customer
                </span>
              </div>

              <div v-if="invoice.customer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p class="text-[11px] font-medium text-slate-400 dark:text-zinc-500">Customer Name</p>
                  <p class="text-base font-extrabold text-slate-900 dark:text-zinc-100 mt-0.5">{{ invoice.customer.name }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-medium text-slate-400 dark:text-zinc-500">Phone / Contact</p>
                  <p class="text-xs font-bold text-slate-700 dark:text-zinc-200 mt-0.5">{{ invoice.customer.phone || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-medium text-slate-400 dark:text-zinc-500">Email Address</p>
                  <p class="text-xs font-medium text-slate-700 dark:text-zinc-300 mt-0.5 truncate">{{ invoice.customer.email || 'N/A' }}</p>
                </div>
                <div v-if="invoice.customer.address" class="md:col-span-3 pt-2 border-t border-slate-50 dark:border-zinc-800/40">
                  <p class="text-[11px] font-medium text-slate-400 dark:text-zinc-500">Billing Address</p>
                  <p class="text-xs font-medium text-slate-700 dark:text-zinc-300 mt-0.5">
                    {{ invoice.customer.address }}
                    <span v-if="invoice.customer.city || invoice.customer.state">
                      , {{ invoice.customer.city }}{{ invoice.customer.city && invoice.customer.state ? ', ' : '' }}{{ invoice.customer.state }}
                    </span>
                  </p>
                </div>
              </div>
              <div v-else class="py-2 text-xs font-bold text-slate-500 dark:text-zinc-400 italic">
                Walk-in Customer (Over-the-Counter Direct Sale)
              </div>
            </div>

            <!-- Items Table Grid -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
              <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-800/20">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 flex items-center gap-2">
                  <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  Purchased Items List
                </h3>
                <span class="text-xs font-bold text-slate-500 dark:text-zinc-400">
                  {{ (invoice.sale_items || []).length }} item(s)
                </span>
              </div>

              <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-xs text-left">
                  <thead class="bg-slate-100/80 dark:bg-zinc-800/80 text-slate-500 dark:text-zinc-400 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-zinc-700/80">
                    <tr>
                      <th class="py-3 px-4">Product Details</th>
                      <th class="py-3 px-2 text-center">QTY</th>
                      <th class="py-3 px-3 text-right">Unit Price</th>
                      <th class="py-3 px-3 text-center">Tax</th>
                      <th class="py-3 px-3 text-right">Discount</th>
                      <th class="py-3 px-4 text-right">Row Total</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                    <tr
                      v-for="(item, idx) in invoice.sale_items"
                      :key="item.id || idx"
                      class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/30 transition-colors"
                    >
                      <!-- Product Details & Tags -->
                      <td class="py-3 px-4">
                        <div class="space-y-1">
                          <div class="flex items-center space-x-2">
                            <span class="font-extrabold text-slate-900 dark:text-zinc-100 text-xs">
                              {{ item.product?.name || 'Product' }}
                            </span>
                            
                            <!-- Wholesale Badge -->
                            <span
                              v-if="item.is_wholesale || isWholesaleInvoice"
                              class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-300/50 dark:border-indigo-800"
                            >
                              W.S
                            </span>
                          </div>

                          <div class="flex items-center gap-3 text-[11px] text-slate-400 dark:text-zinc-500">
                            <span v-if="item.product?.sku">SKU: <strong class="text-slate-600 dark:text-zinc-400">{{ item.product.sku }}</strong></span>
                            <span v-if="item.warehouse?.name">WH: <strong class="text-slate-600 dark:text-zinc-400">{{ item.warehouse.name }}</strong></span>
                          </div>

                          <p v-if="item.description" class="text-[11px] text-slate-500 dark:text-zinc-400 italic bg-slate-50 dark:bg-zinc-800/40 p-1.5 rounded-md border border-slate-100 dark:border-zinc-800/80">
                            {{ item.description }}
                          </p>
                        </div>
                      </td>

                      <!-- Quantity -->
                      <td class="py-3 px-2 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-zinc-800 font-extrabold text-slate-800 dark:text-zinc-200">
                          {{ item.quantity }}
                        </span>
                      </td>

                      <!-- Unit Price -->
                      <td class="py-3 px-3 text-right font-bold text-slate-800 dark:text-zinc-200">
                        {{ formatCurrency(item.unit_price) }}
                      </td>

                      <!-- Tax -->
                      <td class="py-3 px-3 text-center">
                        <span v-if="item.tax_rate > 0 || item.tax" class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                          {{ item.tax?.name || `Tax ${item.tax_rate}%` }}
                        </span>
                        <span v-else class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">
                          No Tax
                        </span>
                      </td>

                      <!-- Line Discount -->
                      <td class="py-3 px-3 text-right font-medium">
                        <span v-if="parseFloat(item.discount_amount) > 0" class="text-rose-600 dark:text-rose-400 font-bold">
                          -{{ formatCurrency(item.discount_amount) }}
                          <span v-if="item.discount_type === 'percentage'" class="text-[10px] text-rose-500">({{ item.discount_amount }}%)</span>
                        </span>
                        <span v-else class="text-slate-400 dark:text-zinc-600">-</span>
                      </td>

                      <!-- Total Row Price -->
                      <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-zinc-100 text-sm">
                        {{ formatCurrency(item.total_amount || item.total_price) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Notes & Terms Footer Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              
              <!-- Notes to Customer Card -->
              <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-1.5">
                <h4 class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Notes to Customer
                </h4>
                <p class="text-xs text-slate-600 dark:text-zinc-300 font-medium leading-relaxed">
                  {{ invoice.notes || 'No custom notes attached to this invoice.' }}
                </p>
              </div>

              <!-- Footer / Terms & Conditions Card -->
              <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-1.5">
                <h4 class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Terms & Conditions
                </h4>
                <p class="text-xs text-slate-600 dark:text-zinc-300 font-medium leading-relaxed">
                  {{ invoice.footer || 'Thank you for your business!' }}
                </p>
              </div>
            </div>

          </div>

          <!-- RIGHT COLUMN (30% width / 1-span): Store Context + Financial Summary Breakdown -->
          <div class="space-y-6">
            
            <!-- Store & Sales Context Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-3.5">
              <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 border-b border-slate-100 dark:border-zinc-800 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Store & Sales Context
              </h3>

              <div class="space-y-3 text-xs">
                <!-- Warehouse Location -->
                <div class="flex justify-between items-center">
                  <span class="text-slate-500 dark:text-zinc-400 font-medium">Warehouse:</span>
                  <span class="font-extrabold text-slate-800 dark:text-zinc-200">
                    {{ invoice.warehouse?.name || 'Main Shop' }}
                  </span>
                </div>

                <!-- Counter / POS Terminal -->
                <div class="flex justify-between items-center">
                  <span class="text-slate-500 dark:text-zinc-400 font-medium">Counter / Terminal:</span>
                  <span class="font-extrabold text-indigo-600 dark:text-indigo-400">
                    {{ invoice.counter ? `${invoice.counter.name} (${invoice.counter.counter_number ? `#${invoice.counter.counter_number}` : ''})` : 'Main Counter' }}
                  </span>
                </div>

                <!-- Sales Representative / Cashier -->
                <div class="flex justify-between items-center">
                  <span class="text-slate-500 dark:text-zinc-400 font-medium">Sales Rep / Cashier:</span>
                  <span class="font-extrabold text-slate-800 dark:text-zinc-200">
                    {{ invoice.salesman ? (invoice.salesman.full_name || `${invoice.salesman.first_name || ''} ${invoice.salesman.last_name || ''}`.trim()) : (invoice.user?.name || 'N/A') }}
                  </span>
                </div>

                <!-- Order Reference Number -->
                <div class="flex justify-between items-center">
                  <span class="text-slate-500 dark:text-zinc-400 font-medium">Order Ref #:</span>
                  <span class="font-bold text-slate-700 dark:text-zinc-300">
                    {{ invoice.order_number || 'N/A' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Complete Financial Breakdown Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-4">
              <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 border-b border-slate-100 dark:border-zinc-800 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Financial Summary Breakdown
              </h3>

              <div class="space-y-2.5 text-xs">
                
                <!-- Subtotal -->
                <div class="flex justify-between font-semibold text-slate-600 dark:text-zinc-400">
                  <span>Subtotal:</span>
                  <span class="font-bold text-slate-900 dark:text-zinc-100">{{ formatCurrency(invoice.subtotal) }}</span>
                </div>

                <!-- System Required Taxes List -->
                <template v-if="autoRequiredTaxesList.length > 0">
                  <div
                    v-for="reqTax in autoRequiredTaxesList"
                    :key="'summary-req-tax-' + reqTax.id"
                    class="flex justify-between items-center font-medium transition-all py-0.5"
                    :class="reqTax.enabled ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-zinc-500 line-through opacity-60'"
                  >
                    <div class="flex items-center gap-1.5">
                      <span>{{ reqTax.name }} ({{ reqTax.rate }}{{ reqTax.type === 'percentage' ? '%' : '' }}):</span>
                      <span
                        :class="[
                          'px-1.5 py-0.2 text-[8px] font-black uppercase tracking-wider rounded border',
                          reqTax.enabled ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-300/60' : 'bg-slate-200 dark:bg-zinc-800 text-slate-500 border-slate-300 dark:border-zinc-700'
                        ]"
                      >
                        {{ reqTax.enabled ? 'Applied' : 'Exempted' }}
                      </span>
                    </div>
                    <span>{{ reqTax.enabled ? '+' + formatCurrency(reqTax.amount) : formatCurrency(0) }}</span>
                  </div>
                </template>

                <!-- Manual Tax -->
                <div v-if="calculatedManualTax > 0" class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                  <span>
                    Taxes (Manual):
                    <span v-if="invoice.manual_tax_type === 'percentage' || invoice.tax_type === 'percentage'" class="text-[10px] text-indigo-500 font-extrabold">({{ invoice.manual_tax_value || invoice.tax_amount }}%)</span>
                  </span>
                  <span class="font-bold text-slate-900 dark:text-zinc-100">+{{ formatCurrency(calculatedManualTax) }}</span>
                </div>

                <!-- Manual Discount -->
                <div v-if="calculatedManualDiscount > 0" class="flex justify-between font-medium text-rose-600 dark:text-rose-400">
                  <span>
                    Discount (Manual):
                    <span v-if="invoice.manual_discount_type === 'percentage' || invoice.discount_type === 'percentage'" class="text-[10px] text-rose-500 font-extrabold">({{ invoice.manual_discount_value || invoice.discount_amount }}%)</span>
                  </span>
                  <span class="font-extrabold">-{{ formatCurrency(calculatedManualDiscount) }}</span>
                </div>

                <!-- Grand Total -->
                <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 dark:text-zinc-100 border-t border-slate-200 dark:border-zinc-800 pt-3 mt-2">
                  <span>Grand Total:</span>
                  <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ formatCurrency(invoice.total_amount) }}</span>
                </div>

                <!-- Payment Details Card & Paid State -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-3 border border-slate-200/60 dark:border-zinc-700/60 space-y-2 mt-3">
                  <div class="flex justify-between items-center text-[11px] font-bold text-slate-500 dark:text-zinc-400">
                    <span>Payment Method:</span>
                    <span class="px-2 py-0.5 rounded bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 text-slate-800 dark:text-zinc-200 uppercase font-black text-[10px]">
                      {{ formatPaymentMethod(invoice.payment_method) }}
                    </span>
                  </div>

                  <!-- Payments breakdown if array -->
                  <div v-if="Array.isArray(invoice.payment_details) && invoice.payment_details.length > 0" class="space-y-1 pt-1 border-t border-slate-200/40 dark:border-zinc-700/40">
                    <div v-for="(pm, pidx) in invoice.payment_details" :key="pidx" class="flex justify-between text-[11px]">
                      <span class="text-slate-500 capitalize">{{ formatPaymentMethod(pm.method) }}:</span>
                      <span class="font-bold text-slate-700 dark:text-zinc-300">{{ formatCurrency(pm.amount) }}</span>
                    </div>
                  </div>

                  <div class="flex justify-between items-center font-bold text-xs pt-1 border-t border-slate-200/50 dark:border-zinc-700/50">
                    <span class="text-emerald-700 dark:text-emerald-400">Total Paid:</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ formatCurrency(invoice.paid_amount) }}</span>
                  </div>

                  <div v-if="parseFloat(invoice.change_amount || 0) > 0" class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-500">Change Given:</span>
                    <span class="text-slate-700 dark:text-zinc-300 font-extrabold">{{ formatCurrency(invoice.change_amount) }}</span>
                  </div>

                  <div v-if="dueAmount > 0" class="flex justify-between items-center font-black text-xs pt-1 border-t border-slate-200/50 dark:border-zinc-700/50 text-rose-600 dark:text-rose-400">
                    <span>Remaining Due:</span>
                    <span class="text-sm font-black">{{ formatCurrency(dueAmount) }}</span>
                  </div>
                </div>

              </div>
            </div>

          </div>

        </div>

      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const { showToast } = useToast();
const currencyStore = useCurrencyStore();

const invoice = ref(null);
const taxes = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchTaxes = async () => {
  try {
    const res = await api.get('/taxes');
    taxes.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
  } catch (e) {
    console.error('Error fetching taxes:', e);
  }
};

const fetchInvoice = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await api.get(`/sales/${route.params.id}`);
    invoice.value = response.data?.sale || response.data;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load invoice details';
    showToast('Error loading invoice', 'error');
  } finally {
    loading.value = false;
  }
};

const isWholesaleInvoice = computed(() => {
  if (!invoice.value) return false;
  return invoice.value.sales_mode === 'wholesale' || invoice.value.pricing_mode === 'wholesale' || invoice.value.is_wholesale === true;
});

const invoiceStatusLabel = computed(() => {
  if (!invoice.value) return '';
  const paid = parseFloat(invoice.value.paid_amount || 0);
  const total = parseFloat(invoice.value.total_amount || 0);
  if (invoice.value.status === 'draft') return 'DRAFT';
  if (invoice.value.status === 'cancelled') return 'CANCELLED';
  if (paid >= total && total > 0) return 'PAID';
  if (paid > 0 && paid < total) return 'PARTIAL';
  return 'UNPAID';
});

const invoiceStatusStyle = computed(() => {
  const lbl = invoiceStatusLabel.value;
  if (lbl === 'PAID') {
    return {
      bg: 'bg-emerald-100 dark:bg-emerald-950/80',
      text: 'text-emerald-700 dark:text-emerald-300',
      border: 'border-emerald-300 dark:border-emerald-800'
    };
  }
  if (lbl === 'PARTIAL') {
    return {
      bg: 'bg-amber-100 dark:bg-amber-950/80',
      text: 'text-amber-700 dark:text-amber-300',
      border: 'border-amber-300 dark:border-amber-800'
    };
  }
  if (lbl === 'UNPAID') {
    return {
      bg: 'bg-rose-100 dark:bg-rose-950/80',
      text: 'text-rose-700 dark:text-rose-300',
      border: 'border-rose-300 dark:border-rose-800'
    };
  }
  return {
    bg: 'bg-slate-100 dark:bg-zinc-800',
    text: 'text-slate-600 dark:text-zinc-300',
    border: 'border-slate-300 dark:border-zinc-700'
  };
});

const disabledRequiredTaxIds = computed(() => {
  if (!invoice.value) return [];
  let disabledIds = [];
  const sale = invoice.value;
  if (sale.disabled_tax_ids !== undefined && sale.disabled_tax_ids !== null) {
    if (typeof sale.disabled_tax_ids === 'string') {
      try { disabledIds = JSON.parse(sale.disabled_tax_ids); } catch(e){}
    } else if (Array.isArray(sale.disabled_tax_ids)) {
      disabledIds = sale.disabled_tax_ids;
    }
  } else if (sale.excluded_tax_ids !== undefined && sale.excluded_tax_ids !== null) {
    if (typeof sale.excluded_tax_ids === 'string') {
      try { disabledIds = JSON.parse(sale.excluded_tax_ids); } catch(e){}
    } else if (Array.isArray(sale.excluded_tax_ids)) {
      disabledIds = sale.excluded_tax_ids;
    }
  } else if (sale.applied_tax_ids !== undefined && sale.applied_tax_ids !== null) {
    let appIds = [];
    if (typeof sale.applied_tax_ids === 'string') {
      try { appIds = JSON.parse(sale.applied_tax_ids); } catch(e){}
    } else if (Array.isArray(sale.applied_tax_ids)) {
      appIds = sale.applied_tax_ids;
    }
    const allReq = taxes.value.filter(t => (t.is_active || t.is_active === 1) && (t.sale_invoice_required || t.sale_invoice_required === 1));
    disabledIds = allReq.map(t => Number(t.id)).filter(id => !appIds.some(aid => Number(aid) === id));
  }
  return (disabledIds || []).map(id => Number(id));
});

const autoRequiredTaxesList = computed(() => {
  if (!invoice.value) return [];
  const sub = parseFloat(invoice.value.subtotal || 0);
  const required = taxes.value.filter(t => (t.is_active || t.is_active === 1 || t.is_active === '1') && (t.sale_invoice_required || t.sale_invoice_required === 1 || t.sale_invoice_required === '1'));
  return required.map(t => {
    const val = parseFloat(t.value) || 0;
    const isEnabled = !disabledRequiredTaxIds.value.some(id => Number(id) === Number(t.id));
    const amt = isEnabled ? (t.type === 'percentage' ? (sub * val) / 100 : val) : 0;
    return {
      id: t.id,
      name: t.name,
      rate: val,
      type: t.type || 'percentage',
      amount: amt,
      enabled: isEnabled
    };
  });
});

const calculatedManualTax = computed(() => {
  if (!invoice.value) return 0;
  const sub = parseFloat(invoice.value.subtotal || 0);
  const rawVal = parseFloat(invoice.value.manual_tax_value ?? invoice.value.tax_amount ?? 0);
  const type = invoice.value.manual_tax_type || invoice.value.tax_type;
  if (type === 'percentage') {
    return (sub * rawVal) / 100;
  }
  return rawVal;
});

const calculatedManualDiscount = computed(() => {
  if (!invoice.value) return 0;
  const sub = parseFloat(invoice.value.subtotal || 0);
  const rawVal = parseFloat(invoice.value.manual_discount_value ?? invoice.value.discount_amount ?? 0);
  const type = invoice.value.manual_discount_type || invoice.value.discount_type;
  if (type === 'percentage') {
    return (sub * rawVal) / 100;
  }
  return rawVal;
});

const dueAmount = computed(() => {
  if (!invoice.value) return 0;
  const total = parseFloat(invoice.value.total_amount || 0);
  const paid = parseFloat(invoice.value.paid_amount || 0);
  return Math.max(0, total - paid);
});

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCurrency = (amount) => {
  return currencyStore.formatPrice(amount || 0);
};

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    card: 'Card',
    bank_transfer: 'Bank Transfer',
    mobile_payment: 'Mobile Payment',
    mixed: 'Mixed Payment'
  };
  return methods[method] || method || 'Cash';
};

const goBack = () => {
  router.push('/sales/invoices');
};

const editInvoice = () => {
  router.push(`/sales/invoices/${route.params.id}/edit`);
};

const printPOSReceipt = () => {
  const printUrl = router.resolve(`/sales/invoices/${route.params.id}?print=1`).href;
  window.open(printUrl, '_blank', 'width=400,height=600');
};

const printPDF = () => {
  window.print();
};

const shareWhatsApp = () => {
  if (!invoice.value) return;
  const custName = invoice.value.customer?.name || 'Customer';
  const phone = (invoice.value.customer?.phone || '').replace(/[^0-9]/g, '');
  const text = encodeURIComponent(
    `Hello ${custName},\nHere is your invoice detail:\nInvoice Number: ${invoice.value.sale_number}\nTotal Amount: ${formatCurrency(invoice.value.total_amount)}\nPaid: ${formatCurrency(invoice.value.paid_amount)}\nDue: ${formatCurrency(dueAmount.value)}\nThank you!`
  );
  const url = phone ? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`;
  window.open(url, '_blank');
};

onMounted(async () => {
  await fetchTaxes();
  await fetchInvoice();
});
</script>

<style scoped>
@media print {
  body {
    background: white !important;
    color: black !important;
  }
  .no-print, button {
    display: none !important;
  }
}
</style>
