<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 bg-slate-900/40 dark:bg-black/50 backdrop-blur-md overflow-y-auto h-full w-full z-[9999] flex items-center justify-center p-4 transition-all duration-300">
      <div class="relative mx-auto p-6 border border-slate-100 dark:border-zinc-800 w-full max-w-lg shadow-2xl rounded-xl bg-white dark:bg-zinc-900 text-left transition-all duration-300">
        
        <!-- Sleek Close Icon Button -->
        <button
          type="button"
          @click="$emit('close')"
          class="absolute top-4 right-4 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all cursor-pointer"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="mb-5 pb-3 border-b border-slate-100 dark:border-zinc-800">
          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">{{ isEdit ? 'Edit Customer' : 'Add New Customer' }}</h3>
        </div>

        <form @submit.prevent="saveCustomer" class="space-y-4">
          <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Full Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. John Doe"
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              :class="{ 'border-red-300 dark:border-red-700': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-[10px] text-red-500">{{ errors.name[0] }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="e.g. john@example.com"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.email }"
              />
              <p v-if="errors.email" class="mt-1 text-[10px] text-red-500">{{ errors.email[0] }}</p>
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Phone</label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="e.g. +1 555 1234"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.phone }"
              />
              <p v-if="errors.phone" class="mt-1 text-[10px] text-red-500">{{ errors.phone[0] }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Mobile</label>
              <input
                v-model="form.mobile"
                type="text"
                placeholder="e.g. +1 555 5678"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.mobile }"
              />
              <p v-if="errors.mobile" class="mt-1 text-[10px] text-red-500">{{ errors.mobile[0] }}</p>
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Tax Number / GSTIN</label>
              <input
                v-model="form.tax_number"
                type="text"
                placeholder="e.g. GSTIN12345"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.tax_number }"
              />
              <p v-if="errors.tax_number" class="mt-1 text-[10px] text-red-500">{{ errors.tax_number[0] }}</p>
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Address</label>
            <textarea
              v-model="form.address"
              rows="2"
              placeholder="Street address, suite, apartment..."
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              :class="{ 'border-red-300 dark:border-red-700': errors.address }"
            ></textarea>
            <p v-if="errors.address" class="mt-1 text-[10px] text-red-500">{{ errors.address[0] }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">City</label>
              <input
                v-model="form.city"
                type="text"
                placeholder="e.g. New York"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.city }"
              />
              <p v-if="errors.city" class="mt-1 text-[10px] text-red-500">{{ errors.city[0] }}</p>
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">State</label>
              <input
                v-model="form.state"
                type="text"
                placeholder="e.g. California"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.state }"
              />
              <p v-if="errors.state" class="mt-1 text-[10px] text-red-500">{{ errors.state[0] }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Credit Limit ($)</label>
              <input
                v-model="form.credit_limit"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.credit_limit }"
              />
              <p v-if="errors.credit_limit" class="mt-1 text-[10px] text-red-500">{{ errors.credit_limit[0] }}</p>
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Status</label>
              <select
                v-model="form.is_active"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 bg-white dark:bg-zinc-950 transition-all"
                :class="{ 'border-red-300 dark:border-red-700': errors.is_active }"
              >
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
              <p v-if="errors.is_active" class="mt-1 text-[10px] text-red-500">{{ errors.is_active[0] }}</p>
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Notes</label>
            <textarea
              v-model="form.notes"
              rows="2"
              placeholder="Additional notes about the customer..."
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              :class="{ 'border-red-300 dark:border-red-700': errors.notes }"
            ></textarea>
            <p v-if="errors.notes" class="mt-1 text-[10px] text-red-500">{{ errors.notes[0] }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-3.5 border-t border-slate-100 dark:border-zinc-800 mt-2">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 h-9 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="!form.name || saving"
              class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : (isEdit ? 'Update Customer' : 'Add Customer') }}
            </button>
          </div>
        </form>
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
