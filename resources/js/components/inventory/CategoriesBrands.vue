<template>
  <div class="w-full mx-auto p-3 sm:p-4 lg:p-5 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-full mx-auto space-y-4">
      
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">
            Categories & Brands
          </h1>
          <p class="text-xs text-gray-550 dark:text-slate-400 mt-1">
            Organize catalog structures, manage nested categories, and define product brands.
          </p>
        </div>

        <!-- Add buttons dynamically based on active tab -->
        <div>
          <button
            v-if="activeTab === 'categories'"
            @click="openCategoryModal()"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-150 shadow-md flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Create Category
          </button>
          <button
            v-if="activeTab === 'brands'"
            @click="openBrandModal()"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-150 shadow-md flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Create Brand
          </button>
        </div>
      </div>

      <!-- Tab Switcher -->
      <div class="flex border-b border-slate-200 dark:border-[#2E2E2E]">
        <button
          @click="activeTab = 'categories'"
          :class="[
            'pb-3.5 px-6 text-sm font-bold border-b-2 transition-all duration-150 cursor-pointer',
            activeTab === 'categories'
              ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
              : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
          ]"
        >
          Categories
        </button>
        <button
          @click="activeTab = 'brands'"
          :class="[
            'pb-3.5 px-6 text-sm font-bold border-b-2 transition-all duration-150 cursor-pointer',
            activeTab === 'brands'
              ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
              : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
          ]"
        >
          Brands
        </button>
      </div>

      <!-- Tab Content: Categories -->
      <div v-if="activeTab === 'categories'" class="space-y-4">
        <!-- Search & Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-3 bg-white dark:bg-[#1E1E1E] p-4 rounded-2xl border border-slate-100 dark:border-[#2E2E2E] shadow-xs">
          <div class="relative flex-1">
            <input
              v-model="categorySearch"
              type="text"
              placeholder="Search categories by name or slug..."
              class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 rounded-xl outline-none transition-all dark:text-slate-250"
            />
            <span class="absolute left-3 top-2.5 text-gray-400 dark:text-slate-400">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </span>
          </div>
          <div class="w-full sm:w-48">
            <CustomFloatingSelect
              v-model="categoryStatusFilter"
              :options="statusFilterOptions"
              placeholder="All Statuses"
              buttonClass="!bg-slate-50 dark:!bg-zinc-950 border-slate-200 dark:border-[#2E2E2E] rounded-xl !py-2"
            />
          </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-[24px] overflow-hidden shadow-xs">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
              <thead class="bg-slate-50 dark:bg-[#252525]">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Slug</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Description</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Parent Category</th>
                  <th class="px-6 py-3.5 text-center text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 bg-white dark:bg-[#1E1E1E] text-xs">
                <tr v-for="cat in filteredCategories" :key="cat.id" class="hover:bg-slate-55/40 dark:hover:bg-[#2D2D2D]/60 transition-colors">
                  <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                    <span class="flex items-center gap-1.5">
                      <span v-if="cat.parent_id" class="text-slate-400">subdirectory_arrow_right</span>
                      {{ cat.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 font-mono text-slate-500 dark:text-slate-400">{{ cat.slug }}</td>
                  <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">{{ cat.description || '-' }}</td>
                  <td class="px-6 py-4">
                    <span v-if="cat.parent" class="px-2.5 py-1 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-lg font-semibold">
                      {{ cat.parent.name }}
                    </span>
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span
                      class="px-2.5 py-0.5 text-[10px] font-black rounded-full uppercase tracking-wider inline-block"
                      :class="cat.is_active ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-slate-400'"
                    >
                      {{ cat.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="openCategoryModal(cat)"
                        class="p-2 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 rounded-xl transition-all cursor-pointer dark:text-slate-400"
                        title="Edit Category"
                      >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                      </button>
                      <button
                        @click="deleteCategory(cat)"
                        class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all cursor-pointer"
                        title="Delete Category"
                      >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredCategories.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-400 font-medium dark:text-slate-400">
                    No categories found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Content: Brands -->
      <div v-if="activeTab === 'brands'" class="space-y-4">
        <!-- Search & Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-3 bg-white dark:bg-[#1E1E1E] p-4 rounded-2xl border border-slate-100 dark:border-[#2E2E2E] shadow-xs">
          <div class="relative flex-1">
            <input
              v-model="brandSearch"
              type="text"
              placeholder="Search brands by name or slug..."
              class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 rounded-xl outline-none transition-all dark:text-slate-250"
            />
            <span class="absolute left-3 top-2.5 text-gray-400 dark:text-slate-400">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </span>
          </div>
          <div class="w-full sm:w-48">
            <CustomFloatingSelect
              v-model="brandStatusFilter"
              :options="statusFilterOptions"
              placeholder="All Statuses"
              buttonClass="!bg-slate-50 dark:!bg-zinc-950 border-slate-200 dark:border-[#2E2E2E] rounded-xl !py-2"
            />
          </div>
        </div>

        <!-- Brands Table -->
        <div class="bg-white dark:bg-[#1E1E1E] border border-slate-100 dark:border-[#2E2E2E] rounded-[24px] overflow-hidden shadow-xs">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
              <thead class="bg-slate-50 dark:bg-[#252525]">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider w-20">Logo</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Slug</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Parent Brand</th>
                  <th class="px-6 py-3.5 text-center text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 dark:text-slate-450 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 bg-white dark:bg-[#1E1E1E] text-xs">
                <tr v-for="brand in filteredBrands" :key="brand.id" class="hover:bg-slate-55/40 dark:hover:bg-[#2D2D2D]/60 transition-colors">
                  <td class="px-6 py-4">
                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 flex items-center justify-center">
                      <img v-if="brand.logo" :src="brand.logo" class="object-contain w-full h-full p-0.5" alt="Brand Logo" />
                      <span v-else class="text-xs font-black text-indigo-600 dark:text-indigo-400">
                        {{ brand.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">{{ brand.name }}</td>
                  <td class="px-6 py-4 font-mono text-slate-500 dark:text-slate-400">{{ brand.slug }}</td>
                  <td class="px-6 py-4">
                    <span v-if="brand.parent" class="px-2.5 py-1 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-lg font-semibold">
                      {{ brand.parent.name }}
                    </span>
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span
                      class="px-2.5 py-0.5 text-[10px] font-black rounded-full uppercase tracking-wider inline-block"
                      :class="brand.is_active ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-slate-400'"
                    >
                      {{ brand.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="openBrandModal(brand)"
                        class="p-2 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 rounded-xl transition-all cursor-pointer dark:text-slate-400"
                        title="Edit Brand"
                      >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                      </button>
                      <button
                        @click="deleteBrand(brand)"
                        class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all cursor-pointer"
                        title="Delete Brand"
                      >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredBrands.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-400 font-medium dark:text-slate-400">
                    No brands found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Create / Edit Category Modal Dialog -->
      <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" role="dialog">
        <div class="relative bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-[28px] max-w-md w-full overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
          <!-- Header -->
          <div class="p-6 border-b border-slate-200 dark:border-[#2E2E2E]">
            <h2 class="text-sm font-extrabold text-gray-900 dark:text-slate-100 uppercase tracking-wider">
              {{ editingCategory ? 'Modify Category' : 'Create New Category' }}
            </h2>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5 dark:text-slate-400">Catalog Hierarchy Parameter</p>
          </div>

          <!-- Form fields -->
          <form @submit.prevent="saveCategory" class="p-6 space-y-4 overflow-y-auto">
            <!-- Category Name -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-450">Category Name *</label>
              <input
                v-model="categoryForm.name"
                type="text"
                required
                placeholder="e.g. Shirts, Hardware..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
              />
            </div>

            <!-- Sub Category (Parent Category) -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-450">Sub Category Of</label>
              <CustomFloatingSelect
                v-model="categoryForm.parent_id"
                :options="categoryFloatingOptions"
                placeholder="Select Parent Category..."
                :searchable="true"
                buttonClass="!bg-slate-50 dark:!bg-zinc-950 border-slate-200 dark:border-[#2E2E2E] rounded-xl !py-2"
              />
            </div>

            <!-- Description -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-450">Description</label>
              <textarea
                v-model="categoryForm.description"
                rows="3"
                placeholder="Provide notes or information about this category..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200 resize-none"
              ></textarea>
            </div>

            <!-- Active Status Toggle -->
            <div class="flex items-center justify-between py-2 border-t border-slate-200 dark:border-[#2E2E2E] mt-2">
              <span class="text-xs font-semibold text-slate-650 dark:text-slate-400">Set Active Status</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="categoryForm.is_active" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 dark:bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 dark:peer-checked:bg-emerald-600"></div>
              </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2E2E2E]">
              <button
                type="button"
                @click="showCategoryModal = false"
                class="px-4 py-2 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingCategory"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all cursor-pointer flex items-center justify-center gap-1"
              >
                <span v-if="savingCategory" class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                <span>{{ savingCategory ? 'Saving...' : 'Save Category' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Create / Edit Brand Modal Dialog -->
      <div v-if="showBrandModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" role="dialog">
        <div class="relative bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-[28px] max-w-md w-full overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
          <!-- Header -->
          <div class="p-6 border-b border-slate-200 dark:border-[#2E2E2E]">
            <h2 class="text-sm font-extrabold text-gray-900 dark:text-slate-100 uppercase tracking-wider">
              {{ editingBrand ? 'Modify Brand' : 'Create New Brand' }}
            </h2>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5 dark:text-slate-400">Identity Details</p>
          </div>

          <!-- Form fields -->
          <form @submit.prevent="saveBrand" class="p-6 space-y-4 overflow-y-auto" enctype="multipart/form-data">
            <!-- Brand Name -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-455">Brand Name *</label>
              <input
                v-model="brandForm.name"
                type="text"
                required
                placeholder="e.g. Nike, Apple..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200"
              />
            </div>

            <!-- Sub Brand (Parent Brand) -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-455">Sub Brand Of</label>
              <CustomFloatingSelect
                v-model="brandForm.parent_id"
                :options="brandFloatingOptions"
                placeholder="Select Parent Brand..."
                :searchable="true"
                buttonClass="!bg-slate-50 dark:!bg-zinc-950 border-slate-200 dark:border-[#2E2E2E] rounded-xl !py-2"
              />
            </div>

            <!-- Brand Logo Upload -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-455">Brand Logo</label>
              <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 flex items-center justify-center shrink-0">
                  <img v-if="logoPreview" :src="logoPreview" class="object-contain w-full h-full p-0.5" alt="Logo Preview" />
                  <span v-else class="text-xl">🖼️</span>
                </div>
                <div class="flex-1">
                  <input
                    type="file"
                    ref="brandLogoInput"
                    accept="image/*"
                    @change="handleLogoChange"
                    class="hidden"
                  />
                  <button
                    type="button"
                    @click="$refs.brandLogoInput.click()"
                    class="px-3.5 py-2 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/85 text-slate-600 dark:text-slate-350 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer"
                  >
                    Select Logo File
                  </button>
                  <button
                    v-if="logoPreview"
                    type="button"
                    @click="clearLogoSelection"
                    class="ml-2 text-rose-500 hover:text-rose-700 font-semibold text-xs cursor-pointer"
                  >
                    Remove
                  </button>
                  <p class="text-[9px] text-gray-400 mt-1">Supported formats: JPG, PNG, GIF, SVG, WEBP. Max 10MB.</p>
                </div>
              </div>
            </div>

            <!-- Active Status Toggle -->
            <div class="flex items-center justify-between py-2 border-t border-slate-200 dark:border-[#2E2E2E] mt-2">
              <span class="text-xs font-semibold text-slate-650 dark:text-slate-400">Set Active Status</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="brandForm.is_active" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 dark:bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 dark:peer-checked:bg-emerald-600"></div>
              </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2E2E2E]">
              <button
                type="button"
                @click="showBrandModal = false"
                class="px-4 py-2 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingBrand"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all cursor-pointer flex items-center justify-center gap-1"
              >
                <span v-if="savingBrand" class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                <span>{{ savingBrand ? 'Saving...' : 'Save Brand' }}</span>
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
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';

const { showToast } = useToast();
const { confirm } = useConfirm();

// State managers
const activeTab = ref('categories');

// Categories States
const categories = ref([]);
const categorySearch = ref('');
const categoryStatusFilter = ref('all');
const showCategoryModal = ref(false);
const editingCategory = ref(null);
const savingCategory = ref(false);
const categoryForm = ref({
  name: '',
  slug: '',
  parent_id: null,
  description: '',
  is_active: true
});

// Brands States
const brands = ref([]);
const brandSearch = ref('');
const brandStatusFilter = ref('all');
const showBrandModal = ref(false);
const editingBrand = ref(null);
const savingBrand = ref(false);
const logoFile = ref(null);
const logoPreview = ref(null);
const brandForm = ref({
  name: '',
  slug: '',
  parent_id: null,
  is_active: true
});

// Loading functions
const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories');
    // Category returns full raw array
    categories.value = res.data;
  } catch (err) {
    showToast('Failed to load categories.', 'error');
  }
};

const fetchBrands = async () => {
  try {
    const res = await axios.get('/api/brands');
    brands.value = res.data;
  } catch (err) {
    showToast('Failed to load brands.', 'error');
  }
};

// Filtered Lists
const filteredCategories = computed(() => {
  return categories.value.filter(cat => {
    const nameMatch = cat.name.toLowerCase().includes(categorySearch.value.toLowerCase()) ||
                      (cat.slug && cat.slug.toLowerCase().includes(categorySearch.value.toLowerCase()));
    
    if (categoryStatusFilter.value === 'active') {
      return nameMatch && cat.is_active;
    } else if (categoryStatusFilter.value === 'inactive') {
      return nameMatch && !cat.is_active;
    }
    return nameMatch;
  });
});

const filteredBrands = computed(() => {
  return brands.value.filter(brand => {
    const nameMatch = brand.name.toLowerCase().includes(brandSearch.value.toLowerCase()) ||
                      (brand.slug && brand.slug.toLowerCase().includes(brandSearch.value.toLowerCase()));
    
    if (brandStatusFilter.value === 'active') {
      return nameMatch && brand.is_active;
    } else if (brandStatusFilter.value === 'inactive') {
      return nameMatch && !brand.is_active;
    }
    return nameMatch;
  });
});

// Status filter options
const statusFilterOptions = [
  { label: 'All Statuses', value: 'all' },
  { label: 'Active Only', value: 'active' },
  { label: 'Inactive Only', value: 'inactive' }
];

// Category drop-down options (Filter out self and descendants when editing)
const categoryOptions = computed(() => {
  if (!editingCategory.value) return categories.value;
  return categories.value.filter(c => c.id !== editingCategory.value.id && c.parent_id !== editingCategory.value.id);
});

const categoryFloatingOptions = computed(() => {
  const options = [{ label: 'None (Root Category)', value: null }];
  categoryOptions.value.forEach(c => {
    options.push({ label: c.name, value: c.id });
  });
  return options;
});

// Brand drop-down options (Filter out self and descendants when editing)
const brandOptions = computed(() => {
  if (!editingBrand.value) return brands.value;
  return brands.value.filter(b => b.id !== editingBrand.value.id && b.parent_id !== editingBrand.value.id);
});

const brandFloatingOptions = computed(() => {
  const options = [{ label: 'None (Root Brand)', value: null }];
  brandOptions.value.forEach(b => {
    options.push({ label: b.name, value: b.id });
  });
  return options;
});

// Category Modal functions
const openCategoryModal = (cat = null) => {
  if (cat) {
    editingCategory.value = cat;
    categoryForm.value = {
      name: cat.name,
      slug: cat.slug || '',
      parent_id: cat.parent_id,
      description: cat.description || '',
      is_active: !!cat.is_active
    };
  } else {
    editingCategory.value = null;
    categoryForm.value = {
      name: '',
      slug: '',
      parent_id: null,
      description: '',
      is_active: true
    };
  }
  showCategoryModal.value = true;
};

const saveCategory = async () => {
  savingCategory.value = true;
  try {
    if (editingCategory.value) {
      await axios.put(`/api/categories/${editingCategory.value.id}`, categoryForm.value);
      showToast('Category updated successfully.', 'success');
    } else {
      await axios.post('/api/categories', categoryForm.value);
      showToast('Category created successfully.', 'success');
    }
    showCategoryModal.value = false;
    await fetchCategories();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save category.';
    showToast(msg, 'error');
  } finally {
    savingCategory.value = false;
  }
};

const deleteCategory = async (cat) => {
  const confirmed = await confirm({
    title: 'Delete Category?',
    message: `Are you sure you want to delete category "${cat.name}"? This action can be undone if soft deleted.`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  if (!confirmed) return;

  try {
    await axios.delete(`/api/categories/${cat.id}`);
    showToast('Category deleted successfully.', 'success');
    await fetchCategories();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to delete category.';
    showToast(msg, 'error');
  }
};

// Brand Modal & File functions
const openBrandModal = (brand = null) => {
  logoFile.value = null;
  if (brand) {
    editingBrand.value = brand;
    brandForm.value = {
      name: brand.name,
      slug: brand.slug || '',
      parent_id: brand.parent_id || null,
      is_active: !!brand.is_active
    };
    logoPreview.value = brand.logo || null;
  } else {
    editingBrand.value = null;
    brandForm.value = {
      name: '',
      slug: '',
      parent_id: null,
      is_active: true
    };
    logoPreview.value = null;
  }
  showBrandModal.value = true;
};

const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate size & type
  if (!file.type.startsWith('image/')) {
    showToast('Please select an image file.', 'error');
    return;
  }
  if (file.size > 10 * 1024 * 1024) {
    showToast('Logo must be less than 10MB.', 'error');
    return;
  }

  logoFile.value = file;
  logoPreview.value = URL.createObjectURL(file);
};

const clearLogoSelection = () => {
  logoFile.value = null;
  logoPreview.value = null;
};

const saveBrand = async () => {
  savingBrand.value = true;

  // Construct FormData
  const formData = new FormData();
  formData.append('name', brandForm.value.name);
  formData.append('slug', brandForm.value.slug || '');
  formData.append('is_active', brandForm.value.is_active ? '1' : '0');
  if (brandForm.value.parent_id) {
    formData.append('parent_id', brandForm.value.parent_id);
  } else {
    formData.append('parent_id', '');
  }
  
  if (logoFile.value) {
    formData.append('logo', logoFile.value);
  }

  try {
    if (editingBrand.value) {
      // Laravel PUT/PATCH file upload bug workaround
      formData.append('_method', 'PUT');
      await axios.post(`/api/brands/${editingBrand.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      showToast('Brand updated successfully.', 'success');
    } else {
      await axios.post('/api/brands', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      showToast('Brand created successfully.', 'success');
    }
    showBrandModal.value = false;
    await fetchBrands();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save brand.';
    showToast(msg, 'error');
  } finally {
    savingBrand.value = false;
  }
};

const deleteBrand = async (brand) => {
  const confirmed = await confirm({
    title: 'Delete Brand?',
    message: `Are you sure you want to delete brand "${brand.name}"? This action can be undone if soft deleted.`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  if (!confirmed) return;

  try {
    await axios.delete(`/api/brands/${brand.id}`);
    showToast('Brand deleted successfully.', 'success');
    await fetchBrands();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to delete brand.';
    showToast(msg, 'error');
  }
};

// Lifecycle
onMounted(() => {
  fetchCategories();
  fetchBrands();
});
</script>
