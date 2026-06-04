<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
          <div class="flex flex-nowrap gap-2 items-center overflow-x-auto hide-scrollbar">
            <button
              @click="activeTab = 'adjustments'"
              :class="[
                'px-3 py-1.5 rounded-md text-[13px] font-medium whitespace-nowrap h-9 w-auto flex items-center justify-center transition-colors',
                activeTab === 'adjustments'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Stock Adjustments
            </button>
            <button
              @click="activeTab = 'purchase-orders'"
              :class="[
                'px-3 py-1.5 rounded-md text-[13px] font-medium whitespace-nowrap h-9 w-auto flex items-center justify-center transition-colors',
                activeTab === 'purchase-orders'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Purchase Orders
            </button>
            <button
              @click="activeTab = 'suppliers'"
              :class="[
                'px-3 py-1.5 rounded-md text-[13px] font-medium whitespace-nowrap h-9 w-auto flex items-center justify-center transition-colors',
                activeTab === 'suppliers'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Suppliers
            </button>
            <button
              @click="activeTab = 'low-stock'"
              :class="[
                'px-3 py-1.5 rounded-md text-[13px] font-medium whitespace-nowrap h-9 w-auto flex items-center justify-center transition-colors',
                activeTab === 'low-stock'
                  ? 'bg-red-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Low Stock Alert
            </button>
            <div class="w-px h-8 bg-gray-300 mx-2 self-center"></div>
            <router-link
              to="/products/create"
              class="px-3 py-1.5 rounded-md text-[13px] font-medium bg-indigo-600 text-white hover:bg-indigo-700 flex items-center justify-center whitespace-nowrap h-9 w-auto shadow-sm transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              Add Manually
            </router-link>
            <button
              @click="showUploadModal = true"
              class="px-3 py-1.5 rounded-md text-[13px] font-medium bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center whitespace-nowrap h-9 w-auto shadow-sm transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
              Upload Inventory
            </button>
          </div>
        </div>

        <!-- Inventory Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Adjustments</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.total_adjustments || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Stock Increases</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.total_increases || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Stock Decreases</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.total_decreases || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Low Stock Items</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.low_stock_products || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-white shadow rounded-lg">
          <!-- Stock Adjustments Tab -->
          <div v-if="activeTab === 'adjustments'" class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-medium text-gray-900">Stock Adjustments</h2>
              <button
                @click="showAdjustmentModal = true"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                New Adjustment
              </button>
            </div>

            <!-- Adjustments List -->
            <div v-if="loadingAdjustments" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading adjustments...</p>
            </div>

            <div v-else-if="adjustments.length === 0" class="text-center py-8 text-gray-500">
              No adjustments found.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Adjustment #
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Product
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Quantity
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Reason
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="adjustment in adjustments" :key="adjustment.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ adjustment.adjustment_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ adjustment.product?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          adjustment.adjustment_type === 'increase'
                            ? 'bg-green-100 text-green-800'
                            : adjustment.adjustment_type === 'decrease'
                            ? 'bg-red-100 text-red-800'
                            : 'bg-blue-100 text-blue-800'
                        ]"
                      >
                        {{ adjustment.adjustment_type_display }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ adjustment.quantity_adjusted }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ adjustment.reason }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(adjustment.adjustment_date) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Low Stock Tab -->
          <div v-if="activeTab === 'low-stock'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Low Stock Alert</h2>

            <div v-if="loadingLowStock" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading low stock products...</p>
            </div>

            <div v-else-if="lowStockProducts.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="mt-2">All products are well stocked!</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="product in lowStockProducts"
                :key="product.id"
                class="bg-red-50 border border-red-200 rounded-lg p-4"
              >
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-medium text-gray-900">{{ product.name }}</h3>
                  <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded">
                    Low Stock
                  </span>
                </div>
                <p class="text-sm text-gray-600 mb-2">SKU: {{ product.sku }}</p>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">
                    Current: {{ product.stock_quantity }}
                  </span>
                  <span class="text-sm text-gray-600">
                    Min: {{ product.min_stock_level }}
                  </span>
                </div>
                <button
                  @click="quickAdjustStock(product)"
                  class="mt-2 w-full bg-red-600 text-white py-1 px-3 rounded text-sm hover:bg-red-700"
                >
                  Quick Adjust
                </button>
              </div>
            </div>
          </div>

          <!-- Other tabs content will be added here -->
          <div v-if="activeTab === 'purchase-orders'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Orders</h2>
            <p class="text-gray-600">Purchase orders management will be implemented here.</p>
          </div>

          <div v-if="activeTab === 'suppliers'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Suppliers</h2>
            <p class="text-gray-600">Supplier management will be implemented here.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showAdjustmentModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <!-- Backdrop with Blur -->
        <div 
          class="fixed inset-0 bg-white/40 backdrop-blur-[12px] transition-opacity" 
          @click="closeAdjustmentModal"
        ></div>

        <!-- Modal Container -->
        <div class="relative bg-white/95 backdrop-blur-md w-full max-w-lg shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-[32px] overflow-hidden border border-white/20 transform transition-all p-2 flex flex-col max-h-[90vh]">
          <!-- Glass Header -->
          <div class="px-8 py-6 flex items-center justify-between flex-shrink-0">
            <div>
              <h3 class="text-2xl font-black text-gray-900 tracking-tight uppercase">Stock Adjustment</h3>
              <p class="text-indigo-600 text-[10px] font-bold uppercase tracking-widest mt-1">Inventory Management Suite</p>
            </div>
            <button @click="closeAdjustmentModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors group">
              <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="px-8 pb-10 overflow-y-auto hide-scrollbar flex-1">
            <form @submit.prevent="createAdjustment" class="flex flex-col space-y-[28px] py-2">
              
              <!-- 1. Product Selection -->
              <div class="relative group z-[60]">
                <label :class="[ (adjustmentForm.product_id || focusedField === 'product_id') ? '-top-2.5 left-4 text-indigo-600 scale-90 translate-y-0 opacity-100 bg-white' : 'top-1/2 -translate-y-1/2 left-5 text-gray-400 opacity-60' ]" class="absolute pointer-events-none transition-all duration-300 z-10 px-2 text-xs font-black uppercase tracking-widest text-left">
                  Product *
                </label>
                <div class="relative flex items-center">
                  <span v-if="selectedProduct" class="absolute left-4 z-20 w-8 h-8 rounded-lg overflow-hidden border border-gray-100 shadow-sm bg-white">
                    <img :src="selectedProduct.image || '/placeholder.png'" class="w-full h-full object-cover">
                  </span>
                  <select
                    v-model="adjustmentForm.product_id"
                    required
                    @focus="focusedField = 'product_id'"
                    @blur="focusedField = null"
                    :class="[selectedProduct ? 'pl-14' : 'pl-5']"
                    class="w-full pr-12 py-[20px] bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition-all duration-300 font-bold text-gray-800 shadow-sm appearance-none cursor-pointer"
                  >
                    <option value="" disabled></option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }} (Stock: {{ product.stock_quantity }})
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                  </div>
                </div>
              </div>

              <!-- Cascading Fields -->
              <transition-group 
                name="cascade" 
                tag="div" 
                class="flex flex-col space-y-[28px]"
              >
                <template v-if="adjustmentForm.product_id">
                  <!-- 2. Adjustment Type -->
                  <div key="type" class="relative group transition-all duration-300 z-[50]">
                    <label :class="[ (adjustmentForm.adjustment_type || focusedField === 'adjustment_type') ? '-top-2.5 left-4 text-indigo-600 scale-90 translate-y-0 opacity-100 bg-white' : 'top-1/2 -translate-y-1/2 left-5 text-gray-400 opacity-60' ]" class="absolute pointer-events-none transition-all duration-300 z-10 px-2 text-xs font-black uppercase tracking-widest text-left">
                      Adjustment Type *
                    </label>
                    <div class="relative flex items-center">
                      <div v-if="adjustmentForm.adjustment_type" class="absolute left-5 z-20 w-3 h-3 rounded-full shadow-sm" :class="[adjustmentForm.adjustment_type === 'increase' ? 'bg-green-500' : adjustmentForm.adjustment_type === 'decrease' ? 'bg-red-500' : 'bg-blue-400']"></div>
                      <select
                        v-model="adjustmentForm.adjustment_type"
                        required
                        @focus="focusedField = 'adjustment_type'"
                        @blur="focusedField = null"
                        :class="[adjustmentForm.adjustment_type ? 'pl-11' : 'pl-5']"
                        class="w-full pr-12 py-[20px] bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition-all duration-300 font-bold text-gray-800 shadow-sm appearance-none cursor-pointer"
                      >
                        <option value="" disabled></option>
                        <option value="increase">Stock Increase</option>
                        <option value="decrease">Stock Decrease</option>
                        <option value="recount">Stock Recount</option>
                      </select>
                      <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                      </div>
                    </div>
                  </div>

                  <!-- 3. Quantity -->
                  <div key="quantity" class="relative group transition-all duration-300 z-[40]">
                    <label :class="[ (adjustmentForm.quantity_adjusted !== '' || focusedField === 'quantity_adjusted') ? '-top-2.5 left-4 text-indigo-600 scale-90 translate-y-0 opacity-100 bg-white' : 'top-1/2 -translate-y-1/2 left-5 text-gray-400 opacity-60' ]" class="absolute pointer-events-none transition-all duration-300 z-10 px-2 text-xs font-black uppercase tracking-widest text-left">
                      {{ adjustmentForm.adjustment_type === 'recount' ? 'New Quantity *' : 'Quantity *' }}
                    </label>
                    <input
                      v-model.number="adjustmentForm.quantity_adjusted"
                      type="number"
                      min="0"
                      required
                      @focus="focusedField = 'quantity_adjusted'"
                      @blur="focusedField = null"
                      class="w-full px-5 py-[20px] bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition-all duration-300 font-black text-gray-800 shadow-sm"
                    />
                  </div>

                  <!-- 4. Reason -->
                  <div key="reason" class="relative group transition-all duration-300 z-[30]">
                    <label :class="[ (adjustmentForm.reason || focusedField === 'reason') ? '-top-2.5 left-4 text-indigo-600 scale-90 translate-y-0 opacity-100 bg-white' : 'top-1/2 -translate-y-1/2 left-5 text-gray-400 opacity-60' ]" class="absolute pointer-events-none transition-all duration-300 z-10 px-2 text-xs font-black uppercase tracking-widest text-left">
                      Reason *
                    </label>
                    <select
                      v-model="adjustmentForm.reason"
                      required
                      @focus="focusedField = 'reason'"
                      @blur="focusedField = null"
                      class="w-full px-5 pr-12 py-[20px] bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition-all duration-300 font-bold text-gray-800 shadow-sm appearance-none cursor-pointer"
                    >
                      <option value="" disabled></option>
                      <option value="Damaged goods">Damaged goods</option>
                      <option value="Expired products">Expired products</option>
                      <option value="Theft/Loss">Theft/Loss</option>
                      <option value="Supplier return">Supplier return</option>
                      <option value="Physical count correction">Physical count correction</option>
                      <option value="System error correction">System error correction</option>
                      <option value="Other">Other</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none text-gray-400">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                  </div>

                  <!-- Smart Fields Grid -->
                  <div key="grid" class="grid grid-cols-2 gap-6 z-[20]">
                    <div class="relative group transition-all duration-300">
                      <label :class="[ (adjustmentForm.batch_number || focusedField === 'batch_number') ? '-top-2.5 left-4 text-indigo-600 scale-90 translate-y-0 opacity-100 bg-white' : 'top-1/2 -translate-y-1/2 left-5 text-gray-400 opacity-60' ]" class="absolute pointer-events-none transition-all duration-300 z-10 px-2 text-xs font-black uppercase tracking-widest text-left">
                        Batch No
                      </label>
                      <input
                        v-model="adjustmentForm.batch_number"
                        type="text"
                        @focus="focusedField = 'batch_number'"
                        @blur="focusedField = null"
                        class="w-full px-5 py-[20px] bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition-all duration-300 font-bold text-gray-800 shadow-sm"
                      />
                    </div>
                    <div class="relative group transition-all duration-300">
                      <label :class="[ (adjustmentForm.expiry_date || focusedField === 'expiry_date') ? '-top-2.5 left-4 text-indigo-600 scale-90 translate-y-0 opacity-100 bg-white' : 'top-1/2 -translate-y-1/2 left-5 text-gray-400 opacity-60' ]" class="absolute pointer-events-none transition-all duration-300 z-10 px-2 text-xs font-black uppercase tracking-widest text-left">
                        Expiry
                      </label>
                      <input
                        v-model="adjustmentForm.expiry_date"
                        :type="focusedField === 'expiry_date' || adjustmentForm.expiry_date ? 'date' : 'text'"
                        @focus="focusedField = 'expiry_date'"
                        @blur="focusedField = null"
                        class="w-full px-5 py-[20px] bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition-all duration-300 font-bold text-gray-800 shadow-sm"
                      />
                    </div>
                  </div>

                  <!-- Notes -->
                  <div key="notes" class="relative group transition-all duration-300 z-[10]">
                    <label :class="[ (adjustmentForm.notes || focusedField === 'notes') ? '-top-2.5 left-4 text-indigo-600 scale-90 translate-y-0 opacity-100 bg-white' : 'top-4 left-5 text-gray-400 opacity-60' ]" class="absolute pointer-events-none transition-all duration-300 z-10 px-2 text-xs font-black uppercase tracking-widest text-left">
                      Internal Notes
                    </label>
                    <textarea
                      v-model="adjustmentForm.notes"
                      rows="2"
                      @focus="focusedField = 'notes'"
                      @blur="focusedField = null"
                      class="w-full px-5 py-5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition-all duration-300 font-medium text-gray-800 shadow-sm"
                    ></textarea>
                  </div>

                  <!-- Attachment -->
                  <div key="attachment" class="relative group transition-all duration-300 z-[5]">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 px-2">
                      Attachment (Optional)
                    </label>
                    <div class="flex items-center justify-center w-full">
                      <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-gray-100 hover:border-indigo-400 transition-all duration-300">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-gray-400 group-hover:text-indigo-500 transition-colors">
                          <svg class="w-8 h-8 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                          <p class="mb-2 text-sm text-gray-500"><span class="font-bold text-indigo-600">Click to upload</span> or drag and drop</p>
                          <p class="text-[10px] uppercase font-bold tracking-widest text-gray-400">IMG, PDF, DOCX (MAX. 5MB)</p>
                        </div>
                        <input type="file" class="hidden" @change="handleAdjustmentAttachment" accept="image/*,.pdf,.doc,.docx" />
                      </label>
                    </div>
                    <div v-if="adjustmentForm.attachment" class="mt-3 py-2 px-3 bg-indigo-50 border border-indigo-100 rounded-lg text-xs text-indigo-700 font-bold flex items-center justify-between">
                      <div class="flex items-center truncate">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        <span class="truncate">{{ adjustmentForm.attachment.name }}</span>
                      </div>
                      <button type="button" @click.prevent="adjustmentForm.attachment = null" class="ml-3 text-indigo-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                      </button>
                    </div>
                  </div>
                </template>
              </transition-group>

              <div v-if="adjustmentFormErrors.length > 0" class="p-4 bg-red-50 rounded-2xl border border-red-100">
                <ul class="text-[11px] font-bold text-red-600 uppercase tracking-tight">
                  <li v-for="error in adjustmentFormErrors" :key="error" class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"/></svg>
                    {{ error }}
                  </li>
                </ul>
              </div>

              <!-- ERP Pill Buttons -->
              <div class="flex items-center space-x-4 pt-4">
                <button
                  type="button"
                  @click="closeAdjustmentModal"
                  class="flex-1 py-4 text-gray-400 font-black rounded-full hover:bg-gray-100 hover:text-gray-600 transition-all text-xs tracking-widest uppercase"
                >
                  Discard
                </button>
                <button
                  type="submit"
                  :disabled="creatingAdjustment"
                  class="flex-[2] py-4 bg-indigo-600 text-white font-black rounded-full shadow-[0_12px_25px_-5px_rgba(79,70,229,0.4)] hover:shadow-none hover:translate-y-0.5 transition-all disabled:opacity-50 text-xs tracking-widest uppercase group"
                >
                  <span v-if="creatingAdjustment" class="flex items-center justify-center">
                    <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Processing...
                  </span>
                  <span v-else class="flex items-center justify-center">
                    Confirm Adjustment
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Upload Inventory Modal -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="showUploadModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showUploadModal = false"></div>

        <div class="relative bg-white w-full max-w-lg shadow-2xl rounded-2xl overflow-hidden transform transition-all">
          <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div>
              <h3 class="text-xl font-bold text-gray-900">Upload Inventory</h3>
              <p class="text-xs text-gray-500 mt-1">Import products and stock from an Excel sheet</p>
            </div>
            <button @click="showUploadModal = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="p-8">
            <div 
              @dragover.prevent="uploadDragging = true"
              @dragleave.prevent="uploadDragging = false"
              @drop.prevent="handleFileUpload"
              :class="[
                'border-2 border-dashed rounded-xl p-8 flex flex-col items-center justify-center transition-all duration-200 text-center cursor-pointer',
                uploadDragging ? 'border-indigo-500 bg-indigo-50/50 scale-[1.02]' : 'border-gray-300 hover:border-indigo-400 bg-gray-50 hover:bg-gray-100/50'
              ]"
              @click="$refs.fileInput.click()"
            >
              <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" accept=".xlsx,.xls,.csv" />
              
              <div v-if="!uploadFile" class="flex flex-col items-center pointer-events-none">
                <div class="w-16 h-16 bg-white rounded-full shadow-sm flex items-center justify-center mb-4 text-indigo-500 border border-gray-100">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h4 class="text-sm font-bold text-gray-900 mb-1">Click to upload or drag and drop</h4>
                <p class="text-xs text-gray-500">Excel or CSV files only</p>
              </div>

              <div v-else class="flex flex-col items-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 text-green-600">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h4 class="text-sm font-bold text-gray-900 mb-1">{{ uploadFile.name }}</h4>
                <p class="text-xs text-green-600 font-medium">Ready to upload</p>
                <button @click.stop="uploadFile = null" class="mt-4 text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1 bg-red-50 rounded-full hover:bg-red-100 transition-colors">
                  Remove File
                </button>
              </div>
            </div>

            <div class="mt-8 flex items-center justify-end space-x-3">
              <button
                @click="showUploadModal = false"
                class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button
                @click="processUpload"
                :disabled="!uploadFile"
                :class="[
                  'px-5 py-2.5 text-sm font-bold rounded-xl shadow-sm transition-all flex items-center',
                  uploadFile ? 'bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-md' : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                ]"
              >
                <span v-if="isUploading">Uploading...</span>
                <span v-else>Upload File</span>
                <svg v-if="uploadFile && !isUploading" class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Reactive data
