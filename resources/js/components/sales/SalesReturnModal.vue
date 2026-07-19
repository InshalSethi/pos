<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 bg-slate-900/40 dark:bg-black/50 backdrop-blur-md overflow-y-auto h-full w-full z-[9999] flex items-center justify-center p-4 transition-all duration-300">
      <div class="relative mx-auto border border-slate-100 dark:border-zinc-800 w-full max-w-2xl min-h-[450px] shadow-2xl rounded-xl bg-white dark:bg-zinc-900 text-left transition-all duration-300 flex flex-col max-h-[90vh]" @click.stop>
        
        <!-- Header -->
        <div class="p-6 pb-4 border-b border-slate-100 dark:border-zinc-800 shrink-0 relative">
          <button
            type="button"
            @click="closeModal"
            class="absolute top-5 right-5 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all cursor-pointer"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Process Sales Return</h3>
          <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Create a return for a previous sale</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="processSalesReturn" class="flex flex-col flex-1 min-h-0" novalidate>
          <div class="flex-1 overflow-y-auto p-6 space-y-6 pr-4 custom-scrollbar">
            
            <!-- Original Sale Selection -->
            <div class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5 required">Search Original Sale</label>
                <div class="relative">
                  <input
                    id="original_sale_search"
                    v-model="saleSearchQuery"
                    type="text"
                    placeholder="Enter sale number or customer name..."
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-750': errors.original_sale_id }"
                    @input="searchSales"
                  />
                  
                  <!-- Search Results Dropdown -->
                  <div v-if="searchResults.length > 0" class="absolute z-50 left-0 right-0 mt-1.5 rounded-lg shadow-lg shadow-slate-200/50 dark:shadow-black/30 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 max-h-48 overflow-y-auto py-1 custom-scrollbar-thin">
                    <div
                      v-for="sale in searchResults"
                      :key="sale.id"
                      @click="selectOriginalSale(sale)"
                      class="px-4 py-2 hover:bg-slate-50 dark:hover:bg-zinc-850 cursor-pointer flex justify-between items-center text-xs border-b border-slate-50 dark:border-zinc-800 last:border-0"
                    >
                      <div class="text-left">
                        <span class="font-bold text-slate-800 dark:text-zinc-105">{{ sale.sale_number }}</span>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500">{{ sale.customer?.name || 'Walk-in Customer' }} | {{ formatDate(sale.sale_date) }}</p>
                      </div>
                      <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(sale.total_amount) }}</span>
                    </div>
                  </div>
                </div>
                <p v-if="errors.original_sale_id" class="mt-1 text-[10px] text-red-505">{{ errors.original_sale_id[0] }}</p>
              </div>

              <!-- Selected Sale Info Card -->
              <div v-if="originalSale" class="p-4 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-xl border border-indigo-100 dark:border-indigo-900/30 text-xs flex justify-between items-center">
                <div class="space-y-1 text-left">
                  <span class="font-bold text-indigo-900 dark:text-indigo-300 text-sm">{{ originalSale.sale_number }}</span>
                  <p class="text-slate-650 dark:text-zinc-400"><span class="font-semibold text-slate-400 dark:text-zinc-500">Customer:</span> {{ originalSale.customer?.name || 'Walk-in Customer' }}</p>
                  <p class="text-slate-650 dark:text-zinc-400"><span class="font-semibold text-slate-400 dark:text-zinc-500">Date:</span> {{ formatDate(originalSale.sale_date) }} | <span class="font-semibold text-slate-400 dark:text-zinc-500">Total:</span> {{ formatCurrency(originalSale.total_amount) }}</p>
                </div>
                <button type="button" @click="clearOriginalSale" class="text-rose-600 hover:text-rose-800 dark:text-rose-450 dark:hover:text-rose-350 font-bold text-[10px] hover:underline cursor-pointer">Clear</button>
              </div>
            </div>

            <!-- Return Details -->
            <div v-if="originalSale" class="space-y-4 border-t border-slate-100 dark:border-zinc-800 pt-4">
              <h4 class="text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Return Details</h4>
              <div class="grid grid-cols-2 gap-4">
                
                <!-- Return Date with Custom Calendar Popover opening upside -->
                <div class="text-left relative">
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5 required">Return Date</label>
                  <div class="relative">
                    <div v-if="showCalendar" class="fixed inset-0 z-40" @click.stop="showCalendar = false"></div>
                    <button
                      type="button"
                      @click="showCalendar = !showCalendar"
                      class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 text-xs bg-white dark:bg-zinc-950 transition-all flex items-center gap-2 text-left cursor-pointer"
                      :class="[form.return_date ? 'text-slate-800 dark:text-zinc-200' : 'text-slate-400 dark:text-zinc-500', { 'border-red-300 dark:border-red-750': errors.return_date }]"
                    >
                      <svg class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="font-medium">{{ form.return_date ? formatDisplayDate(form.return_date) : 'Select date' }}</span>
                    </button>

                    <!-- Custom Calendar Popover (upside) -->
                    <div v-if="showCalendar" class="absolute z-50 left-0 bottom-full mb-1.5 w-64 rounded-xl shadow-lg shadow-slate-200/50 dark:shadow-black/30 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-3 select-none">
                      <!-- Month/Year Nav -->
                      <div class="flex items-center justify-between mb-2.5">
                        <button type="button" @click="calPrevMonth" class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors cursor-pointer">
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        
                        <div class="flex items-center space-x-1">
                          <!-- Month Dropdown -->
                          <div class="relative">
                            <div v-if="showMonthList" class="fixed inset-0 z-40" @click.stop="showMonthList = false"></div>
                            <button
                              type="button"
                              @click="showMonthList = !showMonthList"
                              class="flex items-center space-x-0.5 px-1 py-0.5 text-[11px] font-bold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors cursor-pointer focus:outline-none"
                            >
                              <span>{{ calMonthName.slice(0, 3) }}</span>
                              <svg class="h-2.5 w-2.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': showMonthList }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>
                            <!-- Month Floating List (upside) -->
                            <div
                              v-if="showMonthList"
                              class="absolute z-55 left-0 bottom-full mb-1 w-20 max-h-40 overflow-y-auto rounded-lg shadow-lg shadow-slate-200/50 dark:shadow-black/30 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 py-0.5 custom-scrollbar-thin text-left animate-in fade-in duration-100"
                            >
                              <button
                                v-for="(name, idx) in monthNames"
                                :key="idx"
                                type="button"
                                @click="selectCalMonth(idx)"
                                class="w-full text-left px-2.5 py-1 text-[10px] font-medium transition-colors cursor-pointer"
                                :class="calMonth === idx ? 'text-rose-600 dark:text-rose-455 bg-rose-50 dark:bg-rose-905/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'"
                              >
                                {{ name.slice(0, 3) }}
                              </button>
                            </div>
                          </div>

                          <!-- Year Dropdown -->
                          <div class="relative">
                            <div v-if="showYearList" class="fixed inset-0 z-40" @click.stop="showYearList = false"></div>
                            <button
                              type="button"
                              @click="showYearList = !showYearList"
                              class="flex items-center space-x-0.5 px-1 py-0.5 text-[11px] font-bold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors cursor-pointer focus:outline-none"
                            >
                              <span>{{ calYear }}</span>
                              <svg class="h-2.5 w-2.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': showYearList }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>
                            <!-- Year Floating List (upside) -->
                            <div
                              v-if="showYearList"
                              class="absolute z-55 left-1/2 -translate-x-1/2 bottom-full mb-1 w-20 max-h-40 overflow-y-auto rounded-lg shadow-lg shadow-slate-200/50 dark:shadow-black/30 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 py-0.5 custom-scrollbar-thin text-left animate-in fade-in duration-100"
                            >
                              <button
                                v-for="y in yearOptions"
                                :key="y"
                                type="button"
                                @click="selectCalYear(y)"
                                class="w-full text-left px-2.5 py-1 text-[10px] font-medium transition-colors cursor-pointer"
                                :class="calYear === y ? 'text-rose-600 dark:text-rose-455 bg-rose-50 dark:bg-rose-905/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'"
                              >
                                {{ y }}
                              </button>
                            </div>
                          </div>
                        </div>

                        <button type="button" @click="calNextMonth" class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors cursor-pointer">
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                      </div>
                      <!-- Day Headers -->
                      <div class="grid grid-cols-7 mb-1">
                        <span v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="text-center text-[9px] font-bold text-slate-400 dark:text-zinc-650 uppercase py-1">{{ d }}</span>
                      </div>
                      <!-- Day Grid -->
                      <div class="grid grid-cols-7">
                        <button
                          v-for="(day, i) in calDays" :key="i"
                          type="button"
                          @click="day.val && selectCalDay(day.val)"
                          :disabled="!day.val"
                          class="h-7 w-full rounded-lg text-[11px] font-medium transition-all cursor-pointer disabled:cursor-default disabled:opacity-0"
                          :class="day.val && isSelectedDay(day.val) ? 'bg-rose-600 text-white font-bold' : day.val && isTodayDay(day.val) ? 'text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-50 dark:hover:bg-rose-900/20' : day.val ? 'text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800' : ''"
                        >{{ day.val || '' }}</button>
                      </div>
                      <!-- Quick Actions -->
                      <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" @click="clearCalDate" class="text-[10px] font-semibold text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 transition-colors cursor-pointer">Clear</button>
                        <button type="button" @click="selectToday" class="text-[10px] font-semibold text-rose-650 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition-colors cursor-pointer">Today</button>
                      </div>
                    </div>
                  </div>
                  <p v-if="errors.return_date" class="mt-1 text-[10px] text-red-505">{{ errors.return_date[0] }}</p>
                </div>

                <!-- Custom Return Reason Dropdown opening upside -->
                <div class="text-left relative">
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5 required">Return Reason</label>
                  <div class="relative">
                    <div v-if="showReasonDropdown" class="fixed inset-0 z-40" @click.stop="showReasonDropdown = false"></div>
                    <button
                      type="button"
                      @click="showReasonDropdown = !showReasonDropdown"
                      class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 text-xs bg-white dark:bg-zinc-950 transition-all flex items-center justify-between text-left cursor-pointer"
                      :class="[form.return_reason ? 'text-slate-800 dark:text-zinc-200' : 'text-slate-400 dark:text-zinc-500', { 'border-red-300 dark:border-red-750': errors.return_reason }]"
                    >
                      <span class="font-medium">{{ form.return_reason ? formatReason(form.return_reason) : 'Select Reason' }}</span>
                      <svg class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': showReasonDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                    
                    <div
                      v-if="showReasonDropdown"
                      class="absolute z-50 left-0 right-0 bottom-full mb-1.5 rounded-lg shadow-lg shadow-slate-200/50 dark:shadow-black/30 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 py-0.5 overflow-hidden"
                    >
                      <button type="button" @click="selectReason('defective')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.return_reason === 'defective' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Defective Product</button>
                      <button type="button" @click="selectReason('wrong_item')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.return_reason === 'wrong_item' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Wrong Item</button>
                      <button type="button" @click="selectReason('customer_change_mind')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.return_reason === 'customer_change_mind' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Customer Changed Mind</button>
                      <button type="button" @click="selectReason('damaged_shipping')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.return_reason === 'damaged_shipping' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Damaged in Shipping</button>
                      <button type="button" @click="selectReason('not_as_described')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-855 text-slate-655 dark:text-zinc-300" :class="form.return_reason === 'not_as_described' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Not as Described</button>
                      <button type="button" @click="selectReason('other')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.return_reason === 'other' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Other</button>
                    </div>
                  </div>
                  <p v-if="errors.return_reason" class="mt-1 text-[10px] text-red-550">{{ errors.return_reason[0] }}</p>
                </div>

              </div>

              <div class="text-left">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Return Notes</label>
                <textarea
                  id="return_notes"
                  v-model="form.return_notes"
                  rows="2"
                  placeholder="Additional notes about the return..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                ></textarea>
              </div>
            </div>

            <!-- Items to Return -->
            <div v-if="originalSale && originalSale.sale_items" class="space-y-4 border-t border-slate-100 dark:border-zinc-800 pt-4">
              <div class="flex justify-between items-center mb-2">
                <h4 class="text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Items to Return</h4>
                <div class="flex space-x-2">
                  <button type="button" @click="selectAllItems" class="text-[10px] font-bold text-slate-450 dark:text-zinc-500 hover:text-slate-700 dark:hover:text-zinc-300 transition-colors cursor-pointer">Select All</button>
                  <span class="text-slate-200 dark:text-zinc-800">|</span>
                  <button type="button" @click="clearAllItems" class="text-[10px] font-bold text-slate-455 dark:text-zinc-500 hover:text-slate-700 dark:hover:text-zinc-300 transition-colors cursor-pointer">Clear All</button>
                </div>
              </div>

              <div class="space-y-3">
                <div v-for="(item, index) in originalSale.sale_items" :key="item.id" class="p-3.5 bg-slate-50 dark:bg-zinc-950/30 rounded-xl border border-slate-100 dark:border-zinc-800">
                  <template v-if="form.return_items && form.return_items[item.id]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                      <div class="flex items-start space-x-2.5 flex-1 text-left">
                        <input
                          :id="`item_${item.id}`"
                          v-model="form.return_items[item.id].selected"
                          type="checkbox"
                          class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer w-4 h-4 mt-0.5"
                          @change="updateItemSelection(item.id)"
                        />
                        <label :for="`item_${item.id}`" class="cursor-pointer text-xs">
                          <strong class="font-bold text-slate-800 dark:text-zinc-200 block">{{ item.product?.name || 'Unknown Product' }}</strong>
                          <span class="text-[10px] text-slate-400 dark:text-zinc-500">
                            SKU: {{ item.product?.sku || 'N/A' }} | 
                            Price: {{ formatCurrency(item.unit_price) }}
                          </span>
                        </label>
                      </div>

                      <div v-if="form.return_items[item.id].selected" class="flex items-center space-x-4">
                        <div class="flex flex-col w-14 text-center">
                          <span class="text-[8px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Sold Qty</span>
                          <span class="text-xs font-bold text-slate-700 dark:text-zinc-300 py-1">
                            {{ item.quantity }}
                          </span>
                        </div>

                        <div class="flex flex-col w-20 text-left">
                          <span class="text-[8px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return Qty</span>
                          <input
                            :id="`qty_${item.id}`"
                            v-model.number="form.return_items[item.id].quantity"
                            type="number"
                            :min="1"
                            :max="item.quantity"
                            class="px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 text-xs font-bold text-center text-slate-800 dark:text-zinc-200 bg-white dark:bg-zinc-955"
                            @input="onQuantityChange(item.id, item.unit_price)"
                          />
                        </div>

                        <div class="flex flex-col text-left w-28">
                          <span class="text-[8px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return Amt</span>
                          <div class="relative flex items-center">
                            <span class="absolute left-2 text-xs text-slate-450 dark:text-zinc-500 font-medium">$</span>
                            <input
                              :id="`amt_${item.id}`"
                              v-model.number="form.return_items[item.id].return_amount"
                              type="number"
                              step="0.01"
                              :min="0"
                              :max="item.unit_price * form.return_items[item.id].quantity"
                              class="w-[100px] pl-5 pr-2 py-1 border border-slate-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 text-xs font-bold text-slate-855 dark:text-zinc-200 bg-white dark:bg-zinc-955"
                              @input="onReturnAmountInput(item.id, item.unit_price * form.return_items[item.id].quantity)"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </div>

            <!-- Return Summary / Refund Method -->
            <div v-if="originalSale" class="space-y-4 border-t border-slate-100 dark:border-zinc-800 pt-4">
              <h4 class="text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Summary & Refund</h4>
              
              <div class="grid grid-cols-2 gap-4">
                
                <!-- Custom Refund Method Dropdown opening upside -->
                <div class="text-left relative">
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5 required">Refund Method</label>
                  <div class="relative">
                    <div v-if="showRefundDropdown" class="fixed inset-0 z-40" @click.stop="showRefundDropdown = false"></div>
                    <button
                      type="button"
                      @click="showRefundDropdown = !showRefundDropdown"
                      class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 text-xs bg-white dark:bg-zinc-950 transition-all flex items-center justify-between text-left cursor-pointer"
                      :class="[form.refund_method ? 'text-slate-800 dark:text-zinc-200' : 'text-slate-400 dark:text-zinc-500', { 'border-red-300 dark:border-red-750': errors.refund_method }]"
                    >
                      <span class="font-medium">{{ form.refund_method ? formatRefundMethod(form.refund_method) : 'Select Refund Method' }}</span>
                      <svg class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': showRefundDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                    
                    <div
                      v-if="showRefundDropdown"
                      class="absolute z-50 left-0 right-0 bottom-full mb-1.5 rounded-lg shadow-lg shadow-slate-200/50 dark:shadow-black/30 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 py-0.5 overflow-hidden"
                    >
                      <button type="button" @click="selectRefundMethod('cash')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.refund_method === 'cash' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Cash</button>
                      <button type="button" @click="selectRefundMethod('card')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.refund_method === 'card' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Card Refund</button>
                      <button type="button" @click="selectRefundMethod('store_credit')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.refund_method === 'store_credit' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Store Credit</button>
                      <button type="button" @click="selectRefundMethod('exchange')" class="w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-650 dark:text-zinc-300" :class="form.refund_method === 'exchange' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'">Exchange</button>
                    </div>
                  </div>
                  <p v-if="errors.refund_method" class="mt-1 text-[10px] text-red-505">{{ errors.refund_method[0] }}</p>
                </div>

                <div class="text-left">
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Total Refund</label>
                  <div class="px-3 py-2 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-lg text-xs font-bold text-rose-650 dark:text-rose-400 text-left flex items-center h-[34px]">
                    {{ formatCurrency(form.total_return_amount) }}
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Footer Buttons -->
          <div class="flex justify-between items-center p-6 border-t border-slate-100 dark:border-zinc-800 shrink-0 bg-slate-50 dark:bg-zinc-900/50">
            <div class="flex items-center space-x-4">
              <div class="flex flex-col text-left">
                <span class="text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Return Amount</span>
                <span class="text-sm font-black text-rose-650 dark:text-rose-400">{{ formatCurrency(form.total_return_amount) }}</span>
              </div>
              <div class="flex flex-col border-l border-slate-200 dark:border-zinc-800 pl-4 text-left">
                <span class="text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Items</span>
                <span class="text-sm font-bold text-slate-800 dark:text-zinc-200">{{ selectedItemsCount }}</span>
              </div>
            </div>
            <div class="flex space-x-3">
              <button
                type="button"
                @click="closeModal"
                class="px-4 h-9 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 rounded-lg text-xs font-semibold transition-all cursor-pointer"
                :disabled="processing"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="processing || !canProcessReturn"
                class="px-4 h-9 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
              >
                <svg v-if="processing" class="w-3.5 h-3.5 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ processing ? 'Processing...' : 'Process Return' }}</span>
              </button>
            </div>
          </div>
        </form>

      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, computed, watch, onUnmounted } from 'vue';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';

