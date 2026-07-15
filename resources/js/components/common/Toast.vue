<template>
  <div class="toast-container">
    <transition-group name="toast" tag="div">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'toast',
          `toast-${toast.type}`
        ]"
      >
        <div class="toast-content">
          <div class="toast-body">
            <div class="toast-icon">
              <!-- Success Outline Icon -->
              <svg v-if="toast.type === 'success'" class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <!-- Error Outline Icon -->
              <svg v-else-if="toast.type === 'error'" class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <!-- Warning Outline Icon -->
              <svg v-else-if="toast.type === 'warning'" class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <!-- Info Outline Icon -->
              <svg v-else class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="toast-message">
              {{ toast.message }}
            </div>
          </div>
          <button @click="removeToast(toast.id)" class="toast-close">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script>
import { globalToasts, useToast } from '@/composables/useToast';

export default {
  name: 'Toast',
  setup() {
    const { removeToast } = useToast();

    return {
      toasts: globalToasts,
      removeToast
    };
  }
};
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast {
  background-color: #0f172a; /* Slate 950 / Very Dark Navy */
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px; /* Premium rounded-xl/2xl feel */
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  min-width: 320px;
  max-width: 420px;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
}

.toast-body {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-w: 0;
}

.toast-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-message {
  flex: 1;
  font-size: 13px;
  font-weight: 600; /* Matching font weight from screenshot */
  line-height: 1.4;
  color: #f8fafc; /* Slate 50 / pure white text */
  word-break: break-word;
  font-family: system-ui, -apple-system, sans-serif;
}

.toast-close {
  flex-shrink: 0;
  margin-left: 14px;
  padding: 4px;
  border: none;
  background: none;
  cursor: pointer;
  border-radius: 6px;
  color: #64748b; /* Slate 500 */
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #f8fafc;
}

/* Animations */
.toast-enter-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(-12px) scale(0.95);
}
.toast-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
.toast-move {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
