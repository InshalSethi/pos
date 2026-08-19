<template>
  <div class="w-full mx-auto p-3 sm:p-4 lg:p-5 bg-slate-50/50 dark:bg-zinc-950 min-h-screen">
    <div class="w-full max-w-full mx-auto space-y-4">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-slate-100 dark:bg-[#252525] border border-slate-200 dark:border-[#2E2E2E] rounded-2xl text-slate-800 dark:text-slate-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-slate-100 tracking-tight">Bill of materials (BOM)</h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 font-medium">Link raw materials and ingredients to finished goods for automated stock deduction and manufacturing cost calculation.</p>
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
            + Create New BOM
          </button>
        </div>
      </div>

      <!-- Datatable Container Card -->
      <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2E2E2E] overflow-hidden shadow-sm flex flex-col justify-between min-h-[500px]">
        
        <!-- Filter Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-100 dark:border-[#2E2E2E]">
          <!-- Left side: Search & Status Filter -->
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
                placeholder="Search by recipe name or SKU..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-200 dark:border-[#2E2E2E] rounded-xl text-gray-900 dark:text-slate-200 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 transition-all placeholder:text-gray-400 dark:placeholder:text-slate-500 font-medium"
              />
            </div>

            <!-- Status Filter -->
            <select
              v-model="tableFilters.is_active"
              @change="fetchRecipes(1)"
              class="px-3 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-200 dark:border-[#2E2E2E] rounded-xl text-gray-800 dark:text-slate-200 font-medium focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 cursor-pointer"
            >
              <option value="">All Statuses</option>
              <option value="true">Active Only</option>
              <option value="false">Inactive Only</option>
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

        <!-- Datatable Area -->
        <div class="w-full overflow-x-auto min-h-[350px]">
          <table class="w-full table-auto align-middle border-collapse">
            <thead>
              <tr class="border-b border-gray-100 dark:border-[#2E2E2E] bg-slate-50/50 dark:bg-[#252525] text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400 select-none">
                <th
                  @click="sortByColumn('name')"
                  class="px-4 py-3.5 text-left cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center gap-1.5">
                    <span>Recipe / Finished Good</span>
                    <span v-if="tableFilters.sort_by === 'name'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th
                  @click="sortByColumn('yield_quantity')"
                  class="px-4 py-3.5 text-left cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center gap-1.5">
                    <span>Yield Quantity</span>
                    <span v-if="tableFilters.sort_by === 'yield_quantity'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th class="px-4 py-3.5 text-center">Ingredients Count</th>
                <th class="px-4 py-3.5 text-center">Total Recipe Cost</th>
                <th class="px-4 py-3.5 text-center">Unit Cost</th>

                <th
                  @click="sortByColumn('is_active')"
                  class="px-4 py-3.5 text-center cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                  <div class="flex items-center justify-center gap-1.5">
                    <span>Status</span>
                    <span v-if="tableFilters.sort_by === 'is_active'" class="text-slate-900 dark:text-white font-bold">
                      {{ tableFilters.sort_order === 'asc' ? '▲' : '▼' }}
                    </span>
                  </div>
                </th>

                <th class="px-4 py-3.5 text-right w-24">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-[#2E2E2E]">
              <!-- Loading State -->
              <tr v-if="loading">
                <td colspan="7" class="px-4 py-16 text-center text-gray-400">
                  <div class="flex justify-center items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-emerald-600" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Fetching Bill of Materials...</span>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-else-if="recipes.length === 0">
                <td colspan="7" class="px-4 py-16 text-center text-gray-500">
                  <div class="flex flex-col items-center max-w-sm mx-auto">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#252525] flex items-center justify-center mb-3 text-slate-500">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                      </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-900 dark:text-slate-200 mb-1">No BOM Recipes Found</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mb-3">Create recipes to map ingredients for items like Burgers, Pizza & Fast Food.</p>
                    <button
                      @click="openModal()"
                      class="px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 text-xs font-bold rounded-xl shadow cursor-pointer"
                    >
                      Create First BOM
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Data Rows -->
              <tr
                v-else
                v-for="recipe in recipes"
                :key="recipe.id"
                class="hover:bg-slate-50/50 dark:hover:bg-[#2D2D2D]/60 transition-colors"
              >
                <!-- Recipe Name / Target Product -->
                <td class="px-4 py-3.5 align-middle">
                  <div class="font-bold text-gray-900 dark:text-slate-100 text-xs sm:text-sm">{{ recipe.name }}</div>
                  <div class="text-[11px] text-gray-400 dark:text-slate-400 mt-0.5">
                    Target Product: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ recipe.product ? recipe.product.name : 'N/A' }}</span>
                    <span v-if="recipe.variation" class="text-slate-400"> ({{ recipe.variation.variation_name_string }})</span>
                  </div>
                </td>

                <!-- Yield Quantity -->
                <td class="px-4 py-3.5 align-middle font-bold text-slate-700 dark:text-slate-300 text-xs">
                  {{ formatNumber(recipe.yield_quantity) }} {{ recipe.unit ? recipe.unit.short_name : 'PCS' }}
                </td>

                <!-- Ingredients Count -->
                <td class="px-4 py-3.5 align-middle text-center">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-[#252525] text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-[#2E2E2E]">
                    {{ recipe.ingredients ? recipe.ingredients.length : 0 }} Raw Materials
                  </span>
                </td>

                <!-- Total Recipe Cost -->
                <td class="px-4 py-3.5 align-middle text-center font-extrabold text-gray-900 dark:text-slate-100 text-xs">
                  ${{ formatMoney(recipe.total_cost) }}
                </td>

                <!-- Unit Cost -->
                <td class="px-4 py-3.5 align-middle text-center font-extrabold text-emerald-600 dark:text-emerald-400 text-xs">
                  ${{ formatMoney(recipe.unit_cost) }}
                </td>

                <!-- Status Toggle -->
                <td class="px-4 py-3.5 align-middle text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5 justify-center">
                    <button
                      type="button"
                      @click.stop="toggleStatus(recipe)"
                      :disabled="togglingStatusId === recipe.id"
                      :class="[
                        'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50',
                        recipe.is_active ? 'bg-emerald-600 dark:bg-emerald-600' : 'bg-gray-300 dark:bg-gray-700'
                      ]"
                      role="switch"
                      :aria-checked="recipe.is_active"
                      :title="recipe.is_active ? 'Click to deactivate' : 'Click to activate'"
                    >
                      <span
                        :class="[
                          'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out flex items-center justify-center',
                          recipe.is_active ? 'translate-x-4' : 'translate-x-0'
                        ]"
                      >
                        <svg v-if="togglingStatusId === recipe.id" class="animate-spin h-2.5 w-2.5 text-emerald-600" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </span>
                    </button>
                    <span
                      class="text-xs font-bold"
                      :class="recipe.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-slate-500'"
                    >
                      {{ recipe.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-4 py-3.5 align-middle text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1">
                    <button
                      @click="editRecipe(recipe)"
                      class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition cursor-pointer"
                      title="Edit BOM"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="deleteRecipe(recipe)"
                      class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition cursor-pointer"
                      title="Delete BOM"
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

        <!-- Datatable Pagination Footer (Matching System Screenshot 2 Exactly) -->
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

    <!-- Recipe Modal (Teleported to body to ensure full z-index coverage above sidebar) -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-slate-200 dark:border-zinc-800">
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between bg-slate-50 dark:bg-zinc-900">
            <div>
              <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ isEditing ? 'Edit BOM' : 'Create BOM' }}</h2>
              <p class="text-xs text-slate-500 dark:text-zinc-400">Define raw materials required to produce this item.</p>
            </div>
            <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
            <!-- General Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">Target Product (Finished Good) *</label>
                <SystemSelect
                  v-model="form.product_id"
                  :options="targetProductOptions"
                  placeholder="Select Finished Good / Product"
                  @update:modelValue="onProductSelect"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">Recipe Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="e.g. Standard Burger Recipe"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 font-medium"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">Yield Quantity *</label>
                <input
                  v-model.number="form.yield_quantity"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="1"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 font-medium"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">Yield Unit</label>
                <SystemSelect
                  v-model="form.unit_id"
                  :options="unitOptions"
                  placeholder="Select Unit"
                />
              </div>
            </div>

            <!-- Status Toggle Switch -->
            <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200 dark:border-zinc-700">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-200 uppercase tracking-wider">Status</label>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">Set as active BOM recipe for stock deduction and manufacturing.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-300 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                <span class="ml-3 text-xs font-bold uppercase tracking-wider" :class="form.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-zinc-500'">
                  {{ form.is_active ? 'Active' : 'Inactive' }}
                </span>
              </label>
            </div>

            <!-- Ingredients Section -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-zinc-100 uppercase tracking-wider">Raw Material Ingredients *</h3>
                <button
                  @click="addIngredientRow()"
                  type="button"
                  class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white text-xs font-bold rounded-lg border border-transparent transition cursor-pointer"
                >
                  + Add Ingredient
                </button>
              </div>

              <div class="space-y-3">
                <div
                  v-for="(ing, index) in form.ingredients"
                  :key="index"
                  class="grid grid-cols-12 gap-2 items-center bg-slate-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-slate-200 dark:border-zinc-700"
                >
                  <div class="col-span-5">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase mb-1">Raw Material</label>
                    <SystemSelect
                      v-model="ing.raw_material_id"
                      :options="rawMaterialOptions"
                      placeholder="Select Ingredient"
                    />
                  </div>

                  <div class="col-span-3">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase mb-1">Quantity Needed</label>
                    <input
                      v-model.number="ing.quantity"
                      type="number"
                      step="0.0001"
                      min="0.0001"
                      placeholder="0.00"
                      class="w-full py-2 px-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 font-medium"
                    />
                  </div>

                  <div class="col-span-3">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase mb-1">Waste %</label>
                    <input
                      v-model.number="ing.waste_percentage"
                      type="number"
                      step="0.1"
                      min="0"
                      max="100"
                      placeholder="0"
                      class="w-full py-2 px-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 font-medium"
                    />
                  </div>

                  <div class="col-span-1 text-right pt-4">
                    <button
                      @click="removeIngredientRow(index)"
                      type="button"
                      class="text-red-500 hover:text-red-700 p-1 cursor-pointer font-bold text-base"
                      title="Remove Item"
                    >
                      &times;
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Instructions & Notes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">Preparation Instructions</label>
                <textarea
                  v-model="form.instructions"
                  rows="3"
                  placeholder="Cooking guidelines, temperature, mixing sequence..."
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 font-medium"
                ></textarea>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 uppercase mb-1">Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  placeholder="Internal notes regarding vendor quality or batch size..."
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 font-medium"
                ></textarea>
              </div>
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
              @click="saveRecipe()"
              :disabled="saving"
              type="button"
              class="px-5 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white font-bold text-sm rounded-xl shadow-xs transition disabled:opacity-50 cursor-pointer"
            >
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Add New Unit Modal (Teleported to Body) -->
    <Teleport to="body">
      <div v-if="showUnitModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);" @click.self="closeUnitModal">
        <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto space-y-4">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-zinc-800">
            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Add New Unit</h4>
            <button @click="closeUnitModal" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 font-bold text-lg cursor-pointer">&times;</button>
          </div>
          <div v-if="unitModalError" class="p-3 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 text-xs rounded-xl border border-red-200 dark:border-red-900/50 font-medium">
            {{ unitModalError }}
          </div>
          <div class="space-y-3">
            <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Unit Name *</label>
              <input type="text" v-model="newUnitForm.name" placeholder="e.g., Kilogram, Litre, Piece" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300 font-medium">
            </div>
            <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Short Name / Code *</label>
              <input type="text" v-model="newUnitForm.short_name" placeholder="e.g., KG, LTR, PCS" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300 font-medium">
            </div>
            <div class="flex items-center justify-between py-1">
              <div>
                <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Is Active</label>
                <p class="text-[9px] text-slate-400 dark:text-slate-500">Visible and active across system.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" v-model="newUnitForm.is_active" class="sr-only peer">
                <div class="w-8 h-4.5 bg-slate-200 dark:bg-[#252525] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-2 text-xs">
            <button type="button" @click="closeUnitModal" class="px-3 py-1 text-slate-500 font-medium hover:text-slate-700 dark:hover:text-slate-300 cursor-pointer">Cancel</button>
            <button type="button" @click="submitNewUnit" :disabled="submittingUnit" class="px-4 py-1.5 bg-emerald-600 text-white font-bold rounded-xl shadow hover:bg-emerald-700 transition-colors flex items-center gap-1.5 disabled:opacity-50 cursor-pointer">
              <svg v-if="submittingUnit" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Create Unit
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

