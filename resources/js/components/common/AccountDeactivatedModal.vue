<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-200"
      @click.prevent
    >
      <div
        class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden text-center p-6 sm:p-8 animate-in zoom-in-95 duration-200"
        @click.stop
      >
        <!-- Deactivation Icon Badge -->
        <div class="mx-auto w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-5 border border-rose-200 dark:border-rose-800/50 shadow-xs">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>

        <!-- Alert Content -->
        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
          Account Deactivated
        </h2>
        <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
          Your account is currently inactive. Please contact your administrator.
        </p>

        <!-- Home Icon Action Button -->
        <div class="mt-8 flex items-center justify-center">
          <button
            type="button"
            @click="handleHomeRedirect"
            class="w-full inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 font-bold text-xs sm:text-sm shadow-md transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Return to Home</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const show = computed(() => authStore.isDeactivated);

const handleHomeRedirect = () => {
  authStore.logout();
  localStorage.removeItem('auth_token');
  localStorage.removeItem('admin_token');
  window.location.href = '/';
};
</script>