export default {
  name: 'SalesReturnModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    originalSaleId: {
      type: [String, Number],
      default: null
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const currencyStore = useCurrencyStore();
    
    const processing = ref(false);
    const errors = ref({});
    const saleSearchQuery = ref('');
    const searchResults = ref([]);
    const originalSale = ref(null);
    const searchTimeout = ref(null);

    // Custom calendar and dropdown toggles
    const showCalendar = ref(false);
    const calMonth = ref(new Date().getMonth());
    const calYear = ref(new Date().getFullYear());
    const showMonthList = ref(false);
    const showYearList = ref(false);

    const showReasonDropdown = ref(false);
    const showRefundDropdown = ref(false);

    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    const form = reactive({
      original_sale_id: '',
      return_date: new Date().toISOString().split('T')[0],
      return_reason: '',
      return_notes: '',
      refund_method: '',
      total_return_amount: 0,
      return_items: {}
    });

    const selectedItemsCount = computed(() => {
      return Object.values(form.return_items).filter(item => item.selected).length;
    });

    const canProcessReturn = computed(() => {
      return originalSale.value && 
             selectedItemsCount.value > 0 && 
             form.return_reason && 
             form.refund_method &&
             form.total_return_amount > 0;
    });

    const resetForm = () => {
      form.original_sale_id = '';
      form.return_date = new Date().toISOString().split('T')[0];
      form.return_reason = '';
      form.return_notes = '';
      form.refund_method = '';
      form.total_return_amount = 0;
      form.return_items = {};
      originalSale.value = null;
      saleSearchQuery.value = '';
      searchResults.value = [];
      errors.value = {};
      
      showCalendar.value = false;
      showReasonDropdown.value = false;
      showRefundDropdown.value = false;
      calMonth.value = new Date().getMonth();
      calYear.value = new Date().getFullYear();
    };

    const searchSales = () => {
      if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
      }

      searchTimeout.value = setTimeout(async () => {
        if (saleSearchQuery.value.length < 2) {
          searchResults.value = [];
          return;
        }

        try {
          const response = await api.get('/sales', {
            params: {
              search: saleSearchQuery.value,
              status: 'completed',
              per_page: 10
            }
          });
          searchResults.value = response.data.data || [];
        } catch (error) {
          console.error('Error searching sales:', error);
        }
      }, 300);
    };

    const selectOriginalSale = async (sale) => {
      try {
        const response = await api.get(`/sales/${sale.id}`);
        originalSale.value = response.data;
        form.original_sale_id = sale.id;
        saleSearchQuery.value = sale.sale_number;
        searchResults.value = [];

        // Initialize return items
        form.return_items = {};
        if (originalSale.value.sale_items) {
          originalSale.value.sale_items.forEach(item => {
            form.return_items[item.id] = {
              selected: false,
              quantity: 1,
              return_amount: 0,
              unit_price: item.unit_price
            };
          });
        }
      } catch (error) {
        showToast('Error loading sale details', 'error');
      }
    };

    const clearOriginalSale = () => {
      originalSale.value = null;
      form.original_sale_id = '';
      form.return_items = {};
      saleSearchQuery.value = '';
      searchResults.value = [];
      calculateReturnTotal();
    };

    const updateItemSelection = (itemId) => {
      if (!form.return_items[itemId].selected) {
        form.return_items[itemId].quantity = 1;
        form.return_items[itemId].return_amount = 0;
      } else {
        const item = originalSale.value.sale_items.find(i => i.id === itemId);
        if (item) {
          form.return_items[itemId].quantity = 1;
          form.return_items[itemId].return_amount = item.unit_price;
        }
      }
      calculateReturnTotal();
    };

    const selectAllItems = () => {
      if (originalSale.value && originalSale.value.sale_items) {
        originalSale.value.sale_items.forEach(item => {
          form.return_items[item.id].selected = true;
          form.return_items[item.id].quantity = item.quantity;
          form.return_items[item.id].return_amount = item.unit_price * item.quantity;
        });
        calculateReturnTotal();
      }
    };

    const clearAllItems = () => {
      Object.keys(form.return_items).forEach(itemId => {
        form.return_items[itemId].selected = false;
        form.return_items[itemId].quantity = 1;
        form.return_items[itemId].return_amount = 0;
      });
      calculateReturnTotal();
    };

    const onQuantityChange = (itemId, unitPrice) => {
      const item = form.return_items[itemId];
      if (item) {
        if (item.quantity < 1) {
          item.quantity = 1;
        }
        const maxQty = originalSale.value.sale_items.find(i => i.id === itemId)?.quantity || 1;
        if (item.quantity > maxQty) {
          item.quantity = maxQty;
        }
        item.return_amount = unitPrice * item.quantity;
        calculateReturnTotal();
      }
    };

    const onReturnAmountInput = (itemId, maxVal) => {
      const item = form.return_items[itemId];
      if (item) {
        let val = item.return_amount;
        if (val > maxVal) {
          item.return_amount = maxVal;
        } else if (val < 0 || isNaN(val)) {
          item.return_amount = 0;
        }
        calculateReturnTotal();
      }
    };

    const calculateReturnTotal = () => {
      let total = 0;
      Object.values(form.return_items).forEach(item => {
        if (item.selected) {
          total += item.return_amount;
        }
      });
      form.total_return_amount = total;
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString();
    };

    const formatCurrency = (amount) => {
      return currencyStore.formatPrice(amount || 0);
    };

    // Calendar Popover Methods
    const selectCalMonth = (idx) => {
      calMonth.value = idx;
      showMonthList.value = false;
    };

    const selectCalYear = (y) => {
      calYear.value = y;
      showYearList.value = false;
    };

    const formatDisplayDate = (dateStr) => {
      if (!dateStr) return '';
      const onlyDate = dateStr.split(' ')[0].split('T')[0];
      const d = new Date(onlyDate + 'T00:00:00');
      if (isNaN(d.getTime())) {
        const fallbackD = new Date(dateStr);
        if (isNaN(fallbackD.getTime())) return 'Invalid Date';
        return fallbackD.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      }
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const calMonthName = computed(() => monthNames[calMonth.value]);

    const yearOptions = computed(() => {
      const currentYear = new Date().getFullYear();
      const yearsList = [];
      for (let y = currentYear + 5; y >= currentYear - 100; y--) {
        yearsList.push(y);
      }
      return yearsList;
    });

    const calDays = computed(() => {
      const firstDay = new Date(calYear.value, calMonth.value, 1).getDay();
      const daysInMonth = new Date(calYear.value, calMonth.value + 1, 0).getDate();
      const days = [];
      for (let i = 0; i < firstDay; i++) days.push({ val: null });
      for (let d = 1; d <= daysInMonth; d++) days.push({ val: d });
      return days;
    });

    const calPrevMonth = () => {
      if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; }
      else calMonth.value--;
    };
    const calNextMonth = () => {
      if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
      else calMonth.value++;
    };

    const selectCalDay = (day) => {
      const m = String(calMonth.value + 1).padStart(2, '0');
      const d = String(day).padStart(2, '0');
      form.return_date = `${calYear.value}-${m}-${d}`;
      showCalendar.value = false;
    };

    const isSelectedDay = (day) => {
      if (!form.return_date) return false;
      const [sy, sm, sd] = form.return_date.split('-').map(Number);
      return sy === calYear.value && sm === calMonth.value + 1 && sd === day;
    };

    const isTodayDay = (day) => {
      const t = new Date();
      return t.getFullYear() === calYear.value && t.getMonth() === calMonth.value && t.getDate() === day;
    };

    const clearCalDate = () => { form.return_date = ''; showCalendar.value = false; };
    const selectToday = () => {
      const t = new Date();
      calMonth.value = t.getMonth(); calYear.value = t.getFullYear();
      selectCalDay(t.getDate());
    };

    // Reason & Refund Select custom handlers
    const selectReason = (val) => {
      form.return_reason = val;
      showReasonDropdown.value = false;
    };

    const formatReason = (val) => {
      if (!val) return '';
      const map = {
        defective: 'Defective Product',
        wrong_item: 'Wrong Item',
        customer_change_mind: 'Customer Changed Mind',
        damaged_shipping: 'Damaged in Shipping',
        not_as_described: 'Not as Described',
        other: 'Other'
      };
      return map[val] || val;
    };

    const selectRefundMethod = (val) => {
      form.refund_method = val;
      showRefundDropdown.value = false;
    };

    const formatRefundMethod = (val) => {
      if (!val) return '';
      const map = {
        cash: 'Cash',
        card: 'Card Refund',
        store_credit: 'Store Credit',
        exchange: 'Exchange'
      };
      return map[val] || val;
    };

    const validateForm = () => {
      const newErrors = {};

      if (!form.original_sale_id) {
        newErrors.original_sale_id = ['Please select an original sale'];
      }

      if (!form.return_reason) {
        newErrors.return_reason = ['Return reason is required'];
      }

      if (!form.refund_method) {
        newErrors.refund_method = ['Refund method is required'];
      }

      if (selectedItemsCount.value === 0) {
        newErrors.items = ['Please select at least one item to return'];
      }

      errors.value = newErrors;
      return Object.keys(newErrors).length === 0;
    };

    const processSalesReturn = async () => {
      if (!validateForm()) {
        showToast('Please fix the validation errors', 'error');
        return;
      }

      processing.value = true;
      errors.value = {};

      try {
        const returnData = {
          original_sale_id: form.original_sale_id,
          return_date: form.return_date,
          return_reason: form.return_reason,
          return_notes: form.return_notes,
          refund_method: form.refund_method,
          total_return_amount: form.total_return_amount,
          return_items: Object.entries(form.return_items)
            .filter(([_, item]) => item.selected)
            .map(([itemId, item]) => ({
              original_item_id: parseInt(itemId),
              quantity: item.quantity,
              return_amount: item.return_amount
            }))
        };

        const response = await api.sales.processReturn(returnData);

        showToast('Return processed successfully', 'success');
        emit('saved', response.data);
        closeModal();
      } catch (error) {
        console.error('Error processing return:', error);
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors || {};
          showToast('Please fix the validation errors', 'error');
        } else {
          showToast(
            error.response?.data?.message || 'Error processing return. Please try again.',
            'error'
          );
        }
      } finally {
        processing.value = false;
      }
    };

    const closeModal = () => {
      resetForm();
      emit('close');
    };

    // Load original sale if provided
    watch(() => props.originalSaleId, async (newId) => {
      if (newId && props.show) {
        try {
          const response = await api.get(`/sales/${newId}`);
          selectOriginalSale(response.data);
        } catch (error) {
          console.error('Error loading original sale:', error);
        }
      }
    });

    watch(() => props.show, (newVal) => {
      if (newVal) {
        document.body.style.overflow = 'hidden';
        resetForm();
        if (props.originalSaleId) {
          // Will be handled by the originalSaleId watcher
        }
      } else {
        document.body.style.overflow = '';
      }
    });

    onUnmounted(() => {
      document.body.style.overflow = '';
    });

    return {
      processing,
      errors,
      saleSearchQuery,
      searchResults,
      originalSale,
      form,
      selectedItemsCount,
      canProcessReturn,
      resetForm,
      searchSales,
      selectOriginalSale,
      clearOriginalSale,
      updateItemSelection,
      selectAllItems,
      clearAllItems,
      onQuantityChange,
      onReturnAmountInput,
      calculateReturnTotal,
      formatDate,
      formatCurrency,
      validateForm,
      processSalesReturn,
      closeModal,
      
      // Custom calendar / dropdown helpers
      showCalendar,
      calMonth,
      calYear,
      calMonthName,
      calDays,
      calPrevMonth,
      calNextMonth,
      selectCalDay,
      isSelectedDay,
      isTodayDay,
      clearCalDate,
      selectToday,
      formatDisplayDate,
      yearOptions,
      monthNames,
      showMonthList,
      showYearList,
      selectCalMonth,
      selectCalYear,
      
      showReasonDropdown,
      selectReason,
      formatReason,
      showRefundDropdown,
      selectRefundMethod,
      formatRefundMethod
    };
  }
};
</script>

<style scoped>
/* Thin scrollbar styling for floating lists */
.custom-scrollbar-thin::-webkit-scrollbar {
  width: 4px;
  height: 4px;
}
.custom-scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar-thin::-webkit-scrollbar-thumb {
  background: #cbd5e1; /* slate-300 */
  border-radius: 2px;
}
.custom-scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; /* slate-400 */
}
.dark .custom-scrollbar-thin::-webkit-scrollbar-thumb {
  background: #3f3f46; /* zinc-700 */
}
.dark .custom-scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: #52525b; /* zinc-600 */
}

/* Custom scrollbar for the modal body */
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; /* slate-300 */
  border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; /* slate-400 */
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #3f3f46; /* zinc-700 */
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #52525b; /* zinc-600 */
}
</style>
