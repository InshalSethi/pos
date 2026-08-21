<template>
  <div class="space-y-6">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
      <div>
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i class="fas fa-ticket-alt"></i>
          </div>
          <div>
            <h1 class="text-xl font-black text-zinc-950 dark:text-white tracking-tight">Coupon Codes Management</h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold mt-0.5">
              Create and manage promotional discount coupons for subscription checkout.
            </p>
          </div>
        </div>
      </div>

      <button
        @click="openCreateModal"
        class="px-5 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer shrink-0"
      >
        <i class="fas fa-plus mr-2"></i> Add Coupon Code
      </button>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
      
      <!-- Filter & Per Page Controls -->
      <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col md:flex-row gap-3 justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
        
        <!-- Left: Search Box -->
        <div class="w-full md:w-80 relative">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
          <input
            type="text"
            v-model="searchQuery"
            @input="debouncedFetch"
            placeholder="Search coupon code or name..."
            class="w-full pl-9 pr-4 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
          />
        </div>

        <!-- Right: Controls -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
          
          <!-- Type Filter -->
          <div class="flex items-center space-x-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-500">Type:</span>
            <select
              v-model="typeFilter"
              @change="onFilterChange"
              class="px-3 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
            >
              <option value="all">All Types</option>
              <option value="percentage">Percentage (%)</option>
              <option value="fixed">Fixed ($)</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="flex items-center space-x-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-500">Status:</span>
            <select
              v-model="statusFilter"
              @change="onFilterChange"
              class="px-3 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
            >
              <option value="all">All Statuses</option>
              <option value="true">Active Only</option>
              <option value="false">Inactive Only</option>
            </select>
          </div>

          <!-- Refresh Button -->
          <button
            @click="fetchData"
            class="w-9 h-9 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer"
            title="Refresh"
          >
            <i class="fas fa-sync-alt text-xs" :class="{ 'fa-spin': loading }"></i>
          </button>
        </div>
      </div>

      <!-- Table View -->
      <div class="overflow-x-auto relative">
        <div v-if="loading" class="absolute top-0 left-0 right-0 h-1 bg-black/10 dark:bg-white/10 overflow-hidden z-10">
          <div class="h-full bg-black dark:bg-white animate-pulse w-full"></div>
        </div>

        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-100/60 dark:bg-zinc-950/60 text-[11px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 select-none">
              <th class="py-3.5 px-6">Coupon Code</th>
              <th class="py-3.5 px-6">Discount</th>
              <th class="py-3.5 px-6">Min Order</th>
              <th class="py-3.5 px-6 text-center">Uses</th>
              <th class="py-3.5 px-6">Expires At</th>
              <th class="py-3.5 px-6 text-center">Status</th>
              <th class="py-3.5 px-6 text-right">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-xs font-semibold">
            <tr v-if="loading && items.length === 0">
              <td colspan="7" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-circle-notch fa-spin text-2xl mb-2 text-black dark:text-white"></i>
                <p class="text-xs font-bold uppercase tracking-wider">Loading coupon codes...</p>
              </td>
            </tr>

            <tr v-else-if="!loading && items.length === 0">
              <td colspan="7" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-ticket-alt text-3xl mb-3 text-zinc-300 dark:text-zinc-700"></i>
                <p class="text-xs font-bold uppercase tracking-wider">No coupon codes found</p>
              </td>
            </tr>

            <tr 
              v-else 
              v-for="item in items" 
              :key="item.id"
              class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <!-- Code & Name -->
              <td class="py-4 px-6">
                <div class="flex items-center space-x-2">
                  <span class="font-black text-black dark:text-white font-mono text-sm px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg tracking-wider border border-zinc-200 dark:border-zinc-700">
                    {{ item.code }}
                  </span>
                </div>
                <p v-if="item.name" class="text-[11px] text-zinc-400 truncate max-w-xs mt-1">{{ item.name }}</p>
              </td>

              <!-- Discount Value -->
              <td class="py-4 px-6 font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                <span v-if="item.type === 'percentage'">{{ item.value }}% OFF</span>
                <span v-else>${{ item.value }} OFF</span>
                <span v-if="item.max_discount_amount > 0" class="block text-[10px] text-zinc-400 font-normal">Cap: ${{ item.max_discount_amount }}</span>
              </td>

              <!-- Min Spend -->
              <td class="py-4 px-6 font-mono font-semibold text-zinc-700 dark:text-zinc-300">
                <span v-if="item.min_order_amount > 0">${{ item.min_order_amount }}</span>
                <span v-else class="text-zinc-400 text-xs">No min</span>
              </td>

              <!-- Uses / Limit -->
              <td class="py-4 px-6 text-center font-mono">
                <span class="font-bold text-zinc-900 dark:text-white">{{ item.used_count }}</span>
                <span class="text-zinc-400"> / {{ item.usage_limit ? item.usage_limit : '∞' }}</span>
              </td>

              <!-- Expiration -->
              <td class="py-4 px-6 text-zinc-600 dark:text-zinc-400">
                <span v-if="item.expires_at" :class="{ 'text-rose-500 font-bold': isExpired(item.expires_at) }">
                  {{ formatDate(item.expires_at) }}
                </span>
                <span v-else class="text-zinc-400 text-xs">Never</span>
              </td>

              <!-- Status Toggle Switch -->
              <td class="py-4 px-6 text-center">
                <button
                  type="button"
                  @click="toggleStatus(item)"
                  class="relative inline-flex items-center cursor-pointer select-none"
                  :title="item.is_active ? 'Click to Disable' : 'Click to Enable'"
                >
                  <div 
                    class="block w-10 h-6 rounded-full transition-colors" 
                    :class="item.is_active ? 'bg-black dark:bg-white' : 'bg-zinc-300 dark:bg-zinc-700'"
                  ></div>
                  <div 
                    class="dot absolute left-0.5 top-0.5 w-5 h-5 rounded-full transition-transform shadow-xs pointer-events-none" 
                    :class="item.is_active ? 'translate-x-4 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-900'"
                  ></div>
                </button>
              </td>

              <!-- Actions -->
              <td class="py-4 px-6 text-right space-x-2">
                <button
                  @click="openEditModal(item)"
                  class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Edit Coupon"
                >
                  <i class="fas fa-edit text-xs"></i>
                </button>

                <button
                  @click="deleteCoupon(item)"
                  class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:hover:bg-rose-900 dark:text-rose-400 transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Delete Coupon"
                >
                  <i class="fas fa-trash-alt text-xs"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div 
        v-if="pagination.total > 0"
        class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-zinc-50/50 dark:bg-zinc-900/50 text-xs text-zinc-600 dark:text-zinc-400 font-semibold"
      >
        <div>
          Showing <span class="font-black text-zinc-900 dark:text-white">{{ pagination.from }}</span> to 
          <span class="font-black text-zinc-900 dark:text-white">{{ pagination.to }}</span> of 
          <span class="font-black text-zinc-900 dark:text-white">{{ pagination.total }}</span> entries
        </div>
      </div>

    </div>

    <!-- Coupon Modal -->
    <CouponModal
      :show="showModal"
      :coupon-id="selectedCouponId"
      @close="closeModal"
      @saved="fetchData"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import CouponModal from './CouponModal.vue';

const items = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all');
const typeFilter = ref('all');
const currentPage = ref(1);

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 15,
  total: 0,
  from: 0,
  to: 0
});

