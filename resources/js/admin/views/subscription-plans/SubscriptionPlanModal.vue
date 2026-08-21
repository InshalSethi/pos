<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="close"></div>

    <!-- Modal Box -->
    <div class="relative bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-2xl overflow-hidden my-8 transform transition-all z-10">
      
      <!-- Header -->
      <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-900/50">
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 rounded-xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-sm shadow-xs">
            <i class="fas fa-tags"></i>
          </div>
          <div>
            <h3 class="text-base font-black text-zinc-950 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit Subscription Plan' : 'Create Subscription Plan' }}
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold mt-0.5">
              Configure subscription pricing, company & user limits, and features.
            </p>
          </div>
        </div>

        <button 
          @click="close" 
          class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-500 hover:text-zinc-900 dark:hover:text-white flex items-center justify-center transition-all cursor-pointer"
        >
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="savePlan" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
        
        <!-- Loading overlay when fetching data -->
        <div v-if="loadingData" class="py-12 text-center">
          <i class="fas fa-circle-notch fa-spin text-2xl text-black dark:text-white mb-2"></i>
          <p class="text-xs font-bold text-zinc-500">Loading plan details...</p>
        </div>

        <div v-else class="space-y-4">
          <!-- Name & Slug -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                Plan Name <span class="text-rose-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.name"
                @input="autoGenerateSlug"
                placeholder="e.g. Advance"
                required
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
              />
            </div>

            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                Slug
              </label>
              <input
                type="text"
                v-model="form.slug"
                placeholder="e.g. advance"
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-mono"
              />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
              Description
            </label>
            <textarea
              v-model="form.description"
              rows="2"
              placeholder="Short description of the plan..."
              class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-semibold"
            ></textarea>
          </div>

          <!-- Pricing & Trial Days -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                Monthly Price ($) <span class="text-rose-500">*</span>
              </label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model.number="form.monthly_price"
                required
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
              />
            </div>

            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                Yearly Price ($) <span class="text-rose-500">*</span>
              </label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model.number="form.yearly_price"
                required
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
              />
            </div>

            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                Trial Days
              </label>
              <input
                type="number"
                min="0"
                v-model.number="form.trial_days"
                placeholder="14"
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
              />
            </div>
          </div>

          <!-- Company & User Limits -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                Allowed Companies <span class="text-rose-500">*</span>
              </label>
              <input
                type="number"
                min="1"
                v-model.number="form.max_companies"
                required
                placeholder="e.g. 2"
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
              />
              <span class="text-[10px] text-zinc-400 mt-1 block">Number of companies allowed under this plan.</span>
            </div>

            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mb-1.5">
                Users Per Company <span class="text-rose-500">*</span>
              </label>
              <input
                type="number"
                min="1"
                v-model.number="form.max_users_per_company"
                required
                placeholder="e.g. 20"
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
              />
              <span class="text-[10px] text-zinc-400 mt-1 block">Number of users allowed per company.</span>
            </div>
          </div>

          <!-- Toggles: Popular, Custom, Active, Order -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-zinc-50 dark:bg-zinc-800/40 p-3.5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800">
            <label class="flex items-center space-x-2 cursor-pointer select-none">
              <input type="checkbox" v-model="form.is_popular" class="w-4 h-4 rounded text-black focus:ring-black border-zinc-300" />
              <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Most Popular</span>
            </label>

            <label class="flex items-center space-x-2 cursor-pointer select-none">
              <input type="checkbox" v-model="form.is_custom" class="w-4 h-4 rounded text-black focus:ring-black border-zinc-300" />
              <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Custom Plan</span>
            </label>

            <label class="flex items-center space-x-2 cursor-pointer select-none">
              <input type="checkbox" v-model="form.is_active" class="w-4 h-4 rounded text-black focus:ring-black border-zinc-300" />
              <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Active</span>
            </label>

            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-zinc-600 dark:text-zinc-400 shrink-0">Order:</span>
              <input
                type="number"
                min="0"
                v-model.number="form.sort_order"
                class="w-16 px-2 py-1 border border-zinc-200 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900"
              />
            </div>
          </div>

          <!-- Features List -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                Key Features List
              </label>
              <button
                type="button"
                @click="addFeature"
                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer flex items-center gap-1"
              >
                <i class="fas fa-plus text-[10px]"></i> Add Feature
              </button>
            </div>

            <div class="space-y-2">
              <div 
                v-for="(feature, index) in form.features" 
                :key="index"
                class="flex items-center gap-2"
              >
                <input
                  type="text"
                  v-model="form.features[index]"
                  placeholder="e.g. 20 Users per Company"
                  class="flex-1 px-3 py-1.5 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs font-medium bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white"
                />
                <button
                  type="button"
                  @click="removeFeature(index)"
                  class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center cursor-pointer shrink-0"
                >
                  <i class="fas fa-times text-xs"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Error Alert -->
          <div v-if="errorMessage" class="p-3 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold">
            {{ errorMessage }}
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end space-x-3">
          <button
            type="button"
            @click="close"
            class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-xs rounded-xl transition-all cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-5 py-2 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer disabled:opacity-50"
          >
            <i v-if="submitting" class="fas fa-circle-notch fa-spin mr-2"></i>
            <span>{{ isEditing ? 'Save Changes' : 'Create Plan' }}</span>
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
  planId: Number,
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.planId);
const loadingData = ref(false);
const submitting = ref(false);
const errorMessage = ref('');

