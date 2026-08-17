<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200"
    style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
    @click.self="$emit('close')"
  >
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-5xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto flex flex-col">
      
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#2E2E2E]/60 pb-4 mb-5 px-1">
        <div class="flex flex-col space-y-0.5">
          <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-zinc-500">
            Incomplete Items Workbench
          </span>
          <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100 tracking-wide">
            Product Drafts Workbench
          </h3>
        </div>

        <div class="flex items-center gap-5">
          <button
            v-show="selectedDraftIds.length > 0" 
            @click="handleBulkDelete" 
            type="button" 
            class="inline-flex items-center gap-1.5 text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 bg-transparent border-0 p-1 rounded-lg transition-colors focus:outline-none group cursor-pointer"
            title="Permanently remove selected slots"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:scale-105">
              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
            <span class="text-xs font-black tracking-wide">{{ selectedDraftIds.length }}</span>
          </button>

          <span v-show="selectedDraftIds.length > 0" class="w-[1px] h-4 bg-slate-200 dark:bg-[#252525]"></span>

          <button
            type="button" 
            @click="$emit('close')" 
            class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 font-medium text-xl transition-all focus:outline-none hover:rotate-90 duration-200 p-1 rounded-lg leading-none select-none cursor-pointer"
          >
            &times;
          </button>
        </div>
      </div>

      <div class="w-full overflow-y-auto border border-slate-200/70 dark:border-[#2E2E2E]/80 rounded-2xl overflow-x-auto shadow-inner bg-slate-50/20 dark:bg-zinc-950/20 custom-scrollbar">
        <table class="w-full min-w-max table-auto align-middle divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 text-xs">
          <thead class="bg-slate-50/80 dark:bg-[#252525]/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 sticky top-0 z-10 backdrop-blur-xs">
            <tr>
              <th class="px-4 py-3.5 text-center w-12">
                <input
                  type="checkbox" 
                  @click="toggleSelectAllDrafts" 
                  :checked="drafts.length > 0 && selectedDraftIds.length === drafts.length"
                  class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5 transition-all"
                >
              </th>
              <th class="px-4 py-3.5 text-left">Product Title</th>
              <th class="px-4 py-3.5 text-left">Category</th>
              <th class="px-4 py-3.5 text-center">Tags</th>
              <th class="px-4 py-3.5 text-center">Product Type</th>
              <th class="px-4 py-3.5 text-center">Prices (W / R)</th>
              <th class="px-4 py-3.5 text-center">Action</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]/40 text-[11px] bg-white dark:bg-[#1E1E1E]">
            <tr v-if="loading">
              <td colspan="7" class="text-center py-6 text-xs text-slate-400 dark:text-zinc-500 italic">Fetching compiled draft lots...</td>
            </tr>
            
            <tr v-else-if="drafts.length === 0">
              <td colspan="7" class="text-center py-6 text-xs text-slate-400 dark:text-zinc-500 italic">No drafted items found in your workbench.</td>
            </tr>

            <tr
              v-else
              v-for="draft in drafts"
              :key="draft.id"
              :class="selectedDraftIds.includes(draft.id) ? 'bg-indigo-50/30 dark:bg-indigo-500/5' : 'hover:bg-slate-50/50 dark:hover:bg-[#2D2D2D]/30'" 
              class="transition-colors duration-150"
            >
              <td class="px-4 py-3.5 align-middle text-center">
                <input
                  type="checkbox" 
                  :value="draft.id" 
                  v-model="selectedDraftIds"
                  class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5 transition-all"
                >
              </td>

              <td class="px-4 py-3.5 align-middle text-xs">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-zinc-950 flex-shrink-0 flex items-center justify-center border border-slate-200/60 dark:border-[#2E2E2E] overflow-hidden shadow-xs">
                    <img v-if="draft.image || draft.image_path || draft.thumbnail || draft.logo" :src="draft.image || draft.image_path || draft.thumbnail || draft.logo" class="w-full h-full object-cover">
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400 font-bold text-[11px]">
                      <span>{{ draft.name ? draft.name.charAt(0).toUpperCase() : 'P' }}</span>
                    </div>
                  </div>
                  <div class="flex flex-col text-left">
                    <span class="font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">{{ draft.name }}</span>
                    <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ draft.sku || draft.id }}</span>
                  </div>
                </div>
              </td>

              <td class="px-4 py-3.5 align-middle text-slate-500 text-left font-medium">{{ draft.category ? draft.category.name : 'No Category' }}</td>

              <td class="px-4 py-3.5 align-middle text-center">
                <div class="flex justify-center items-center gap-1 flex-wrap max-w-[130px] mx-auto">
                  <template v-if="draft.tags && draft.tags.length > 0">
                    <span v-for="(tag, i) in draft.tags" :key="i" class="text-[9px] font-bold bg-slate-100 text-slate-500 dark:bg-[#252525] dark:text-zinc-400 px-1.5 py-0.5 rounded-md">
                      #{{ typeof tag === 'object' ? tag.name : tag }}
                    </span>
                  </template>
                  <span v-else class="text-slate-300 dark:text-zinc-700 font-black">-</span>
                </div>
              </td>

              <td class="px-4 py-3.5 align-middle text-center">
                <span
                  :class="draft.variations_count > 0 ? 'bg-red-50 text-red-700 dark:bg-red-500 dark-text-black' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400'" 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-md font-bold text-[10px]"
                >
                  {{ draft.variations_count > 0 ? 'Variant Product' : 'Simple Product' }}
                </span>
              </td>

              <td class="px-4 py-3.5 align-middle text-center font-semibold text-slate-700 dark:text-zinc-300">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">${{ draft.selling_price || draft.retail_price || 0 }}</span>
                <span v-if="draft.wholesale_price" class="text-slate-400 text-[10px] ml-1">/ ${{ draft.wholesale_price }}</span>
              </td>

              <td class="px-4 py-3.5 align-middle text-center">
                <button
                  @click="$emit('edit', draft); $emit('close');"
                  type="button"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 transition-colors cursor-pointer"
                >
                  Edit & Complete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  drafts: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'edit', 'delete-selected']);

const selectedDraftIds = ref([]);

const toggleSelectAllDrafts = () => {
  if (selectedDraftIds.value.length === props.drafts.length) {
    selectedDraftIds.value = [];
  } else {
    selectedDraftIds.value = props.drafts.map(d => d.id);
  }
};

const handleBulkDelete = () => {
  emit('delete-selected', selectedDraftIds.value);
  selectedDraftIds.value = [];
};
</script>