const activeTab = ref('adjustments');
const summary = ref({});
const adjustments = ref([]);
const lowStockProducts = ref([]);
const products = ref([]);
const loadingAdjustments = ref(false);
const loadingLowStock = ref(false);
const showAdjustmentModal = ref(false);
const creatingAdjustment = ref(false);
const adjustmentFormErrors = ref([]);
const focusedField = ref(null);

// Upload state
const showUploadModal = ref(false);
const uploadFile = ref(null);
const uploadDragging = ref(false);
const isUploading = ref(false);
const fileInput = ref(null);

// Adjustment form
const adjustmentForm = ref({
  product_id: '',
  adjustment_type: '',
  quantity_adjusted: '',
  reason: '',
  notes: '',
  batch_number: '',
  expiry_date: '',
  attachment: null
});

const selectedProduct = computed(() => {
  return products.value.find(p => p.id === adjustmentForm.value.product_id);
});

// Methods
const fetchSummary = async () => {
  try {
    const response = await axios.get('/api/inventory/summary');
    summary.value = response.data;
  } catch (error) {
    console.error('Error fetching inventory summary:', error);
  }
};

const fetchAdjustments = async () => {
  loadingAdjustments.value = true;
  try {
    const response = await axios.get('/api/inventory-adjustments');
    adjustments.value = response.data.data;
  } catch (error) {
    console.error('Error fetching adjustments:', error);
  } finally {
    loadingAdjustments.value = false;
  }
};