const defaultForm = () => ({
  name: '',
  slug: '',
  description: '',
  monthly_price: 0,
  yearly_price: 0,
  trial_days: 0,
  max_companies: 1,
  max_users_per_company: 1,
  is_popular: false,
  is_custom: false,
  is_active: true,
  sort_order: 0,
  features: ['']
});

const form = ref(defaultForm());

const autoGenerateSlug = () => {
  if (!isEditing.value) {
    form.value.slug = form.value.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
  }
};

const addFeature = () => {
  form.value.features.push('');
};

const removeFeature = (index) => {
  form.value.features.splice(index, 1);
};

const fetchPlanData = async () => {
  if (!props.planId) return;
  loadingData.value = true;
  try {
    const { data } = await axios.get(`/admin/api/subscription-plans/${props.planId}`);
    const plan = data.data;
    form.value = {
      name: plan.name || '',
      slug: plan.slug || '',
      description: plan.description || '',
      monthly_price: parseFloat(plan.monthly_price) || 0,
      yearly_price: parseFloat(plan.yearly_price) || 0,
      trial_days: parseInt(plan.trial_days) || 0,
      max_companies: parseInt(plan.max_companies) || 1,
      max_users_per_company: parseInt(plan.max_users_per_company) || 1,
      is_popular: !!plan.is_popular,
      is_custom: !!plan.is_custom,
      is_active: !!plan.is_active,
      sort_order: parseInt(plan.sort_order) || 0,
      features: Array.isArray(plan.features) && plan.features.length ? [...plan.features] : ['']
    };
  } catch (e) {
    console.error("Failed to load plan", e);
    errorMessage.value = "Failed to load plan details.";
  } finally {
    loadingData.value = false;
  }
};

watch(() => props.show, (newVal) => {
  errorMessage.value = '';
  if (newVal) {
    if (props.planId) {
      fetchPlanData();
    } else {
      form.value = defaultForm();
    }
  }
});

const close = () => {
  emit('close');
};

const savePlan = async () => {
  submitting.value = true;
  errorMessage.value = '';
  
  // Clean empty features
  const cleanFeatures = form.value.features.filter(f => f && f.trim() !== '');

  const payload = {
    ...form.value,
    features: cleanFeatures
  };

  try {
    if (isEditing.value) {
      await axios.put(`/admin/api/subscription-plans/${props.planId}`, payload);
    } else {
      await axios.post('/admin/api/subscription-plans', payload);
    }
    emit('saved');
    close();
  } catch (e) {
    console.error("Failed to save plan", e);
    errorMessage.value = e.response?.data?.message || "Failed to save subscription plan.";
  } finally {
    submitting.value = false;
  }
};
</script>
