<template>
  <div class="w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-7xl mx-auto space-y-6">
      <!-- Header Page Title -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">📦</span>
            Inventory Groups
          </h1>
          <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
            Manage product classifications, nested hierarchies, tax inheritance, and bulk price modifications.
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2.5">
          <button
            @click="openCreateModal"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-150 shadow-md flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Create Group
          </button>
        </div>
      </div>

      <!-- Alert / Toast Messages -->
      <div v-if="toast" class="p-4 rounded-xl border flex items-center justify-between text-xs font-semibold animate-in fade-in slide-in-from-top duration-300" :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-300 dark:border-emerald-900/50' : 'bg-rose-50 text-rose-800 border-rose-100 dark:bg-rose-950/20 dark:text-rose-300 dark:border-rose-900/50'">
        <span>{{ toast.message }}</span>
        <button @click="toast = null" class="text-gray-400 hover:text-gray-600 font-bold dark:text-slate-400">&times;</button>
      </div>

      <!-- Main Content Area: Split Pane -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Groups Tree & List (4 cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-[24px] p-5 shadow-xs space-y-4 flex flex-col justify-between min-h-[50vh]">
          <div class="space-y-4 flex-1 flex flex-col min-h-0">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Group Directory</h3>
              <span class="px-2 py-0.5 bg-slate-100 dark:bg-[#252525] text-[10px] font-bold text-slate-500 dark:text-slate-400 rounded-full">
                {{ pagination.total }} Total
              </span>
            </div>

            <!-- Search Bar -->
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search groups..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 dark:focus:border-indigo-500 rounded-xl outline-none transition-all dark:text-slate-200"
                @input="debouncedFetch"
              />
              <span class="absolute left-3 top-2.5 text-gray-400 dark:text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              </span>
            </div>

            <!-- Group List Tree -->
            <div class="space-y-1.5 max-h-[60vh] overflow-y-auto custom-scrollbar flex-1 pr-1">
              <div
                v-for="group in groups"
                :key="group.id"
                @click="selectGroup(group)"
                class="flex items-center justify-between p-3.5 rounded-xl border transition-all cursor-pointer group"
                :class="selectedGroup?.id === group.id
                  ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-900/60 text-slate-900 dark:text-slate-100'
                  : 'bg-transparent border-slate-100/50 dark:border-[#2E2E2E]/40 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400'"
              >
                <div class="min-w-0 flex items-center gap-2.5">
                  <span class="text-sm shrink-0">
                    {{ group.parent_id ? '➖' : '📦' }}
                  </span>
                  <div class="truncate">
                    <h4 class="text-xs font-bold truncate">{{ group.name }}</h4>
                    <p class="text-[10px] text-gray-400 dark:text-slate-400 truncate mt-0.5">
                      {{ group.parent ? `Child of ${group.parent.name}` : 'Parent Group' }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <span class="px-1.5 py-0.5 text-[8px] font-black rounded uppercase tracking-wider" :class="group.is_active ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600' : 'bg-slate-100 dark:bg-[#252525] text-slate-400 dark:text-slate-400'">
                    {{ group.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>

              <div v-if="groups.length === 0" class="text-center py-8 text-xs text-gray-400 font-medium dark:text-slate-400">
                No groups found.
              </div>
            </div>
          </div>

          <!-- Pagination Control -->
          <div v-if="pagination.last_page > 1" class="pt-3 border-t border-slate-200 dark:border-[#2E2E2E] flex items-center justify-between text-xs">
            <span class="text-gray-400 text-[10px] dark:text-slate-400">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            <div class="flex items-center gap-1">
              <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-2.5 py-1 rounded-lg border border-slate-200/65 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50 text-[10px]"
              >
                Prev
              </button>
              <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-2.5 py-1 rounded-lg border border-slate-200/65 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50 text-[10px]"
              >
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- Right Column: Detail View, Bulk Pricing, and Product Inclusions (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
          <div v-if="selectedGroup" class="bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-[24px] p-6 shadow-xs space-y-6 animate-in fade-in duration-200">
            
            <!-- Selected Group Title & Quick Info -->
            <div class="flex justify-between items-start gap-4 pb-4 border-b border-slate-100 dark:border-[#2E2E2E]">
              <div>
                <div class="flex items-center gap-2">
                  <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">{{ selectedGroup.name }}</h2>
                  <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-full">
                    {{ selectedGroup.products_count || 0 }} Items Linked
                  </span>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-400 mt-1">
                  {{ selectedGroup.description || 'No description provided.' }}
                </p>
              </div>
              
              <div class="flex gap-2">
                <button
                  @click="openEditModal(selectedGroup)"
                  class="p-2 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 rounded-xl transition-all cursor-pointer dark:text-slate-400"
                  title="Edit Group Details"
                >
                  <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
                <button
                  @click="deleteGroup(selectedGroup)"
                  class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all cursor-pointer"
                  title="Delete Group"
                >
                  <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
            </div>

            <!-- Section Grid: Attributes & Bulk Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Left Panel: Settings Summary -->
              <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Group Settings</h3>
                
                <div class="space-y-3">
                  <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-zinc-950 rounded-xl border border-slate-100 dark:border-[#2E2E2E]">
                    <span class="text-xs text-gray-500 dark:text-slate-400">Parent Category</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                      {{ selectedGroup.parent ? selectedGroup.parent.name : 'None (Root Group)' }}
                    </span>
                  </div>

                  <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-zinc-950 rounded-xl border border-slate-100 dark:border-[#2E2E2E]">
                    <span class="text-xs text-gray-500 dark:text-slate-400">Inherited Tax Rate</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                      {{ selectedGroup.tax ? `${selectedGroup.tax.name} (${parseFloat(selectedGroup.tax.value)}%)` : 'Not Assigned' }}
                    </span>
                  </div>

                  <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-zinc-950 rounded-xl border border-slate-200 dark:border-[#2E2E2E]">
                    <span class="text-xs text-gray-500 dark:text-slate-400">Default Discount/Markup</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                      <span v-if="selectedGroup.discount_type">
                        {{ selectedGroup.discount_type === 'markup_percentage' ? `Markup: ${parseFloat(selectedGroup.discount_value)}%` : `Discount: ${parseFloat(selectedGroup.discount_value)}%` }}
                      </span>
                      <span v-else>None</span>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Panel: Bulk Actions Form (Mass Pricing & Taxes) -->
              <div class="p-5 bg-gradient-to-br from-indigo-50/20 to-white dark:from-[#252525] dark:to-[#1E1E1E] rounded-[20px] border border-slate-100 dark:border-[#2E2E2E] space-y-4">
                <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest flex items-center gap-1">
                  <span>⚡</span> Bulk Pricing & Tax Action
                </h3>
                
                <div class="space-y-3">
                  <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Action Type</label>
                    <select
                      v-model="bulkAction.action_type"
                      class="w-full px-3 py-2 text-xs bg-white dark:bg-zinc-950 border border-slate-250 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-100 font-semibold"
                    >
                      <option value="markup_percentage">Apply Markup Percentage (%)</option>
                      <option value="discount_percentage">Apply Discount Percentage (%)</option>
                      <option value="set_tax">Assign Inherited Tax Group</option>
                    </select>
                  </div>

                  <!-- Input Value -->
                  <div v-if="bulkAction.action_type !== 'set_tax'">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Percentage Value (%)</label>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      v-model="bulkAction.value"
                      placeholder="Enter percentage (e.g. 15)"
                      class="w-full px-3 py-2 text-xs bg-white dark:bg-zinc-950 border border-slate-250 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-100"
                    />
                  </div>

                  <!-- Select Tax Category -->
                  <div v-else>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Select Tax Group</label>
                    <select
                      v-model="bulkAction.tax_id"
                      class="w-full px-3 py-2 text-xs bg-white dark:bg-zinc-950 border border-slate-250 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-100 font-semibold"
                    >
                      <option :value="null">Remove Tax Rule</option>
                      <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                        {{ tax.name }} ({{ parseFloat(tax.value) }}%)
                      </option>
                    </select>
                  </div>

                  <!-- Apply to Subcategories -->
                  <label class="flex items-center gap-2 py-1 cursor-pointer select-none text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                    <input
                      type="checkbox"
                      v-model="bulkAction.apply_to_subcategories"
                      class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                    />
                    Apply recursively to all nested child groups
                  </label>

                  <!-- Trigger Button -->
                  <button
                    @click="runBulkAction"
                    :disabled="loadingBulkAction"
                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-150 shadow-md cursor-pointer flex items-center justify-center gap-1.5"
                  >
                    <span v-if="loadingBulkAction" class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                    <span>{{ loadingBulkAction ? 'Applying Rules...' : 'Apply Rules' }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Section: Group-Wise reporting / Product Lookup -->
            <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-[#2E2E2E]">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Group Products Lookup</h3>
                <div class="relative w-48">
                  <input
                    v-model="productSearch"
                    type="text"
                    placeholder="Filter products..."
                    class="w-full px-2.5 py-1 text-[11px] bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 rounded-lg outline-none text-slate-800 dark:text-slate-350"
                  />
                </div>
              </div>

              <!-- Products List table -->
              <div class="overflow-x-auto border border-slate-200 dark:border-[#2E2E2E] rounded-2xl">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
                  <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                      <th class="px-4 py-2.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">SKU</th>
                      <th class="px-4 py-2.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Name</th>
                      <th class="px-4 py-2.5 text-right text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Cost Price</th>
                      <th class="px-4 py-2.5 text-right text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Selling Price</th>
                      <th class="px-4 py-2.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Tax Rate</th>
                      <th class="px-4 py-2.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Discount</th>
                      <th class="px-4 py-2.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Stock</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 bg-white dark:bg-[#1E1E1E] text-xs">
                    <tr v-for="prod in filteredProducts" :key="prod.id" class="hover:bg-slate-50/50 dark:hover:bg-[#2D2D2D]/80">
                      <td class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400">{{ prod.sku }}</td>
                      <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-205">{{ prod.name }}</td>
                      <td class="px-4 py-2.5 text-right font-medium text-slate-500 dark:text-slate-400">${{ parseFloat(prod.cost_price).toFixed(2) }}</td>
                      <td class="px-4 py-2.5 text-right font-bold text-slate-800 dark:text-slate-100">${{ parseFloat(prod.selling_price).toFixed(2) }}</td>
                      <td class="px-4 py-2.5 text-center font-semibold text-slate-600 dark:text-slate-400">{{ parseFloat(prod.tax_rate) }}%</td>
                      <td class="px-4 py-2.5 text-center font-bold">
                        <span v-if="prod.discount_value > 0" class="text-rose-600 bg-rose-50 dark:bg-rose-950/20 px-1.5 py-0.5 rounded text-[10px]">
                          -{{ parseFloat(prod.discount_value) }}{{ prod.discount_type === 'percentage' ? '%' : '' }}
                        </span>
                        <span v-else class="text-gray-400 dark:text-slate-400">-</span>
                      </td>
                      <td class="px-4 py-2.5 text-center font-bold" :class="prod.stock_quantity <= prod.min_stock_level ? 'text-rose-600' : 'text-slate-700 dark:text-slate-300'">
                        {{ prod.stock_quantity }}
                      </td>
                    </tr>
                    <tr v-if="filteredProducts.length === 0">
                      <td colspan="7" class="px-4 py-8 text-center text-gray-400 font-medium dark:text-slate-400">
                        No products associated with this group matching filters.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- No Selected Group Placeholder -->
          <div v-else class="bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-[24px] p-12 text-center shadow-xs">
            <div class="text-4xl mb-3">📂</div>
            <h3 class="text-sm font-extrabold text-slate-700 dark:text-slate-300 mb-1">Select an Inventory Group</h3>
            <p class="text-xs text-gray-400 max-w-xs mx-auto dark:text-slate-400">
              Choose a category from the directory list on the left to manage attributes, assign bulk rules, or view associated products.
            </p>
          </div>
        </div>
      </div>

      <!-- Create / Edit Group Modal Dialog -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" role="dialog">
        <div class="relative bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-[28px] max-w-md w-full overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
          
          <!-- Header -->
          <div class="p-6 border-b border-slate-200 dark:border-[#2E2E2E]">
            <h2 class="text-sm font-extrabold text-gray-900 dark:text-slate-100 uppercase tracking-wider">
              {{ editingGroup ? 'Modify Group' : 'Create New Group' }}
            </h2>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5 dark:text-slate-400">Classification Parameters</p>
          </div>

          <!-- Form fields -->
          <form @submit.prevent="saveGroup" class="p-6 space-y-4 overflow-y-auto">
            <!-- Group Name -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Group Name *</label>
              <input
                v-model="groupForm.name"
                type="text"
                required
                placeholder="e.g. Clothing, Furniture..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
              />
            </div>

            <!-- Parent Group -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Parent Group (Hierarchy)</label>
              <select
                v-model="groupForm.parent_id"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200 font-semibold"
              >
                <option :value="null">None (Root Level Group)</option>
                <option v-for="g in parentGroupOptions" :key="g.id" :value="g.id">
                  {{ g.name }}
                </option>
              </select>
            </div>

            <!-- Default Tax Rate -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Default Tax Group</label>
              <select
                v-model="groupForm.tax_id"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200 font-semibold"
              >
                <option :value="null">None (Inherit Global Default)</option>
                <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                  {{ tax.name }} ({{ parseFloat(tax.value) }}%)
                </option>
              </select>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Description</label>
              <textarea
                v-model="groupForm.description"
                rows="3"
                placeholder="Provide information about this classification..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200 resize-none"
              ></textarea>
            </div>

            <!-- Active Status Toggle -->
            <div class="flex items-center justify-between py-2 border-t border-slate-200 dark:border-[#2E2E2E] mt-2">
              <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Set Active Status</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="groupForm.is_active" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 dark:bg-[#252525] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2E2E2E]">
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
                <span>{{ saving ? 'Saving...' : 'Save Group' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// States
const groups = ref([]);
const allGroups = ref([]);
const taxes = ref([]);
const searchQuery = ref('');
const productSearch = ref('');
const selectedGroup = ref(null);
const groupProducts = ref([]);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const showModal = ref(false);
const editingGroup = ref(null);
const saving = ref(false);
const toast = ref(null);

const groupForm = ref({
  name: '',
  description: '',
  parent_id: null,
  tax_id: null,
  is_active: true
});

const bulkAction = ref({
  action_type: 'markup_percentage',
  value: 0,
  tax_id: null,
  apply_to_subcategories: false
});
const loadingBulkAction = ref(false);

// Load Directory & Taxes
const loadData = async () => {
  try {
    const [catRes, taxRes] = await Promise.all([
      axios.get('/api/categories'),
      axios.get('/api/taxes')
    ]);
    allGroups.value = catRes.data;
    taxes.value = taxRes.data;

    // Reselect selectedGroup to update count/objects
    if (selectedGroup.value) {
      const updated = allGroups.value.find(g => g.id === selectedGroup.value.id);
      if (updated) {
        selectGroup(updated);
      } else {
        selectedGroup.value = null;
      }
    }
  } catch (error) {
    showToast('error', 'Failed to load group inventory directory.');
  }
};

const fetchGroups = async () => {
  try {
    const params = {
      paginate: true,
      per_page: 10,
      page: pagination.value.current_page,
      search: searchQuery.value
    };
    const res = await axios.get('/api/categories', { params });
    groups.value = res.data.data;
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total
    };
  } catch (error) {
    showToast('error', 'Failed to load group directory.');
  }
};

const goToPage = (page) => {
  pagination.value.current_page = page;
  fetchGroups();
};

let debounceTimeout = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchGroups();
  }, 350);
};

const selectGroup = async (group) => {
  selectedGroup.value = group;
  groupProducts.value = [];
  try {
    const res = await axios.get(`/api/categories/${group.id}`);
    groupProducts.value = res.data.products || [];
  } catch (error) {
    showToast('error', 'Failed to load associated products.');
  }
};

const parentGroupOptions = computed(() => {
  // If editing, filter out self and descendants to prevent loops
  if (!editingGroup.value) return allGroups.value;
  return allGroups.value.filter(g => g.id !== editingGroup.value.id && g.parent_id !== editingGroup.value.id);
});

const filteredProducts = computed(() => {
  if (!productSearch.value.trim()) return groupProducts.value;
  return groupProducts.value.filter(p =>
    p.name.toLowerCase().includes(productSearch.value.toLowerCase()) ||
    p.sku.toLowerCase().includes(productSearch.value.toLowerCase())
  );
});

// Modal Actions
const openCreateModal = () => {
  editingGroup.value = null;
  groupForm.value = {
    name: '',
    description: '',
    parent_id: null,
    tax_id: null,
    is_active: true
  };
  showModal.value = true;
};

const openEditModal = (group) => {
  editingGroup.value = group;
  groupForm.value = {
    name: group.name,
    description: group.description,
    parent_id: group.parent_id,
    tax_id: group.tax_id,
    is_active: group.is_active
  };
  showModal.value = true;
};

const saveGroup = async () => {
  saving.value = true;
  try {
    if (editingGroup.value) {
      const res = await axios.put(`/api/categories/${editingGroup.value.id}`, groupForm.value);
      showToast('success', 'Group updated successfully.');
      selectedGroup.value = res.data.category;
    } else {
      await axios.post('/api/categories', groupForm.value);
      showToast('success', 'New group created successfully.');
    }
    showModal.value = false;
    await loadData();
    await fetchGroups();
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Error occurred while saving group parameters.';
    showToast('error', errorMsg);
  } finally {
    saving.value = false;
  }
};

const deleteGroup = async (group) => {
  if (!confirm(`Are you sure you want to delete the group "${group.name}"?`)) return;
  try {
    await axios.delete(`/api/categories/${group.id}`);
    showToast('success', 'Group removed successfully.');
    selectedGroup.value = null;
    await loadData();
    await fetchGroups();
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Cannot delete group with child nesting or active products.';
    showToast('error', errorMsg);
  }
};

// Apply Bulk Actions (Pricing / Discounts / Tax Assignment)
const runBulkAction = async () => {
  if (!selectedGroup.value) return;
  
  // Validation
  if (bulkAction.value.action_type !== 'set_tax' && (bulkAction.value.value === null || bulkAction.value.value === '')) {
    showToast('error', 'Please enter a valid percentage value.');
    return;
  }

  const confirmMsg = bulkAction.value.action_type === 'set_tax'
    ? `Apply tax rule changes to all products under "${selectedGroup.value.name}"?`
    : `Execute bulk price updates for products under "${selectedGroup.value.name}"? This action modifies active catalog selling prices.`;

  if (!confirm(confirmMsg)) return;

  loadingBulkAction.value = true;
  try {
    await axios.post(`/api/categories/${selectedGroup.value.id}/apply-pricing`, bulkAction.value);
    showToast('success', 'Bulk pricing/tax rule updates applied successfully.');
    
    // Refresh display
    await selectGroup(selectedGroup.value);
    await loadData();
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Failed to apply bulk actions.';
    showToast('error', errorMsg);
  } finally {
    loadingBulkAction.value = false;
  }
};

// Helper: Toast alerts
const showToast = (type, message) => {
  toast.value = { type, message };
  setTimeout(() => {
    if (toast.value?.message === message) {
      toast.value = null;
    }
  }, 4000);
};

onMounted(() => {
  loadData();
  fetchGroups();
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
</style>
