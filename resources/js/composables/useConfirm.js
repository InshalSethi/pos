import { ref } from 'vue';

const confirmState = ref({
  isOpen: false,
  title: 'Confirm Action',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  type: 'danger', // 'danger' | 'warning' | 'primary'
  resolve: null,
});

export function useConfirm() {
  const confirm = (options = {}) => {
    let title = 'Confirm Action';
    let message = '';
    let confirmText = 'Yes, Delete';
    let cancelText = 'Cancel';
    let type = 'danger';

    if (typeof options === 'string') {
      message = options;
    } else {
      title = options.title || title;
      message = options.message || '';
      confirmText = options.confirmText || confirmText;
      cancelText = options.cancelText || cancelText;
      type = options.type || type;
    }

    return new Promise((resolve) => {
      confirmState.value = {
        isOpen: true,
        title,
        message,
        confirmText,
        cancelText,
        type,
        resolve,
      };
    });
  };

  const handleConfirm = () => {
    if (confirmState.value.resolve) {
      confirmState.value.resolve(true);
    }
    confirmState.value.isOpen = false;
  };

  const handleCancel = () => {
    if (confirmState.value.resolve) {
      confirmState.value.resolve(false);
    }
    confirmState.value.isOpen = false;
  };

  return {
    confirmState,
    confirm,
    handleConfirm,
    handleCancel,
  };
}

export const globalConfirmState = confirmState;
