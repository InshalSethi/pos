<template>
  <div class="bg-[#f4f6f8] min-h-screen pb-12 font-sans text-slate-800">
    <!-- Header -->
    <div class="px-6 py-4 max-w-5xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button @click="$router.back()" type="button" class="p-1.5 rounded-md hover:bg-slate-200 text-slate-600 transition-colors border border-transparent">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </button>
        <h1 class="text-xl font-bold text-slate-900">{{ form.name || 'New Product' }}</h1>
        <span v-if="form.status === 'active'" class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs font-semibold">Active</span>
        <span v-else class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold">Draft</span>
      </div>
      <div class="flex items-center gap-2">
         <button @click="$router.back()" type="button" class="px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200 rounded-lg transition-colors">Discard</button>
         <button type="submit" form="product-form" :disabled="loading" class="px-3 py-1.5 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors shadow-sm flex items-center">
           <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
           Save
         </button>
      </div>
    </div>

    <!-- Form Layout -->
    <div class="w-full max-w-[1400px] mx-auto px-4 py-6">
      <form id="product-form" @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start w-full">
        
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- General Info Card -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-800 mb-1">Title</label>
                <input v-model="form.name" type="text" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-0 focus:border-slate-300 text-sm" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-800 mb-1">Description</label>
                <!-- Fake toolbar for description -->
                <div class="border border-slate-300 rounded-lg overflow-hidden shadow-sm focus-within:outline-none focus-within:ring-0 focus-within:border-slate-300 transition-shadow">
                  <div class="bg-slate-50 border-b border-slate-300 px-3 py-2 flex items-center gap-2 text-slate-600 overflow-x-auto">
                     <span class="text-sm font-medium cursor-pointer hover:text-slate-900">Paragraph</span>
                     <svg class="w-4 h-4 cursor-pointer hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                     <div class="w-px h-4 bg-slate-300 mx-1"></div>
                     <span class="font-serif font-bold text-sm px-1 cursor-pointer hover:text-slate-900">B</span>
                     <span class="font-serif italic text-sm px-1 cursor-pointer hover:text-slate-900">I</span>
                     <span class="underline text-sm px-1 cursor-pointer hover:text-slate-900">U</span>
                     <span class="font-medium text-sm px-1 cursor-pointer hover:text-slate-900">A</span>
                     <svg class="w-4 h-4 cursor-pointer hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                     <div class="w-px h-4 bg-slate-300 mx-1"></div>
                     <svg class="w-4 h-4 cursor-pointer hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                     <svg class="w-4 h-4 cursor-pointer hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                     <svg class="w-4 h-4 cursor-pointer hover:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  <textarea v-model="form.description" rows="6" class="w-full px-3 py-3 bg-white border-none focus:ring-0 text-sm resize-y outline-none" placeholder=""></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Product Identifiers Card -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
             <div class="mb-4">
                 <h2 class="text-base font-semibold text-slate-900">Product Identifiers</h2>
             </div>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
               <div>
                  <label class="block text-sm font-medium text-slate-800 mb-1">Product SKU *</label>
                  <input v-model="form.sku" type="text" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-0 focus:border-slate-300 text-sm font-medium" required placeholder="Auto-generated or custom">
               </div>
               <div>
                  <label class="block text-sm font-medium text-slate-800 mb-1">Barcode</label>
                  <div class="relative">
                    <input v-model="form.barcode" type="text" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-0 focus:border-slate-300 text-sm font-medium pr-10" placeholder="Scan or enter barcode">
                    <button type="button" @click="scanning = true" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" />
                      </svg>
                    </button>
                  </div>
               </div>
                <div>
                   <label class="block text-sm font-medium text-slate-800 mb-1">Unit</label>
                   <SystemSelect v-model="form.unit_id" :options="unitOptions" placeholder="Select Unit" />
                </div>
               <div>
                  <label class="block text-sm font-medium text-slate-800 mb-1 flex items-center gap-2">
                    Total Quantity
                    <span v-if="isVariantMode" class="text-[10px] font-bold text-indigo-500 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded-md">Auto-Sum</span>
                  </label>
                  <input type="number" :value="isVariantMode ? totalVariantStock : form.stock_quantity" @input="onStockInput($event)" :readonly="isVariantMode" :class="isVariantMode ? 'bg-slate-100 text-slate-500 font-extrabold cursor-not-allowed border-slate-200' : 'bg-white border-slate-300 focus:ring-0 focus:border-slate-300'" class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none text-sm font-medium transition-all" placeholder="0">
               </div>
               <div>
                  <label class="block text-sm font-medium text-slate-800 mb-1">Min Alert</label>
                  <input v-model="form.min_stock_level" type="number" min="0" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-0 focus:border-slate-300 text-sm font-medium" placeholder="0">
               </div>
             </div>
          </div>

          <!-- Simple Pricing Parameters Card -->
          <div v-show="!isVariantMode"
               class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-3xl p-5 shadow-sm mt-6">
              <div class="border-b border-slate-100 dark:border-zinc-800 pb-3 mb-4">
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Product Pricing</h3>
                  <p class="text-[11px] text-slate-400">Set default cost and selling metrics for standalone single products.</p>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div>
                      <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Purchase Cost ($)</label>
                      <input type="number" v-model="form.cost_price" step="0.01" placeholder="0.00" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl focus:outline-none">
                  </div>
                  <div>
                      <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Retail Price ($) *</label>
                      <input type="number" v-model="form.selling_price" step="0.01" placeholder="0.00" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-emerald-600 font-bold focus:outline-none" :required="!isVariantMode">
                  </div>
                  <div>
                      <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Wholesale Price ($) *</label>
                      <input type="number" v-model="form.wholesale_price" step="0.01" placeholder="0.00" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-indigo-600 font-bold focus:outline-none" :required="!isVariantMode">
                  </div>
                  <div>
                      <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Tax Rate (%)</label>
                      <input type="number" v-model="form.tax_rate" step="0.01" placeholder="0.00" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl focus:outline-none">
                  </div>
              </div>
          </div>

          <!-- Variation-wise Matrix Allocation -->
          <div v-show="isVariantMode" class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mt-6 animate-in fade-in duration-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Dynamic Variation Sourcing Matrix</h4>
                        <p class="text-[10px] text-slate-400">Select explicit multi-option combinations to build targeted retail/wholesale stock metrics manually.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-2 rounded-2xl border border-slate-200/40">
                        <div v-for="(attr, aIdx) in attributes" :key="aIdx" class="flex flex-col">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-tight px-1 mb-0.5">Select {{ attr.name }}</span>
                            <SystemSelect v-model="selectedCombo[attr.name]" :options="attr.values.map(val => ({ label: val, value: val }))" placeholder="Choose" class="min-w-[120px]" />
                        </div>

                        <button type="button" @click="addNewManualRow" class="mt-3 px-3 py-1.5 text-[11px] font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow transition-all active:scale-95">
                            + Add Combo
                        </button>
                    </div>
                </div>

                <div class="w-full overflow-x-auto border border-slate-200 rounded-xl shadow-inner custom-scrollbar">
                    <table class="w-full min-w-max table-auto align-middle divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-3 py-2.5 text-left sticky left-0 bg-slate-50 z-10 w-48 shadow-[1px_0_0_0_#e2e8f0]">Variant Profile</th>
                                <th class="px-3 py-2.5 text-left min-w-[120px]">Purchase Cost ({{ currencyStore.symbol }})</th>
                                <th class="px-3 py-2.5 text-left min-w-[120px]">Retail Price ({{ currencyStore.symbol }}) *</th>
                                <th class="px-3 py-2.5 text-left min-w-[120px]">Wholesale ({{ currencyStore.symbol }}) *</th>
                                <th class="px-3 py-2.5 text-left min-w-[100px]">Tax Rate (%)</th>
                                <th class="px-3 py-2.5 text-left min-w-[100px]">Stock Qty *</th>

                                <th class="px-3 py-2.5 text-left min-w-[140px]">Expiry Date</th>
                                <th class="px-3 py-2.5 text-center sticky right-0 bg-slate-50 z-10 shadow-[-1px_0_0_0_#e2e8f0]">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-if="form.variations.length === 0">
                                <td colspan="12" class="text-center py-6 text-xs text-slate-400 italic">No variants configured. Click "+ Add Combo" to create the default variant.</td>
                            </tr>

                            <tr v-for="(row, index) in form.variations" :key="index" class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-2 font-bold text-slate-900 sticky left-0 bg-white shadow-[1px_0_0_0_#e2e8f0] z-10">{{ row.name_string }}</td>
                                <td class="px-3 py-2"><input type="number" v-model="row.cost_price" step="0.01" class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-full text-amber-600 focus:outline-none focus:ring-0 focus:border-slate-300"></td>
                                <td class="px-3 py-2"><input type="number" v-model="row.retail_price" step="0.01" required class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-full text-emerald-600 focus:outline-none focus:ring-0 focus:border-slate-300"></td>
                                <td class="px-3 py-2"><input type="number" v-model="row.wholesale_price" step="0.01" required class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-full text-slate-900 font-bold focus:outline-none focus:ring-0 focus:border-slate-300"></td>
                                <td class="px-3 py-2"><input type="number" v-model="row.tax_rate" step="0.01" class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-full focus:outline-none focus:ring-0 focus:border-slate-300"></td>
                                <td class="px-3 py-2"><input type="number" v-model="row.stock_qty" required class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-full font-bold focus:outline-none focus:ring-0 focus:border-slate-300"></td>

                                <td class="px-3 py-2"><input type="date" v-model="row.expiry_date" class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-[11px] w-full focus:outline-none focus:ring-0 focus:border-slate-300"></td>
                                <td class="px-3 py-2 text-center sticky right-0 bg-white shadow-[-1px_0_0_0_#e2e8f0] z-10"><button type="button" @click="removeRow(index)" class="text-rose-500 font-bold hover:text-rose-700 text-sm">&times;</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="lg:col-span-1 space-y-6 flex flex-col">
          
          <!-- Status Card -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
             <h2 class="text-base font-semibold text-slate-900 mb-4">Product status</h2>
             <SystemSelect v-model="form.status" :options="statusOptions" placeholder="Select Status" />
             <p class="text-[13px] text-slate-500 mt-3 leading-relaxed">
               {{ form.status === 'active' ? 'This product will be available on all sales channels.' : 'This product will be hidden from all sales channels.' }}
             </p>
          </div>



          <!-- Organization Card -->
          <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
             <h2 class="text-base font-semibold text-slate-900 mb-4">Product organization</h2>
             
             <div class="space-y-4">
               <div>
                 <label class="block text-sm font-medium text-slate-800 mb-1">Product Category</label>
                 <SystemSelect v-model="form.category_id" :options="categoryOptions" placeholder="Select Category" />
                 <p class="text-[13px] text-slate-500 mt-2 leading-relaxed">
                   Helps you manage products, and determines tax rates and/or exemptions for this product.
                 </p>
               </div>

               <div>
                 <label class="block text-sm font-medium text-slate-800 mb-1">Tags</label>
                 <div class="border border-slate-300 rounded-lg shadow-sm p-1.5 focus-within:ring-0 focus-within:border-slate-300 bg-white flex flex-wrap gap-1.5 min-h-[42px]">
                   <span v-for="(tag, index) in form.tags" :key="index" class="bg-slate-100 text-slate-700 text-[13px] font-medium px-2 py-0.5 rounded flex items-center gap-1 border border-slate-200">
                      {{ tag }}
                      <button type="button" @click="removeTag(index)" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                      </button>
                   </span>
                   <input type="text" v-model="newTag" @keydown.enter.prevent="addTag" @keydown.backspace="handleTagBackspace" class="flex-1 border-none focus:ring-0 p-1 text-sm min-w-[80px] outline-none" placeholder="Find or create tags">
                 </div>
               </div>
             </div>
          </div>

          <!-- Product Variations Toggle Card -->
          <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4">
              <div class="flex items-center justify-between">
                  <div>
                      <h3 class="text-sm font-bold text-slate-900 dark:text-white">Product Variations</h3>
                      <p class="text-[11px] text-slate-400">Enable variant-specific pricing options.</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer select-none">
                      <input type="checkbox" v-model="form.has_variations" class="sr-only peer">
                      <div class="w-8 h-4.5 bg-slate-200 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                  </label>
              </div>
          </div>

          <!-- Variations Card -->
          <div v-show="isVariantMode" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3">
              <h3 class="text-sm font-bold text-slate-900">Variations (Mandatory)</h3>
              <p class="text-[11px] text-slate-400">Build options to configure product pricing & inventory.</p>
            </div>

            <div class="space-y-3">
              <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                <button type="button" @click="showOptionsModal = true" class="w-full py-2 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-all">
                    + Add Options (Size, Color, GB)
                </button>
              </div>

              <div class="flex flex-col gap-1.5 pt-1">
                <div v-for="(attr, index) in attributes" :key="index" class="flex items-center justify-between bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-200">
                  <span class="text-[11px] font-bold text-slate-700">
                      <strong>{{ attr.name }}</strong>: <span class="font-medium text-slate-500">{{ attr.values.join(', ') }}</span>
                  </span>
                  <button type="button" @click="removeAttributeGroup(index)" class="text-rose-500 font-bold text-xs hover:text-rose-700">&times;</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Product Media Card -->
          <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4">
              <div class="border-b border-slate-100 dark:border-zinc-800 pb-3">
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Product Media</h3>
                  <p class="text-[11px] text-slate-400">Upload primary images and thumbnail gallery showcasing this product.</p>
              </div>
              
              <div class="flex flex-wrap gap-4">
                  <!-- Uploaded Image -->
                  <div v-if="form.image_url" class="relative w-40 h-40 border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden group">
                    <img :src="form.image_url" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <button type="button" @click="removeImage" class="p-1.5 bg-white text-red-600 rounded shadow hover:bg-slate-50">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </div>
                  </div>
                  <!-- Upload Button -->
                  <button type="button" @click="$refs.imageInputRef.click()" class="w-full h-40 border-[1.5px] border-dashed border-slate-200 dark:border-zinc-800 rounded-2xl flex flex-col items-center justify-center gap-2 hover:bg-slate-50/50 dark:hover:bg-zinc-800/50 transition-colors focus:outline-none">
                    <span class="text-sm font-medium text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 px-3 py-1 rounded-lg">Add</span>
                    <span class="text-xs font-medium text-indigo-600 hover:underline">Add from URL</span>
                  </button>
                  <input ref="imageInputRef" type="file" @change="handleImageUpload" class="hidden" accept="image/*">
              </div>
          </div>

        </div>
      </form>

      <!-- Error Notifications -->
      <transition-group name="list" tag="div" class="fixed bottom-6 left-1/2 -translate-x-1/2 space-y-2 z-50">
        <div v-for="(error, index) in errors" :key="index" class="bg-slate-900 text-white px-4 py-3 rounded-lg shadow-lg flex items-center text-sm font-medium">
          <svg class="w-4 h-4 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ error }}
        </div>
      </transition-group>
    </div>
    
    <!-- Barcode Scanner -->
    <BarcodeScanner v-if="scanning" @scan="onScan" @close="scanning = false" />

    <!-- Options & Attributes Modal -->
    <div v-if="showOptionsModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity">
      <div class="absolute inset-0" @click="showOptionsModal = false"></div>
      <div class="relative w-full max-w-sm p-5 bg-white rounded-2xl border border-slate-200 shadow-xl space-y-3 z-10">
        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Configure Attribute Group</h4>
        <div>
            <label class="text-[10px] font-bold text-slate-400 block mb-1">Option Name</label>
            <input type="text" v-model="tempAttrName" placeholder="e.g., Color, Size, Storage" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-300">
        </div>
        <div>
            <label class="text-[10px] font-bold text-slate-400 block mb-1">Option Values (Comma separated)</label>
            <input type="text" v-model="tempAttrValues" @keydown.enter.prevent="addAttributesGroup" placeholder="e.g., Pink, Black, 128GB, 256GB" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-300">
        </div>
        <div class="flex justify-end gap-2 pt-2 text-xs">
            <button type="button" @click="showOptionsModal = false" class="px-3 py-1 text-slate-400">Cancel</button>
            <button type="button" @click="addAttributesGroup" class="px-4 py-1.5 bg-indigo-600 text-white font-bold rounded-xl shadow hover:bg-indigo-700 transition-colors">Add & Apply</button>
        </div>
      </div>
    </div>

    <!-- Add New Category Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity animate-in fade-in duration-200">
      <div class="absolute inset-0" @click="closeCategoryModal"></div>
      <div class="relative w-full max-w-md p-6 bg-white rounded-2xl border border-slate-200 shadow-xl space-y-4 z-10">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
          <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Add New Category</h4>
          <button @click="closeCategoryModal" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>
        
        <div v-if="categoryModalError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200 font-medium">
          {{ categoryModalError }}
        </div>

        <div class="space-y-3">
          <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1">Category Name *</label>
              <input type="text" v-model="newCategoryForm.name" placeholder="e.g., Electronics, Apparel" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-300 font-medium">
          </div>
          <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1">Parent Category</label>
              <SystemSelect v-model="newCategoryForm.parent_id" :options="parentCategoryOptions" placeholder="None (Top Level)" />
          </div>
          <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1">Description</label>
              <textarea v-model="newCategoryForm.description" rows="3" placeholder="Describe the category..." class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-300 resize-none font-medium"></textarea>
          </div>
          <div class="flex items-center justify-between py-1">
              <div>
                  <label class="text-[10px] font-bold text-slate-400">Is Active</label>
                  <p class="text-[9px] text-slate-400">Visible and active across system.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                  <input type="checkbox" v-model="newCategoryForm.is_active" class="sr-only peer">
                  <div class="w-8 h-4.5 bg-slate-200 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
              </label>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 text-xs">
            <button type="button" @click="closeCategoryModal" class="px-3 py-1 text-slate-400 font-medium hover:text-slate-600">Cancel</button>
            <button type="button" @click="submitNewCategory" :disabled="submittingCategory" class="px-4 py-1.5 bg-indigo-600 text-white font-bold rounded-xl shadow hover:bg-indigo-700 transition-colors flex items-center gap-1.5 disabled:opacity-50">
                <svg v-if="submittingCategory" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Create Category
            </button>
        </div>
      </div>
    </div>

    <!-- Add New Unit Modal -->
    <div v-if="showUnitModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity animate-in fade-in duration-200">
      <div class="absolute inset-0" @click="closeUnitModal"></div>
      <div class="relative w-full max-w-md p-6 bg-white rounded-2xl border border-slate-200 shadow-xl space-y-4 z-10">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
          <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Add New Unit</h4>
          <button @click="closeUnitModal" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>
        
        <div v-if="unitModalError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200 font-medium">
          {{ unitModalError }}
        </div>

        <div class="space-y-3">
          <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1">Unit Name *</label>
              <input type="text" v-model="newUnitForm.name" placeholder="e.g., Kilogram, Litre, Piece" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-300 font-medium">
          </div>
          <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1">Short Name / Code *</label>
              <input type="text" v-model="newUnitForm.short_name" placeholder="e.g., KG, LTR, PCS" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-300 font-medium">
          </div>
          <div class="flex items-center justify-between py-1">
              <div>
                  <label class="text-[10px] font-bold text-slate-400">Is Active</label>
                  <p class="text-[9px] text-slate-400">Visible and active across system.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                  <input type="checkbox" v-model="newUnitForm.is_active" class="sr-only peer">
                  <div class="w-8 h-4.5 bg-slate-200 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
              </label>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 text-xs">
            <button type="button" @click="closeUnitModal" class="px-3 py-1 text-slate-400 font-medium hover:text-slate-600">Cancel</button>
            <button type="button" @click="submitNewUnit" :disabled="submittingUnit" class="px-4 py-1.5 bg-indigo-600 text-white font-bold rounded-xl shadow hover:bg-indigo-700 transition-colors flex items-center gap-1.5 disabled:opacity-50">
                <svg v-if="submittingUnit" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Create Unit
            </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import BarcodeScanner from '@/components/common/BarcodeScanner.vue';
import SystemSelect from '@/components/common/SystemSelect.vue';
import { useCurrencyStore } from '@/stores/currency';
import axios from 'axios';

const currencyStore = useCurrencyStore();

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
    wholesale_price: '',
    selling_price: '',
    stock_quantity: '',
    min_stock_level: '',
    max_stock_level: '',
    unit_of_measure: '',
    unit_id: '',
    track_inventory: true,
    is_active: true,
    status: 'active',
    category_id: '',
    weight: '',
    dimensions: '',
    tax_rate: '',
    discount_type: '',
    discount_value: 0,
    supplier_id: '',
    batch_number: '',
    expiry_date: '',
    tags: [],
    has_variations: data?.variations && data.variations.length > 0 ? true : false,
    variations: [],
    attributes: [],
    image: null,
    image_url: null
  };

  if (!data) return defaults;

  const sanitized = { ...defaults };
  Object.keys(defaults).forEach(key => {
    if (key in data) {
      if (data[key] !== undefined && data[key] !== null) {
        if (key === 'variations') {
           sanitized[key] = data[key].map(v => ({...v, name_string: v.variation_name_string || v.name_string}));
        } else {
           sanitized[key] = data[key];
        }
      } else {
        if (typeof defaults[key] === 'string') {
          sanitized[key] = '';
        }
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
const newTag = ref('');
const showOptionsModal = ref(false);

const attributes = ref(props.initialData?.attributes || []);
const tempAttrName = ref('');
const tempAttrValues = ref('');

const selectedCombo = ref({});

const addAttributesGroup = () => {
    if (!tempAttrName.value || !tempAttrValues.value) return;
    let vals = tempAttrValues.value.split(',').map(v => v.trim()).filter(v => v !== '');
    attributes.value.push({ name: tempAttrName.value, values: vals });
    tempAttrName.value = '';
    tempAttrValues.value = '';
    showOptionsModal.value = false;
};

const removeAttributeGroup = (index) => {
    attributes.value.splice(index, 1);
};

const addNewManualRow = () => {
    let nameString = 'Default Variant';
    if (attributes.value.length > 0) {
        let missingSelection = attributes.value.some(attr => !selectedCombo.value[attr.name]);
        if (missingSelection) {
            alert('Please select a value for all attribute types before adding!');
            return;
        }
        let nameParts = attributes.value.map(attr => selectedCombo.value[attr.name]);
        nameString = nameParts.join(' / ');
    }

    let duplicate = form.value.variations.some(r => r.name_string === nameString);
    if (duplicate) {
        alert('This specific variation combination has already been added to the matrix.');
        return;
    }

    form.value.variations.push({
        name_string: nameString,
        barcode: '',
        cost_price: '',
        retail_price: '',
        wholesale_price: '',
        tax_rate: '',
        discount_type: '',
        discount_value: '',
        stock_qty: 0,
        min_stock_alert: 0,
        unit_of_measure: '',
        expiry_date: ''
    });
};

const removeRow = (idx) => {
    form.value.variations.splice(idx, 1);
};

const addTag = () => {
  const tag = newTag.value.trim();
  if (tag && !form.value.tags.includes(tag)) {
    form.value.tags.push(tag);
  }
  newTag.value = '';
};

const removeTag = (index) => {
  form.value.tags.splice(index, 1);
};

const handleTagBackspace = () => {
  if (newTag.value === '' && form.value.tags.length > 0) {
    form.value.tags.pop();
  }
};

const categoryOptions = computed(() => {
  const list = Array.isArray(categories.value)
    ? categories.value
    : (categories.value && Array.isArray(categories.value.data) ? categories.value.data : []);
  const options = list.map(c => ({ label: c?.name || '', value: c?.id || '' }));
  options.push({ label: '+ ADD New Category', value: 'add_new_category' });
  return options;
});

const parentCategoryOptions = computed(() => {
  const list = Array.isArray(categories.value)
    ? categories.value
    : (categories.value && Array.isArray(categories.value.data) ? categories.value.data : []);
  return [
    { label: 'None (Top Level)', value: '' },
    ...list.map(c => ({ label: c?.name || '', value: c?.id || '' }))
  ];
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

const discountOptions = computed(() => [
  { label: 'No Discount', value: '' },
  { label: `Fixed Amount (${currencyStore.symbol})`, value: 'fixed' },
  { label: 'Percentage (%)', value: 'percentage' }
]);

const units = ref([]);

const unitOptions = computed(() => {
  const list = Array.isArray(units.value) ? units.value : [];
  const options = list.map(u => ({ label: `${u?.name} (${u?.short_name})`, value: u?.id }));
  options.push({ label: '+ ADD New Unit', value: 'add_new_unit' });
  return options;
});

const statusOptions = [
  { label: 'Active', value: 'active' },
  { label: 'Draft', value: 'draft' }
];

onMounted(async () => {
  try {
    const [catResponse, supResponse, unitResponse] = await Promise.all([
      axios.get('/api/categories'),
      axios.get('/api/suppliers'),
      axios.get('/api/units')
    ]);
    categories.value = catResponse?.data || [];
    suppliers.value = supResponse?.data || [];
    units.value = unitResponse?.data || [];
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

// ─── Variant Mode Detection ────────────────────────────────────────────────
const isVariantMode = computed(() => !!form.value.has_variations);

// Clear variation structures when toggle switch is turned off
watch(() => form.value.has_variations, (newVal) => {
  if (!newVal) {
    attributes.value = [];
    form.value.variations = [];
  }
});

// ─── Auto-Sum Calculation ──────────────────────────────────────────────────
// Reactively sums all stock_qty values across every variation row
const totalVariantStock = computed(() => {
  if (!isVariantMode.value) return form.value.stock_quantity || 0;
  return form.value.variations.reduce((total, row) => {
    const qty = parseInt(row.stock_qty);
    return total + (isNaN(qty) ? 0 : qty);
  }, 0);
});

// Keep form.stock_quantity in sync so the backend always receives the correct total
watch(totalVariantStock, (val) => {
  if (isVariantMode.value) form.value.stock_quantity = val;
});

// Handler for the Total Quantity input — only writes when not in variant mode
const onStockInput = (event) => {
  if (!isVariantMode.value) {
    form.value.stock_quantity = parseInt(event.target.value) || 0;
  }
};

// ─── Add New Category Logic ────────────────────────────────────────────────
const showCategoryModal = ref(false);
const submittingCategory = ref(false);
const categoryModalError = ref('');
const newCategoryForm = ref({
  name: '',
  parent_id: '',
  description: '',
  is_active: true
});

const openCategoryModal = () => {
  newCategoryForm.value = {
    name: '',
    parent_id: '',
    description: '',
    is_active: true
  };
  categoryModalError.value = '';
  showCategoryModal.value = true;
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
};

const submitNewCategory = async () => {
  if (!newCategoryForm.value.name.trim()) {
    categoryModalError.value = 'Category name is required.';
    return;
  }
  
  submittingCategory.value = true;
  categoryModalError.value = '';

  try {
    const response = await axios.post('/api/categories', {
      name: newCategoryForm.value.name.trim(),
      description: newCategoryForm.value.description.trim() || null,
      parent_id: newCategoryForm.value.parent_id || null,
      is_active: newCategoryForm.value.is_active
    });

    if (response.data && response.data.category) {
      const newCat = response.data.category;
      
      // Add to local categories list
      categories.value.push(newCat);
      
      // Auto-select the newly created category
      form.value.category_id = newCat.id;
      
      closeCategoryModal();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      categoryModalError.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      categoryModalError.value = error.response?.data?.message || 'An error occurred while creating the category.';
    }
  } finally {
    submittingCategory.value = false;
  }
};

watch(() => form.value.category_id, (newVal, oldVal) => {
  if (newVal === 'add_new_category') {
    openCategoryModal();
    form.value.category_id = oldVal || '';
  }
});

// ─── Add New Unit Modal state & handlers ───────────────────
const showUnitModal = ref(false);
const submittingUnit = ref(false);
const unitModalError = ref('');
const newUnitForm = ref({
  name: '',
  short_name: '',
  is_active: true
});

const openUnitModal = () => {
  newUnitForm.value = {
    name: '',
    short_name: '',
    is_active: true
  };
  unitModalError.value = '';
  showUnitModal.value = true;
};

const closeUnitModal = () => {
  showUnitModal.value = false;
};

const submitNewUnit = async () => {
  if (!newUnitForm.value.name.trim()) {
    unitModalError.value = 'Unit name is required.';
    return;
  }
  if (!newUnitForm.value.short_name.trim()) {
    unitModalError.value = 'Short name / Code is required.';
    return;
  }
  
  submittingUnit.value = true;
  unitModalError.value = '';

  try {
    const response = await axios.post('/api/units', {
      name: newUnitForm.value.name.trim(),
      short_name: newUnitForm.value.short_name.trim().toUpperCase(),
      is_active: newUnitForm.value.is_active
    });

    if (response.data && response.data.unit) {
      const newUnit = response.data.unit;
      units.value.push(newUnit);
      form.value.unit_id = newUnit.id;
      closeUnitModal();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      unitModalError.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      unitModalError.value = error.response?.data?.message || 'An error occurred while creating the unit.';
    }
  } finally {
    submittingUnit.value = false;
  }
};

watch(() => form.value.unit_id, (newVal, oldVal) => {
  if (newVal === 'add_new_unit') {
    openUnitModal();
    form.value.unit_id = oldVal || '';
  }
});

const submit = () => {
  // Ensure stock_quantity is up-to-date before emitting
  if (isVariantMode.value) form.value.stock_quantity = totalVariantStock.value;

  const payload = { 
    ...form.value, 
    is_active: form.value.status === 'active',
    attributes: isVariantMode.value ? attributes.value : [],
    has_variations: isVariantMode.value,
    variations: isVariantMode.value ? form.value.variations : []
  };
  emit('submit', payload);
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

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
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
