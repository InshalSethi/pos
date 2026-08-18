<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200"
      style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
      @click.self="$emit('close')"
    >
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-hidden z-10">
        
        <!-- Sleek Close Icon Button -->
        <button
          type="button"
          @click="$emit('close')"
          class="absolute top-5 right-5 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all cursor-pointer z-50"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div v-if="supplierData" class="flex flex-col flex-1 min-h-0">
          <!-- Header Area -->
          <div class="p-6 pb-4 border-b border-slate-100 dark:border-zinc-800 shrink-0 relative pr-12">
            <div class="flex items-center space-x-4">
              <img
                v-if="supplierData.profile_image"
                :src="getStorageUrl(supplierData.profile_image)"
                class="w-14 h-14 rounded-full object-cover ring-2 ring-blue-500/30 shadow-md cursor-pointer shrink-0"
                @click="downloadFile(getStorageUrl(supplierData.profile_image), supplierData.name + '_photo.jpg')"
                title="Click to download photo"
                alt="Profile Photo"
              />
              <div v-else class="w-14 h-14 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-500 dark:text-zinc-400 font-bold text-lg ring-2 ring-slate-200 dark:ring-zinc-700 shrink-0">
                {{ supplierData.name ? supplierData.name.charAt(0).toUpperCase() : 'S' }}
              </div>

              <div>
                <div class="flex items-center space-x-2">
                  <h3 class="text-lg font-extrabold text-slate-800 dark:text-zinc-100 tracking-tight leading-none">{{ supplierData.name }}</h3>
                  <span
                    :class="supplierData.is_active ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold"
                  >
                    {{ supplierData.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                <p v-if="supplierData.company_name" class="text-xs font-semibold text-slate-500 dark:text-zinc-400 mt-1">
                  {{ supplierData.company_name }}
                </p>
                <p class="text-[10px] text-slate-400 dark:text-zinc-500 font-medium mt-1">
                  Supplier ID: #{{ supplierData.id }} &middot; Member since {{ formatDate(supplierData.created_at) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Content Body -->
          <div class="flex-1 overflow-y-auto p-6 space-y-5 pr-4 custom-scrollbar">
            <!-- Company & Contact Details -->
            <div>
              <h4 class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-3">Company & Contact Info</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Email</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold break-all">{{ supplierData.email || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Phone</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.phone || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Mobile</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.mobile || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Tax Number</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.tax_number || '-' }}</span>
                </div>
                <div class="col-span-2" v-if="supplierData.website">
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Website</span>
                  <a :href="supplierData.website" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline break-all">{{ supplierData.website }}</a>
                </div>
              </div>
            </div>

            <div class="border-t border-slate-100 dark:border-zinc-800/80"></div>

            <!-- Address / Location Details -->
            <div>
              <h4 class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-3">Address & Location</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Address</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.address || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">City</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.city || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">State</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.state || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Postal Code</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.postal_code || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Country</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ supplierData.country || '-' }}</span>
                </div>
              </div>
            </div>

            <div class="border-t border-slate-100 dark:border-zinc-800/80"></div>

            <!-- Financial Details -->
            <div>
              <h4 class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-3">Financial Overview</h4>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Credit Limit</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ currencySymbol }}{{ formatNumber(supplierData.credit_limit) }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Advance Balance</span>
                  <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">{{ currencySymbol }}{{ formatNumber(supplierData.advance_balance) }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Payment Terms</span>
                  <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">{{ supplierData.payment_terms_days || 30 }} Days</span>
                </div>
              </div>
            </div>

            <!-- Notes Section -->
            <div v-if="supplierData.notes">
              <div class="border-t border-slate-100 dark:border-zinc-800/80 my-4"></div>
              <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Notes</span>
              <p class="text-xs text-slate-600 dark:text-zinc-400 bg-slate-50 dark:bg-zinc-900/60 p-3 rounded-lg border border-slate-100 dark:border-zinc-800/60 leading-relaxed">{{ supplierData.notes }}</p>
            </div>

            <!-- Attachments -->
            <div v-if="getAttachmentItems(supplierData).length > 0">
              <div class="border-t border-slate-100 dark:border-zinc-800/80 my-4"></div>
              <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-2">Attachments ({{ getAttachmentItems(supplierData).length }})</span>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div
                  v-for="att in getAttachmentItems(supplierData)"
                  :key="att.url"
                  class="p-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-lg flex items-center justify-between"
                >
                  <div class="flex items-center space-x-2 truncate">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300 truncate">{{ att.filename }}</span>
                  </div>
                  <button
                    @click="downloadFile(att.url, att.filename)"
                    class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-medium cursor-pointer transition-colors shrink-0 flex items-center space-x-1"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Fallback loading state -->
        <div v-else class="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-zinc-500">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 dark:border-zinc-600 border-t-blue-600 mb-2"></div>
          <span class="text-xs">Loading supplier details...</span>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, watch, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { downloadAttachmentFile } from '@/utils/downloadAttachment';
import api from '@/services/api';

export default {
  name: 'SupplierViewModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    supplier: {
      type: Object,
      default: null
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const authStore = useAuthStore();
    const currencyStore = useCurrencyStore();

    const currencySymbol = computed(() => {
      return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$';
    });

    const loading = ref(false);
    const supplierData = ref(props.supplier || null);

    const getStorageUrl = (path) => {
      if (!path) return '';
      if (path.startsWith('http') || path.startsWith('/storage/')) return path;
      return '/storage/' + path;
    };

    const getAttachmentItems = (supplier) => {
      if (!supplier) return [];
      if (supplier.attachments_urls && supplier.attachments_urls.length > 0) {
        return supplier.attachments_urls;
      }
      if (!supplier.attachments || !Array.isArray(supplier.attachments)) return [];
      return supplier.attachments.map((path, idx) => ({
        index: idx,
        url: path.startsWith('http') || path.startsWith('/storage/') ? path : '/storage/' + path,
        path: path,
        filename: path.split('/').pop() || `Attachment ${idx + 1}`
      }));
    };

    const downloadFile = (url, filename) => {
      downloadAttachmentFile(url, filename || 'attachment');
    };

    const loadSupplierDetails = async () => {
      if (!props.supplier) {
        supplierData.value = null;
        return;
      }

      // Always start with props data as fallback
      supplierData.value = props.supplier;

      // If we have an ID, try to load fresh data from API
      if (props.supplier.id) {
        loading.value = true;

        try {
          const response = await api.get(`/suppliers/${props.supplier.id}`);
          supplierData.value = response.data;
        } catch (error) {
          console.error('Error loading supplier details from API:', error);
          // Keep using props data as fallback
          supplierData.value = props.supplier;
        } finally {
          loading.value = false;
        }
      }
    };

    const formatNumber = (value) => {
      return new Intl.NumberFormat().format(value || 0);
    };

    const formatDate = (dateString) => {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        loadSupplierDetails();
      }
    }, { immediate: true });

    // Watch for supplier prop changes to reload data
    watch(() => props.supplier, (newSupplier) => {
      if (newSupplier) {
        supplierData.value = newSupplier;
        if (props.show) {
          loadSupplierDetails();
        }
      }
    }, { immediate: true, deep: true });

    onMounted(() => {
      if (props.supplier) {
        supplierData.value = props.supplier;
        loadSupplierDetails();
      }
    });

    return {
      currencySymbol,
      loading,
      supplierData,
      formatNumber,
      formatDate,
      getStorageUrl,
      getAttachmentItems,
      downloadFile
    };
  }
};
</script>
