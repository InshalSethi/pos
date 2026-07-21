<template>
  <div class="w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-7xl mx-auto">
      
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <h1 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Variants</h1>
          <!-- Star Option -->
          <button 
            type="button" 
            @click="isStarred = !isStarred" 
            class="text-slate-400 hover:text-amber-400 transition-colors focus:outline-none dark:text-slate-400"
          >
            <svg 
              class="w-7 h-7" 
              :class="{ 'fill-amber-400 text-amber-400': isStarred, 'text-slate-300': !isStarred }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.773-.564-.374-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z"
              />
            </svg>
          </button>
        </div>

        <div class="flex items-center gap-3">
          <!-- Bulk Action Button -->
          <button 
            v-if="selectedIds.length > 0"
            type="button" 
            @click="bulkDelete"
            class="px-4 py-2 text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:text-rose-400 rounded-xl border border-rose-200 dark:border-rose-900/40 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete Selected ({{ selectedIds.length }})
          </button>

          <!-- Solid Green Primary Action Button -->
          <button 
            type="button" 
            @click="openModal" 
            class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-full shadow-sm hover:shadow transition-all flex items-center gap-2 active:scale-95 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            New Variant
          </button>
        </div>
      </div>

      <!-- Search / Filter Results Input Block -->
      <div class="mb-6">
        <div class="relative w-full">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search or filter results.." 
            class="w-full pl-12 pr-4 py-3 bg-white dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-2xl shadow-sm focus:outline-none focus:ring-1 focus:ring-slate-200 dark:focus:ring-indigo-500 focus:border-slate-300 dark:focus:border-indigo-500 text-sm placeholder-slate-400 dark:placeholder-slate-500 transition-all font-medium dark:text-slate-100"
          />
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Alerts / Messages -->
      <div v-if="feedbackMsg" :class="feedbackClass" class="mb-6 p-4 rounded-xl border flex items-center justify-between text-sm font-medium transition-all">
        <span class="flex items-center gap-2">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ feedbackMsg }}
        </span>
        <button @click="feedbackMsg = ''" class="text-current opacity-70 hover:opacity-100">&times;</button>
      </div>

      <!-- Data Table Card -->
      <div class="bg-white dark:bg-[#1E1E1E] rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-sm overflow-hidden">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-20">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-600 mx-auto"></div>
          <p class="mt-3 text-slate-500 text-sm font-medium dark:text-slate-400">Loading variants...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredAttributes.length === 0" class="text-center py-20 px-4">
          <div class="w-16 h-16 bg-slate-50 dark:bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-[#2E2E2E]">
            <svg class="w-8 h-8 text-slate-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">No variants found</h3>
          <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-xs mx-auto">Create global attributes (e.g. Size, Color, Capacity) to manage product variation options globally.</p>
          <button 
            type="button" 
            @click="openModal"
            class="mt-4 px-4 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-all cursor-pointer"
          >
            Add First Variant
          </button>
        </div>

        <!-- Data Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full table-auto border-collapse">
            <thead>
              <tr class="bg-slate-50 dark:bg-[#252525] border-b border-slate-200 dark:border-[#2E2E2E] text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <!-- Checkbox Header -->
                <th class="w-12 px-6 py-4 text-left">
                  <input 
                    type="checkbox" 
                    @change="toggleSelectAll" 
                    :checked="isAllSelected"
                    class="w-4.5 h-4.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                  />
                </th>
                <th class="px-6 py-4 text-left font-bold">Name</th>
                <th class="px-6 py-4 text-right font-bold w-48">Values</th>
                <th class="px-6 py-4 text-center font-bold w-24">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]">
              <tr 
                v-for="attr in filteredAttributes" 
                :key="attr.id"
                class="hover:bg-slate-50/60 dark:hover:bg-[#2D2D2D]/80 transition-colors group"
              >
                <!-- Checkbox -->
                <td class="px-6 py-4 align-middle">
                  <input 
                    type="checkbox" 
                    v-model="selectedIds"
                    :value="attr.id"
                    class="w-4.5 h-4.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                  />
                </td>
                <!-- Name Column (Left-aligned, bold) -->
                <td class="px-6 py-4 align-middle font-bold text-slate-900 dark:text-slate-100">
                  <div class="flex flex-col">
                    <span>{{ attr.name }}</span>
                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 tracking-tight mt-0.5">
                      {{ (attr.values || []).map(v => v.value).join(', ') }}
                    </span>
                  </div>
                </td>
                <!-- Values Column (Right-aligned count) -->
                <td class="px-6 py-4 align-middle text-right font-bold text-slate-600 text-sm dark:text-slate-400">
                  <span class="bg-slate-100 dark:bg-[#252525] text-slate-700 dark:text-slate-300 px-2.5 py-1 rounded-full text-xs font-bold border border-slate-200/40 dark:border-[#2E2E2E]/40">
                    {{ (attr.values || []).length }}
                  </span>
                </td>
                <!-- Actions -->
                <td class="px-6 py-4 align-middle text-center">
                  <div class="flex items-center justify-center gap-1">
                    <button 
                      type="button" 
                      @click="editAttribute(attr)"
                      class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/20 transition-all focus:outline-none cursor-pointer"
                      title="Edit Variant"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button 
                      type="button" 
                      @click="deleteAttribute(attr.id)"
                      class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all focus:outline-none cursor-pointer dark:text-slate-400"
                      title="Delete Variant"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- New Variant / Edit Modal -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

          <!-- Modal Box -->
          <div class="relative bg-white dark:bg-[#1E1E1E] w-full max-w-md p-6 rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-2xl space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-[#2E2E2E]">
              <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">{{ isEditing ? 'Edit Global Variant' : 'Configure Global Variant' }}</h3>
              <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg focus:outline-none dark:text-slate-400">&times;</button>
            </div>

            <div v-if="modalError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-202 font-medium">
              {{ modalError }}
            </div>

            <form @submit.prevent="submitVariant" class="space-y-4">
              <div>
                <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1 uppercase tracking-wider">Option Name</label>
                <input 
                  v-model="modalForm.name"
                  type="text" 
                  placeholder="e.g., Color, Size, GB, Material" 
                  class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-sm focus:outline-none focus:border-indigo-500 font-medium focus:bg-white focus:ring-1 focus:ring-indigo-500 dark:text-slate-100"
                  required
                />
              </div>

              <div>
                <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1 uppercase tracking-wider">Option Values (Comma separated)</label>
                <input 
                  v-model="modalForm.valuesRaw"
                  type="text" 
                  placeholder="e.g., Red, Blue, Green" 
                  class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-sm focus:outline-none focus:border-indigo-500 font-medium focus:bg-white focus:ring-1 focus:ring-indigo-500 dark:text-slate-100"
                  required
                />
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Separate individual value tags using commas.</p>
              </div>

              <div class="flex justify-end gap-2 pt-2 text-xs">
                <button 
                  type="button" 
                  @click="closeModal" 
                  class="px-4 py-2 text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-slate-200"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="submitting" 
                  class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow hover:bg-emerald-700 transition-colors flex items-center gap-1.5 disabled:opacity-50 cursor-pointer"
                >
                  <svg v-if="submitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isEditing ? 'Update Variant' : 'Add Variant' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Starred state