const showModal = ref(false);
const selectedCouponId = ref(null);

let debounceTimer = null;

const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    currentPage.value = 1;
    fetchData();
  }, 350);
};

const onFilterChange = () => {
  currentPage.value = 1;
  fetchData();
};

const fetchData = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/admin/api/coupons', {
      params: {
        page: currentPage.value,
        search: searchQuery.value,
        status: statusFilter.value,
        type: typeFilter.value,
      }
    });

    items.value = data.data || [];
    pagination.value = {
      currentPage: data.current_page || 1,
      lastPage: data.last_page || 1,
      perPage: data.per_page || 15,
      total: data.total || 0,
      from: data.from || 0,
      to: data.to || 0
    };
  } catch (e) {
    console.error("Failed to fetch coupons datatable", e);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  selectedCouponId.value = null;
  showModal.value = true;
};

const openEditModal = (item) => {
  selectedCouponId.value = item.id;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedCouponId.value = null;
};

const toggleStatus = async (item) => {
  const newStatus = !item.is_active;
  item.is_active = newStatus;
  try {
    await axios.put(`/admin/api/coupons/${item.id}`, {
      ...item,
      is_active: newStatus
    });
  } catch (e) {
    item.is_active = !newStatus;
    console.error("Failed to toggle coupon status", e);
  }
};

const deleteCoupon = async (item) => {
  if (confirm(`Are you sure you want to delete coupon code "${item.code}"?`)) {
    try {
      await axios.delete(`/admin/api/coupons/${item.id}`);
      fetchData();
    } catch (e) {
      alert("Failed to delete coupon.");
    }
  }
};

const isExpired = (dateString) => {
  if (!dateString) return false;
  const isoStr = typeof dateString === 'string' ? dateString.replace(' ', 'T') : dateString;
  return new Date(isoStr) < new Date();
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const isoStr = typeof dateString === 'string' ? dateString.replace(' ', 'T') : dateString;
  return new Date(isoStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

onMounted(() => {
  fetchData();
});
</script>
