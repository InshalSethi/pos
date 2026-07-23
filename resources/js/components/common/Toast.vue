<template>
  <div class="fixed top-5 right-5 z-[999999] pointer-events-none space-y-2.5 max-w-sm w-full">
    <TransitionGroup
      name="toast"
      tag="div"
      class="flex flex-col gap-2.5"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto w-full overflow-hidden rounded-2xl bg-[#0f172a] dark:bg-[#121212] border border-white/10 shadow-2xl p-4 flex items-center gap-3 text-slate-50 relative"
      >
        <!-- Icon badge container -->
        <div class="shrink-0 flex items-center justify-center">
          <!-- Error Icon (Exclamation mark in red circle) -->
          <div v-if="toast.type === 'error'" class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-500 flex items-center justify-center font-black text-xs shrink-0">
            !
          </div>
          <!-- Success Icon (Checkmark in green circle) -->
          <div v-else-if="toast.type === 'success'" class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <!-- Warning Icon -->
          <div v-else-if="toast.type === 'warning'" class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-black text-xs shrink-0">
            !
          </div>
          <!-- Info Icon -->
          <div v-else class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 font-bold text-xs">
            i
          </div>
        </div>

        <!-- Message Body -->
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold leading-normal text-white dark:text-white" style="color: #ffffff !important;">
            {{ toast.message }}
          </p>
        </div>

        <!-- Close Button -->
        <div class="shrink-0 flex">
          <button
            @click="removeToast(toast.id)"
            class="shrink-0 p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-all focus:outline-none cursor-pointer"
          >
            <span class="sr-only">Close</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { globalToasts, useToast } from '@/composables/useToast';

const toasts = globalToasts;
const { removeToast } = useToast();
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
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
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
