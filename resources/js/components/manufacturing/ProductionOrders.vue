<template>
  <div class="w-full mx-auto p-3 sm:p-4 lg:p-5 bg-slate-50/50 dark:bg-zinc-950 min-h-screen">
    <div class="w-full max-w-full mx-auto space-y-4">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-slate-100 dark:bg-[#252525] border border-slate-200 dark:border-[#2E2E2E] rounded-2xl text-slate-800 dark:text-slate-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.11a2 2 0 01-1.022-.547l-2.387-.477a6 6 0 00-3.86.517" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-slate-100 tracking-tight">Batch Production & Manufacturing</h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 font-medium">Execute production runs to consume raw material inventory and automatically increase finished good stock.</p>
          </div>
        </div>

        <div class="flex items-center gap-3 self-end sm:self-auto">
          <button
            @click="openModal()"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold rounded-full shadow-xs transition-all duration-200 text-sm cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            + New Production Order
          </button>
        </div>
      </div>

      <!-- Datatable Container Card -->
      <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2E2E2E] overflow-hidden shadow-sm flex flex-col justify-between min-h-[500px]">
        
        <!-- Filter Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-100 dark:border-[#2E2E2E]">
          <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search Bar -->
            <div class="relative w-full sm:w-72">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </span>
              <input
                type="text"
                v-model="tableFilters.search"
                @input="handleSearchInput"
                placeholder="Search Order # or Product..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-200 dark:border-[#2E2E2E] rounded-xl text-gray-900 dark:text-slate-200 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 transition-all placeholder:text-gray-400 dark:placeholder:text-slate-500 font-medium"
              />
            </div>

            <!-- Status Filter -->
            <select
              v-model="tableFilters.status"
              @change="fetchOrders(1)"
              class="px-3 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-200 dark:border-[#2E2E2E] rounded-xl text-gray-800 dark:text-slate-200 font-medium focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 cursor-pointer"
            >
              <option value="">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>

            <!-- Target Warehouse Filter -->
            <select
              v-model="tableFilters.warehouse_id"
              @change="fetchOrders(1)"
              class="px-3 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-200 dark:border-[#2E2E2E] rounded-xl text-gray-800 dark:text-slate-200 font-medium focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 cursor-pointer"
            >
              <option value="">All Warehouses</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>

            <!-- Reset Filters -->
            <button
              v-if="hasActiveFilters"
              @click="resetFilters()"
              class="px-3 py-1.5 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40 rounded-xl text-xs font-semibold hover:bg-rose-100 transition cursor-pointer"
            >
              Reset Filters
            </button>
          </div>
        </div>

        <!-- Datatable Table Area -->
        <div class="w-full overflow-x-auto min-h-[350px]">
          <table class="w-full table-auto align-middle border-collapse">
            <thead>
              <tr class="border-b border-gray-100 dark:border-[#2E2E2E] bg-slate-50/50 dark:bg-[#252525] text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400 select-none">
                <th
                  @click="sortByColumn('production_number')"
                  class="px-4 py-3.5 text-left cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center gap-1.5">
                    <span>Order #</span>
                    <span v-if="tableFilters.sort_by === 'production_number'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th class="px-4 py-3.5 text-left">Finished Good Item</th>
                <th class="px-4 py-3.5 text-left">Warehouse</th>

                <th
                  @click="sortByColumn('quantity_to_produce')"
                  class="px-4 py-3.5 text-center cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center justify-center gap-1.5">
                    <span>Qty Produced</span>
                    <span v-if="tableFilters.sort_by === 'quantity_to_produce'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th
                  @click="sortByColumn('total_cost')"
                  class="px-4 py-3.5 text-center cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center justify-center gap-1.5">
                    <span>Batch Cost</span>
                    <span v-if="tableFilters.sort_by === 'total_cost'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th
                  @click="sortByColumn('status')"
                  class="px-4 py-3.5 text-center cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center justify-center gap-1.5">
                    <span>Status</span>
                    <span v-if="tableFilters.sort_by === 'status'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th
                  @click="sortByColumn('production_date')"
                  class="px-4 py-3.5 text-center cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center justify-center gap-1.5">
                    <span>Production Date</span>
                    <span v-if="tableFilters.sort_by === 'production_date'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th class="px-4 py-3.5 text-center">Expiry Date</th>
                <th class="px-4 py-3.5 text-right w-44">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-[#2E2E2E]">
              <!-- Loading State -->
              <tr v-if="loading">
                <td colspan="9" class="px-4 py-16 text-center text-gray-400">
                  <div class="flex justify-center items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-emerald-600" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Fetching production orders...</span>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-else-if="orders.length === 0">
                <td colspan="9" class="px-4 py-16 text-center text-gray-500">
                  <div class="flex flex-col items-center max-w-sm mx-auto">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#252525] flex items-center justify-center mb-3 text-slate-500">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.11a2 2 0 01-1.022-.547l-2.387-.477a6 6 0 00-3.86.517" />
                      </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-900 dark:text-slate-200 mb-1">No Production Orders Found</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mb-3">Start a production batch to create finished stock from raw materials.</p>
                    <button
                      @click="openModal()"
                      class="px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 text-xs font-bold rounded-xl shadow cursor-pointer"
                    >
                      + New Production Order
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Data Rows -->
              <tr
                v-else
                v-for="order in orders"
                :key="order.id"
                class="hover:bg-slate-50/50 dark:hover:bg-[#2D2D2D]/60 transition-colors"
              >
                <!-- Order # -->
                <td class="px-4 py-3.5 font-mono font-bold text-slate-900 dark:text-slate-100 text-xs">
                  {{ order.production_number }}
                </td>

                <!-- Finished Good Item -->
                <td class="px-4 py-3.5">
                  <div class="font-bold text-slate-900 dark:text-slate-100 text-xs sm:text-sm">{{ order.product ? order.product.name : 'N/A' }}</div>
                  <div class="text-[11px] text-slate-400">Recipe: {{ order.recipe ? order.recipe.name : 'N/A' }}</div>
                </td>

                <!-- Warehouse -->
                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-semibold text-xs">
                  {{ order.warehouse ? order.warehouse.name : 'Default' }}
                </td>

                <!-- Qty Produced -->
                <td class="px-4 py-3.5 text-center font-extrabold text-emerald-600 dark:text-emerald-400 text-xs">
                  +{{ formatNumber(order.quantity_to_produce) }} units
                </td>

                <!-- Batch Cost -->
                <td class="px-4 py-3.5 text-center font-extrabold text-slate-900 dark:text-slate-100 text-xs">
                  ${{ formatMoney(order.total_cost) }}
                </td>

                <!-- Status -->
                <td class="px-4 py-3.5 text-center">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold"
                    :class="getStatusBadgeClass(order.status)"
                  >
                    {{ formatStatus(order.status) }}
                  </span>
                </td>

                <!-- Production Date -->
                <td class="px-4 py-3.5 text-center text-slate-600 dark:text-slate-400 font-medium text-xs">
                  {{ formatDate(order.production_date) }}
                </td>

                <!-- Expiry Date -->
                <td class="px-4 py-3.5 text-center text-slate-600 dark:text-slate-400 font-medium text-xs">
                  {{ order.expiry_date ? formatDate(order.expiry_date) : 'N/A' }}
                </td>

                <!-- Actions -->
                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1">
                    <!-- Complete Button (If draft/in_progress) -->
                    <button
                      v-if="order.status !== 'completed' && order.status !== 'cancelled'"
                      @click="completeOrder(order)"
                      class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition cursor-pointer"
                      title="Complete Batch & Add Stock"
                    >
                      Complete
                    </button>

                    <!-- Edit Button -->
                    <button
                      @click="editOrder(order)"
                      class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition cursor-pointer"
                      title="Edit Production Order"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>

                    <!-- Delete Button -->
                    <button
                      @click="deleteOrder(order)"
                      class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition cursor-pointer"
                      title="Delete Production Order"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Datatable Pagination Footer (Standard Theme Footer) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-gray-100 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E]">
          <!-- Left side: Showing X to Y of Z results | ROWS: dropdown -->
          <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-slate-400 font-medium">
            <span>
              Showing <span class="font-bold text-gray-900 dark:text-slate-200">{{ tablePagination.from || 0 }}</span>
              to <span class="font-bold text-gray-900 dark:text-slate-200">{{ tablePagination.to || 0 }}</span>
              of <span class="font-bold text-gray-900 dark:text-slate-200">{{ tablePagination.total || 0 }}</span> results
            </span>
            
            <div class="h-4 w-px bg-gray-200 dark:bg-[#2E2E2E]"></div>

            <div class="flex items-center gap-2">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">ROWS:</span>
              <select
                v-model.number="tablePagination.per_page"
                @change="handlePerPageChange"
                class="px-2.5 py-1 bg-slate-50 dark:bg-[#252525] border border-gray-200 dark:border-[#2E2E2E] rounded-lg text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none cursor-pointer"
              >
                <option :value="10">10</option>
                <option :value="15">15</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
            </div>
          </div>

          <!-- Right side: First, Prev, Page numbers, Next, Last -->
          <div class="flex items-center gap-1.5 text-xs">
            <button
              @click="changePage(1)"
              :disabled="tablePagination.current_page <= 1"
              class="px-3 py-1.5 border border-gray-200 dark:border-[#2E2E2E] rounded-lg bg-white dark:bg-[#1E1E1E] text-slate-600 dark:text-slate-300 font-medium disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-[#252525] transition cursor-pointer"
            >
              First
            </button>

            <button
              @click="changePage(tablePagination.current_page - 1)"
              :disabled="tablePagination.current_page <= 1"
              class="px-3 py-1.5 border border-gray-200 dark:border-[#2E2E2E] rounded-lg bg-white dark:bg-[#1E1E1E] text-slate-600 dark:text-slate-300 font-medium disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-[#252525] transition cursor-pointer"
            >
              Prev
            </button>

            <button
              v-for="p in paginationPages"
              :key="p"
              @click="changePage(p)"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer',
                p === tablePagination.current_page
                  ? 'bg-emerald-600 text-white shadow-xs'
                  : 'bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#252525]'
              ]"
            >
              {{ p }}
            </button>

            <button
              @click="changePage(tablePagination.current_page + 1)"
              :disabled="tablePagination.current_page >= tablePagination.last_page"
              class="px-3 py-1.5 border border-gray-200 dark:border-[#2E2E2E] rounded-lg bg-white dark:bg-[#1E1E1E] text-slate-600 dark:text-slate-300 font-medium disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-[#252525] transition cursor-pointer"
            >
              Next
            </button>

            <button
              @click="changePage(tablePagination.last_page)"
              :disabled="tablePagination.current_page >= tablePagination.last_page"
              class="px-3 py-1.5 border border-gray-200 dark:border-[#2E2E2E] rounded-lg bg-white dark:bg-[#1E1E1E] text-slate-600 dark:text-slate-300 font-medium disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-[#252525] transition cursor-pointer"
            >
              Last
            </button>
          </div>
        </div>

      </div>

    </div>

    <!-- Create/Edit Production Order Modal (TELEPORTED TO BODY TO COVER SIDEBAR OVERLAY) -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200 dark:border-zinc-800">
          
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between bg-slate-50 dark:bg-zinc-900">
            <div>
              <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                {{ editingOrderId ? `Edit Production Order Batch #${editingOrderNumber}` : 'Create Production Order Batch' }}
              </h2>
              <p class="text-xs text-slate-500 dark:text-zinc-400">Produce finished goods and automatically adjust inventory stock level.</p>
            </div>
            <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
          </div>

          <!-- Modal Form Body -->
          <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
            <!-- Select Recipe (Finished Goods Search Dropdown) -->
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Select Recipe * (Finished Goods)</label>
              <SystemSelect
                v-model="form.recipe_id"
                :options="recipeOptions"
                placeholder="Search Finished Goods / Recipe"
              />
            </div>

            <!-- Warehouse Search Dropdown & Quantity -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Warehouse *</label>
                <SystemSelect
                  v-model="form.warehouse_id"
                  :options="warehouseOptions"
                  placeholder="Search & Select Warehouse"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Quantity to Produce *</label>
                <input
                  v-model.number="form.quantity_to_produce"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="e.g. 5"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>
            </div>

            <!-- Status Dropdown & Production Date -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Status *</label>
                <select
                  v-model="form.status"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium cursor-pointer"
                >
                  <option value="draft">Draft</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Production Date *</label>
                <input
                  v-model="form.production_date"
                  type="date"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Expiry Date</label>
                <input
                  v-model="form.expiry_date"
                  type="date"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>
            </div>

            <!-- Auto Complete Checkbox -->
            <div v-if="!editingOrderId" class="p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
              <div>
                <label for="autoCompleteBatch" class="text-xs font-bold text-slate-800 dark:text-zinc-200 block cursor-pointer">Immediately Deduct Ingredients & Complete Batch</label>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">Adds quantity directly to finished item stock (e.g., 10 + 5 = 15).</p>
              </div>
              <input
                v-model="form.auto_complete"
                type="checkbox"
                id="autoCompleteBatch"
                class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-zinc-600 cursor-pointer"
              />
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Notes</label>
              <textarea
                v-model="form.notes"
                rows="2"
                placeholder="Batch notes, chef name, shift info..."
                class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
              ></textarea>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 flex items-center justify-end gap-3">
            <button
              @click="closeModal()"
              type="button"
              class="px-4 py-2 bg-slate-200 dark:bg-zinc-800 hover:bg-slate-300 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 text-sm font-semibold rounded-xl transition cursor-pointer"
            >
              Cancel
            </button>
            <button
              @click="saveOrder()"
              :disabled="saving"
              type="button"
              class="px-5 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white font-bold text-sm rounded-xl shadow-xs transition disabled:opacity-50 cursor-pointer"
            >
              {{ saving ? 'Processing...' : (editingOrderId ? 'Update Production Order' : 'Create Production Order') }}
            </button>
          </div>

        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import SystemSelect from '@/components/common/SystemSelect.vue';

