<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="$emit('close')"></div>
      
      <!-- Modal -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-white rounded-lg shadow-xl">
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
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
                  <h3 class="text-xl font-semibold text-white">{{ isEdit ? 'Edit Customer' : 'Add New Customer' }}</h3>
                  <p class="text-blue-100">{{ isEdit ? 'Update customer information' : 'Create a new customer profile' }}</p>
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
            <form @submit.prevent="saveCustomer" class="p-6">
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
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name }"
                        required
                      />
                      <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                      <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.email }"
                      />
                      <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input
                          id="phone"
                          v-model="form.phone"
                          type="text"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.phone }"
                        />
                        <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
                      </div>
                      <div>
                        <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                        <input
                          id="mobile"
                          v-model="form.mobile"
                          type="text"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.mobile }"
                        />
                        <p v-if="errors.mobile" class="mt-1 text-sm text-red-600">{{ errors.mobile[0] }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Address Information -->
                  <div class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Address Information</h4>
                    
                    <div>
                      <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                      <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.address }"
                      ></textarea>
                      <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address[0] }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input
                          id="city"
                          v-model="form.city"
                          type="text"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.city }"
                        />
                        <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city[0] }}</p>
                      </div>
                      <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input
                          id="state"
                          v-model="form.state"
                          type="text"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.state }"
                        />
                        <p v-if="errors.state" class="mt-1 text-sm text-red-600">{{ errors.state[0] }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Business Information -->
                <div class="space-y-4">
                  <h4 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Business Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label for="tax_number" class="block text-sm font-medium text-gray-700 mb-1">Tax Number</label>
                      <input
                        id="tax_number"
                        v-model="form.tax_number"
                        type="text"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.tax_number }"
                      />
                      <p v-if="errors.tax_number" class="mt-1 text-sm text-red-600">{{ errors.tax_number[0] }}</p>
                    </div>
                    <div>
                      <label for="credit_limit" class="block text-sm font-medium text-gray-700 mb-1">Credit Limit ($)</label>
                      <input
                        id="credit_limit"
                        v-model="form.credit_limit"
                        type="number"
                        step="0.01"
                        min="0"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.credit_limit }"
                      />
                      <p v-if="errors.credit_limit" class="mt-1 text-sm text-red-600">{{ errors.credit_limit[0] }}</p>
                    </div>
                    <div>
                      <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                      <select
                        id="is_active"
                        v-model="form.is_active"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.is_active }"
                      >
                        <option :value="true">Active</option>
                        <option :value="false">Inactive</option>
                      </select>
                      <p v-if="errors.is_active" class="mt-1 text-sm text-red-600">{{ errors.is_active[0] }}</p>
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
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.notes }"
                    placeholder="Additional notes about the customer..."
                  ></textarea>
                  <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes[0] }}</p>
                </div>
              </div>
              
              <!-- Footer -->
              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                <button 
                  type="button" 
                  @click="$emit('close')" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="saving" 
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ saving ? 'Saving...' : (isEdit ? 'Update Customer' : 'Create Customer') }}
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
import api from '@/services/api';

export default {
  name: 'CustomerModalSimple',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    customer: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
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
      phone: '',
      mobile: '',
      address: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      date_of_birth: '',
      gender: '',
      tax_number: '',
      notes: '',
      credit_limit: 0,
      is_active: true
    });

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'credit_limit') {
          form[key] = 0;
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
    };

    const loadCustomerData = () => {
      if (props.customer && props.isEdit) {
        Object.keys(form).forEach(key => {
          if (props.customer[key] !== undefined) {
            form[key] = props.customer[key];
          }
        });
      }
    };

    const saveCustomer = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const url = props.isEdit ? `/customers/${props.customer.id}` : '/customers';
        const method = props.isEdit ? 'put' : 'post';

        await api[method](url, form);

        showToast(
          props.isEdit ? 'Customer updated successfully' : 'Customer created successfully',
          'success'
        );

        emit('saved');
        emit('close');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error saving customer', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        resetForm();
        loadCustomerData();
      }
    });

    return {
      form,
      errors,
      saving,
      saveCustomer
    };
  }
};
</script>

<style scoped>
/* Loading spinner animation */
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
