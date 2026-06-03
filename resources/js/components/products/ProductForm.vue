<template>
  <div class="bg-gray-50 min-h-screen pb-12">
    <!-- Professional Header -->
    <div class="px-6 py-6 bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 text-white shadow-2xl mb-8 relative overflow-hidden">
      <!-- Decorative background elements -->
      <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-64 h-64 bg-purple-400 opacity-10 rounded-full blur-3xl"></div>
      
      <div class="max-w-7xl mx-auto flex justify-between items-center relative z-10">
        <div class="flex items-center space-x-6">
          <button @click="$router.back()" class="p-3 rounded-2xl transition-all duration-300 flex items-center justify-center group shadow-lg" style="background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2);">
            <svg class="w-6 h-6 transform group-hover:-translate-x-1 transition-transform text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          
          <div class="flex items-center space-x-4">
            <div class="p-3 rounded-2xl shadow-inner flex items-center justify-center" style="background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3);">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-black uppercase tracking-tighter">{{ title }}</h1>
              <p class="text-indigo-100 text-xs font-bold opacity-70 tracking-widest uppercase">{{ subtitle }}</p>
            </div>
          </div>
        </div>
        
        <div class="flex items-center space-x-4">
          <button @click="$router.back()" class="px-6 py-3 bg-transparent hover:bg-white/10 text-white font-black rounded-2xl transition-all border-2 text-xs tracking-widest uppercase" style="border-color: rgba(255, 255, 255, 0.2);">
            Cancel
          </button>
          <button type="submit" form="product-form" :disabled="loading" class="px-8 py-3 bg-white text-indigo-600 font-black rounded-2xl shadow-[0_10px_20px_rgba(0,0,0,0.2)] hover:shadow-none hover:translate-y-0.5 transition-all flex items-center text-xs tracking-widest uppercase group">
            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <svg v-else class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
            {{ loading ? 'Saving...' : 'Save Product' }}
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-4">
      <form id="product-form" @submit.prevent="submit" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
          
          <!-- Left Column: Image & Status -->
          <div class="lg:col-span-4 space-y-6">
            <!-- Image Section -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition-all hover:shadow-md">
              <label class="block text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider flex items-center">
                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Product Image
              </label>
              <!-- Image preview box -->
              <div class="relative border-2 border-dashed border-gray-200 rounded-3xl overflow-hidden bg-gray-50 h-64 flex flex-col items-center justify-center">
                <img v-if="form.image_url" :src="form.image_url" class="absolute inset-0 w-full h-full object-contain p-4">
                <div v-else class="text-center p-8">
                  <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mb-4 mx-auto shadow-sm border border-gray-100">
                    <svg class="h-8 w-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <p class="text-sm font-semibold text-gray-500">No image selected</p>
                  <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF — max 2MB</p>
                </div>
                <!-- Remove button overlay -->
                <button v-if="form.image_url" type="button" @click="removeImage"
                  class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg transition-all hover:scale-110">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <!-- Upload button -->
              <div class="mt-4 space-y-2">
                <input ref="imageInputRef" type="file" @change="handleImageUpload" class="hidden" accept="image/*">
                <button type="button" @click="$refs.imageInputRef.click()"
                  class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl transition-all shadow-sm hover:shadow-md active:scale-[0.98]">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                  </svg>
                  {{ form.image ? 'Change Image' : 'Upload from PC' }}
                </button>
                <div v-if="form.image" class="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2 border border-gray-100">
                  <span class="text-xs text-gray-600 truncate">📎 {{ form.image.name }}</span>
                  <button type="button" @click="removeImage" class="ml-2 text-red-400 hover:text-red-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Status & Settings -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-5">
              <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 border-b pb-2">Status & Controls</label>
              
              <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white hover:border-indigo-100 transition-all cursor-pointer group" @click="form.is_active = !form.is_active">
                <div class="flex items-center">
                  <div :class="[form.is_active ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-500']" class="p-2 rounded-lg mr-3 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  </div>
                  <span class="text-sm font-bold text-gray-700 uppercase tracking-wide">Active Status</span>
                </div>
                <div :class="[form.is_active ? 'bg-green-500' : 'bg-gray-300']" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out ring-offset-2 focus:ring-2 focus:ring-green-500">
                  <span :class="[form.is_active ? 'translate-x-5' : 'translate-x-0']" class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </div>
              </div>

              <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white hover:border-indigo-100 transition-all cursor-pointer group" @click="form.track_inventory = !form.track_inventory">
                <div class="flex items-center">
                   <div :class="[form.track_inventory ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-500']" class="p-2 rounded-lg mr-3 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                  </div>
                  <span class="text-sm font-bold text-gray-700 uppercase tracking-wide">Track Stock</span>
                </div>
                <div :class="[form.track_inventory ? 'bg-indigo-500' : 'bg-gray-300']" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out ring-offset-2 focus:ring-2 focus:ring-indigo-500">
                  <span :class="[form.track_inventory ? 'translate-x-5' : 'translate-x-0']" class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Details -->
          <div class="lg:col-span-8 space-y-8">
            <!-- General Information Cards -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
               <div class="absolute top-0 right-0 p-4 opacity-5">
                  <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 13a1 1 0 112 0 1 1 0 01-2 0zm0-7a1 1 0 112 0v4a1 1 0 11-2 0V6z"></path></svg>
               </div>
               <div class="flex items-center space-x-3 mb-8 border-b border-gray-50 pb-4">
                  <div class="bg-indigo-600 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  </div>
                  <h2 class="text-lg font-bold text-gray-800 uppercase tracking-widest">General Information</h2>
               </div>

               <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div class="md:col-span-2">
                  <label class="block text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2 ml-1">Product Name *</label>
                  <input v-model="form.name" type="text" required class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-transparent transition-all duration-300 font-medium text-gray-800 shadow-inner" placeholder="Enter Product Name">
                </div>
                <div>
                  <SystemSelect
                    v-model="form.category_id"
                    :options="categoryOptions"
                    label="Category Selection"
                    label-color="text-indigo-600"
                    placeholder="Search & Select Category"
                  />
                </div>
                <div>
                  <label class="block text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2 ml-1">SKU *</label>
                  <input v-model="form.sku" type="text" required class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-transparent transition-all duration-300 font-medium text-gray-800 shadow-inner" placeholder="CODE-00XX">
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2 ml-1">Barcode / UPC</label>
                  <div class="flex space-x-3">
                    <div class="relative flex-1">
                       <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                       </span>
                       <input v-model="form.barcode" type="text" class="w-full pl-12 pr-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-transparent transition-all duration-300 font-medium text-gray-800 shadow-inner" placeholder="Scan or Enter Barcode">
                    </div>
                    <button type="button" @click="scanning = true" class="px-6 py-4 bg-indigo-50 text-indigo-700 rounded-2xl hover:bg-indigo-100 border border-indigo-200 transition-all flex items-center shadow-sm font-bold active:scale-95 group">
                      <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                      SCAN
                    </button>
                  </div>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2 ml-1">Product Description</label>
                  <textarea v-model="form.description" rows="3" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-transparent transition-all duration-300 font-medium text-gray-800 shadow-inner" placeholder="Provide detailed information..."></textarea>
                </div>
               </div>
            </div>

            <!-- Pricing & Inventory Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Pricing Card -->
              <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center space-x-3 mb-8 border-b border-gray-50 pb-4 text-emerald-700">
                    <div class="bg-emerald-600 p-2 rounded-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 8V7m0 1v1m0-1c0-1.657-1.343-3-3-3h0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 uppercase tracking-widest">Finance</h2>
                </div>
                <div class="space-y-6">
                   <div>
                    <label class="block text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2 ml-1">Cost Price ($)</label>
                    <input v-model="form.cost_price" type="number" step="0.01" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-bold text-gray-700 shadow-inner">
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2 ml-1">Selling Price ($) *</label>
                    <input v-model="form.selling_price" type="number" step="0.01" required class="w-full px-5 py-4 bg-gray-50 border border-emerald-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-bold text-emerald-800 shadow-inner text-xl">
                  </div>
                   <div>
                    <label class="block text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2 ml-1">Tax Rate (%)</label>
                    <div class="relative">
                      <input v-model="form.tax_rate" type="number" step="0.01" class="w-full pl-5 pr-12 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-bold text-gray-700 shadow-inner [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                      <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none">
                        <span class="text-lg font-black text-emerald-500 opacity-40">%</span>
                      </div>
                    </div>
                  </div>
                  <div class="pt-6 border-t border-gray-100">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 ml-1">Discounting Strategy</label>
                    <div class="grid grid-cols-12 gap-4">
                      <div class="col-span-7">
                        <SystemSelect
                          v-model="form.discount_type"
                          :options="discountOptions"
                          focus-color="focus:ring-emerald-500"
                          icon-color="group-hover:text-emerald-500"
                        />
                      </div>
                      <div v-if="form.discount_type" class="col-span-5 relative">
                        <input v-model="form.discount_value" type="number" step="0.01" class="w-full pl-5 pr-10 py-4 bg-gray-50 border border-emerald-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-black text-emerald-700 shadow-inner [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                          <span class="text-sm font-black text-emerald-500 opacity-60">
                            {{ form.discount_type === 'percentage' ? '%' : '$' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Inventory Card -->
              <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center space-x-3 mb-8 border-b border-gray-50 pb-4 text-amber-700">
                    <div class="bg-amber-600 p-2 rounded-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 uppercase tracking-widest">Inventory</h2>
                </div>
                <div class="space-y-6">
                  <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-7">
                      <label class="block text-xs font-bold text-amber-600 uppercase tracking-widest mb-2 ml-1">Stock Level</label>
                      <input v-model="form.stock_quantity" type="number" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-black text-gray-700 shadow-inner" placeholder="0">
                    </div>
                    <div class="col-span-5">
                      <SystemSelect
                        v-model="form.unit_of_measure"
                        :options="unitOptions"
                        label="Unit"
                        label-color="text-amber-600"
                        focus-color="focus:ring-amber-500"
                        icon-color="group-hover:text-amber-500"
                      />
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-[11px] font-black text-amber-600 uppercase tracking-tight mb-2 ml-1">Critical Stock Level</label>
                      <input v-model="form.min_stock_level" type="number" placeholder="Alert at..." class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-bold text-gray-700 shadow-inner">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-amber-600 uppercase tracking-tight mb-2 ml-1">Maximum Stock</label>
                        <input v-model="form.max_stock_level" type="number" placeholder="Limit..." class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-bold text-gray-700 shadow-inner">
                    </div>
                  </div>

                  <!-- Next-Gen Smart Fields -->
                  <div class="pt-6 border-t border-gray-100 space-y-6">
                    <h3 class="text-xs font-black text-indigo-500 uppercase tracking-widest ml-1">Smart Inventory Tracking</h3>
                    
                    <div>
                      <SystemSelect
                        v-model="form.supplier_id"
                        :options="supplierOptions"
                        label="Assigned Supplier"
                        label-color="text-indigo-600"
                        placeholder="Select Supplier for Auto-PO"
                      />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-[11px] font-black text-indigo-600 uppercase tracking-tight mb-2 ml-1">Batch Number</label>
                        <input v-model="form.batch_number" type="text" placeholder="LOT-XXX" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all font-bold text-gray-700 shadow-inner">
                      </div>
                      <div>
                        <label class="block text-[11px] font-black text-indigo-600 uppercase tracking-tight mb-2 ml-1">Expiry Date</label>
                        <input v-model="form.expiry_date" type="date" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all font-bold text-gray-700 shadow-inner">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer Action Help -->
            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 flex items-start space-x-4">
               <div class="bg-indigo-600 p-2 rounded-lg mt-1">
                 <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
               </div>
               <div>
                 <h4 class="text-indigo-900 font-bold uppercase tracking-wider text-sm mb-1">Final Checklist</h4>
                 <p class="text-indigo-700 text-xs leading-relaxed">Ensure all fields marked with * are filled. Accurate pricing and stock levels are critical for real-time POS calculations. Product images should be clear and well-lit for faster recognition in the checkout grid.</p>
               </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Full Screen Error Notifications -->
      <transition-group name="list" tag="div" class="fixed bottom-6 right-6 space-y-3 z-[100]">
        <div v-for="(error, index) in errors" :key="index" class="bg-red-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center space-x-3 transform transition-all hover:scale-105 duration-300">
           <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
           <span class="font-bold text-sm tracking-wide uppercase">{{ error }}</span>
        </div>
      </transition-group>
    </div>

    <!-- Barcode Scanner -->
    <BarcodeScanner v-if="scanning" @scan="onScan" @close="scanning = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import BarcodeScanner from '@/components/common/BarcodeScanner.vue';
import SystemSelect from '@/components/common/SystemSelect.vue';
import axios from 'axios';

const props = defineProps({
  initialData: { type: Object, default: () => ({}) },
  title: String,
  subtitle: String,
  loading: Boolean,
  errors: Array
});

const emit = defineEmits(['submit']);

const sanitizeInitialData = (data) => {
  const defaults = {
    name: '',
    description: '',
    sku: '',
    barcode: '',
    cost_price: '',
    selling_price: '',
    stock_quantity: '',
    min_stock_level: '',
    max_stock_level: '',
    unit_of_measure: 'pcs',
    track_inventory: true,
    is_active: true,
    category_id: '',
    weight: '',
    dimensions: '',
    tax_rate: '',
    discount_type: '',
    discount_value: 0,
    supplier_id: '',
    batch_number: '',
    expiry_date: '',
    image: null,
    image_url: null
  };

  if (!data) return defaults;

  const sanitized = { ...defaults };
  Object.keys(defaults).forEach(key => {
    if (data[key] !== undefined && data[key] !== null) {
      sanitized[key] = data[key];
    } else {
      if (typeof defaults[key] === 'string') {
        sanitized[key] = '';
      }
    }
  });

  return sanitized;
};

const form = ref(sanitizeInitialData(props.initialData));

const categories = ref([]);
const suppliers = ref([]);
const scanning = ref(false);
const imageInputRef = ref(null);

const categoryOptions = computed(() => {
  const list = Array.isArray(categories.value)
    ? categories.value
    : (categories.value && Array.isArray(categories.value.data) ? categories.value.data : []);
  return list.map(c => ({ label: c?.name || '', value: c?.id || '' }));
});

const supplierOptions = computed(() => {
  const list = Array.isArray(suppliers.value)
    ? suppliers.value
    : (suppliers.value && Array.isArray(suppliers.value.data) ? suppliers.value.data : []);
  return [
    { label: 'Select Supplier for Auto-PO', value: '' },
    ...list.map(s => ({ label: s?.name || '', value: s?.id || '' }))
  ];
});

const discountOptions = [
  { label: 'No Discount', value: '' },
  { label: 'Fixed Amount ($)', value: 'fixed' },
  { label: 'Percentage (%)', value: 'percentage' }
];

const unitOptions = [
  { label: 'PCS', value: 'pcs' },
  { label: 'KG', value: 'kg' },
  { label: 'LTR', value: 'liters' },
  { label: 'BOX', value: 'boxes' }
];

onMounted(async () => {
  try {
    const [catResponse, supResponse] = await Promise.all([
      axios.get('/api/categories'),
      axios.get('/api/suppliers')
    ]);
    categories.value = catResponse?.data || [];
    suppliers.value = supResponse?.data || [];
  } catch (error) {
    console.error('Error fetching dynamic data:', error);
  }
});

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      alert('Image size exceeds 2MB limit');
      return;
    }
    form.value.image = file;
    form.value.image_url = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  form.value.image = null;
  form.value.image_url = null;
  if (imageInputRef.value) {
    imageInputRef.value.value = '';
  }
};

const onScan = (barcode) => {
  form.value.barcode = barcode;
  scanning.value = false;
};

const submit = () => {
  emit('submit', { ...form.value });
};
</script>

<style scoped>
.list-enter-active, .list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

/* Custom scrollbar for better professional look */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
