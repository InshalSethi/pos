<template>
  <Teleport to="body">
    <div
      v-if="confirmState.isOpen"
      class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-slate-900/30 dark:bg-slate-950/80 backdrop-blur-md transition-all duration-200 animate-in fade-in"
      @click.self="handleCancel"
    >
      <div class="relative bg-white dark:bg-[#141414] border border-slate-200/80 dark:border-[#2E2E2E] rounded-[28px] max-w-sm w-full p-6 shadow-2xl text-center animate-in zoom-in-95 duration-200">
        
        <!-- Header Icon Badge -->
        <div class="mx-auto mb-4 w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg shrink-0"
          :class="[
            confirmState.type === 'danger' ? 'bg-rose-500/10 border border-rose-500/20 text-rose-500 shadow-rose-500/10' :
            confirmState.type === 'warning' ? 'bg-amber-500/10 border border-amber-500/20 text-amber-500 dark:text-amber-400 shadow-amber-500/10' :
            'bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 shadow-indigo-500/10'
          ]"
        >
          <!-- Trash Icon for Danger -->
          <svg v-if="confirmState.type === 'danger'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          <!-- Warning Icon -->
          <svg v-else-if="confirmState.type === 'warning'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <!-- Info/Primary Icon -->
          <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <!-- Title -->
        <h3 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-wide mb-1.5">
          {{ confirmState.title || 'Confirm Action' }}
        </h3>

        <!-- Message -->
        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6 px-2">
          {{ confirmState.message }}
        </p>

        <!-- Actions Buttons -->
        <div class="flex items-center gap-3">
          <button
            type="button"
            @click="handleCancel"
            class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs uppercase tracking-wider transition-all cursor-pointer border border-slate-200 dark:border-slate-700/50"
          >
            {{ confirmState.cancelText || 'Cancel' }}
          </button>

          <button
            type="button"
            @click="handleConfirm"
            class="flex-1 px-4 py-2.5 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all shadow-md cursor-pointer"
            :class="[
              confirmState.type === 'danger' ? 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/30' :
              confirmState.type === 'warning' ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/30' :
              'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/30'
            ]"
          >
            {{ confirmState.confirmText || 'Confirm' }}
          </button>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { globalConfirmState as confirmState, useConfirm } from '@/composables/useConfirm';

const { handleConfirm, handleCancel } = useConfirm();
</script>
