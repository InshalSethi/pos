<template>
  <div class="space-y-6">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
      <div>
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i class="fas fa-cubes-stacked"></i>
          </div>
          <div>
            <h1 class="text-xl font-black text-zinc-950 dark:text-white tracking-tight">Form Builder Management</h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold mt-0.5">
              Create dynamic custom forms tailored to specific business types and operational areas.
            </p>
          </div>
        </div>
      </div>

      <button
        @click="openCreateModal"
        class="px-5 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer shrink-0"
      >
        <i class="fas fa-plus mr-2"></i> Create Dynamic Form
      </button>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
      
      <!-- Filter Bar -->
      <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col lg:flex-row gap-3 justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
        
        <!-- Left: Search Box -->
        <div class="w-full lg:w-80 relative">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
          <input
            type="text"
            v-model="searchQuery"
            @input="debouncedFetch"
            placeholder="Search custom forms..."
            class="w-full pl-9 pr-4 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
          />
        </div>

        <!-- Right: Business Type & Area of Use Filters & Per Page -->
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-end">
          
          <!-- Business Type Filter -->
          <div class="flex items-center space-x-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-500">Business:</span>
            <select
              v-model="businessTypeFilter"
              @change="onFilterChange"
              class="px-3 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer max-w-[160px] truncate"
            >
              <option value="all">All Businesses</option>
              <option v-for="type in metaOptions.business_types" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </div>

          <!-- Area of Use Filter -->
          <div class="flex items-center space-x-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-500">Area:</span>
            <select
              v-model="areaOfUseFilter"
              @change="onFilterChange"
              class="px-3 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
            >
              <option value="all">All Areas</option>
              <option v-for="area in metaOptions.areas_of_use" :key="area.value" :value="area.value">
                {{ area.label }}
              </option>
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
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>

          <!-- Refresh Button -->
          <button
            @click="fetchData"
            class="w-9 h-9 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer"
            title="Refresh List"
          >
            <i class="fas fa-sync-alt text-xs" :class="{ 'fa-spin': loading }"></i>
          </button>
        </div>
      </div>

      <!-- Table View -->
      <div class="overflow-x-auto relative">
        <!-- Progress bar indicator -->
        <div v-if="loading" class="absolute top-0 left-0 right-0 h-1 bg-black/10 dark:bg-white/10 overflow-hidden z-10">
          <div class="h-full bg-black dark:bg-white animate-pulse w-full"></div>
        </div>

        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-100/60 dark:bg-zinc-950/60 text-[11px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 select-none">
              <th class="py-3.5 px-6">Form Title</th>
              <th class="py-3.5 px-6">Business Type</th>
              <th class="py-3.5 px-6">Area of Use</th>
              <th class="py-3.5 px-6 text-center">Total Fields</th>
              <th class="py-3.5 px-6 text-center">Status</th>
              <th class="py-3.5 px-6 text-right">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-xs font-semibold">
            <!-- Loading Row -->
            <tr v-if="loading && items.length === 0">
              <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-circle-notch fa-spin text-2xl mb-2 text-black dark:text-white"></i>
                <p class="text-xs font-bold uppercase tracking-wider">Loading dynamic forms...</p>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-else-if="!loading && items.length === 0">
              <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-cubes-stacked text-3xl mb-3 text-zinc-300 dark:text-zinc-700"></i>
                <p class="text-xs font-bold uppercase tracking-wider">No dynamic custom forms found</p>
                <p class="text-[11px] text-zinc-400 mt-1">Click "Create Dynamic Form" to build custom fields.</p>
              </td>
            </tr>

            <!-- Data Rows -->
            <tr 
              v-else 
              v-for="item in items" 
              :key="item.id"
              class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <!-- Form Title & Description -->
              <td class="py-4 px-6">
                <div>
                  <span class="font-extrabold text-zinc-950 dark:text-white text-xs tracking-tight block">
                    {{ item.name }}
                  </span>
                  <span class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate max-w-xs block font-normal">
                    {{ item.description || 'No description provided' }}
                  </span>
                </div>
              </td>

              <!-- Business Type -->
              <td class="py-4 px-6">
                <span 
                  class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-extrabold border"
                  :class="item.business_type 
                    ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border-zinc-200 dark:border-zinc-700' 
                    : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-900/50'"
                >
                  <i :class="item.business_type?.icon || 'fas fa-globe'" class="mr-1.5 text-[10px]"></i>
                  {{ item.business_type?.name || 'Global Default' }}
                </span>
              </td>

              <!-- Area of Use -->
              <td class="py-4 px-6">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black bg-black text-white dark:bg-white dark:text-black">
                  <i class="fas fa-layer-group mr-1.5 text-[9px]"></i>
                  {{ formatAreaLabel(item.area_of_use) }}
                </span>
              </td>

              <!-- Total Fields Count -->
              <td class="py-4 px-6 text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                  {{ Array.isArray(item.fields) ? item.fields.length : 0 }} fields
                </span>
              </td>

              <!-- Status Toggle -->
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
                <!-- Preview Button -->
                <button
                  @click="openPreviewModal(item)"
                  class="w-8 h-8 rounded-xl bg-zinc-900 text-white dark:bg-white dark:text-black hover:bg-black dark:hover:bg-zinc-200 transition-all cursor-pointer inline-flex items-center justify-center shadow-2xs"
                  title="Rendered Form Preview"
                >
                  <i class="fas fa-eye text-xs"></i>
                </button>

                <!-- Edit Button -->
                <button
                  @click="openEditModal(item)"
                  class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Edit Form Builder"
                >
                  <i class="fas fa-edit text-xs"></i>
                </button>

                <!-- Delete Button -->
                <button
                  @click="openDeleteModal(item)"
                  class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:hover:bg-rose-900 dark:text-rose-400 transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Delete Form Builder"
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
        <div>
          Showing <span class="font-black text-zinc-900 dark:text-white">{{ pagination.from }}</span> to 
          <span class="font-black text-zinc-900 dark:text-white">{{ pagination.to }}</span> of 
          <span class="font-black text-zinc-900 dark:text-white">{{ pagination.total }}</span> forms
        </div>

        <div class="flex items-center space-x-1.5 select-none">
          <button
            @click="goToPage(pagination.currentPage - 1)"
            :disabled="pagination.currentPage <= 1 || loading"
            class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all font-bold cursor-pointer"
          >
            <i class="fas fa-chevron-left text-[10px] mr-1"></i> Prev
          </button>

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

    <!-- Rendered Form Preview Modal -->
    <FormPreviewModal
      :show="showPreviewModal"
      :form-item="selectedPreviewFormItem"
      @close="closePreviewModal"
    />

    <!-- Form Builder Edit / Create Modal -->
    <FormBuilderModal
      :show="showModal"
      :form-id="selectedFormId"
      @close="closeModal"
      @saved="fetchData"
    />

    <!-- Form Delete Modal -->
    <FormDeleteModal
      :show="showDeleteModal"
      :form-item="selectedFormItem"
      @close="closeDeleteModal"
      @deleted="fetchData"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import FormBuilderModal from './FormBuilderModal.vue';
import FormDeleteModal from './FormDeleteModal.vue';
import FormPreviewModal from './FormPreviewModal.vue';

const items = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const businessTypeFilter = ref('all');
const areaOfUseFilter = ref('all');
const perPage = ref(10);
const currentPage = ref(1);

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 10,
  total: 0,
  from: 0,
  to: 0
});

