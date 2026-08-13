<template>
  <div class="space-y-6">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
      <div>
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i class="fas fa-briefcase"></i>
          </div>
          <div>
            <h1 class="text-xl font-black text-zinc-950 dark:text-white tracking-tight">Business Types Management</h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold mt-0.5">
              Manage industry classifications available during user registration and company onboarding.
            </p>
          </div>
        </div>
      </div>

      <button
        @click="openCreateModal"
        class="px-5 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer shrink-0"
      >
        <i class="fas fa-plus mr-2"></i> Add Business Type
      </button>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
      
      <!-- Filter & Per Page Controls -->
      <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col md:flex-row gap-3 justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
        
        <!-- Left: Search Box -->
        <div class="w-full md:w-80 relative">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
          <input
            type="text"
            v-model="searchQuery"
            @input="debouncedFetch"
            placeholder="Search business types..."
            class="w-full pl-9 pr-4 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
          />
        </div>

        <!-- Right: Status Filter & Per Page & Refresh -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
          
          <!-- Status Filter -->
          <div class="flex items-center space-x-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-500">Status:</span>
            <select
              v-model="statusFilter"
              @change="onFilterChange"
              class="px-3 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
            >
              <option value="all">All Statuses</option>
              <option value="true">Active Only</option>
              <option value="false">Inactive Only</option>
            </select>
          </div>

          <!-- Per Page Select -->
          <div class="flex items-center space-x-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-500">Show:</span>
            <select
              v-model="perPage"
              @change="onPerPageChange"
              class="px-3 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
            >
              <option :value="10">10 per page</option>
              <option :value="25">25 per page</option>
              <option :value="50">50 per page</option>
              <option :value="100">100 per page</option>
            </select>
          </div>

          <!-- Refresh Button -->
          <button
            @click="fetchData"
            class="w-9 h-9 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer"
            title="Refresh Datatable"
          >
            <i class="fas fa-sync-alt text-xs" :class="{ 'fa-spin': loading }"></i>
          </button>
        </div>
      </div>

      <!-- Table View -->
      <div class="overflow-x-auto relative">
        <!-- Top Loading Progress Bar -->
        <div v-if="loading" class="absolute top-0 left-0 right-0 h-1 bg-black/10 dark:bg-white/10 overflow-hidden z-10">
          <div class="h-full bg-black dark:bg-white animate-pulse w-full"></div>
        </div>

        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-100/60 dark:bg-zinc-950/60 text-[11px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 select-none">
              
              <!-- Sort Order Header -->
              <th 
                @click="toggleSort('sort_order')"
                class="py-3.5 px-6 w-24 text-center cursor-pointer hover:text-zinc-950 dark:hover:text-white transition-colors"
              >
                <div class="flex items-center justify-center space-x-1.5">
                  <span>Order</span>
                  <i :class="getSortIcon('sort_order')"></i>
                </div>
              </th>

              <!-- Name Header -->
              <th 
                @click="toggleSort('name')"
                class="py-3.5 px-6 cursor-pointer hover:text-zinc-950 dark:hover:text-white transition-colors"
              >
                <div class="flex items-center space-x-1.5">
                  <span>Business Type</span>
                  <i :class="getSortIcon('name')"></i>
                </div>
              </th>

              <!-- Slug Header -->
              <th 
                @click="toggleSort('slug')"
                class="py-3.5 px-6 cursor-pointer hover:text-zinc-950 dark:hover:text-white transition-colors"
              >
                <div class="flex items-center space-x-1.5">
                  <span>Slug</span>
                  <i :class="getSortIcon('slug')"></i>
                </div>
              </th>

              <th class="py-3.5 px-6">Description</th>

              <!-- Status Header -->
              <th 
                @click="toggleSort('is_active')"
                class="py-3.5 px-6 text-center cursor-pointer hover:text-zinc-950 dark:hover:text-white transition-colors"
              >
                <div class="flex items-center justify-center space-x-1.5">
                  <span>Status</span>
                  <i :class="getSortIcon('is_active')"></i>
                </div>
              </th>

              <th class="py-3.5 px-6 text-right">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-xs font-semibold">
            <!-- Loading Row -->
            <tr v-if="loading && items.length === 0">
              <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-circle-notch fa-spin text-2xl mb-2 text-black dark:text-white"></i>
                <p class="text-xs font-bold uppercase tracking-wider">Loading business types...</p>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-else-if="!loading && items.length === 0">
              <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-folder-open text-3xl mb-3 text-zinc-300 dark:text-zinc-700"></i>
                <p class="text-xs font-bold uppercase tracking-wider">No business types found</p>
                <p class="text-[11px] text-zinc-400 mt-1">Try adjusting search query or add a new business type.</p>
              </td>
            </tr>

            <!-- Data Rows -->
            <tr 
              v-else 
              v-for="item in items" 
              :key="item.id"
              class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <!-- Sort Order -->
              <td class="py-4 px-6 text-center">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 font-black text-xs text-zinc-700 dark:text-zinc-300">
                  {{ item.sort_order }}
                </span>
              </td>

              <!-- Icon & Name -->
              <td class="py-4 px-6">
                <div class="flex items-center space-x-3">
                  <div class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white flex items-center justify-center text-sm shrink-0 border border-zinc-200 dark:border-zinc-700">
                    <i :class="item.icon || 'fas fa-store'"></i>
                  </div>
                  <span class="font-extrabold text-zinc-950 dark:text-white text-xs tracking-tight">
                    {{ item.name }}
                  </span>
                </div>
              </td>

              <!-- Slug -->
              <td class="py-4 px-6 text-zinc-500 font-mono text-[11px]">
                {{ item.slug }}
              </td>

              <!-- Description -->
              <td class="py-4 px-6 text-zinc-600 dark:text-zinc-400 max-w-xs truncate" :title="item.description">
                {{ item.description || '-' }}
              </td>

              <!-- Status Toggle Switch -->
              <td class="py-4 px-6 text-center">
                <button
                  type="button"
                  @click="toggleStatus(item)"
                  class="relative inline-flex items-center cursor-pointer select-none"
                  :title="item.is_active ? 'Click to Disable' : 'Click to Enable'"
                >
                  <div 
                    class="block w-10 h-6 rounded-full transition-colors" 
                    :class="item.is_active ? 'bg-black dark:bg-white' : 'bg-zinc-300 dark:bg-zinc-700'"
                  ></div>
                  <div 
                    class="dot absolute left-0.5 top-0.5 w-5 h-5 rounded-full transition-transform shadow-xs pointer-events-none" 
                    :class="item.is_active ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-900'"
                  ></div>
                </button>
              </td>

              <!-- Actions -->
              <td class="py-4 px-6 text-right space-x-2">
                <button
                  @click="openEditModal(item)"
                  class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Edit Business Type"
                >
                  <i class="fas fa-edit text-xs"></i>
                </button>

                <button
                  @click="openDeleteModal(item)"
                  class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:hover:bg-rose-900 dark:text-rose-400 transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Delete Business Type"
                >
                  <i class="fas fa-trash-alt text-xs"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Datatable Pagination Footer -->
      <div 
        v-if="pagination.total > 0"
        class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-zinc-50/50 dark:bg-zinc-900/50 text-xs text-zinc-600 dark:text-zinc-400 font-semibold"
      >
        <!-- Entry Count Info -->
        <div>
          Showing <span class="font-black text-zinc-900 dark:text-white">{{ pagination.from }}</span> to 
          <span class="font-black text-zinc-900 dark:text-white">{{ pagination.to }}</span> of 
          <span class="font-black text-zinc-900 dark:text-white">{{ pagination.total }}</span> entries
        </div>

        <!-- Page Numbers Navigation -->
        <div class="flex items-center space-x-1.5 select-none">
          <!-- Previous Button -->
          <button
            @click="goToPage(pagination.currentPage - 1)"
            :disabled="pagination.currentPage <= 1 || loading"
            class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all font-bold cursor-pointer"
          >
            <i class="fas fa-chevron-left text-[10px] mr-1"></i> Prev
          </button>

          <!-- Dynamic Page Buttons -->
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="typeof page === 'number' && goToPage(page)"
            :disabled="page === '...' || loading"
            class="w-8 h-8 rounded-xl flex items-center justify-center font-extrabold text-xs transition-all cursor-pointer"
            :class="[
              page === pagination.currentPage 
                ? 'bg-black text-white dark:bg-white dark:text-black shadow-xs' 
                : page === '...'
                  ? 'cursor-default text-zinc-400'
                  : 'border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800'
            ]"
          >
            {{ page }}
          </button>

          <!-- Next Button -->
          <button
            @click="goToPage(pagination.currentPage + 1)"
            :disabled="pagination.currentPage >= pagination.lastPage || loading"
            class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all font-bold cursor-pointer"
          >
            Next <i class="fas fa-chevron-right text-[10px] ml-1"></i>
          </button>
        </div>
      </div>

    </div>

    <!-- Modals -->
    <BusinessTypeModal
      :show="showModal"
      :type-id="selectedTypeId"
      @close="closeModal"
      @saved="fetchData"
    />

    <BusinessTypeDeleteModal
      :show="showDeleteModal"
      :type-item="selectedTypeItem"
      @close="closeDeleteModal"
      @deleted="fetchData"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import BusinessTypeModal from './BusinessTypeModal.vue';
