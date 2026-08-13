<template>
  <div v-if="show" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200" @click.self="close">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden transition-all text-left">
      
      <!-- Header -->
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50 shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i :class="isEditing ? 'fas fa-user-edit' : 'fas fa-user-plus'"></i>
          </div>
          <div>
            <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">{{ isEditing ? 'Edit User' : 'Add New User' }}</h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold">{{ isEditing ? 'Update user account details and operational status' : 'Create a new website user account' }}</p>
          </div>
        </div>

        <button @click="close" class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer">
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Content / Form -->
      <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
        <!-- Loading State -->
        <div v-if="loading" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
          <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-black dark:text-white"></i>
          <p class="text-xs font-bold uppercase tracking-wider">Fetching User Information...</p>
        </div>

        <form v-else @submit.prevent="submitForm" class="space-y-5">
          <!-- Error Alert -->
          <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 p-4 rounded-2xl text-xs font-bold flex items-start space-x-3">
            <i class="fas fa-exclamation-circle text-base shrink-0 mt-0.5"></i>
            <div>
              <p>{{ errorMessage }}</p>
              <ul v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mt-2 list-disc list-inside space-y-1">
                <li v-for="(errors, field) in validationErrors" :key="field">
                  {{ errors[0] }}
                </li>
              </ul>
            </div>
          </div>

          <!-- Full Name -->
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
              Full Name <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              v-model="form.name" 
              required 
              class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
              placeholder="e.g. John Doe"
            >
          </div>

          <!-- Email Address -->
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
              Email Address <span class="text-rose-500">*</span>
            </label>
            <input 
              type="email" 
              v-model="form.email" 
              required 
              class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
              placeholder="user@example.com"
            >
          </div>

          <!-- Phone Number -->
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
              Phone Number
            </label>
            <input 
              type="text" 
              v-model="form.phone" 
              class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
              placeholder="+1 (555) 000-0000"
            >
          </div>

          <!-- Password -->
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
              Password <span v-if="!isEditing" class="text-rose-500">*</span>
            </label>
            <input 
              type="password" 
              v-model="form.password" 
              :required="!isEditing" 
              class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
              :placeholder="isEditing ? 'Leave blank to keep current password' : 'Create a secure password'"
            >
            <p v-if="isEditing" class="text-[11px] font-semibold text-zinc-400 mt-1">Leave blank if you do not wish to change password.</p>
          </div>

          <!-- Active Status Toggle -->
          <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
            <label class="flex items-center justify-between cursor-pointer select-none">
              <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-900 dark:text-white block">User Active Status</span>
                <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 block mt-0.5">
                  {{ form.is_active ? 'Account is Active and permitted to log into POS.' : 'Account is Inactive / Disabled from accessing POS.' }}
                </span>
              </div>
              <div class="relative shrink-0 ml-4">
                <input type="checkbox" v-model="form.is_active" class="sr-only">
                <div 
                  class="block w-12 h-7 rounded-full transition-colors cursor-pointer" 
                  :class="form.is_active ? 'bg-black dark:bg-white' : 'bg-zinc-300 dark:bg-zinc-700'"
                ></div>
                <div 
                  class="dot absolute left-1 top-1 w-5 h-5 rounded-full transition-transform shadow-xs pointer-events-none" 
                  :class="form.is_active ? 'translate-x-5 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-900'"
                ></div>
              </div>
            </label>
          </div>

          <!-- Hidden Submit Button for Enter Key -->
          <button type="submit" class="hidden"></button>
        </form>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-end space-x-3 shrink-0">
        <button
          type="button"
          @click="close"
          class="px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold text-xs rounded-xl transition-all cursor-pointer"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="submitForm"
          :disabled="submitting || loading"
          class="px-6 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all disabled:opacity-50 flex items-center cursor-pointer"
        >
          <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-save mr-2"></i>
          {{ isEditing ? 'Save Changes' : 'Create User' }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  userId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.userId);
const loading = ref(false);
const submitting = ref(false);
const errorMessage = ref('');
const validationErrors = ref({});

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  is_active: true
});

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    phone: '',
    password: '',
    is_active: true
  };
  errorMessage.value = '';
  validationErrors.value = {};
};

const loadUser = async () => {
  if (!props.userId) return;
  loading.value = true;
  errorMessage.value = '';
  try {
    const { data } = await axios.get(`/admin/api/users/${props.userId}`);
    const user = data.data;
    form.value.name = user.name || '';
    form.value.email = user.email || '';
    form.value.phone = user.phone || '';
    form.value.password = '';
    form.value.is_active = Boolean(user.is_active);
  } catch (e) {
    console.error("Failed to load user", e);
    errorMessage.value = 'Failed to load user details.';
  } finally {
    loading.value = false;
  }
};

const close = () => {
  resetForm();
  emit('close');
};

const submitForm = async () => {
  if (submitting.value) return;
  submitting.value = true;
  errorMessage.value = '';
  validationErrors.value = {};

  try {
    const payload = {
      name: form.value.name,
      email: form.value.email,
      phone: form.value.phone,
      is_active: Boolean(form.value.is_active)
    };

    if (form.value.password) {
      payload.password = form.value.password;
    }

    if (isEditing.value) {
      await axios.put(`/admin/api/users/${props.userId}`, payload);
    } else {
      payload.password = form.value.password;
      await axios.post('/admin/api/users', payload);
    }

    emit('saved');
    close();
  } catch (e) {
    if (e.response && e.response.status === 422) {
      errorMessage.value = 'Please correct the validation errors below.';
      validationErrors.value = e.response.data.errors;
    } else {
      errorMessage.value = e.response?.data?.message || 'An error occurred while saving user.';
    }
  } finally {
    submitting.value = false;
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm();
    if (props.userId) {
      loadUser();
    }
  }
});

watch(() => props.userId, (newVal) => {
  if (props.show && newVal) {
    loadUser();
  }
});
</script>