const fetchLowStockProducts = async () => {
  loadingLowStock.value = true;
  try {
    const response = await axios.get('/api/inventory/low-stock');
    lowStockProducts.value = response.data.data;
  } catch (error) {
    console.error('Error fetching low stock products:', error);
  } finally {
    loadingLowStock.value = false;
  }
};

const fetchProducts = async () => {
  try {
    const response = await axios.get('/api/products', {
      params: { per_page: 100, is_active: true }
    });
    products.value = response.data.data;
  } catch (error) {
    console.error('Error fetching products:', error);
  }
};

const createAdjustment = async () => {
  creatingAdjustment.value = true;
  adjustmentFormErrors.value = [];

  try {
    await axios.post('/api/inventory-adjustments', adjustmentForm.value);

    closeAdjustmentModal();
    fetchAdjustments();
    fetchSummary();
    fetchLowStockProducts();

    // Refresh products to show updated stock
    fetchProducts();

  } catch (error) {
    if (error.response?.data?.errors) {
      adjustmentFormErrors.value = Object.values(error.response.data.errors).flat();
    } else {
      adjustmentFormErrors.value = [error.response?.data?.message || 'An error occurred'];
    }
  } finally {
    creatingAdjustment.value = false;
  }
};

const closeAdjustmentModal = () => {
  showAdjustmentModal.value = false;
  adjustmentForm.value = {
    product_id: '',
    adjustment_type: '',
    quantity_adjusted: '',
    reason: '',
    notes: '',
    batch_number: '',
    expiry_date: '',
    attachment: null
  };
  adjustmentFormErrors.value = [];
};