import BusinessTypeDeleteModal from './BusinessTypeDeleteModal.vue';

const items = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all');
const perPage = ref(10);
const currentPage = ref(1);

const sortBy = ref('sort_order');
const sortDir = ref('asc');

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 10,
  total: 0,
  from: 0,
  to: 0
});

const showModal = ref(false);
const selectedTypeId = ref(null);

const showDeleteModal = ref(false);
const selectedTypeItem = ref(null);

let debounceTimer = null;

const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    currentPage.value = 1;
    fetchData();
  }, 350);
};

const onFilterChange = () => {
  currentPage.value = 1;
  fetchData();
};

const onPerPageChange = () => {
  currentPage.value = 1;
  fetchData();
};

const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDir.value = 'asc';
  }
  currentPage.value = 1;
  fetchData();
};

const getSortIcon = (column) => {
  if (sortBy.value !== column) {
    return 'fas fa-sort text-zinc-300 dark:text-zinc-700 text-[10px] ml-1';
  }
  return sortDir.value === 'asc' 
    ? 'fas fa-sort-up text-black dark:text-white text-xs ml-1' 
    : 'fas fa-sort-down text-black dark:text-white text-xs ml-1';
};

const goToPage = (page) => {
  if (page < 1 || page > pagination.value.lastPage || page === currentPage.value) return;
  currentPage.value = page;
  fetchData();
};

