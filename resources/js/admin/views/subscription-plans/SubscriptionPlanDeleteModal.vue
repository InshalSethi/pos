<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="close"></div>

    <!-- Modal Box -->
    <div class="relative bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-md overflow-hidden transform transition-all z-10 p-6 text-center">
      
      <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black text-xl mx-auto mb-4">
        <i class="fas fa-exclamation-triangle"></i>
      </div>

      <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">
        Delete Subscription Plan
      </h3>
      
      <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 font-medium">
        Are you sure you want to delete <strong class="text-zinc-900 dark:text-white font-bold">{{ planItem?.name }}</strong>?
      </p>

      <!-- Action Buttons -->
      <div class="mt-6 flex justify-center space-x-3">
        <button
          type="button"
          @click="close"
          class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs rounded-xl transition-all cursor-pointer"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="deletePlan"
          :disabled="deleting"
          class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer disabled:opacity-50"
        >
          <i v-if="deleting" class="fas fa-circle-notch fa-spin mr-2"></i>
          <span>Delete Plan</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  planItem: Object,
});

const emit = defineEmits(['close', 'deleted']);

const deleting = ref(false);

const close = () => {
  emit('close');
};

const deletePlan = async () => {
  if (!props.planItem?.id) return;
  deleting.value = true;
  try {
    await axios.delete(`/admin/api/subscription-plans/${props.planItem.id}`);
    emit('deleted');
    close();
  } catch (e) {
    console.error("Failed to delete plan", e);
  } finally {
    deleting.value = false;
  }
};
</script>