const isStarred = ref(false);

// Query state
const searchQuery = ref('');

// Table selection
const selectedIds = ref([]);

// Attributes list
const attributes = ref([]);
const loading = ref(false);
const submitting = ref(false);

// Feedback message
const feedbackMsg = ref('');
const feedbackClass = ref('');

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const modalError = ref('');
const modalForm = ref({
  name: '',
  valuesRaw: ''
});

// Computed filtering
const filteredAttributes = computed(() => {
  if (!attributes.value) return [];
  if (!searchQuery.value.trim()) return attributes.value;
  const q = searchQuery.value.toLowerCase().trim();
  return attributes.value.filter(attr => {
    const matchesName = attr.name ? attr.name.toLowerCase().includes(q) : false;
    const matchesValues = (attr.values || []).some(v => v.value && v.value.toLowerCase().includes(q));
    return matchesName || matchesValues;
  });
});

// Select All Computed and Toggle
const isAllSelected = computed(() => {
  return filteredAttributes.value.length > 0 && selectedIds.value.length === filteredAttributes.value.length;
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = filteredAttributes.value.map(attr => attr.id);
  }
};

// Open/Close Modal
const openModal = () => {
  isEditing.value = false;
  editingId.value = null;
  modalForm.value = {
    name: '',
    valuesRaw: ''
  };
  modalError.value = '';
  showModal.value = true;
};

