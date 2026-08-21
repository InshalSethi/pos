<template>
  <div v-if="isElectron" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm transition-all duration-300 border"
       :class="badgeClasses">
    <!-- Status Icon -->
    <span class="relative flex h-2.5 w-2.5 mr-2">
      <span v-if="syncStore.isSyncing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
      <span v-else-if="syncStore.isOnline" class="animate-pulse absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
      <span :class="dotClasses" class="relative inline-flex rounded-full h-2.5 w-2.5"></span>
    </span>

    <!-- Status Text -->
    <span>
      <template v-if="syncStore.isSyncing">Syncing Data...</template>
      <template v-else-if="syncStore.isOnline">Online</template>
      <template v-else>Offline Mode</template>
    </span>

    <!-- Pending Counter Badge -->
    <span v-if="syncStore.pendingCount > 0" 
          class="ml-2 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-bold animate-bounce">
      {{ syncStore.pendingCount }} pending
    </span>

    <!-- Manual Sync Trigger Button -->
    <button v-if="syncStore.isOnline" 
            @click="syncStore.triggerSync()" 
            title="Force Sync Now" 
            class="ml-2 hover:opacity-80 transition-opacity focus:outline-none">
      <svg class="w-3.5 h-3.5 text-current" :class="{ 'animate-spin': syncStore.isSyncing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue';
import { useSyncStore } from '@/stores/sync';

const syncStore = useSyncStore();
const isElectron = ref(typeof window !== 'undefined' && !!window.electronAPI);

onMounted(() => {
  if (isElectron.value) {
    syncStore.init();
  }
});

const badgeClasses = computed(() => {
  if (syncStore.isSyncing) {
    return 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800';
  }
  if (syncStore.isOnline) {
    return 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800';
  }
  return 'bg-amber-500/10 text-amber-600 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800';
});

const dotClasses = computed(() => {
  if (syncStore.isSyncing) return 'bg-blue-500';
  if (syncStore.isOnline) return 'bg-emerald-500';
  return 'bg-amber-500';
});
</script>