const orders = ref([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const editingOrderId = ref(null);
const editingOrderNumber = ref('');

const recipesList = ref([]);
const warehouses = ref([]);
let searchDebounceTimer = null;

const tablePagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const tableFilters = ref({
  search: '',
  status: '',
  warehouse_id: '',
  sort_by: 'created_at',
  sort_order: 'desc',
});

const form = ref({
  recipe_id: null,
  warehouse_id: null,
  quantity_to_produce: 10,
  production_date: new Date().toISOString().substring(0, 10),
  expiry_date: '',
  status: 'draft',
  auto_complete: true,
  notes: '',
});

// Watch auto_complete checkbox to reflect in status
watch(() => form.value.auto_complete, (newVal) => {
  if (!editingOrderId.value && newVal) {
    form.value.status = 'completed';
  } else if (!editingOrderId.value && !newVal && form.value.status === 'completed') {
    form.value.status = 'draft';
  }
});

const recipeOptions = computed(() => {
  return recipesList.value.map(r => ({
    label: `${r.name} (Product: ${r.product ? r.product.name : 'Finished Good'})`,
    value: r.id
  }));
});

const warehouseOptions = computed(() => {
  return warehouses.value.map(w => ({
    label: w.name,
    value: w.id
  }));
});

const hasActiveFilters = computed(() => {
  return !!(tableFilters.value.search || tableFilters.value.status || tableFilters.value.warehouse_id);
});

const paginationPages = computed(() => {
  const pages = [];
  const current = tablePagination.value.current_page;
  const last = tablePagination.value.last_page;
  const delta = 2;
  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i);
  }
  return pages;
});

