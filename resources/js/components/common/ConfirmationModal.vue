<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="handleBackdropClick">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <!-- Icon based on type -->
          <div class="flex-shrink-0 mr-3">
            <div v-if="type === 'danger'" class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div v-else-if="type === 'warning'" class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div v-else-if="type === 'info'" class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div v-else class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
          </div>
        </div>
        <button v-if="showCloseButton" @click="cancel" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="mb-6">
        <p class="text-sm text-gray-500" v-html="message"></p>
        
        <!-- Additional details if provided -->
        <div v-if="details" class="mt-3 p-3 bg-gray-50 rounded-md">
          <p class="text-xs text-gray-600" v-html="details"></p>
        </div>

        <!-- Input field for confirmation text if required -->
        <div v-if="requireConfirmationText" class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Type <strong>{{ confirmationText }}</strong> to confirm:
          </label>
          <input
            v-model="userConfirmationText"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
            :placeholder="confirmationText"
            @keyup.enter="confirm"
          />
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3">
        <button
          @click="cancel"
          type="button"
          class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500"
          :disabled="loading"
        >
          {{ cancelText }}
        </button>
        <button
          @click="confirm"
          type="button"
          :class="confirmButtonClass"
          :disabled="loading || (requireConfirmationText && userConfirmationText !== confirmationText)"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? loadingText : confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  details: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'danger', // danger, warning, info, default
    validator: (value) => ['danger', 'warning', 'info', 'default'].includes(value)
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  loadingText: {
    type: String,
    default: 'Processing...'
  },
  loading: {
    type: Boolean,
    default: false
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  requireConfirmationText: {
    type: Boolean,
    default: false
  },
  confirmationText: {
    type: String,
    default: 'DELETE'
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['confirm', 'cancel', 'update:show']);

// Reactive data
const userConfirmationText = ref('');

// Computed
const confirmButtonClass = computed(() => {
  const baseClass = 'px-4 py-2 rounded-md focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center';
  
  switch (props.type) {
    case 'danger':
      return `${baseClass} bg-red-600 text-white hover:bg-red-700 focus:ring-red-500`;
    case 'warning':
      return `${baseClass} bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500`;
    case 'info':
      return `${baseClass} bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500`;
    default:
      return `${baseClass} bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500`;
  }
});

// Methods
const confirm = () => {
  if (props.requireConfirmationText && userConfirmationText.value !== props.confirmationText) {
    return;
  }
  emit('confirm');
};

const cancel = () => {
  emit('cancel');
  emit('update:show', false);
};

const handleBackdropClick = () => {
  if (props.closeOnBackdrop && !props.loading) {
    cancel();
  }
};

// Watch for show prop changes to reset confirmation text
watch(() => props.show, (newValue) => {
  if (newValue) {
    userConfirmationText.value = '';
  }
});

// Handle escape key
const handleEscape = (event) => {
  if (event.key === 'Escape' && props.show && !props.loading) {
    cancel();
  }
};

// Add/remove event listeners
watch(() => props.show, (newValue) => {
  if (newValue) {
    document.addEventListener('keydown', handleEscape);
    document.body.style.overflow = 'hidden';
  } else {
    document.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
  }
});
</script>

<style scoped>
/* Additional styles if needed */
</style>
