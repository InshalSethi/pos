<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-hidden h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-[#12141a] text-slate-800 dark:text-zinc-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-hidden z-10" @click.stop>
        
        <!-- Header -->
        <div class="p-6 pb-4 border-b border-slate-100 dark:border-zinc-800 shrink-0 relative">
          <!-- Sleek Close Icon Button -->
          <button
            type="button"
            @click="closeModal"
            class="absolute top-5 right-5 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all cursor-pointer"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">{{ isEdit ? 'Edit Supplier' : 'Add New Supplier' }}</h3>
        </div>

        <!-- Tab Navigation (Clean text tabs, no icons) -->
        <div class="flex border-b border-slate-200 dark:border-zinc-800 px-6 pt-3 gap-1 text-[11px] shrink-0 bg-slate-50/50 dark:bg-zinc-900/40">
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'basic' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'basic'"
          >
            Basic Info
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'contact' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'contact'"
          >
            Contact
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'address' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'address'"
          >
            Address
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'business' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'business'"
          >
            Business
          </button>
        </div>

        <form @submit.prevent="saveSupplier" class="flex flex-col flex-1 min-h-0">
          <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4 custom-scrollbar">
            <!-- Basic Information Tab -->
            <div v-if="activeTab === 'basic'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Supplier Name *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="Enter supplier name"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.name }"
                  />
                  <p v-if="errors.name" class="mt-1 text-[10px] text-red-500">{{ errors.name[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Company Name</label>
                  <input
                    v-model="form.company_name"
                    type="text"
                    placeholder="Enter company name"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.company_name }"
                  />
                  <p v-if="errors.company_name" class="mt-1 text-[10px] text-red-500">{{ errors.company_name[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Tax Number</label>
                  <input
                    v-model="form.tax_number"
                    type="text"
                    placeholder="Enter tax number"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.tax_number }"
                  />
                  <p v-if="errors.tax_number" class="mt-1 text-[10px] text-red-500">{{ errors.tax_number[0] }}</p>
                </div>

                <div>
                  <CustomFloatingSelect
                    label="Status"
                    v-model="form.is_active"
                    :options="statusOptions"
                  />
                  <p v-if="errors.is_active" class="mt-1 text-[10px] text-red-500">{{ errors.is_active[0] }}</p>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  placeholder="Add any additional notes about this supplier..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.notes }"
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-[10px] text-red-500">{{ errors.notes[0] }}</p>
              </div>
            </div>

            <!-- Contact Information Tab -->
            <div v-if="activeTab === 'contact'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email Address</label>
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="supplier@example.com"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.email }"
                  />
                  <p v-if="errors.email" class="mt-1 text-[10px] text-red-500">{{ errors.email[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                  <input
                    v-model="form.phone"
                    type="text"
                    placeholder="+1 (555) 123-4567"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.phone }"
                  />
                  <p v-if="errors.phone" class="mt-1 text-[10px] text-red-500">{{ errors.phone[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Mobile Number</label>
                  <input
                    v-model="form.mobile"
                    type="text"
                    placeholder="+1 (555) 987-6543"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.mobile }"
                  />
                  <p v-if="errors.mobile" class="mt-1 text-[10px] text-red-500">{{ errors.mobile[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Website</label>
                  <input
                    v-model="form.website"
                    type="url"
                    placeholder="https://www.supplier.com"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.website }"
                  />
                  <p v-if="errors.website" class="mt-1 text-[10px] text-red-500">{{ errors.website[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Address Information Tab -->
            <div v-if="activeTab === 'address'" class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Street Address</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  placeholder="Enter full street address..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.address }"
                ></textarea>
                <p v-if="errors.address" class="mt-1 text-[10px] text-red-500">{{ errors.address[0] }}</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">City</label>
                  <input
                    v-model="form.city"
                    type="text"
                    placeholder="Enter city"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.city }"
                  />
                  <p v-if="errors.city" class="mt-1 text-[10px] text-red-500">{{ errors.city[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">State / Province</label>
                  <input
                    v-model="form.state"
                    type="text"
                    placeholder="Enter state or province"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.state }"
                  />
                  <p v-if="errors.state" class="mt-1 text-[10px] text-red-500">{{ errors.state[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Postal Code</label>
                  <input
                    v-model="form.postal_code"
                    type="text"
                    placeholder="Enter postal code"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.postal_code }"
                  />
                  <p v-if="errors.postal_code" class="mt-1 text-[10px] text-red-500">{{ errors.postal_code[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Country</label>
                  <input
                    v-model="form.country"
                    type="text"
                    placeholder="Enter country"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.country }"
                  />
                  <p v-if="errors.country" class="mt-1 text-[10px] text-red-500">{{ errors.country[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Business Information Tab -->
            <div v-if="activeTab === 'business'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Credit Limit ({{ currencySymbol }})</label>
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
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Payment Terms (Days)</label>
                  <input
                    v-model="form.payment_terms_days"
                    type="number"
                    min="0"
                    placeholder="30"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.payment_terms_days }"
                  />
                  <p v-if="errors.payment_terms_days" class="mt-1 text-[10px] text-red-500">{{ errors.payment_terms_days[0] }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Buttons (Clean text buttons, no icons) -->
          <div class="flex justify-end space-x-3 p-6 border-t border-slate-100 dark:border-zinc-800 shrink-0 bg-slate-50/50 dark:bg-zinc-900/50">
            <button
              type="button"
              @click="closeModal"
              class="px-4 h-9 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : (isEdit ? 'Update Supplier' : 'Create Supplier') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch, nextTick, computed, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { useToast } from '@/composables/useToast';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';
import api from '@/services/api';

export default {
  name: 'SupplierModal',
  components: {
    CustomFloatingSelect
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    supplier: {
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
    const authStore = useAuthStore();
    const currencyStore = useCurrencyStore();

    const currencySymbol = computed(() => {
      return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$';
    });

    const statusOptions = [
      { value: true, label: 'Active' },
      { value: false, label: 'Inactive' }
    ];

    const saving = ref(false);
    const errors = ref({});
    const activeTab = ref('basic');

    const form = reactive({
      name: '',
      company_name: '',
      email: '',
      phone: '',
      mobile: '',
      address: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      tax_number: '',
      website: '',
      notes: '',
      credit_limit: 0,
      payment_terms_days: 30,
      is_active: true
    });

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'credit_limit') {
          form[key] = 0;
        } else if (key === 'payment_terms_days') {
          form[key] = 30;
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
    };

    const loadSupplierData = () => {
      if (!props.supplier || !props.isEdit) {
        return;
      }

      try {
        const supplierData = {
          name: props.supplier.name || '',
          company_name: props.supplier.company_name || '',
          email: props.supplier.email || '',
          phone: props.supplier.phone || '',
          mobile: props.supplier.mobile || '',
          address: props.supplier.address || '',
          city: props.supplier.city || '',
          state: props.supplier.state || '',
          postal_code: props.supplier.postal_code || '',
          country: props.supplier.country || '',
          tax_number: props.supplier.tax_number || '',
          website: props.supplier.website || '',
          notes: props.supplier.notes || '',
          credit_limit: parseFloat(props.supplier.credit_limit) || 0,
          payment_terms_days: parseInt(props.supplier.payment_terms_days) || 30,
          is_active: Boolean(props.supplier.is_active)
        };

        Object.assign(form, supplierData);
      } catch (error) {
        console.error('Error loading supplier data:', error);
      }
    };

    const handleKeyDown = (e) => {
      if (e.key === 'Escape' && props.show) {
        closeModal();
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        activeTab.value = 'basic';
        if (props.isEdit && props.supplier) {
          nextTick(() => {
            loadSupplierData();
          });
        } else {
          resetForm();
        }
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', handleKeyDown);
      } else {
        document.body.style.overflow = '';
        window.removeEventListener('keydown', handleKeyDown);
      }
    }, { immediate: true });

    onUnmounted(() => {
      document.body.style.overflow = '';
      window.removeEventListener('keydown', handleKeyDown);
    });

    watch(() => props.supplier, (newVal) => {
      if (props.show && props.isEdit && newVal) {
        nextTick(() => {
          loadSupplierData();
        });
      }
    }, { deep: true, immediate: true });

    const closeModal = () => {
      resetForm();
      emit('close');
    };

    const saveSupplier = async () => {
      saving.value = true;
      errors.value = {};

      try {
        let response;
        if (props.isEdit && props.supplier?.id) {
          response = await api.put(`/suppliers/${props.supplier.id}`, form);
          showToast('Supplier updated successfully!', 'success');
        } else {
          response = await api.post('/suppliers', form);
          showToast('Supplier created successfully!', 'success');
        }

        emit('saved', response.data.data || response.data);
        closeModal();
      } catch (error) {
        console.error('Error saving supplier:', error);
        if (error.response && error.response.status === 422) {
          errors.value = error.response.data.errors || {};
          showToast('Please fix the validation errors below.', 'error');
        } else {
          const message = error.response?.data?.message || 'Failed to save supplier';
          showToast(message, 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    return {
      currencySymbol,
      statusOptions,
      saving,
      errors,
      activeTab,
      form,
      closeModal,
      saveSupplier
    };
  }
};
</script>
