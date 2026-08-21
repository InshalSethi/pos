<template>
  <div class="space-y-6">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
      <div>
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i class="fas fa-tags"></i>
          </div>
          <div>
            <h1 class="text-xl font-black text-zinc-950 dark:text-white tracking-tight">Subscription Plans Management</h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold mt-0.5">
              Manage subscription pricing, trial periods, company limits, and user limits.
            </p>
          </div>
        </div>
      </div>

      <button
        @click="openCreateModal"
        class="px-5 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer shrink-0"
      >
        <i class="fas fa-plus mr-2"></i> Add Subscription Plan
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
            placeholder="Search plans..."
            class="w-full pl-9 pr-4 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
          />
        </div>

        <!-- Right: Controls -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
          
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
              <th class="py-3.5 px-6 w-16 text-center">Order</th>
              <th class="py-3.5 px-6">Plan Name</th>
              <th class="py-3.5 px-6">Pricing</th>
              <th class="py-3.5 px-6">Companies</th>
              <th class="py-3.5 px-6">Users / Co</th>
              <th class="py-3.5 px-6 text-center">Popular</th>
              <th class="py-3.5 px-6 text-center">Status</th>
              <th class="py-3.5 px-6 text-right">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-xs font-semibold">
            <tr v-if="loading && items.length === 0">
              <td colspan="8" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-circle-notch fa-spin text-2xl mb-2 text-black dark:text-white"></i>
                <p class="text-xs font-bold uppercase tracking-wider">Loading subscription plans...</p>
              </td>
            </tr>

            <tr v-else-if="!loading && items.length === 0">
              <td colspan="8" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                <i class="fas fa-folder-open text-3xl mb-3 text-zinc-300 dark:text-zinc-700"></i>
                <p class="text-xs font-bold uppercase tracking-wider">No subscription plans found</p>
              </td>
            </tr>

            <tr 
              v-else 
              v-for="item in items" 
              :key="item.id"
              class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <!-- Order -->
              <td class="py-4 px-6 text-center font-black text-zinc-500">
                {{ item.sort_order }}
              </td>

              <!-- Name & Badges -->
              <td class="py-4 px-6">
                <div class="flex items-center space-x-2">
                  <span class="font-extrabold text-zinc-950 dark:text-white text-sm tracking-tight">
                    {{ item.name }}
                  </span>
                  <span v-if="item.is_custom" class="px-2 py-0.5 text-[10px] font-black uppercase rounded-md bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                    Custom
                  </span>
                  <span v-if="item.trial_days > 0" class="px-2 py-0.5 text-[10px] font-black uppercase rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    {{ item.trial_days }}d Trial
                  </span>
                </div>
                <p class="text-[11px] text-zinc-400 truncate max-w-xs mt-0.5">{{ item.description }}</p>
              </td>

              <!-- Pricing -->
              <td class="py-4 px-6 font-mono font-extrabold text-zinc-800 dark:text-zinc-200">
                <div v-if="item.is_custom" class="text-xs text-zinc-500">Contact Sales</div>
                <div v-else-if="item.monthly_price == 0" class="text-emerald-600 dark:text-emerald-400 font-extrabold">Free</div>
                <div v-else>
                  <div>${{ item.monthly_price }} <span class="text-[10px] font-normal text-zinc-400">/ mo</span></div>
                  <div class="text-[10px] text-zinc-400 font-normal">${{ item.yearly_price }} / yr</div>
                </div>
              </td>

              <!-- Companies -->
              <td class="py-4 px-6">
                <span class="px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 font-black rounded-lg text-zinc-900 dark:text-zinc-100 text-xs">
                  {{ item.max_companies }} {{ item.max_companies === 1 ? 'Company' : 'Companies' }}
                </span>
              </td>

              <!-- Users per Company -->
              <td class="py-4 px-6">
                <span class="px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 font-black rounded-lg text-zinc-900 dark:text-zinc-100 text-xs">
                  {{ item.max_users_per_company }} {{ item.max_users_per_company === 1 ? 'User' : 'Users' }}
                </span>
              </td>

              <!-- Popular -->
              <td class="py-4 px-6 text-center">
                <span 
                  v-if="item.is_popular" 
                  class="px-2.5 py-1 text-[10px] font-black uppercase rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                >
                  Popular
                </span>
                <span v-else class="text-zinc-400 text-xs">-</span>
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
                  title="Edit Plan"
                >
                  <i class="fas fa-edit text-xs"></i>
                </button>

                <button
                  @click="openDeleteModal(item)"
                  class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:hover:bg-rose-900 dark:text-rose-400 transition-all cursor-pointer inline-flex items-center justify-center"
                  title="Delete Plan"
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

    <!-- Modals -->
    <SubscriptionPlanModal
      :show="showModal"
      :plan-id="selectedPlanId"
      @close="closeModal"
      @saved="fetchData"
    />

    <SubscriptionPlanDeleteModal
      :show="showDeleteModal"
      :plan-item="selectedPlanItem"
      @close="closeDeleteModal"
      @deleted="fetchData"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SubscriptionPlanModal from './SubscriptionPlanModal.vue';
import SubscriptionPlanDeleteModal from './SubscriptionPlanDeleteModal.vue';

const items = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all');
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
const selectedPlanId = ref(null);

const showDeleteModal = ref(false);
const selectedPlanItem = ref(null);

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
    const { data } = await axios.get('/admin/api/subscription-plans', {
      params: {
        page: currentPage.value,
        search: searchQuery.value,
        status: statusFilter.value,
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
    console.error("Failed to fetch subscription plans datatable", e);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  selectedPlanId.value = null;
  showModal.value = true;
};

const openEditModal = (item) => {
  selectedPlanId.value = item.id;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedPlanId.value = null;
};

const openDeleteModal = (item) => {
  selectedPlanItem.value = item;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedPlanItem.value = null;
};

const toggleStatus = async (item) => {
  const newStatus = !item.is_active;
  item.is_active = newStatus;
  try {
    await axios.put(`/admin/api/subscription-plans/${item.id}`, {
      ...item,
      is_active: newStatus
    });
  } catch (e) {
    item.is_active = !newStatus;
    console.error("Failed to toggle status", e);
  }
};

onMounted(() => {
  fetchData();
});
</script>