onMounted(() => {
  fetchOrders();
  fetchRecipes();
  fetchWarehouses();
});

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page: page,
      per_page: tablePagination.value.per_page,
      search: tableFilters.value.search,
      status: tableFilters.value.status,
      warehouse_id: tableFilters.value.warehouse_id,
      sort_by: tableFilters.value.sort_by,
      sort_order: tableFilters.value.sort_order,
    };
    const res = await axios.get('/api/production-orders', { params });
    const dataObj = res.data;
    
    if (dataObj.data !== undefined) {
      orders.value = dataObj.data;
      tablePagination.value = {
        current_page: dataObj.current_page || 1,
        last_page: dataObj.last_page || 1,
        per_page: dataObj.per_page || tablePagination.value.per_page,
        total: dataObj.total || 0,
        from: dataObj.from || 0,
        to: dataObj.to || 0,
      };
    } else {
      orders.value = dataObj || [];
    }
  } catch (err) {
    console.error('Failed to fetch production orders:', err);
  } finally {
    loading.value = false;
  }
};

const handleSearchInput = () => {
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    fetchOrders(1);
  }, 300);
};

const sortByColumn = (col) => {
  if (tableFilters.value.sort_by === col) {
    tableFilters.value.sort_order = tableFilters.value.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    tableFilters.value.sort_by = col;
    tableFilters.value.sort_order = 'asc';
  }
  fetchOrders(1);
};

