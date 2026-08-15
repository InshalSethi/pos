<template>
  <div class="expense-category-list">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden">
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
        <!-- Custom Status Column -->
        <template #column-status="{ item }">
          <span
            :class="[
              'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider',
              item.is_active
                ? 'bg-slate-900 text-white dark:bg-emerald-950/60 dark:text-emerald-300 border border-slate-900 dark:border-emerald-900/50'
                : 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400 border border-slate-200 dark:border-zinc-700'
            ]"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="item.is_active ? 'bg-white dark:bg-emerald-400' : 'bg-slate-400'"></span>
            {{ item.is_active ? 'Active' : 'Inactive' }}
          </span>
        </template>

        <!-- Custom Actions Column -->
        <template #column-actions="{ item }">
          <div class="flex items-center justify-end gap-1.5">
            <button
              @click="$emit('edit-category', item)"
              class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40 transition-all cursor-pointer"
              title="Edit Category"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button
              @click="deleteCategory(item)"
              class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition-all cursor-pointer"
              title="Delete Category"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>
    </div>
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
    align: 'left',
    class: 'font-mono text-xs font-bold text-slate-600 dark:text-zinc-400'
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

    if (aValue == null) aValue = '';
    if (bValue == null) bValue = '';

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
  if (!confirm(`Are you sure you want to delete category "${category.name}"?`)) {
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

onMounted(() => {
  fetchCategories();
});

defineExpose({
  fetchCategories
});
</script>
