<template>
  <div class="space-y-6">
    <!-- Header & Valuation KPI Banner -->
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-sm border border-slate-200 dark:border-[#2E2E2E] p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-3">
            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4m-4 0H7m4 0v10" />
              </svg>
            </span>
            Fixed Assets & Infrastructure Registry
          </h1>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 font-medium">
            Track business assets with live depreciation & automatic double-entry accounting journal entries.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="openModal()"
            class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            Register New Fixed Asset
          </button>
        </div>
      </div>

      <!-- Financial Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-[#2E2E2E]">
        <div class="bg-slate-50 dark:bg-[#252525] p-4 rounded-xl border border-slate-200/80 dark:border-[#2E2E2E]">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Active Assets</span>
          <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 mt-1">{{ summary.total_assets_count || 0 }} items</div>
        </div>

        <div class="bg-slate-50 dark:bg-[#252525] p-4 rounded-xl border border-slate-200/80 dark:border-[#2E2E2E]">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Original Cost</span>
          <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 mt-1">${{ formatMoney(summary.total_purchase_cost) }}</div>
        </div>

        <div class="bg-slate-50 dark:bg-[#252525] p-4 rounded-xl border border-slate-200/80 dark:border-[#2E2E2E]">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Accumulated Depreciation</span>
          <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">-${{ formatMoney(summary.total_accumulated_depreciation) }}</div>
        </div>

        <div class="bg-emerald-50 dark:bg-emerald-950/20 p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/40">
          <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Net Current Valuation</span>
          <div class="text-2xl font-extrabold text-emerald-800 dark:text-emerald-300 mt-1">${{ formatMoney(summary.total_current_valuation) }}</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-sm border border-slate-200 dark:border-[#2E2E2E] p-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Search Asset</label>
          <input
            v-model="filters.search"
            @input="fetchAssets()"
            type="text"
            placeholder="Asset code, item name, location..."
            class="w-full py-2 px-3 bg-slate-50 dark:bg-[#252525] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:border-slate-400"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Status</label>
          <select
            v-model="filters.status"
            @change="fetchAssets()"
            class="w-full py-2 px-3 bg-slate-50 dark:bg-[#252525] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer"
          >
            <option value="">All Statuses</option>
            <option value="in_use">In Use</option>
            <option value="in_maintenance">In Maintenance</option>
            <option value="disposed">Disposed / Written Off</option>
          </select>
        </div>

        <div class="flex items-end">
          <button
            @click="resetFilters()"
            class="px-4 py-2 bg-slate-100 dark:bg-[#252525] hover:bg-slate-200 dark:hover:bg-[#2D2D2D] text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Reset Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Asset Table -->
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-sm border border-slate-200 dark:border-[#2E2E2E] overflow-hidden">
      <div v-if="loading" class="p-12 text-center text-slate-500 dark:text-slate-400">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-600 border-t-transparent mb-2"></div>
        <p class="text-xs font-semibold">Loading fixed asset registry...</p>
      </div>

      <div v-else-if="assets.length === 0" class="p-12 text-center">
        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4m-4 0H7m4 0v10" />
        </svg>
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No Fixed Assets Found</h3>
        <p class="text-slate-500 dark:text-slate-400 text-xs mt-1">Register shop furniture, fridges, ovens, and equipment to track true business valuation.</p>
        <button
          @click="openModal()"
          class="mt-4 px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 text-white text-xs font-bold rounded-xl cursor-pointer"
        >
          Register First Fixed Asset
        </button>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 dark:bg-[#252525] border-b border-slate-200 dark:border-[#2E2E2E] text-[10px] font-bold text-slate-400 uppercase tracking-wider">
              <th class="py-3.5 px-4">Code / Item Name</th>
              <th class="py-3.5 px-4">Purchase Cost</th>
              <th class="py-3.5 px-4">Useful Life</th>
              <th class="py-3.5 px-4">Depreciation</th>
              <th class="py-3.5 px-4">Current Valuation</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E] text-xs">
            <tr v-for="asset in assets" :key="asset.id" class="hover:bg-slate-50/50 dark:hover:bg-[#2D2D2D]/60 transition">
              <td class="py-3.5 px-4">
                <div class="font-bold text-slate-900 dark:text-slate-100">{{ asset.asset_name }}</div>
                <div class="text-[11px] font-mono text-slate-400">{{ asset.asset_code }} <span v-if="asset.location" class="font-sans text-slate-400">• {{ asset.location }}</span></div>
              </td>

              <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-slate-100">
                ${{ formatMoney(asset.purchase_cost) }}
              </td>

              <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-medium">
                {{ asset.useful_life_years }} Years ({{ asset.depreciation_method.replace('_', ' ') }})
              </td>

              <td class="py-3.5 px-4 font-extrabold text-rose-600 dark:text-rose-400">
                -${{ formatMoney(asset.accumulated_depreciation) }}
              </td>

              <td class="py-3.5 px-4 font-extrabold text-emerald-600 dark:text-emerald-400">
                ${{ formatMoney(asset.current_valuation) }}
              </td>

              <td class="py-3.5 px-4">
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold"
                  :class="getStatusBadgeClass(asset.status)"
                >
                  {{ formatStatus(asset.status) }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-right space-x-1">
                <button
                  @click="editAsset(asset)"
                  class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition cursor-pointer"
                  title="Edit Asset"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  @click="deleteAsset(asset)"
                  class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition cursor-pointer"
                  title="Delete Asset"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Register / Edit Asset Modal (TELEPORTED TO BODY TO COVER SIDEBAR OVERLAY) -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200 dark:border-zinc-800">
          
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between bg-slate-50 dark:bg-zinc-900">
            <div>
              <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ isEditing ? 'Edit Fixed Asset' : 'Register New Fixed Asset' }}</h2>
              <p class="text-xs text-slate-500 dark:text-zinc-400">Select fixed asset item and record depreciation parameters.</p>
            </div>
            <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
          </div>

          <!-- Modal Form Body -->
          <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
            <!-- Select Item (Fixed Assets Search Dropdown) -->
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Select Item * (Fixed Asset)</label>
              <SystemSelect
                v-model="form.product_id"
                :options="productOptions"
                placeholder="Search & Select Fixed Asset Item"
              />
            </div>

            <!-- Cost & Purchase Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Purchase Cost * ($)</label>
                <input
                  v-model.number="form.purchase_cost"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Purchase Date *</label>
                <input
                  v-model="form.purchase_date"
                  type="date"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>
            </div>

            <!-- Useful Life & Salvage Value -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Useful Life (Years) *</label>
                <input
                  v-model.number="form.useful_life_years"
                  type="number"
                  min="1"
                  max="50"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Salvage / Residual Value ($)</label>
                <input
                  v-model.number="form.salvage_value"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>
            </div>

            <!-- Depreciation Method & Location -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Depreciation Method</label>
                <select
                  v-model="form.depreciation_method"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium cursor-pointer"
                >
                  <option value="straight_line">Straight Line</option>
                  <option value="declining_balance">Declining Balance (Double)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Location / Department</label>
                <input
                  v-model="form.location"
                  type="text"
                  placeholder="e.g. Main Bakery Counter, Kitchen Floor"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>
            </div>

            <!-- Status & Serial Number -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Status</label>
                <select
                  v-model="form.status"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium cursor-pointer"
                >
                  <option value="active">Active (In Use)</option>
                  <option value="in_maintenance">In Maintenance</option>
                  <option value="disposed">Disposed / Written Off</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Serial / Model Number</label>
                <input
                  v-model="form.serial_number"
                  type="text"
                  placeholder="Optional serial #"
                  class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
                />
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">Notes / Warranty Details</label>
              <textarea
                v-model="form.notes"
                rows="2"
                placeholder="Supplier contact, warranty period..."
                class="w-full py-2 px-3 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-400 font-medium"
              ></textarea>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 flex items-center justify-end gap-3">
            <button @click="closeModal()" type="button" class="px-4 py-2 bg-slate-200 dark:bg-zinc-800 hover:bg-slate-300 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 text-sm font-semibold rounded-xl transition cursor-pointer">Cancel</button>
            <button @click="saveAsset()" :disabled="saving" type="button" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white font-bold text-sm rounded-xl shadow-xs transition disabled:opacity-50 cursor-pointer">
              {{ saving ? 'Processing...' : (isEditing ? 'Update Asset' : 'Register Asset') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import SystemSelect from '@/components/common/SystemSelect.vue';

const assets = ref([]);
const fixedAssetProducts = ref([]);

const summary = ref({
  total_assets_count: 0,
  total_purchase_cost: 0,
  total_accumulated_depreciation: 0,
  total_current_valuation: 0,
});

const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const filters = ref({
  search: '',
  status: '',
});

const form = ref({
  product_id: null,
  asset_name: '',
  purchase_cost: 1000,
  purchase_date: new Date().toISOString().substring(0, 10),
  useful_life_years: 5,
  salvage_value: 0,
  depreciation_method: 'straight_line',
  location: '',
  serial_number: '',
  status: 'active',
  notes: '',
});

const productOptions = computed(() => {
  return fixedAssetProducts.value.map(p => ({
    label: `${p.name} ${p.sku ? `(SKU: ${p.sku})` : ''}`,
    value: p.id
  }));
});

watch(() => form.value.product_id, (newProdId) => {
  if (newProdId) {
    const selected = fixedAssetProducts.value.find(p => p.id === newProdId);
    if (selected) {
      form.value.asset_name = selected.name;
      if (selected.cost_price > 0 && (!form.value.purchase_cost || form.value.purchase_cost === 1000)) {
        form.value.purchase_cost = parseFloat(selected.cost_price);
      }
    }
  }
});

onMounted(() => {
  fetchAssets();
  fetchSummary();
  fetchFixedAssetProducts();
});

const fetchFixedAssetProducts = async () => {
  try {
    const res = await axios.get('/api/products', { params: { item_type: 'fixed_asset', per_page: 500 } });
    fixedAssetProducts.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to fetch fixed asset products:', err);
  }
};

const fetchAssets = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/assets', { params: filters.value });
    assets.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to fetch assets:', err);
  } finally {
    loading.value = false;
  }
};

const fetchSummary = async () => {
  try {
    const res = await axios.get('/api/assets/summary');
    summary.value = res.data;
  } catch (err) {
    console.error('Failed to fetch asset summary:', err);
  }
};

const resetFilters = () => {
  filters.value = { search: '', status: '' };
  fetchAssets();
};

const openModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    product_id: fixedAssetProducts.value.length > 0 ? fixedAssetProducts.value[0].id : null,
    asset_name: fixedAssetProducts.value.length > 0 ? fixedAssetProducts.value[0].name : '',
    purchase_cost: fixedAssetProducts.value.length > 0 && fixedAssetProducts.value[0].cost_price > 0 
      ? parseFloat(fixedAssetProducts.value[0].cost_price) 
      : 1000,
    purchase_date: new Date().toISOString().substring(0, 10),
    useful_life_years: 5,
    salvage_value: 0,
    depreciation_method: 'straight_line',
    location: '',
    serial_number: '',
    status: 'active',
    notes: '',
  };
  showModal.value = true;
};

const editAsset = (asset) => {
  isEditing.value = true;
  editingId.value = asset.id;
  form.value = {
    product_id: asset.product_id,
    asset_name: asset.asset_name,
    purchase_cost: parseFloat(asset.purchase_cost),
    purchase_date: asset.purchase_date ? asset.purchase_date.substring(0, 10) : '',
    useful_life_years: asset.useful_life_years,
    salvage_value: parseFloat(asset.salvage_value || 0),
    depreciation_method: asset.depreciation_method,
    location: asset.location,
    serial_number: asset.serial_number,
    status: asset.status,
    notes: asset.notes,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveAsset = async () => {
  if (!form.value.product_id || !form.value.purchase_cost || !form.value.purchase_date) {
    alert('Please select a fixed asset item, purchase cost and date.');
    return;
  }

  saving.value = true;
  try {
    if (isEditing.value) {
      await axios.put(`/api/assets/${editingId.value}`, form.value);
    } else {
      await axios.post('/api/assets', form.value);
    }
    closeModal();
    fetchAssets();
    fetchSummary();
  } catch (err) {
    console.error('Failed to save asset:', err);
    alert(err.response?.data?.message || 'Error saving asset');
  } finally {
    saving.value = false;
  }
};

const deleteAsset = async (asset) => {
  if (!confirm(`Are you sure you want to delete fixed asset "${asset.asset_name}"?`)) return;

  try {
    await axios.delete(`/api/assets/${asset.id}`);
    fetchAssets();
    fetchSummary();
  } catch (err) {
    console.error('Failed to delete asset:', err);
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'in_use': case 'active': return 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50';
    case 'in_maintenance': return 'bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50';
    case 'disposed': return 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-zinc-700';
    default: return 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-zinc-700';
  }
};

const formatStatus = (status) => {
  if (!status) return 'In Use';
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatMoney = (val) => parseFloat(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
