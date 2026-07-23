<template>
  <div class="w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-7xl mx-auto space-y-6">
      
      <!-- Header Page Title -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">🏢</span>
            Warehouses & Locations
          </h1>
          <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
            Manage multi-warehouse stock allocations, primary branches, and location specific inventories.
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2.5">
          <button
            @click="openCreateModal"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-150 shadow-md flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Create Warehouse
          </button>
        </div>
      </div>



      <!-- Main Layout Pane -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Warehouses list (4 cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-[24px] p-5 shadow-xs space-y-4 flex flex-col justify-between min-h-[50vh]">
          <div class="space-y-4 flex-1 flex flex-col min-h-0">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Storage Facilities</h3>
              <span class="px-2 py-0.5 bg-slate-100 dark:bg-[#252525] text-[10px] font-bold text-slate-600 dark:text-slate-400 rounded-full">
                {{ pagination.total }} Facilities
              </span>
            </div>

            <!-- Search Bar -->
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Filter locations..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 rounded-xl outline-none transition-all dark:text-slate-200"
                @input="debouncedFetch"
              />
              <span class="absolute left-3 top-2.5 text-gray-400 dark:text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              </span>
            </div>

            <!-- List -->
            <div class="space-y-1.5 max-h-[60vh] overflow-y-auto custom-scrollbar flex-1 pr-1">
              <div
                v-for="warehouse in warehouses"
                :key="warehouse.id"
                @click="selectWarehouse(warehouse)"
                class="flex items-center justify-between p-3.5 rounded-xl border transition-all cursor-pointer group"
                :class="selectedWarehouse?.id === warehouse.id
                  ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-900/60 text-slate-900 dark:text-slate-100'
                  : 'bg-transparent border-slate-100/50 dark:border-[#2E2E2E]/40 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400'"
              >
                <div class="min-w-0 flex items-center gap-2.5">
                  <span class="text-sm shrink-0">
                    {{ warehouse.is_primary ? '🏠' : '🏬' }}
                  </span>
                  <div class="truncate">
                    <h4 class="text-xs font-bold truncate">{{ warehouse.name }}</h4>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 truncate mt-0.5">
                      {{ warehouse.code }} | {{ warehouse.city || 'No Location' }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <span v-if="warehouse.is_primary" class="px-1.5 py-0.5 text-[8px] font-black rounded uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                    Primary
                  </span>
                  <span class="px-1.5 py-0.5 text-[8px] font-black rounded uppercase tracking-wider" :class="warehouse.is_active ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600' : 'bg-slate-100 dark:bg-[#252525] text-slate-400 dark:text-slate-400'">
                    {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>

              <div v-if="warehouses.length === 0" class="text-center py-8 text-xs text-gray-400 font-medium dark:text-slate-400">
                No storage locations found.
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.last_page > 1" class="pt-3 border-t border-slate-100 dark:border-[#2E2E2E] flex items-center justify-between text-xs">
            <span class="text-gray-400 text-[10px] dark:text-slate-400">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            <div class="flex items-center gap-1">
              <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-2.5 py-1 rounded-lg border border-slate-200/60 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50 text-[10px]"
              >
                Prev
              </button>
              <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-2.5 py-1 rounded-lg border border-slate-200/60 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50 text-[10px]"
              >
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- Right Column: Details, Stock Lists & Parameters (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
          <div v-if="selectedWarehouse" class="bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-[24px] p-6 shadow-xs space-y-6 animate-in fade-in duration-200">
            
            <!-- Header Panel details -->
            <div class="flex justify-between items-start gap-4 pb-4 border-b border-slate-100 dark:border-[#2E2E2E]">
              <div>
                <div class="flex items-center gap-2">
                  <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">{{ selectedWarehouse.name }}</h2>
                  <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-full">
                    {{ inventoryTotal }} Active Stock Lines
                  </span>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-400 mt-1">
                  Code: <span class="font-bold text-slate-600 dark:text-slate-300">{{ selectedWarehouse.code }}</span> 
                  <span v-if="selectedWarehouse.city"> | {{ selectedWarehouse.address }}, {{ selectedWarehouse.city }}, {{ selectedWarehouse.country }}</span>
                </p>
              </div>

              <!-- Action buttons -->
              <div class="flex gap-2">
                <button
                  @click="openEditModal(selectedWarehouse)"
                  class="p-2 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 rounded-xl transition-all cursor-pointer dark:text-slate-400"
                  title="Modify Details"
                >
                  <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
                <button
                  @click="deleteWarehouse(selectedWarehouse)"
                  :disabled="selectedWarehouse.is_primary"
                  class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all disabled:opacity-30 cursor-pointer"
                  title="Remove Location"
                >
                  <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
            </div>

            <!-- Parameters Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-100 dark:border-[#2E2E2E] rounded-2xl flex flex-col justify-between">
                <div>
                  <span class="text-[10px] font-bold text-slate-400 uppercase block tracking-wider dark:text-slate-400">Facility Address</span>
                  <span class="text-xs text-slate-700 dark:text-slate-300 font-bold block mt-1.5">
                    {{ selectedWarehouse.address || 'Not Provided' }}
                  </span>
                  <span class="text-[10px] text-gray-400 block mt-0.5 dark:text-slate-400">
                    {{ selectedWarehouse.city }} {{ selectedWarehouse.state }} {{ selectedWarehouse.zip_code }}
                  </span>
                </div>
              </div>

              <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-100 dark:border-[#2E2E2E] rounded-2xl flex flex-col justify-between">
                <div>
                  <span class="text-[10px] font-bold text-slate-400 uppercase block tracking-wider dark:text-slate-400">Operations Contact</span>
                  <span class="text-xs text-slate-700 dark:text-slate-300 font-bold block mt-1.5">
                    Phone: {{ selectedWarehouse.phone || 'None' }}
                  </span>
                  <span class="text-[10px] text-gray-400 block mt-0.5 dark:text-slate-400">
                    Email: {{ selectedWarehouse.email || 'None' }}
                  </span>
                </div>
              </div>

              <!-- Assigned / Sales Counters Card -->
              <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-100 dark:border-[#2E2E2E] rounded-2xl flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block tracking-wider dark:text-slate-400">Sales Counters</span>
                    <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-full">
                      {{ (selectedWarehouse.counters || []).filter(c => c.status === 'active').length }} Active
                    </span>
                  </div>
                  
                  <div v-if="selectedWarehouse.counters && selectedWarehouse.counters.length > 0" class="mt-2.5 space-y-1.5 max-h-[114px] overflow-y-auto custom-thin-scroll pr-1">
                    <div
                      v-for="counter in selectedWarehouse.counters"
                      :key="counter.id"
                      class="flex items-center justify-between p-2 bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-xl text-xs"
                    >
                      <div class="flex items-center gap-1.5 truncate">
                        <span class="text-xs">🖥️</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200 truncate">{{ counter.name }}</span>
                        <span v-if="counter.counter_number" class="text-[9px] px-1.5 py-0.2 bg-slate-100 dark:bg-[#252525] text-slate-500 rounded font-semibold shrink-0">
                          {{ counter.counter_number }}
                        </span>
                      </div>
                      <span
                        class="px-1.5 py-0.5 text-[8px] font-black rounded uppercase tracking-wider shrink-0"
                        :class="counter.status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600' : 'bg-slate-100 dark:bg-[#252525] text-slate-400'"
                      >
                        {{ counter.status || 'active' }}
                      </span>
                    </div>
                  </div>
                  <div v-else class="mt-3 text-[10px] text-gray-400 dark:text-slate-400 italic">
                    No counters configured for this facility.
                  </div>
                </div>
              </div>
            </div>

            <!-- Section: Inventory Stock Logs inside warehouse -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-[#2E2E2E]">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Inventory Allocation lookup</h3>
                <div class="relative w-48">
                  <input
                    v-model="inventorySearch"
                    @input="debouncedInventorySearch"
                    type="text"
                    placeholder="Search allocated SKU/Name..."
                    class="w-full px-2.5 py-1.5 text-[10px] bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 rounded-xl outline-none text-slate-800 dark:text-slate-300"
                  />
                </div>
              </div>

              <!-- Stock list -->
              <div class="overflow-x-auto border border-slate-100 dark:border-[#2E2E2E] rounded-2xl">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
                  <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                      <th class="px-4 py-2.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">SKU</th>
                      <th class="px-4 py-2.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Product Name</th>
                      <th class="px-4 py-2.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Qty Allocated</th>
                      <th class="px-4 py-2.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Safety Min</th>
                      <th class="px-4 py-2.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 bg-white dark:bg-[#1E1E1E] text-xs">
                    <tr v-for="stock in inventoryItems" :key="stock.id" class="hover:bg-slate-50/50 dark:hover:bg-[#2D2D2D]/80">
                      <td class="px-4 py-2.5 font-bold text-slate-500 dark:text-slate-400">
                        {{ stock.variation ? stock.variation.sku : stock.product?.sku }}
                      </td>
                      <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-200">
                        <div>{{ stock.product?.name }}</div>
                        <div v-if="stock.variation" class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                          {{ stock.variation.variation_name_string }}
                        </div>
                      </td>
                      <td class="px-4 py-2.5 text-center font-bold text-slate-800 dark:text-slate-100">
                        {{ stock.stock_qty }}
                      </td>
                      <td class="px-4 py-2.5 text-center font-semibold text-slate-400 dark:text-slate-400">
                        {{ stock.min_stock_level || 0 }}
                      </td>
                      <td class="px-4 py-2.5 text-center">
                        <span 
                          class="px-2 py-0.5 text-[9px] font-black rounded-lg uppercase tracking-wide"
                          :class="stock.stock_qty <= (stock.min_stock_level || 0)
                            ? 'bg-rose-50 dark:bg-rose-950/20 text-rose-600'
                            : 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600'"
                        >
                          {{ stock.stock_qty <= (stock.min_stock_level || 0) ? 'Low Stock' : 'Good' }}
                        </span>
                      </td>
                    </tr>
                    <tr v-if="inventoryItems.length === 0">
                      <td colspan="5" class="px-4 py-8 text-center text-gray-400 font-medium dark:text-slate-400">
                        No product stocks found allocated to this warehouse.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Stock Pagination -->
              <div v-if="inventoryLastPage > 1" class="pt-3 border-t border-slate-100 dark:border-[#2E2E2E] flex items-center justify-between text-xs">
                <span class="text-gray-455 dark:text-slate-400 text-[10px]">
                  Showing {{ inventoryItems.length }} of {{ inventoryTotal }} items
                </span>
                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    @click="goToInventoryPage(inventoryPage - 1)"
                    :disabled="inventoryPage === 1 || inventoryLoading"
                    class="px-2.5 py-1 rounded-lg border border-slate-200/60 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50 text-[10px]"
                  >
                    Prev
                  </button>
                  <button
                    type="button"
                    @click="goToInventoryPage(inventoryPage + 1)"
                    :disabled="inventoryPage === inventoryLastPage || inventoryLoading"
                    class="px-2.5 py-1 rounded-lg border border-slate-200/60 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50 text-[10px]"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Placeholder -->
          <div v-else class="bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-[24px] p-12 text-center shadow-xs">
            <div class="text-4xl mb-3">🏢</div>
            <h3 class="text-sm font-extrabold text-slate-700 dark:text-slate-300 mb-1">Select a Storage Facility</h3>
            <p class="text-xs text-gray-400 max-w-xs mx-auto dark:text-slate-400">
              Select a facility from the locations list on the left to review operational parameters and view item configurations.
            </p>
          </div>
        </div>
      </div>

      <!-- Create / Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/30 dark:bg-slate-950/80 backdrop-blur-md transition-all" role="dialog">
        <div class="relative bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-[28px] max-w-lg w-full overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
          
          <!-- Header -->
          <div class="p-6 border-b border-slate-100 dark:border-[#2E2E2E]">
            <h2 class="text-sm font-extrabold text-gray-900 dark:text-slate-100 uppercase tracking-wider">
              {{ editingWarehouse ? 'Modify Storage Location' : 'New Storage Location' }}
            </h2>
            <p class="text-[10px] text-gray-450 uppercase tracking-widest mt-0.5">Parameters details</p>
          </div>

          <!-- Forms -->
          <form @submit.prevent="saveWarehouse" class="p-6 space-y-4 overflow-y-auto">
            
            <!-- Grid basic info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Warehouse Name *</label>
                <input
                  v-model="warehouseForm.name"
                  type="text"
                  required
                  placeholder="Main Depot, Branch B..."
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Location/Code *</label>
                <input
                  v-model="warehouseForm.code"
                  type="text"
                  required
                  placeholder="WH-001..."
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>
            </div>

            <!-- Address -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Street Address</label>
              <input
                v-model="warehouseForm.address"
                type="text"
                placeholder="123 Storage Lane..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
              />
            </div>

            <!-- Grid Location details -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">City</label>
                <input
                  v-model="warehouseForm.city"
                  type="text"
                  placeholder="City"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">State</label>
                <input
                  v-model="warehouseForm.state"
                  type="text"
                  placeholder="State"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Zip Code</label>
                <input
                  v-model="warehouseForm.zip_code"
                  type="text"
                  placeholder="Zip"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Country</label>
                <input
                  v-model="warehouseForm.country"
                  type="text"
                  placeholder="Country"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>
            </div>

            <!-- Contacts info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Phone Number</label>
                <input
                  v-model="warehouseForm.phone"
                  type="text"
                  placeholder="+1 (555) 123-4567"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Operational Email</label>
                <input
                  v-model="warehouseForm.email"
                  type="email"
                  placeholder="depot@business.com"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
                />
              </div>
            </div>

            <!-- Sales Counters Dynamic Inputs -->
            <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-[#2E2E2E]">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Assigned Sales Counters</h3>
                  <p class="text-[9px] text-gray-400 dark:text-slate-400">Configure POS billing counters assigned to this facility.</p>
                </div>
                <button
                  type="button"
                  @click="addCounterField"
                  class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-extrabold rounded-xl text-[10px] uppercase tracking-wider transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                  Add Counter
                </button>
              </div>

              <div v-if="warehouseForm.counters && warehouseForm.counters.length > 0" class="space-y-2 max-h-56 overflow-y-auto custom-scrollbar pr-1 pt-1" @click="openCounterDropdownIndex = null">
                <div
                  v-for="(counter, idx) in warehouseForm.counters"
                  :key="idx"
                  class="flex items-center gap-2 p-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200/70 dark:border-[#2E2E2E] rounded-xl relative"
                >
                  <div class="flex-1">
                    <input
                      v-model="counter.name"
                      type="text"
                      placeholder="Counter Name (e.g. Main Counter)"
                      required
                      class="w-full px-2.5 py-1 text-xs bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg outline-none focus:border-indigo-500 text-slate-800 dark:text-slate-200"
                    />
                  </div>
                  <div class="w-24">
                    <input
                      v-model="counter.counter_number"
                      type="text"
                      placeholder="Code (C-01)"
                      class="w-full px-2.5 py-1 text-xs bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg outline-none focus:border-indigo-500 text-slate-800 dark:text-slate-200"
                    />
                  </div>
                  
                  <!-- Floating Premium Status Dropdown (Teleported to Body to avoid container clipping) -->
                  <div class="relative w-28 shrink-0">
                    <button
                      type="button"
                      @click.stop="toggleCounterStatusDropdown(idx, $event)"
                      class="w-full px-2.5 py-1 text-xs bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] hover:border-indigo-500/60 dark:hover:border-indigo-500/60 rounded-lg outline-none flex items-center justify-between text-slate-800 dark:text-slate-200 transition-all cursor-pointer shadow-xs"
                    >
                      <div class="flex items-center gap-1.5 truncate">
                        <span class="w-2 h-2 rounded-full shrink-0" :class="counter.status === 'active' ? 'bg-emerald-500 shadow-xs shadow-emerald-500/50' : 'bg-slate-400'"></span>
                        <span class="font-medium capitalize text-[11px]">{{ counter.status || 'active' }}</span>
                      </div>
                      <svg
                        class="w-3 h-3 text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': openCounterDropdownIndex === idx }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>

                    <!-- Teleported Menu escaping parent overflow completely -->
                    <Teleport to="body">
                      <div
                        v-if="openCounterDropdownIndex === idx"
                        class="fixed inset-0 z-[9998]"
                        @click.stop="openCounterDropdownIndex = null"
                      ></div>

                      <div
                        v-if="openCounterDropdownIndex === idx"
                        :style="dropdownPosStyle"
                        class="fixed bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl p-1 z-[9999] animate-in fade-in zoom-in-95 duration-150"
                        @click.stop
                      >
                        <button
                          type="button"
                          @click.stop="setCounterStatus(idx, 'active')"
                          class="w-full flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg transition-colors cursor-pointer text-left"
                          :class="counter.status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold' : 'hover:bg-slate-50 dark:hover:bg-[#252525] text-slate-700 dark:text-slate-300'"
                        >
                          <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Active</span>
                          </div>
                          <svg v-if="counter.status === 'active'" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <button
                          type="button"
                          @click.stop="setCounterStatus(idx, 'inactive')"
                          class="w-full flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg transition-colors cursor-pointer text-left mt-0.5"
                          :class="counter.status === 'inactive' ? 'bg-slate-100 dark:bg-[#252525] text-slate-900 dark:text-slate-100 font-bold' : 'hover:bg-slate-50 dark:hover:bg-[#252525] text-slate-700 dark:text-slate-300'"
                        >
                          <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Inactive</span>
                          </div>
                          <svg v-if="counter.status === 'inactive'" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                      </div>
                    </Teleport>
                  </div>

                  <button
                    type="button"
                    @click="removeCounterField(idx)"
                    class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-all cursor-pointer shrink-0"
                    title="Remove counter"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </div>
              <div v-else class="py-2.5 px-4 text-center bg-slate-50 dark:bg-zinc-950 border border-dashed border-slate-200 dark:border-[#2E2E2E] rounded-xl">
                <p class="text-[10px] text-gray-400 dark:text-slate-400">No counters configured. Click "Add Counter" to assign billing counters.</p>
              </div>
            </div>

            <!-- Toggles primary & active -->
            <div class="space-y-2.5 pt-2 border-t border-slate-100 dark:border-[#2E2E2E]">
              <!-- Primary toggle -->
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Set Primary Location</span>
                  <p class="text-[9px] text-gray-400 dark:text-slate-400">Warning: Setting this true will disable primary status of other locations.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="warehouseForm.is_primary" class="sr-only peer" :disabled="editingWarehouse && editingWarehouse.is_primary">
                  <div class="w-11 h-6 bg-slate-200 dark:bg-[#252525] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 peer-disabled:opacity-40"></div>
                </label>
              </div>

              <!-- Active toggle -->
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Active Status</span>
                  <p class="text-[9px] text-gray-400 dark:text-slate-400">Inactive locations will be hidden in transfer orders dispatch selections.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="warehouseForm.is_active" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-200 dark:bg-[#252525] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
              </div>
            </div>

            <!-- Actions footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#2E2E2E]">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all cursor-pointer flex items-center justify-center gap-1"
              >
                <span v-if="saving" class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                <span>{{ saving ? 'Saving...' : 'Save Location' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

// States
const warehouses = ref([]);
const searchQuery = ref('');
const selectedWarehouse = ref(null);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

// Warehouse Inventory states
const inventoryItems = ref([]);
const inventoryPage = ref(1);
const inventorySearch = ref('');
const inventoryTotal = ref(0);
const inventoryLastPage = ref(1);
const inventoryLoading = ref(false);

const showModal = ref(false);
const editingWarehouse = ref(null);
const saving = ref(false);
const toast = ref(null);
const openCounterDropdownIndex = ref(null);
const dropdownPosStyle = ref({});

const toggleCounterStatusDropdown = (index, event) => {
  if (openCounterDropdownIndex.value === index) {
    openCounterDropdownIndex.value = null;
    return;
  }
  openCounterDropdownIndex.value = index;

  if (event && event.currentTarget) {
    const rect = event.currentTarget.getBoundingClientRect();
    const dropdownHeight = 68;
    const spaceAbove = rect.top;

    if (spaceAbove > dropdownHeight + 10) {
      // Position directly above button (no gap, matching width and left alignment)
      dropdownPosStyle.value = {
        top: `${rect.top - dropdownHeight - 2}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`
      };
    } else {
      // Position directly below button
      dropdownPosStyle.value = {
        top: `${rect.bottom + 2}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`
      };
    }
  }
};

const setCounterStatus = (index, status) => {
  if (warehouseForm.value.counters && warehouseForm.value.counters[index]) {
    warehouseForm.value.counters[index].status = status;
  }
  openCounterDropdownIndex.value = null;
};

const warehouseForm = ref({
  name: '',
  code: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  country: '',
  phone: '',
  email: '',
  is_primary: false,
  is_active: true,
  counters: []
});

const addCounterField = () => {
  const nextNum = (warehouseForm.value.counters ? warehouseForm.value.counters.length : 0) + 1;
  if (!warehouseForm.value.counters) {
    warehouseForm.value.counters = [];
  }
  warehouseForm.value.counters.push({
    id: null,
    name: `Counter ${nextNum}`,
    counter_number: `C-0${nextNum}`,
    status: 'active'
  });
};

const removeCounterField = (index) => {
  if (warehouseForm.value.counters) {
    warehouseForm.value.counters.splice(index, 1);
  }
};

// Load directory list
const fetchWarehouses = async () => {
  try {
    const params = {
      search: searchQuery.value
    };
    const res = await axios.get('/api/warehouses', { params });
    warehouses.value = res.data || [];
    pagination.value = {
      current_page: 1,
      last_page: 1,
      total: warehouses.value.length
    };
    if (warehouses.value.length > 0 && !selectedWarehouse.value) {
      selectWarehouse(warehouses.value[0]);
    } else if (selectedWarehouse.value) {
      const match = warehouses.value.find(w => w.id === selectedWarehouse.value.id);
      if (match) {
        selectedWarehouse.value = match;
      }
    }
  } catch (error) {
    showToast('error', 'Failed to load warehouses locations.');
  }
};

const goToPage = (page) => {
  pagination.value.current_page = page;
  fetchWarehouses();
};

let debounceTimeout = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchWarehouses();
  }, 350);
};

// Select a warehouse and fetch its inventory list
const selectWarehouse = async (warehouse) => {
  try {
    const res = await axios.get(`/api/warehouses/${warehouse.id}`);
    selectedWarehouse.value = res.data;
  } catch (e) {
    selectedWarehouse.value = warehouse;
  }
  inventoryPage.value = 1;
  inventorySearch.value = '';
  await fetchWarehouseInventory();
};

const fetchWarehouseInventory = async () => {
  if (!selectedWarehouse.value) return;
  inventoryLoading.value = true;
  try {
    const res = await axios.get(`/api/warehouses/${selectedWarehouse.value.id}/inventory`, {
      params: {
        page: inventoryPage.value,
        per_page: 10,
        search: inventorySearch.value
      }
    });
    inventoryItems.value = res.data.data || [];
    inventoryPage.value = res.data.current_page || 1;
    inventoryLastPage.value = res.data.last_page || 1;
    inventoryTotal.value = res.data.total || 0;
  } catch (error) {
    showToast('error', 'Failed to load warehouse inventory.');
    inventoryItems.value = [];
  } finally {
    inventoryLoading.value = false;
  }
};

const goToInventoryPage = (page) => {
  inventoryPage.value = page;
  fetchWarehouseInventory();
};

let inventorySearchTimeout = null;
const debouncedInventorySearch = () => {
  clearTimeout(inventorySearchTimeout);
  inventorySearchTimeout = setTimeout(() => {
    inventoryPage.value = 1;
    fetchWarehouseInventory();
  }, 350);
};

// Modals
const openCreateModal = () => {
  editingWarehouse.value = null;
  warehouseForm.value = {
    name: '',
    code: '',
    address: '',
    city: '',
    state: '',
    zip_code: '',
    country: '',
    phone: '',
    email: '',
    is_primary: false,
    is_active: true,
    counters: [
      { id: null, name: 'Counter 1', counter_number: 'C-01', status: 'active' }
    ]
  };
  showModal.value = true;
};

const openEditModal = (warehouse) => {
  editingWarehouse.value = warehouse;
  warehouseForm.value = {
    name: warehouse.name,
    code: warehouse.code || '',
    address: warehouse.address || '',
    city: warehouse.city || '',
    state: warehouse.state || '',
    zip_code: warehouse.zip_code || '',
    country: warehouse.country || '',
    phone: warehouse.phone || '',
    email: warehouse.email || '',
    is_primary: !!warehouse.is_primary,
    is_active: !!warehouse.is_active,
    counters: (warehouse.counters || []).map(c => ({
      id: c.id,
      name: c.name,
      counter_number: c.counter_number || '',
      status: c.status || 'active'
    }))
  };
  showModal.value = true;
};

const saveWarehouse = async () => {
  saving.value = true;
  try {
    if (editingWarehouse.value) {
      const res = await axios.put(`/api/warehouses/${editingWarehouse.value.id}`, warehouseForm.value);
      showToast('success', 'Location updated successfully.');
      selectedWarehouse.value = res.data.warehouse;
    } else {
      await axios.post('/api/warehouses', warehouseForm.value);
      showToast('success', 'New warehouse facility registered.');
    }
    showModal.value = false;
    await fetchWarehouses();
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Error occurred while saving location details.';
    showToast('error', errorMsg);
  } finally {
    saving.value = false;
  }
};

const { confirm } = useConfirm();

const deleteWarehouse = async (warehouse) => {
  if (warehouse.is_primary) {
    showToast('error', 'Cannot delete primary operational facility.');
    return;
  }

  const confirmed = await confirm({
    title: 'Delete Storage Facility?',
    message: `Are you sure you want to delete warehouse "${warehouse.name}"? This will erase all location stock logs!`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    await axios.delete(`/api/warehouses/${warehouse.id}`);
    showToast('success', 'Facility removed.');
    selectedWarehouse.value = null;
    await fetchWarehouses();
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Cannot delete warehouse containing stock lines.';
    showToast('error', errorMsg);
  }
};

const { showToast: triggerToast } = useToast();

const showToast = (typeOrMsg, msgOrType) => {
  if (typeOrMsg === 'error' || typeOrMsg === 'success' || typeOrMsg === 'warning' || typeOrMsg === 'info') {
    triggerToast(msgOrType, typeOrMsg);
  } else {
    triggerToast(typeOrMsg, msgOrType || 'info');
  }
};

onMounted(() => {
  fetchWarehouses();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.5);
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}

.custom-thin-scroll::-webkit-scrollbar {
  width: 2px !important;
}
.custom-thin-scroll::-webkit-scrollbar-button {
  display: none !important;
}
.custom-thin-scroll::-webkit-scrollbar-track {
  background: transparent !important;
}
.custom-thin-scroll::-webkit-scrollbar-thumb {
  background: #4b5563 !important;
  border-radius: 9999px !important;
}
.custom-thin-scroll::-webkit-scrollbar-thumb:hover {
  background: #6b7280 !important;
}
</style>
