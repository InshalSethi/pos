<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header with Title and Actions -->
    <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
      <div>
        <h2 v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</h2>
        <p v-if="subtitle" class="text-sm text-gray-600">{{ subtitle }}</p>
      </div>
      
      <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
        <!-- Search -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search"
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 w-full sm:w-64"
            @input="handleSearch"
          />
        </div>
        
        <!-- Action Buttons -->
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <!-- Table Header -->
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider',
                column.align === 'center' ? 'text-center' : column.align === 'right' ? 'text-right' : 'text-left',
                column.sortable ? 'cursor-pointer hover:bg-gray-100' : ''
              ]"
              @click="column.sortable ? handleSort(column.key) : null"
            >
              <div class="flex items-center" :class="column.align === 'center' ? 'justify-center' : column.align === 'right' ? 'justify-end' : 'justify-start'">
                <span>{{ column.label }}</span>
                <template v-if="column.sortable">
                  <svg
                    v-if="sortField === column.key && sortOrder === 'asc'"
                    class="ml-1 h-4 w-4"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z" />
                  </svg>
                  <svg
                    v-else-if="sortField === column.key && sortOrder === 'desc'"
                    class="ml-1 h-4 w-4"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z" />
                  </svg>
                  <svg
                    v-else
                    class="ml-1 h-4 w-4 text-gray-300"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z" />
                  </svg>
                </template>
              </div>
            </th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Loading State -->
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500">
              <div class="flex justify-center items-center">
                <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...
              </div>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-else-if="!data || data.length === 0">
            <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium text-gray-900 mb-1">{{ emptyMessage || 'No data found' }}</p>
                <p class="text-gray-500">{{ emptySubMessage || 'Try adjusting your search or filter criteria.' }}</p>
              </div>
            </td>
          </tr>

          <!-- Data Rows -->
          <tr v-else v-for="(item, index) in data" :key="getRowKey(item, index)" class="hover:bg-gray-50">
            <td
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 whitespace-nowrap text-sm',
                column.align === 'center' ? 'text-center' : column.align === 'right' ? 'text-right' : 'text-left',
                column.class || 'text-gray-900'
              ]"
            >
              <!-- Custom slot for column content -->
              <slot
                :name="`column-${column.key}`"
                :item="item"
                :value="getNestedValue(item, column.key)"
                :index="index"
              >
                <!-- Default content -->
                <template v-if="column.type === 'currency'">
                  ${{ formatCurrency(getNestedValue(item, column.key)) }}
                </template>
                <template v-else-if="column.type === 'date'">
                  {{ formatDate(getNestedValue(item, column.key)) }}
                </template>
                <template v-else-if="column.type === 'badge'">
                  <span :class="getBadgeClass(getNestedValue(item, column.key), column.badgeColors)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ column.badgeLabels ? column.badgeLabels[getNestedValue(item, column.key)] : getNestedValue(item, column.key) }}
                  </span>
                </template>
                <template v-else>
                  {{ getNestedValue(item, column.key) }}
                </template>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer with Pagination -->
    <div v-if="pagination && !loading" class="px-6 py-3 border-t border-gray-200 bg-gray-50">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
        <!-- Results Info -->
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} results
        </div>

        <!-- Items per page -->
        <div class="flex items-center space-x-2">
          <label class="text-sm text-gray-700">Items per page:</label>
          <select
            v-model="perPage"
            @change="handlePerPageChange"
            class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center space-x-1">
          <button
            @click="goToPage(1)"
            :disabled="pagination.current_page === 1"
            class="px-2 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            First
          </button>
          <button
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-2 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          
          <template v-for="page in visiblePages" :key="page">
            <button
              @click="goToPage(page)"
              :class="[
                'px-3 py-1 text-sm border rounded-md',
                page === pagination.current_page
                  ? 'bg-green-600 text-white border-green-600'
                  : 'border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
          </template>
          
          <button
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-2 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
          <button
            @click="goToPage(pagination.last_page)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-2 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Last
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { debounce } from '@/utils/debounce';

// Props
const props = defineProps({
  title: String,
  subtitle: String,
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Object,
    default: null
  },
  emptyMessage: String,
  emptySubMessage: String,
  storageKey: {
    type: String,
    default: 'datatable-state'
  },
  perPageOptions: {
    type: Array,
    default: () => [10, 25, 50, 100]
  },
  defaultPerPage: {
    type: Number,
    default: 25
  },
  defaultSort: {
    type: Object,
    default: () => ({ field: null, order: 'asc' })
  },
  initialSearch: {
    type: String,
    default: ''
  },
  initialPerPage: {
    type: Number,
    default: null
  }
});

// Emits
const emit = defineEmits(['search', 'sort', 'page-change', 'per-page-change']);

// Reactive data
const searchQuery = ref(props.initialSearch || '');
const sortField = ref(props.defaultSort.field);
const sortOrder = ref(props.defaultSort.order);
const perPage = ref(props.initialPerPage || props.defaultPerPage);

// Computed
const visiblePages = computed(() => {
  if (!props.pagination) return [];
  
  const current = props.pagination.current_page;
  const last = props.pagination.last_page;
  const pages = [];
  
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Methods
const handleSearch = debounce(() => {
  saveState();
  emit('search', searchQuery.value);
}, 300);

const handleSort = (field) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortOrder.value = 'asc';
  }
  
  saveState();
  emit('sort', { field: sortField.value, order: sortOrder.value });
};

const goToPage = (page) => {
  if (page >= 1 && page <= props.pagination.last_page) {
    saveState();
    emit('page-change', page);
  }
};

const handlePerPageChange = () => {
  saveState();
  emit('per-page-change', perPage.value);
};

const getRowKey = (item, index) => {
  return item.id || item.uuid || index;
};

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj);
};

const formatCurrency = (value) => {
  if (value == null) return '0.00';
  return parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString();
};

const getBadgeClass = (value, colors = {}) => {
  const defaultColors = {
    success: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    danger: 'bg-red-100 text-red-800',
    info: 'bg-blue-100 text-blue-800',
    secondary: 'bg-gray-100 text-gray-800'
  };
  
  return colors[value] || defaultColors[value] || defaultColors.secondary;
};

// State management
const saveState = () => {
  const state = {
    searchQuery: searchQuery.value,
    sortField: sortField.value,
    sortOrder: sortOrder.value,
    perPage: perPage.value,
    timestamp: Date.now()
  };
  
  localStorage.setItem(props.storageKey, JSON.stringify(state));
};

const loadState = () => {
  try {
    const saved = localStorage.getItem(props.storageKey);
    if (saved) {
      const state = JSON.parse(saved);

      // Only load state if it's recent (within 24 hours)
      if (Date.now() - state.timestamp < 24 * 60 * 60 * 1000) {
        // Use initial values from props if provided, otherwise use saved state
        if (!props.initialSearch) {
          searchQuery.value = state.searchQuery || '';
        }
        if (!props.initialPerPage) {
          perPage.value = state.perPage || props.defaultPerPage;
        }
        sortField.value = state.sortField || props.defaultSort.field;
        sortOrder.value = state.sortOrder || props.defaultSort.order;
      }
    }
  } catch (error) {
    console.warn('Failed to load DataTable state:', error);
  }
};

// Lifecycle
onMounted(() => {
  loadState();
});

// Auto-save state on page unload
onUnmounted(() => {
  saveState();
});

// Watch for external changes
watch(() => props.defaultSort, (newSort) => {
  if (!sortField.value) {
    sortField.value = newSort.field;
    sortOrder.value = newSort.order;
  }
}, { immediate: true });
</script>
