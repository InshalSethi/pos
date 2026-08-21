<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-zinc-900 w-full max-w-xl rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      
      <!-- Modal Header -->
      <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 rounded-xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-sm">
            <i class="fas fa-ticket-alt"></i>
          </div>
          <div>
            <h3 class="text-base font-black text-zinc-950 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit Coupon Code' : 'Add New Coupon Code' }}
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold">
              Set discount rules, usage limits, and expiration dates.
            </p>
          </div>
        </div>

        <button 
          @click="close" 
          class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-500 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer"
        >
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <form @submit.prevent="save" class="p-6 overflow-y-auto space-y-4 custom-scrollbar">
        
        <!-- Error Alert -->
        <div v-if="errorMessage" class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-bold flex items-center">
          <i class="fas fa-exclamation-circle mr-2 text-sm"></i>
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Coupon Code & Name -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Coupon Code <span class="text-rose-500">*</span>
            </label>
            <input 
              v-model="form.code" 
              type="text" 
              required
              @input="form.code = form.code.toUpperCase()"
              placeholder="e.g. SAVE20"
              class="w-full px-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold uppercase tracking-wider"
            />
            <p v-if="errors.code" class="text-[10px] text-rose-500 font-bold mt-1">{{ errors.code[0] }}</p>
          </div>

          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Coupon Name / Label
            </label>
            <input 
              v-model="form.name" 
              type="text" 
              placeholder="e.g. Summer Sale 20% Off"
              class="w-full px-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold"
            />
          </div>
        </div>

        <!-- Discount Type & Value -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Discount Type <span class="text-rose-500">*</span>
            </label>
            <select 
              v-model="form.type" 
              required
              class="w-full px-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
            >
              <option value="percentage">Percentage (%)</option>
              <option value="fixed">Fixed Amount ($)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Discount Value <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">
                {{ form.type === 'percentage' ? '%' : '$' }}
              </span>
              <input 
                v-model="form.value" 
                type="number" 
                step="0.01" 
                min="0.01" 
                required
                placeholder="20"
                class="w-full pl-8 pr-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold"
              />
            </div>
            <p v-if="errors.value" class="text-[10px] text-rose-500 font-bold mt-1">{{ errors.value[0] }}</p>
          </div>
        </div>

        <!-- Min Spend & Max Discount (Caps) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Min Order Amount ($)
            </label>
            <input 
              v-model="form.min_order_amount" 
              type="number" 
              step="0.01" 
              min="0"
              placeholder="0.00"
              class="w-full px-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold"
            />
            <p class="text-[10px] text-zinc-400 mt-1">Minimum plan price required to use</p>
          </div>

          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Max Discount Amount ($)
            </label>
            <input 
              v-model="form.max_discount_amount" 
              type="number" 
              step="0.01" 
              min="0"
              placeholder="Optional cap"
              class="w-full px-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold"
            />
            <p class="text-[10px] text-zinc-400 mt-1">Cap for percentage discounts (optional)</p>
          </div>
        </div>

        <!-- Usage Limit & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Usage Limit
            </label>
            <input 
              v-model="form.usage_limit" 
              type="number" 
              min="1"
              placeholder="Unlimited if empty"
              class="w-full px-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold"
            />
          </div>

          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Status
            </label>
            <div class="flex items-center space-x-3 pt-1.5">
              <button
                type="button"
                @click="form.is_active = !form.is_active"
                class="relative inline-flex items-center cursor-pointer select-none"
              >
                <div 
                  class="block w-10 h-6 rounded-full transition-colors" 
                  :class="form.is_active ? 'bg-black dark:bg-white' : 'bg-zinc-300 dark:bg-zinc-700'"
                ></div>
                <div 
                  class="dot absolute left-0.5 top-0.5 w-5 h-5 rounded-full transition-transform shadow-xs pointer-events-none" 
                  :class="form.is_active ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-900'"
                ></div>
              </button>
              <span class="text-xs font-bold text-zinc-900 dark:text-white">
                {{ form.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Expiration Date -->
        <div>
          <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
            Expiry Date <span class="text-rose-500">*</span>
          </label>
          <input 
            v-model="form.expires_at" 
            type="date" 
            required
            class="w-full px-3.5 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white text-xs font-bold"
          />
          <p v-if="errors.expires_at" class="text-[10px] text-rose-500 font-bold mt-1">{{ errors.expires_at[0] }}</p>
        </div>

        <!-- Modal Footer -->
        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end space-x-3">
          <button 
            type="button" 
            @click="close"
            class="px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-extrabold text-xs rounded-xl transition-all cursor-pointer"
          >
            Cancel
          </button>
          
          <button 
            type="submit"
            :disabled="saving"
            class="px-6 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer disabled:opacity-50"
          >
            <i v-if="saving" class="fas fa-circle-notch fa-spin mr-2"></i>
            <span>{{ saving ? 'Saving...' : (isEditing ? 'Update Coupon' : 'Create Coupon') }}</span>
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  couponId: [Number, String],
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.couponId);
const saving = ref(false);
const errorMessage = ref('');
const errors = ref({});

const form = ref({
  code: '',
  name: '',
  type: 'percentage',
  value: 20,
  min_order_amount: 0,
  max_discount_amount: null,
  usage_limit: null,
  expires_at: '',
  is_active: true,
});

const resetForm = () => {
  form.value = {
    code: '',
    name: '',
    type: 'percentage',
    value: 20,
    min_order_amount: 0,
    max_discount_amount: null,
    usage_limit: null,
    expires_at: '',
    is_active: true,
  };
  errors.value = {};
  errorMessage.value = '';
};

watch(() => props.show, async (newVal) => {
  if (newVal) {
    resetForm();
    if (props.couponId) {
      fetchCouponDetails();
    }
  }
});

const fetchCouponDetails = async () => {
  try {
    const { data } = await axios.get(`/admin/api/coupons/${props.couponId}`);
    const coupon = data.data || data;
    form.value = {
      code: coupon.code || '',
      name: coupon.name || '',
      type: coupon.type || 'percentage',
      value: coupon.value || 0,
      min_order_amount: coupon.min_order_amount || 0,
      max_discount_amount: coupon.max_discount_amount || null,
      usage_limit: coupon.usage_limit || null,
      expires_at: coupon.expires_at ? coupon.expires_at.slice(0, 10) : '',
      is_active: coupon.is_active ?? true,
    };
  } catch (e) {
    errorMessage.value = 'Failed to load coupon details.';
  }
};

const close = () => {
  emit('close');
};

const save = async () => {
  saving.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    if (isEditing.value) {
      await axios.put(`/admin/api/coupons/${props.couponId}`, form.value);
    } else {
      await axios.post('/admin/api/coupons', form.value);
    }
    emit('saved');
    close();
  } catch (e) {
    if (e.response?.data?.errors) {
      errors.value = e.response.data.errors;
    } else {
      errorMessage.value = e.response?.data?.message || 'Failed to save coupon code.';
    }
  } finally {
    saving.value = false;
  }
};
</script>
