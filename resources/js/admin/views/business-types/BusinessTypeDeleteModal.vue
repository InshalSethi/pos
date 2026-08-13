<template>
  <div v-if="show" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200" @click.self="close">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transition-all text-left">
      
      <!-- Modal Body -->
      <div class="p-6 text-center">
        <div class="w-14 h-14 rounded-3xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mx-auto mb-4 border border-rose-200 dark:border-rose-900/50">
          <i class="fas fa-trash-alt"></i>
        </div>

        <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight mb-2">
          Delete Business Type?
        </h3>

        <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold mb-6">
          Are you sure you want to delete <span class="font-extrabold text-zinc-900 dark:text-white">"{{ typeName }}"</span>? This category will no longer be available for business registration.
        </p>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 p-3 rounded-xl text-xs font-bold mb-4">
          {{ errorMessage }}
        </div>

        <div class="flex space-x-3">
          <button
            type="button"
            @click="close"
            class="flex-1 px-4 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold text-xs rounded-xl transition-all cursor-pointer"
          >
            Cancel
          </button>

          <button
            type="button"
            @click="confirmDelete"
            :disabled="deleting"
            class="flex-1 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all disabled:opacity-50 flex items-center justify-center cursor-pointer"
          >
            <i v-if="deleting" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-trash-alt mr-2"></i>
            Confirm Delete
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  typeItem: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'deleted']);

const deleting = ref(false);
const errorMessage = ref('');

const typeName = computed(() => props.typeItem?.name || 'this Business Type');

const close = () => {
  errorMessage.value = '';
  emit('close');
};

const confirmDelete = async () => {
  if (!props.typeItem?.id || deleting.value) return;

  deleting.value = true;
  errorMessage.value = '';

  try {
    await axios.delete(`/admin/api/business-types/${props.typeItem.id}`);
    emit('deleted');
    close();
  } catch (e) {
    console.error("Failed to delete business type", e);
    errorMessage.value = e.response?.data?.message || 'Failed to delete business type.';
  } finally {
    deleting.value = false;
  }
};
</script>