const recipes = ref([]);
const loading = ref(false);
const saving = ref(false);
const togglingStatusId = ref(null);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const productsList = ref([]);
const units = ref([]);

// Add New Unit Modal State
const showUnitModal = ref(false);
const submittingUnit = ref(false);
const unitModalError = ref('');
const newUnitForm = ref({
  name: '',
  short_name: '',
  is_active: true
});

// Datatable Filter & Pagination State
const tableFilters = ref({
  search: '',
  is_active: '',
  sort_by: 'created_at',
  sort_order: 'desc'
});

const tablePagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

let searchTimeout = null;

const form = ref({
  product_id: null,
  product_variation_id: null,
  name: '',
  yield_quantity: 1,
  unit_id: null,
  instructions: '',
  notes: '',
  is_active: true,
  ingredients: [],
});

const hasActiveFilters = computed(() => {
  return tableFilters.value.search !== '' || tableFilters.value.is_active !== '';
});

const paginationPages = computed(() => {
  const pages = [];
  const current = tablePagination.value.current_page || 1;
  const last = tablePagination.value.last_page || 1;
  
  let start = Math.max(1, current - 2);
  let end = Math.min(last, current + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

// Target Products where can_be_sold is true AND can_be_purchased is false
const finishedGoodProducts = computed(() => {
  return productsList.value.filter(p => {
    const canBeSold = p.can_be_sold === true || p.can_be_sold === 1 || p.can_be_sold === '1';
    const canBePurchased = p.can_be_purchased === true || p.can_be_purchased === 1 || p.can_be_purchased === '1';
    return canBeSold && !canBePurchased;
  });
});

const targetProductOptions = computed(() => {
  return finishedGoodProducts.value.map(p => ({
    label: `${p.name} (${p.sku || 'No SKU'})`,
    value: p.id
  }));
});

// Products of type 'raw_material' or with can_be_purchased=true & can_be_sold=false
const rawMaterialProducts = computed(() => {
  return productsList.value.filter(p => {
    const isRawType = p.item_type === 'raw_material';
    const isRawFlags = (p.can_be_purchased === true || p.can_be_purchased === 1 || p.can_be_purchased === '1') &&
                       !(p.can_be_sold === true || p.can_be_sold === 1 || p.can_be_sold === '1');
    return isRawType || isRawFlags;
  });
});

const rawMaterialOptions = computed(() => {
  return rawMaterialProducts.value.map(rm => ({
    label: `${rm.name} (Cost: $${formatMoney(rm.cost_price || rm.purchase_price || 0)})`,
    value: rm.id
  }));
});

const unitOptions = computed(() => {
  const list = Array.isArray(units.value) ? units.value : [];
  const options = list.map(u => ({ label: `${u.name} (${u.short_name})`, value: u.id }));
  options.push({ label: '+ ADD New Unit', value: 'add_new_unit' });
  return options;
});

// Watch for '+ ADD New Unit' selection in Yield Unit
watch(() => form.value.unit_id, (val) => {
  if (val === 'add_new_unit') {
    form.value.unit_id = null;
    openUnitModal();
  }
});

onMounted(() => {
  fetchRecipes(1);
  fetchProducts();
  fetchUnits();
});

const fetchRecipes = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page: page,
      per_page: tablePagination.value.per_page,
      search: tableFilters.value.search,
      is_active: tableFilters.value.is_active,
      sort_by: tableFilters.value.sort_by,
      sort_order: tableFilters.value.sort_order
    };

    const res = await axios.get('/api/recipes', { params });
    
    if (res.data && res.data.data) {
      recipes.value = res.data.data;
      tablePagination.value = {
        current_page: res.data.current_page || 1,
        last_page: res.data.last_page || 1,
        per_page: res.data.per_page || 15,
        total: res.data.total || 0,
        from: res.data.from || 0,
        to: res.data.to || 0
      };
    } else {
      recipes.value = Array.isArray(res.data) ? res.data : [];
      tablePagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: tablePagination.value.per_page,
        total: recipes.value.length,
        from: recipes.value.length ? 1 : 0,
        to: recipes.value.length
      };
    }
  } catch (err) {
    console.error('Failed to fetch recipes:', err);
  } finally {
    loading.value = false;
  }
};

const handleSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchRecipes(1);
  }, 350);
};

const sortByColumn = (col) => {
  if (tableFilters.value.sort_by === col) {
    tableFilters.value.sort_order = tableFilters.value.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    tableFilters.value.sort_by = col;
    tableFilters.value.sort_order = 'asc';
  }
  fetchRecipes(1);
};

const handlePerPageChange = () => {
  fetchRecipes(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= tablePagination.value.last_page) {
    fetchRecipes(page);
  }
};

const resetFilters = () => {
  tableFilters.value = {
    search: '',
    is_active: '',
    sort_by: 'created_at',
    sort_order: 'desc'
  };
  fetchRecipes(1);
};

const toggleStatus = async (recipe) => {
  togglingStatusId.value = recipe.id;
  try {
    const updatedActiveState = !recipe.is_active;
    await axios.put(`/api/recipes/${recipe.id}`, {
      product_id: recipe.product_id,
      product_variation_id: recipe.product_variation_id,
      name: recipe.name,
      yield_quantity: recipe.yield_quantity,
      unit_id: recipe.unit_id,
      instructions: recipe.instructions,
      notes: recipe.notes,
      is_active: updatedActiveState,
      ingredients: recipe.ingredients ? recipe.ingredients.map(ing => ({
        raw_material_id: ing.raw_material_id,
        raw_material_variation_id: ing.raw_material_variation_id,
        quantity: ing.quantity,
        waste_percentage: ing.waste_percentage,
      })) : []
    });
    recipe.is_active = updatedActiveState;
  } catch (err) {
    console.error('Failed to toggle status:', err);
    alert(err.response?.data?.message || 'Failed to update recipe status');
  } finally {
    togglingStatusId.value = null;
  }
};