const editAttribute = (attr) => {
  isEditing.value = true;
  editingId.value = attr.id;
  modalForm.value = {
    name: attr.name,
    valuesRaw: (attr.values || []).map(v => v.value).join(', ')
  };
  modalError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  isEditing.value = false;
  editingId.value = null;
};

// Fetch variants
const fetchVariants = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/attributes');
    attributes.value = res.data || [];
  } catch (err) {
    console.error('Failed to fetch variants:', err);
    showFeedback('Failed to load variants.', 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    loading.value = false;
  }
};

// Submit Variant (Create or Update)
const submitVariant = async () => {
  if (!modalForm.value.name.trim() || !modalForm.value.valuesRaw.trim()) {
    modalError.value = 'Please complete all required fields.';
    return;
  }

  const values = modalForm.value.valuesRaw
    .split(',')
    .map(v => v.trim())
    .filter(v => v !== '');

  if (values.length === 0) {
    modalError.value = 'Please provide at least one option value.';
    return;
  }

  submitting.value = true;
  modalError.value = '';

  try {
    if (isEditing.value && editingId.value) {
      await axios.put(`/api/attributes/${editingId.value}`, {
        name: modalForm.value.name.trim(),
        values: values
      });
      showFeedback('Variant updated successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    } else {
      await axios.post('/api/attributes', {
        name: modalForm.value.name.trim(),
        values: values
      });
      showFeedback('Variant added successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    }

    closeModal();
    fetchVariants();
  } catch (err) {
    console.error('Failed to save variant:', err);
    if (err.response?.status === 422) {
      modalError.value = err.response.data.message || 'Validation error.';
    } else {
      modalError.value = isEditing.value 
        ? 'An error occurred while updating the variant.' 
        : 'An error occurred while creating the variant.';
    }
  } finally {
    submitting.value = false;
  }
};

// Delete Variant
const deleteAttribute = async (id) => {
  if (!confirm('Are you sure you want to delete this variant? This will delete all its values.')) return;

  try {
    await axios.delete(`/api/attributes/${id}`);
    showFeedback('Variant deleted successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    selectedIds.value = selectedIds.value.filter(x => x !== id);
    fetchVariants();
  } catch (err) {
    console.error('Failed to delete variant:', err);
    showFeedback('Failed to delete variant.', 'bg-rose-50 text-rose-800 border-rose-200');
  }
};

// Bulk Delete
const bulkDelete = async () => {
  if (selectedIds.value.length === 0) return;
  if (!confirm(`Are you sure you want to delete the ${selectedIds.value.length} selected variants?`)) return;

  try {
    await Promise.all(selectedIds.value.map(id => axios.delete(`/api/attributes/${id}`)));
    showFeedback('Selected variants deleted successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    selectedIds.value = [];
    fetchVariants();
  } catch (err) {
    console.error('Failed bulk delete:', err);
    showFeedback('Failed to delete some variants.', 'bg-rose-50 text-rose-800 border-rose-200');
  }
};

// Helper for UI feedback
const showFeedback = (msg, cls) => {
  feedbackMsg.value = msg;
  feedbackClass.value = cls;
  setTimeout(() => {
    if (feedbackMsg.value === msg) {
      feedbackMsg.value = '';
    }
  }, 4000);
};

// On mount
onMounted(() => {
  fetchVariants();
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
