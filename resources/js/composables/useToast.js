import { ref } from 'vue';

const toasts = ref([]);
let toastId = 0;

export function useToast() {
  const showToast = (message, type = 'info', duration = 5000) => {
    const id = ++toastId;
    const toast = {
      id,
      message,
      type,
      visible: true
    };

    toasts.value.push(toast);

    // Auto remove toast after duration
    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }

    return id;
  };

  const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  };

  const clearAllToasts = () => {
    toasts.value = [];
  };

  const success = (message, duration = 5000) => showToast(message, 'success', duration);
  const error = (message, duration = 5000) => showToast(message, 'error', duration);
  const info = (message, duration = 5000) => showToast(message, 'info', duration);
  const warning = (message, duration = 5000) => showToast(message, 'warning', duration);

  return {
    toasts,
    showToast,
    removeToast,
    clearAllToasts,
    success,
    error,
    info,
    warning
  };
}

// Global toast state for components that need to display toasts
export const globalToasts = toasts;