const metaOptions = ref({
  areas_of_use: [
    { value: 'sale_invoice', label: 'Sale invoice' },
    { value: 'sale_return', label: 'Sale Return' },
    { value: 'purchase_invoice', label: 'Purchase Invoice' },
    { value: 'purchase_return', label: 'Purchase Return' },
    { value: 'items', label: 'Items' },
    { value: 'expenses', label: 'Expenses' },
    { value: 'payment_out', label: 'Payment Out' },
    { value: 'payment_receipt', label: 'Payment Receipt' }
  ],
  business_types: []
});

const showModal = ref(false);
const selectedFormId = ref(null);

const showDeleteModal = ref(false);
const selectedFormItem = ref(null);

const showPreviewModal = ref(false);
const selectedPreviewFormItem = ref(null);

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

const goToPage = (page) => {
  if (page < 1 || page > pagination.value.lastPage || page === currentPage.value) return;
  currentPage.value = page;
  fetchData();
};

const formatAreaLabel = (areaVal) => {
  const found = metaOptions.value.areas_of_use.find(a => a.value === areaVal);
  return found ? found.label : areaVal;
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

const fetchMetaOptions = async () => {
  try {
    const { data } = await axios.get('/admin/api/custom-forms/meta-options');
    if (data.areas_of_use) metaOptions.value.areas_of_use = data.areas_of_use;
    if (data.business_types) metaOptions.value.business_types = data.business_types;
  } catch (e) {
    console.error("Failed to fetch meta options", e);
  }
};

const fetchData = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/admin/api/custom-forms-data', {
      params: {
        page: currentPage.value,
        per_page: perPage.value,
        search: searchQuery.value,
        business_type_id: businessTypeFilter.value,
        area_of_use: areaOfUseFilter.value
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
    console.error("Failed to fetch custom forms datatable", e);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  selectedFormId.value = null;
  showModal.value = true;
};

const openEditModal = (item) => {
  selectedFormId.value = item.id;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedFormId.value = null;
};

const openPreviewModal = (item) => {
  selectedPreviewFormItem.value = item;
  showPreviewModal.value = true;
};

const closePreviewModal = () => {
  showPreviewModal.value = false;
  selectedPreviewFormItem.value = null;
};

const openDeleteModal = (item) => {
  selectedFormItem.value = item;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedFormItem.value = null;
};

const toggleStatus = async (item) => {
  const newStatus = !item.is_active;
  item.is_active = newStatus; // Optimistic update
  try {
    await axios.put(`/admin/api/custom-forms/${item.id}`, {
      name: item.name,
      business_type_id: item.business_type_id,
      area_of_use: item.area_of_use,
      description: item.description,
      fields: item.fields,
      is_active: newStatus
    });
  } catch (e) {
    item.is_active = !newStatus; // Revert on failure
    console.error("Failed to toggle status", e);
  }
};

onMounted(() => {
  fetchMetaOptions();
  fetchData();
});
</script>