const handlePerPageChange = () => {
  fetchOrders(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= tablePagination.value.last_page) {
    fetchOrders(page);
  }
};

const fetchRecipes = async () => {
  try {
    const res = await axios.get('/api/recipes', { params: { is_active: true, per_page: 500 } });
    recipesList.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to fetch recipes:', err);
  }
};

const fetchWarehouses = async () => {
  try {
    const res = await axios.get('/api/warehouses');
    warehouses.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to fetch warehouses:', err);
  }
};

const resetFilters = () => {
  tableFilters.value = {
    search: '',
    status: '',
    warehouse_id: '',
    sort_by: 'created_at',
    sort_order: 'desc',
  };
  fetchOrders(1);
};

const openModal = () => {
  editingOrderId.value = null;
  editingOrderNumber.value = '';
  form.value = {
    recipe_id: recipesList.value.length > 0 ? recipesList.value[0].id : null,
    warehouse_id: warehouses.value.length > 0 ? warehouses.value[0].id : null,
    quantity_to_produce: 10,
    production_date: new Date().toISOString().substring(0, 10),
    expiry_date: '',
    status: 'completed',
    auto_complete: true,
    notes: '',
  };
  showModal.value = true;
};

const editOrder = (order) => {
  editingOrderId.value = order.id;
  editingOrderNumber.value = order.production_number;
  form.value = {
    recipe_id: order.recipe_id,
    warehouse_id: order.warehouse_id,
    quantity_to_produce: parseFloat(order.quantity_to_produce),
    production_date: order.production_date ? order.production_date.substring(0, 10) : '',
    expiry_date: order.expiry_date ? order.expiry_date.substring(0, 10) : '',
    status: order.status || 'draft',
    auto_complete: order.status === 'completed',
    notes: order.notes || '',
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingOrderId.value = null;
  editingOrderNumber.value = '';
};

const saveOrder = async () => {
  if (!form.value.recipe_id || !form.value.warehouse_id || form.value.quantity_to_produce <= 0) {
    alert('Please select a recipe, warehouse and specify quantity to produce.');
    return;
  }

  saving.value = true;
  try {
    if (editingOrderId.value) {
      await axios.put(`/api/production-orders/${editingOrderId.value}`, form.value);
    } else {
      await axios.post('/api/production-orders', form.value);
    }
    closeModal();
    fetchOrders(tablePagination.value.current_page);
  } catch (err) {
    console.error('Failed to save production order:', err);
    alert(err.response?.data?.message || 'Error saving production order');
  } finally {
    saving.value = false;
  }
};

const deleteOrder = async (order) => {
  if (!confirm(`Are you sure you want to delete production order #${order.production_number}?`)) return;

  try {
    await axios.delete(`/api/production-orders/${order.id}`);
    fetchOrders(tablePagination.value.current_page);
  } catch (err) {
    console.error('Failed to delete production order:', err);
    alert(err.response?.data?.message || 'Error deleting production order');
  }
};

const completeOrder = async (order) => {
  if (!confirm(`Complete production order #${order.production_number}? Raw material stock will be deducted and ${order.quantity_to_produce} units will be added to finished good stock.`)) return;

  try {
    await axios.post(`/api/production-orders/${order.id}/complete`);
    fetchOrders(tablePagination.value.current_page);
  } catch (err) {
    console.error('Failed to complete order:', err);
    alert(err.response?.data?.message || 'Error completing production order');
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50';
    case 'in_progress': return 'bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50';
    case 'cancelled': return 'bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50';
    default: return 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-zinc-700';
  }
};

const formatStatus = (status) => {
  if (!status) return 'Draft';
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatNumber = (val) => parseFloat(val || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
const formatMoney = (val) => parseFloat(val || 0).toFixed(2);
const formatDate = (val) => val ? new Date(val).toLocaleDateString() : 'N/A';
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