const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/products', { params: { per_page: 1000 } });
    productsList.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to fetch products:', err);
  }
};

const fetchUnits = async () => {
  try {
    const res = await axios.get('/api/units');
    units.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to fetch units:', err);
  }
};

const openModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    product_id: null,
    product_variation_id: null,
    name: '',
    yield_quantity: 1,
    unit_id: null,
    instructions: '',
    notes: '',
    is_active: true,
    ingredients: [
      { raw_material_id: null, raw_material_variation_id: null, quantity: 1, waste_percentage: 0 }
    ],
  };
  showModal.value = true;
};

const editRecipe = (recipe) => {
  isEditing.value = true;
  editingId.value = recipe.id;
  form.value = {
    product_id: recipe.product_id,
    product_variation_id: recipe.product_variation_id,
    name: recipe.name,
    yield_quantity: recipe.yield_quantity,
    unit_id: recipe.unit_id,
    instructions: recipe.instructions,
    notes: recipe.notes,
    is_active: Boolean(recipe.is_active),
    ingredients: recipe.ingredients ? recipe.ingredients.map(ing => ({
      raw_material_id: ing.raw_material_id,
      raw_material_variation_id: ing.raw_material_variation_id,
      quantity: ing.quantity,
      waste_percentage: ing.waste_percentage,
    })) : [],
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const openUnitModal = () => {
  newUnitForm.value = { name: '', short_name: '', is_active: true };
  unitModalError.value = '';
  showUnitModal.value = true;
};

const closeUnitModal = () => {
  showUnitModal.value = false;
};

const submitNewUnit = async () => {
  if (!newUnitForm.value.name || !newUnitForm.value.short_name) {
    unitModalError.value = 'Please provide both Unit Name and Short Name.';
    return;
  }

  submittingUnit.value = true;
  unitModalError.value = '';

  try {
    const res = await axios.post('/api/units', newUnitForm.value);
    const createdUnit = res.data.data || res.data;
    await fetchUnits();
    if (createdUnit && createdUnit.id) {
      form.value.unit_id = createdUnit.id;
    }
    closeUnitModal();
  } catch (err) {
    console.error('Failed to create unit:', err);
    unitModalError.value = err.response?.data?.message || 'Failed to create new unit.';
  } finally {
    submittingUnit.value = false;
  }
};

const onProductSelect = () => {
  const prod = finishedGoodProducts.value.find(p => p.id === form.value.product_id);
  if (prod && !form.value.name) {
    form.value.name = `${prod.name} Recipe`;
  }
};

const addIngredientRow = () => {
  form.value.ingredients.push({
    raw_material_id: null,
    raw_material_variation_id: null,
    quantity: 1,
    waste_percentage: 0,
  });
};

const removeIngredientRow = (index) => {
  if (form.value.ingredients.length > 1) {
    form.value.ingredients.splice(index, 1);
  }
};

const saveRecipe = async () => {
  if (!form.value.product_id || !form.value.name || form.value.ingredients.length === 0) {
    alert('Please fill in all required fields and add at least 1 raw material ingredient.');
    return;
  }

  saving.value = true;
  try {
    if (isEditing.value) {
      await axios.put(`/api/recipes/${editingId.value}`, form.value);
    } else {
      await axios.post('/api/recipes', form.value);
    }
    closeModal();
    fetchRecipes(tablePagination.value.current_page);
  } catch (err) {
    console.error('Failed to save recipe:', err);
    alert(err.response?.data?.message || 'Error saving recipe');
  } finally {
    saving.value = false;
  }
};

const deleteRecipe = async (recipe) => {
  if (!confirm(`Are you sure you want to delete "${recipe.name}"?`)) return;
  try {
    await axios.delete(`/api/recipes/${recipe.id}`);
    fetchRecipes(tablePagination.value.current_page);
  } catch (err) {
    console.error('Failed to delete recipe:', err);
  }
};

const formatNumber = (val) => {
  return parseFloat(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
};

const formatMoney = (val) => {
  return parseFloat(val || 0).toFixed(2);
};
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