const quickAdjustStock = (product) => {
  adjustmentForm.value = {
    product_id: product.id,
    adjustment_type: 'increase',
    quantity_adjusted: product.min_stock_level - product.stock_quantity,
    reason: 'Low stock replenishment',
    notes: `Quick adjustment to bring stock to minimum level`
  };
  showAdjustmentModal.value = true;
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const handleFileUpload = (event) => {
  let file;
  if (event.type === 'drop') {
    file = event.dataTransfer.files[0];
  } else {
    file = event.target.files[0];
  }
  
  if (file && (
    file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
    file.type === 'application/vnd.ms-excel' || 
    file.type === 'text/csv' ||
    file.name.endsWith('.xlsx') || 
    file.name.endsWith('.xls') || 
    file.name.endsWith('.csv')
  )) {
    uploadFile.value = file;
  } else {
    alert('Please upload a valid Excel or CSV file.');
  }
};

const handleAdjustmentAttachment = (event) => {
  let file;
  if (event.type === 'drop') {
    file = event.dataTransfer.files[0];
  } else {
    file = event.target.files[0];
  }
  
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert('File size exceeds 5MB limit.');
      return;
    }
    adjustmentForm.value.attachment = file;
  }
};

const processUpload = async () => {
  if (!uploadFile.value) return;
  
  isUploading.value = true;
  
  // Simulate an upload delay for UX
  setTimeout(() => {
    isUploading.value = false;
    showUploadModal.value = false;
    uploadFile.value = null;
    alert('Inventory uploaded successfully!');
    // After a real upload, we would refresh the inventory data here:
    // fetchSummary();
    // fetchProducts();
  }, 1500);
};

// Lifecycle
onMounted(() => {
  fetchSummary();
  fetchAdjustments();
  fetchLowStockProducts();
  fetchProducts();
});
</script>

<style scoped>
.cascade-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.cascade-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.98);
}
.cascade-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.cascade-item {
  transition: all 0.4s ease;
}

/* Stagger indices for cascade effect */
.cascade-enter-active:nth-child(2) { delay: 0.1s; }
.cascade-enter-active:nth-child(3) { delay: 0.2s; }
.cascade-enter-active:nth-child(4) { delay: 0.3s; }
.cascade-enter-active:nth-child(5) { delay: 0.4s; }

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
