<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="$emit('close')"></div>
      
      <!-- Modal -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-white rounded-lg shadow-xl">
          <!-- Header -->
          <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-semibold text-white">{{ isEdit ? 'Edit User' : 'Add New User' }}</h3>
                  <p class="text-indigo-100">{{ isEdit ? 'Update user information' : 'Create a new user profile' }}</p>
                </div>
              </div>
              <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="max-h-[70vh] overflow-y-auto">
            <form @submit.prevent="saveUser" class="p-6">
              <div class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- Basic Information -->
                  <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Basic Information</h4>
                    
                    <div>
                      <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name }"
                        required
                      />
                      <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                      <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.email }"
                        required
                      />
                      <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                    </div>

                    <div>
                      <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                      <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.phone }"
                      />
                      <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
                    </div>

                    <div v-if="!isEdit" class="space-y-4">
                      <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                        <div class="relative">
                          <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 pr-10"
                            :class="{ 'border-red-300': errors.password }"
                            required
                          />
                          <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                          >
                            <svg v-if="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m1.406-1.407A10.014 10.014 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-4.293 5.774M6.228 6.228L17.772 17.772M9 9l6 6" />
                            </svg>
                          </button>
                        </div>
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
                      </div>

                      <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                        <div class="relative">
                          <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 pr-10"
                            :class="{ 'border-red-300': errors.password }"
                            required
                          />
                          <button 
                            type="button" 
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                          >
                            <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m1.406-1.407A10.014 10.014 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-4.293 5.774M6.228 6.228L17.772 17.772M9 9l6 6" />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Account Settings -->
                  <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Account Settings</h4>
                    
                    <div>
                      <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                      <select
                        id="role"
                        v-model="form.role"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        required
                      >
                        <option v-for="role in rolesList" :key="role.id" :value="role.name">
                          {{ role.name }}
                        </option>
                      </select>
                    </div>

                    <div>
                      <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                      <select
                        id="is_active"
                        v-model="form.is_active"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      >
                        <option :value="true">Active</option>
                        <option :value="false">Inactive</option>
                      </select>
                      <p v-if="errors.is_active" class="mt-1 text-sm text-red-600">{{ errors.is_active[0] }}</p>
                    </div>

                    <div>
                      <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                      <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      ></textarea>
                      <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address[0] }}</p>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div>
                  <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                  <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Internal notes about this user..."
                  ></textarea>
                  <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes[0] }}</p>
                </div>
              </div>
              
              <!-- Footer -->
              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                <button 
                  type="button" 
                  @click="$emit('close')" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="saving" 
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ saving ? 'Saving...' : (isEdit ? 'Update User' : 'Create User') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

export default {
  name: 'UserCreateForm',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    rolesList: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();

    const saving = ref(false);
    const errors = ref({});

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'user',
      phone: '',
      address: '',
      notes: '',
      is_active: true
    });
    
    const showPassword = ref(false);
    const showConfirmPassword = ref(false);

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'role') {
          form[key] = props.rolesList.find(r => r.name === 'user') ? 'user' : (props.rolesList[0]?.name || '');
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
    };

    const loadUserData = () => {
      if (props.user && props.isEdit) {
        Object.keys(form).forEach(key => {
          if (props.user[key] !== undefined) {
            form[key] = props.user[key];
          }
        });
        
        // Load role from the roles array (Spatie relationship)
        if (props.user.roles && props.user.roles.length > 0) {
          form.role = props.user.roles[0].name;
        } else if (props.user.role_name) {
          form.role = props.user.role_name;
        }
      }
    };

    const saveUser = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const url = props.isEdit ? `/api/users/${props.user.id}` : '/api/users';
        const method = props.isEdit ? 'put' : 'post';

        // Filter out password if it's empty during edit
        const payload = { ...form };
        if (props.isEdit && !payload.password) {
          delete payload.password;
        }

        await axios[method](url, payload);

        showToast(
          props.isEdit ? 'User updated successfully' : 'User created successfully',
          'success'
        );

        emit('saved');
        emit('close');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error saving user', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        resetForm();
        loadUserData();
      }
    });

    return {
      form,
      errors,
      saving,
      showPassword,
      showConfirmPassword,
      saveUser
    };
  }
};
</script>

<style scoped>
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
