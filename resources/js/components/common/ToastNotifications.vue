<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <TransitionGroup
      name="toast"
      tag="div"
      class="space-y-2"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="toastClasses(toast.type)"
        class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <!-- Success Icon -->
              <svg v-if="toast.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <!-- Error Icon -->
              <svg v-else-if="toast.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <!-- Warning Icon -->
              <svg v-else-if="toast.type === 'warning'" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <!-- Info Icon -->
              <svg v-else class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900" v-html="toast.message"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeToast(toast.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        <!-- Progress bar for auto-dismiss -->
        <div v-if="toast.duration > 0" class="h-1 bg-gray-200">
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

const toastClasses = (type) => {
  const baseClasses = 'border-l-4';
  switch (type) {
    case 'success':
      return `${baseClasses} border-green-400`;
    case 'error':
      return `${baseClasses} border-red-400`;
    case 'warning':
      return `${baseClasses} border-yellow-400`;
    case 'info':
    default:
      return `${baseClasses} border-blue-400`;
  }
};

const progressBarClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-green-400';
    case 'error':
      return 'bg-red-400';
    case 'warning':
      return 'bg-yellow-400';
    case 'info':
    default:
      return 'bg-blue-400';
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
