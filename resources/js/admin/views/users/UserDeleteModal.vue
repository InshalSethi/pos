<template>
  <div v-if="show" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200" @click.self="close">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transition-all text-left">
      
      <!-- Header -->
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-rose-50/50 dark:bg-rose-950/20">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div>
            <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">Confirm User Deletion</h3>
            <p class="text-xs text-rose-600 dark:text-rose-400 font-bold uppercase tracking-wider">Permanent Action</p>
          </div>
        </div>

        <button @click="close" class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer">
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-4">
        <p class="text-xs text-zinc-700 dark:text-zinc-300 font-semibold leading-relaxed">
          Are you sure you want to permanently delete <strong class="text-zinc-950 dark:text-white font-black">{{ userName }}</strong>?
        </p>

        <!-- Warning Callout Box -->
        <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 p-4 rounded-2xl space-y-2">
          <div class="flex items-center text-rose-700 dark:text-rose-400 font-extrabold text-xs">
            <i class="fas fa-database mr-2"></i> Data Eradication Warning
          </div>
          <p class="text-[11px] text-rose-600/90 dark:text-rose-300/90 font-medium leading-normal">
            This action will completely erase the user account, <strong>all registered companies</strong>, and <strong>all related operational data</strong> (products, inventory, sales, bank accounts, and transactions) permanently from the database.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-end space-x-3 shrink-0">
        <button
          type="button"
          @click="close"
          class="px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold text-xs rounded-xl transition-all cursor-pointer"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="confirmDelete"
          :disabled="deleting"
          class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all disabled:opacity-50 flex items-center cursor-pointer"
        >
          <i v-if="deleting" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-trash-alt mr-2"></i>
          Delete User & All Related Data
        </button>
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
  user: {
    type: Object,
    default: () => null
  }
});

const emit = defineEmits(['close', 'deleted']);

const deleting = ref(false);

const userName = computed(() => props.user?.name || 'this user');

const close = () => {
  deleting.value = false;
  emit('close');
};

const confirmDelete = async () => {
  if (!props.user || !props.user.id || deleting.value) return;

  deleting.value = true;
  try {
    await axios.delete(`/admin/api/users/${props.user.id}`);
    emit('deleted');
    close();
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to delete user and related data.');
  } finally {
    deleting.value = false;
  }
};
</script>