const visiblePages = computed(() => {
  const current = pagination.value.currentPage;
  const last = pagination.value.lastPage;
  const pages = [];

  if (last <= 7) {
    for (let i = 1; i <= last; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 3) pages.push('...');
    
    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }

    if (current < last - 2) pages.push('...');
    pages.push(last);
  }

  return pages;
});

const fetchData = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/admin/api/business-types-data', {
      params: {
        page: currentPage.value,
        per_page: perPage.value,
        search: searchQuery.value,
        status: statusFilter.value,
        sort_by: sortBy.value,
        sort_dir: sortDir.value
      }
    });

    items.value = data.data || [];
    pagination.value = {
      currentPage: data.current_page || 1,
      lastPage: data.last_page || 1,
      perPage: data.per_page || 10,
      total: data.total || 0,
      from: data.from || 0,
      to: data.to || 0
    };
  } catch (e) {
    console.error("Failed to fetch business types datatable", e);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  selectedTypeId.value = null;
  showModal.value = true;
};

const openEditModal = (item) => {
  selectedTypeId.value = item.id;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedTypeId.value = null;
};

const openDeleteModal = (item) => {
  selectedTypeItem.value = item;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedTypeItem.value = null;
};

const toggleStatus = async (item) => {
  const newStatus = !item.is_active;
  item.is_active = newStatus; // Optimistic update
  try {
    await axios.put(`/admin/api/business-types/${item.id}`, {
      name: item.name,
      description: item.description,
      icon: item.icon,
      sort_order: item.sort_order,
      is_active: newStatus
    });
  } catch (e) {
    item.is_active = !newStatus; // Revert on failure
    console.error("Failed to toggle status", e);
  }
};

onMounted(() => {
  fetchData();
});
</script>
