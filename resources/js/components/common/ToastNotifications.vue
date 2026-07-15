<template>
  <div class="fixed top-20 right-4 z-[9999] space-y-2 max-w-sm w-full">
    <TransitionGroup
      name="toast"
      tag="div"
      class="space-y-2"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto w-full overflow-hidden rounded-2xl bg-[#0f172a] border border-white/5 shadow-2xl p-4 flex items-center gap-3 text-slate-50 relative"
      >
        <div class="flex-shrink-0">
          <!-- Success Icon -->
          <svg v-if="toast.type === 'success'" class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <!-- Error Icon -->
          <svg v-else-if="toast.type === 'error'" class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <!-- Warning Icon -->
          <svg v-else-if="toast.type === 'warning'" class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <!-- Info Icon -->
          <svg v-else class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold leading-normal text-white dark:text-white" style="color: #ffffff !important;" v-html="toast.message"></p>
        </div>
        <div class="flex-shrink-0 flex">
          <button
            @click="removeToast(toast.id)"
            class="flex-shrink-0 p-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-all focus:outline-none cursor-pointer"
          >
            <span class="sr-only">Close</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <!-- Progress bar for auto-dismiss -->
        <div v-if="toast.duration > 0" class="absolute bottom-0 left-0 right-0 h-0.5 bg-white/5 overflow-hidden rounded-b-2xl">
          <div 
            :class="progressBarClass(toast.type)"
            class="h-full transition-all duration-100 ease-linear"
            :style="{ width: getProgressWidth(toast) + '%' }"
          ></div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useToast } from '@/composables/useToast';

const { toasts, removeToast } = useToast();

// Progress tracking
const progressIntervals = ref(new Map());

const progressBarClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-emerald-500';
    case 'error':
      return 'bg-rose-500';
    case 'warning':
      return 'bg-amber-500';
    case 'info':
    default:
      return 'bg-blue-500';
  }
};

const getProgressWidth = (toast) => {
  if (!toast.duration || toast.duration <= 0) return 100;
  
  const elapsed = Date.now() - (toast.createdAt || Date.now());
  const progress = Math.max(0, 100 - (elapsed / toast.duration) * 100);
  return progress;
};

// Watch for new toasts and set up progress tracking
const watchToasts = () => {
  toasts.value.forEach(toast => {
    if (!toast.createdAt) {
      toast.createdAt = Date.now();
    }
    
    if (toast.duration > 0 && !progressIntervals.value.has(toast.id)) {
      const interval = setInterval(() => {
        const elapsed = Date.now() - toast.createdAt;
        if (elapsed >= toast.duration) {
          clearInterval(interval);
          progressIntervals.value.delete(toast.id);
          removeToast(toast.id);
        }
      }, 100);
      
      progressIntervals.value.set(toast.id, interval);
    }
  });
  
  // Clean up intervals for removed toasts
  progressIntervals.value.forEach((interval, toastId) => {
    if (!toasts.value.find(t => t.id === toastId)) {
      clearInterval(interval);
      progressIntervals.value.delete(toastId);
    }
  });
};

// Watch toasts changes
let watchInterval;
onMounted(() => {
  watchInterval = setInterval(watchToasts, 100);
});

onUnmounted(() => {
  if (watchInterval) {
    clearInterval(watchInterval);
  }
  
  // Clean up all progress intervals
  progressIntervals.value.forEach(interval => clearInterval(interval));
  progressIntervals.value.clear();
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
