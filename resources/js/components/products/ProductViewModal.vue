<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/50 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200"
    style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
    @click.self="$emit('close')"
  >
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-4xl shadow-2xl rounded-2xl bg-white dark:bg-[#18181B] text-slate-800 dark:text-slate-100 overflow-hidden transition-all duration-300 z-10 max-h-[90vh] flex flex-col my-auto">
      
      <!-- Modal Header -->
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800/80 px-6 py-4 bg-slate-50/50 dark:bg-zinc-900/50">
        <div class="flex items-center gap-3">
          <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                {{ product?.type ? product.type.toUpperCase() : 'PRODUCT' }} DETAILS
              </span>
              <span v-if="product?.status === 'active'" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30">Active</span>
              <span v-else-if="product?.status === 'inactive'" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Inactive</span>
              <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30">Draft</span>
            </div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-snug">
              {{ product?.name || 'Loading details...' }}
            </h3>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            v-if="product"
            @click="$emit('edit', product); $emit('close');"
            type="button"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-900/40 transition-all cursor-pointer"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Edit Item</span>
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg transition-colors focus:outline-none cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto space-y-6 custom-scrollbar max-h-[calc(90vh-130px)]">
        <div v-if="loading" class="py-12 text-center text-slate-400 dark:text-slate-500 font-semibold text-sm italic">
          Loading item details...
        </div>

        <template v-else-if="product">
          <!-- 1. Top Section: Images & Key Metadata Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Product Image / Gallery Preview -->
            <div class="md:col-span-1 flex flex-col items-center">
              <div class="w-full aspect-square rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden bg-slate-50 dark:bg-zinc-900 flex items-center justify-center shadow-inner relative group">
                <img
                  v-if="productImages.length > 0"
                  :src="productImages[0]"
                  class="w-full h-full object-cover"
                />
                <div v-else class="flex flex-col items-center justify-center p-4 text-slate-300 dark:text-zinc-700">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="text-xs font-bold mt-1 uppercase tracking-wider text-slate-400 dark:text-zinc-600">No Image</span>
                </div>
              </div>

              <!-- Gallery Thumbnails (if multiple images) -->
              <div v-if="productImages.length > 1" class="flex items-center gap-2 mt-3 overflow-x-auto max-w-full py-1">
                <div
                  v-for="(img, idx) in productImages"
                  :key="idx"
                  class="w-12 h-12 rounded-lg border border-slate-200 dark:border-zinc-800 overflow-hidden shrink-0 shadow-xs"
                >
                  <img :src="img" class="w-full h-full object-cover" />
                </div>
              </div>
            </div>

            <!-- General Info Cards -->
            <div class="md:col-span-2 space-y-4">
              <!-- Badges Row -->
              <div class="flex flex-wrap items-center gap-2">
                <span v-if="product.sku" class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300 border border-slate-200 dark:border-zinc-700">
                  SKU: {{ product.sku }}
                </span>
                <span v-if="product.barcode" class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300 border border-slate-200 dark:border-zinc-700">
                  Barcode: {{ product.barcode }}
                </span>
                <span v-if="product.brand?.name || product.brand_name" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/30">
                  Brand: {{ product.brand?.name || product.brand_name }}
                </span>
                <span v-if="product.unit?.name || product.unit_of_measure" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-900/30">
                  Unit: {{ product.unit?.name || product.unit_of_measure }}
                </span>
              </div>

              <!-- Category Hierarchy -->
              <div class="p-3.5 bg-slate-50 dark:bg-zinc-900/60 rounded-xl border border-slate-100 dark:border-zinc-800/80">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Category Hierarchy</span>
                <div class="flex items-center gap-1.5 flex-wrap">
                  <template v-if="categoryHierarchy.length > 0">
                    <span
                      v-for="(catName, idx) in categoryHierarchy"
                      :key="idx"
                      class="inline-flex items-center text-xs font-semibold"
                    >
                      <span class="px-2 py-0.5 rounded-md bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-800 dark:text-slate-200">
                        {{ catName }}
                      </span>
                      <svg v-if="idx < categoryHierarchy.length - 1" class="w-3.5 h-3.5 text-slate-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                  </template>
                  <span v-else class="text-xs text-slate-400 italic">No category assigned</span>
                </div>
              </div>

              <!-- Tags -->
              <div v-if="parsedTags.length > 0">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Tags</span>
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="(tag, tidx) in parsedTags"
                    :key="tidx"
                    class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/30"
                  >
                    #{{ tag }}
                  </span>
                </div>
              </div>

              <!-- Service Details (if service) -->
              <div v-if="product.type === 'service' && (product.service_type || product.service_detail)" class="p-3 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/30">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-0.5">Service Details</span>
                <p class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                  <strong v-if="product.service_type">{{ product.service_type }}: </strong>
                  <span>{{ product.service_detail || 'N/A' }}</span>
                </p>
              </div>
            </div>
          </div>

          <!-- 2. Descriptions Section -->
          <div v-if="product.short_description || product.description" class="p-4 bg-slate-50 dark:bg-zinc-900/50 rounded-2xl border border-slate-100 dark:border-zinc-800/80 space-y-3">
            <div v-if="product.short_description">
              <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Short Description</span>
              <p class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-relaxed">
                {{ product.short_description }}
              </p>
            </div>
            <div v-if="product.description">
              <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Full Description</span>
              <div class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed prose dark:prose-invert max-w-none" v-html="product.description"></div>
            </div>
          </div>

          <!-- 3. Financials & Pricing Summary -->
          <div class="space-y-2">
            <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Pricing & Financials</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-800 text-center">
                <span class="block text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500">Cost / Purchase</span>
                <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">
                  {{ formatPrice(product.cost_price || 0) }}
                </span>
              </div>
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-800 text-center">
                <span class="block text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500">Retail / Selling</span>
                <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">
                  {{ formatPrice(product.selling_price || product.retail_price || 0) }}
                </span>
              </div>
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-800 text-center">
                <span class="block text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500">Wholesale</span>
                <span class="text-sm font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 block">
                  {{ formatPrice(product.wholesale_price || 0) }}
                </span>
              </div>
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-800 text-center">
                <span class="block text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500">Tax Rate</span>
                <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">
                  {{ product.tax_rate ? product.tax_rate + '%' : '0%' }}
                </span>
              </div>
            </div>
          </div>

          <!-- 4. Inventory & Stock Status -->
          <div class="space-y-2">
            <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Inventory & Stock</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-800 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Stock</span>
                <span class="text-sm font-black text-slate-900 dark:text-white">
                  {{ product.stock_quantity ?? 'N/A' }}
                </span>
              </div>
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-800 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Track Inventory</span>
                <span :class="product.track_inventory ? 'text-emerald-600 dark:text-emerald-400 font-bold text-xs' : 'text-slate-400 text-xs'">
                  {{ product.track_inventory ? 'Yes' : 'No' }}
                </span>
              </div>
              <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-800 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Returnable</span>
                <span :class="product.is_returnable ? 'text-emerald-600 dark:text-emerald-400 font-bold text-xs' : 'text-slate-400 text-xs'">
                  {{ product.is_returnable ? 'Yes' : 'No' }}
                </span>
              </div>
            </div>
          </div>

          <!-- 5. Product Variations (If Variant Product) -->
          <div v-if="product.variations && product.variations.length > 0" class="space-y-2">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Product Variations ({{ product.variations.length }})
              </h4>
            </div>
            <div class="border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-inner">
              <table class="w-full text-xs text-left">
                <thead class="bg-slate-100 dark:bg-zinc-900 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                  <tr>
                    <th class="px-3 py-2">Variant</th>
                    <th class="px-3 py-2">SKU</th>
                    <th class="px-3 py-2 text-right">Cost</th>
                    <th class="px-3 py-2 text-right">Retail</th>
                    <th class="px-3 py-2 text-right">Wholesale</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 bg-white dark:bg-zinc-950">
                  <tr v-for="variant in product.variations" :key="variant.id" class="hover:bg-slate-50 dark:hover:bg-zinc-900/50">
                    <td class="px-3 py-2 font-extrabold text-slate-800 dark:text-slate-200">
                      {{ variant.variation_name_string || variant.combination_key || 'Default Variant' }}
                    </td>
                    <td class="px-3 py-2 font-mono text-slate-500">{{ variant.sku || '-' }}</td>
                    <td class="px-3 py-2 text-right font-medium text-slate-700 dark:text-slate-300">
                      {{ formatPrice(variant.cost_price || 0) }}
                    </td>
                    <td class="px-3 py-2 text-right font-bold text-emerald-600 dark:text-emerald-400">
                      {{ formatPrice(variant.retail_price || variant.selling_price || 0) }}
                    </td>
                    <td class="px-3 py-2 text-right font-medium text-indigo-600 dark:text-indigo-400">
                      {{ formatPrice(variant.wholesale_price || 0) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-3 border-t border-slate-100 dark:border-zinc-800/80 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-end gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
        >
          Close
        </button>
        <button
          v-if="product"
          type="button"
          @click="$emit('edit', product); $emit('close');"
          class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm cursor-pointer"
        >
          Edit Item Details
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useCurrencyStore } from '../../stores/currency';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  product: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['close', 'edit']);

const currencyStore = useCurrencyStore();

const formatPrice = (val) => {
  if (currencyStore && typeof currencyStore.formatPrice === 'function') {
    return currencyStore.formatPrice(val);
  }
  return '$' + Number(val || 0).toFixed(2);
};

const productImages = computed(() => {
  if (!props.product) return [];
  let imgs = [];
  if (props.product.images) {
    if (Array.isArray(props.product.images)) {
      imgs = props.product.images;
    } else if (typeof props.product.images === 'string') {
      try {
        const parsed = JSON.parse(props.product.images);
        if (Array.isArray(parsed)) imgs = parsed;
      } catch (e) {
        imgs = [props.product.images];
      }
    }
  }
  if (imgs.length === 0) {
    const single = props.product.image_path || props.product.image || props.product.thumbnail;
    if (single) imgs.push(single);
  }
  return imgs;
});

const categoryHierarchy = computed(() => {
  if (!props.product || !props.product.category) return [];
  const chain = [];
  let curr = props.product.category;
  while (curr) {
    chain.unshift(curr.name);
    curr = curr.parent;
  }
  return chain;
});

const parsedTags = computed(() => {
  if (!props.product || !props.product.tags) return [];
  let tagsArray = [];
  if (Array.isArray(props.product.tags)) {
    tagsArray = props.product.tags;
  } else if (typeof props.product.tags === 'string') {
    try {
      const parsed = JSON.parse(props.product.tags);
      tagsArray = Array.isArray(parsed) ? parsed : [props.product.tags];
    } catch (e) {
      tagsArray = props.product.tags.split(',').map(t => t.trim()).filter(Boolean);
    }
  }
  return tagsArray.map(tag => (typeof tag === 'object' ? tag.name || tag.label || '' : String(tag))).filter(Boolean);
});
</script>
