<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 bg-slate-900/40 dark:bg-black/50 backdrop-blur-md overflow-y-auto h-full w-full z-[9999] flex items-center justify-center p-4 transition-all duration-300">
      <div class="relative mx-auto border border-slate-150 dark:border-zinc-800 w-full max-w-lg shadow-2xl rounded-xl bg-white dark:bg-zinc-900 text-left transition-all duration-300 flex flex-col max-h-[90vh]">
        
        <!-- Sleek Close Icon Button -->
        <button
          type="button"
          @click="$emit('close')"
          class="absolute top-5 right-5 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-55 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all cursor-pointer z-50"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div v-if="customer" class="flex flex-col flex-1 min-h-0">
          <!-- Header Area -->
          <div class="p-6 pb-4 border-b border-slate-100 dark:border-zinc-800 shrink-0 relative pr-12">
            <div class="flex items-center space-x-2">
              <h3 class="text-lg font-extrabold text-slate-800 dark:text-zinc-100 tracking-tight leading-none">{{ customer.name }}</h3>
              <span
                :class="customer.is_active ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold"
              >
                {{ customer.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-zinc-500 font-medium mt-1.5">
              Customer ID: #{{ customer.id }} &middot; Member since {{ formatDate(customer.created_at) }}
            </p>
          </div>

          <!-- Content Body -->
          <div class="flex-1 overflow-y-auto p-6 space-y-5 pr-4 custom-scrollbar">
            <!-- Personal Details -->
            <div>
              <h4 class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-3">Personal Details</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Email</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold break-all">{{ customer.email || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Phone</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.phone || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Mobile</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.mobile || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Date of Birth</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.date_of_birth ? formatDate(customer.date_of_birth) : '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Gender</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold capitalize">{{ customer.gender || '-' }}</span>
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
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.address || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">City</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.city || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">State</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.state || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Postal Code</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.postal_code || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Country</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.country || '-' }}</span>
                </div>
              </div>
            </div>

            <div class="border-t border-slate-100 dark:border-zinc-800/80"></div>

            <!-- Financial Details -->
            <div>
              <h4 class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-3">Financial Overview</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Tax Number</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">{{ customer.tax_number || '-' }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Credit Limit</span>
                  <span class="text-xs text-slate-700 dark:text-zinc-300 font-semibold">${{ formatNumber(customer.credit_limit) }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Total Purchases</span>
                  <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">${{ formatNumber(customer.total_purchases) }}</span>
                </div>
                <div>
                  <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Loyalty Points</span>
                  <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">{{ formatNumber(customer.loyalty_points) }}</span>
                </div>
              </div>
            </div>

            <!-- Notes (Optional) -->
            <div v-if="customer.notes">
              <div class="border-t border-slate-100 dark:border-zinc-800/80 my-4"></div>
              <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Notes</span>
              <p class="text-xs text-slate-600 dark:text-zinc-400 bg-slate-50 dark:bg-zinc-950 p-2.5 rounded-lg border border-slate-100 dark:border-zinc-800/40 leading-relaxed italic">
                {{ customer.notes }}
              </p>
            </div>
          </div>
        </div>

        <!-- Fallback loading state -->
        <div v-else class="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-zinc-500">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 dark:border-zinc-600 border-t-blue-600 mb-2"></div>
          <span class="text-xs">Loading customer details...</span>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'CustomerViewModalSimple',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    customer: {
      type: Object,
      default: null
    }
  },
  emits: ['close'],
  setup() {
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

    return {
      formatNumber,
      formatDate
    };
  }
};
</script>
