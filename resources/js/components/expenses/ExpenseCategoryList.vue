<template>
  <div class="expense-category-list">
    <!-- DataTable -->
    <DataTable
      title="Expense Categories"
      subtitle="Manage expense categories and their hierarchy"
      :columns="tableColumns"
      :data="filteredCategories"
      :loading="loading"
      storage-key="expense-categories-table-state"
      empty-message="No categories found"
      empty-sub-message="Get started by creating a new expense category."
      @search="handleSearch"
      @sort="handleSort"
    >
      <!-- Custom column content -->
      <template #column-status="{ item }">
        <span :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
          {{ item.is_active ? 'Active' : 'Inactive' }}
        </span>
      </template>

      <template #column-actions="{ item }">
        <div class="flex justify-end space-x-2">
          <button
            @click="$emit('edit-category', item)"
            class="text-indigo-600 hover:text-indigo-900"
            title="Edit"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </button>
          <button
            @click="deleteCategory(item)"
            class="text-red-600 hover:text-red-900"
            title="Delete"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

// Props and Emits
const emit = defineEmits(['edit-category', 'refresh']);

// Reactive data
const categories = ref([]);
const loading = ref(false);
const filteredCategories = ref([]);

// Table columns configuration
const tableColumns = ref([
  {
    key: 'name',
    label: 'Name',
    sortable: true,
    align: 'left'
  },
  {
    key: 'code',
    label: 'Code',
    sortable: true,
    align: 'left'
  },
  {
    key: 'description',
    label: 'Description',
    sortable: false,
    align: 'left'
  },
  {
    key: 'parent.name',
    label: 'Parent Category',
    sortable: true,
    align: 'left'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'right'
  }
]);

// Methods
const fetchCategories = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/expense-categories');
    categories.value = response.data;
    filteredCategories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  } finally {
    loading.value = false;
  }
};

const handleSearch = (searchQuery) => {
  if (!searchQuery) {
    filteredCategories.value = categories.value;
    return;
  }

  const query = searchQuery.toLowerCase();
  filteredCategories.value = categories.value.filter(category =>
    category.name.toLowerCase().includes(query) ||
    (category.code && category.code.toLowerCase().includes(query)) ||
    (category.description && category.description.toLowerCase().includes(query)) ||
    (category.parent?.name && category.parent.name.toLowerCase().includes(query))
  );
};

const handleSort = (sortData) => {
  const { field, order } = sortData;

  filteredCategories.value.sort((a, b) => {
    let aValue = field.includes('.') ? field.split('.').reduce((obj, key) => obj?.[key], a) : a[field];
    let bValue = field.includes('.') ? field.split('.').reduce((obj, key) => obj?.[key], b) : b[field];

    // Handle null/undefined values
    if (aValue == null) aValue = '';
    if (bValue == null) bValue = '';

    // Convert to strings for comparison
    aValue = String(aValue).toLowerCase();
    bValue = String(bValue).toLowerCase();

    if (order === 'asc') {
      return aValue.localeCompare(bValue);
    } else {
      return bValue.localeCompare(aValue);
    }
  });
};

const deleteCategory = async (category) => {
  if (!confirm('Are you sure you want to delete this category?')) {
    return;
  }

  try {
    await axios.delete(`/api/expense-categories/${category.id}`);
    await fetchCategories();
  } catch (error) {
    console.error('Error deleting category:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

// Lifecycle
onMounted(() => {
  fetchCategories();
});
</script>
