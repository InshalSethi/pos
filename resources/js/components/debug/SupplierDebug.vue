<template>
  <div class="supplier-debug p-4">
    <h2 class="text-2xl font-bold mb-4">Supplier Module Debug</h2>
    
    <!-- Authentication Status -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="text-lg font-semibold mb-2">Authentication Status</h3>
      <p><strong>Is Authenticated:</strong> {{ authStore.isAuthenticated }}</p>
      <p><strong>User:</strong> {{ authStore.user?.name || 'Not logged in' }}</p>
      <p><strong>Token:</strong> {{ authStore.token ? 'Present' : 'Missing' }}</p>
      <p><strong>Permissions:</strong> {{ authStore.permissions.join(', ') || 'None' }}</p>
    </div>

    <!-- API Test -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="text-lg font-semibold mb-2">API Test</h3>
      <button @click="testSuppliersAPI" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">
        Test Suppliers API
      </button>
      <button @click="testSupplierDetails" class="bg-green-500 text-white px-4 py-2 rounded">
        Test Supplier Details
      </button>
      
      <div v-if="apiResult" class="mt-4 p-3 bg-gray-100 rounded">
        <h4 class="font-semibold">API Result:</h4>
        <pre class="text-sm">{{ JSON.stringify(apiResult, null, 2) }}</pre>
      </div>
      
      <div v-if="apiError" class="mt-4 p-3 bg-red-100 rounded">
        <h4 class="font-semibold text-red-700">API Error:</h4>
        <pre class="text-sm text-red-600">{{ JSON.stringify(apiError, null, 2) }}</pre>
      </div>
    </div>

    <!-- Supplier Data Test -->
    <div class="mb-6 p-4 border rounded">
      <h3 class="text-lg font-semibold mb-2">Supplier Data Test</h3>
      <div v-if="suppliers.length > 0">
        <p><strong>Suppliers loaded:</strong> {{ suppliers.length }}</p>
        <div class="mt-2">
          <h4 class="font-semibold">First Supplier:</h4>
          <pre class="text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(suppliers[0], null, 2) }}</pre>
        </div>
        
        <div class="mt-4">
          <button @click="testViewModal" class="bg-purple-500 text-white px-4 py-2 rounded mr-2">
            Test View Modal
          </button>
          <button @click="testEditModal" class="bg-orange-500 text-white px-4 py-2 rounded">
            Test Edit Modal
          </button>
        </div>
      </div>
      <div v-else>
        <p class="text-red-600">No suppliers loaded</p>
      </div>
    </div>

    <!-- Form Values Display -->
    <div v-if="showEditModal" class="mb-6 p-4 border rounded bg-yellow-50">
      <h3 class="text-lg font-semibold mb-2">Current Form State (Debug)</h3>
      <div class="text-sm">
        <p><strong>Selected Supplier:</strong></p>
        <pre class="bg-gray-100 p-2 rounded text-xs">{{ JSON.stringify(selectedSupplier, null, 2) }}</pre>
      </div>
    </div>

    <!-- Modal Test -->
    <SupplierViewModal
      v-if="showViewModal"
      :show="showViewModal"
      :supplier="selectedSupplier"
      @close="closeViewModal"
    />

    <SupplierModal
      v-if="showEditModal"
      :show="showEditModal"
      :supplier="selectedSupplier"
      :is-edit="true"
      @close="closeEditModal"
      @saved="handleSupplierSaved"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import SupplierViewModal from '@/components/suppliers/SupplierViewModal.vue';
import SupplierModal from '@/components/suppliers/SupplierModal.vue';

export default {
  name: 'SupplierDebug',
  components: {
    SupplierViewModal,
    SupplierModal
  },
  setup() {
    const authStore = useAuthStore();
    
    const apiResult = ref(null);
    const apiError = ref(null);
    const suppliers = ref([]);
    const selectedSupplier = ref(null);
    const showViewModal = ref(false);
    const showEditModal = ref(false);

    const testSuppliersAPI = async () => {
      apiResult.value = null;
      apiError.value = null;
      
      try {
        console.log('Testing suppliers API...');
        const response = await api.get('/suppliers');
        console.log('Suppliers API response:', response);
        
        apiResult.value = {
          status: response.status,
          data: response.data,
          headers: response.headers
        };
        
        suppliers.value = response.data.data || [];
        
      } catch (error) {
        console.error('Suppliers API error:', error);
        apiError.value = {
          status: error.response?.status,
          message: error.response?.data?.message || error.message,
          data: error.response?.data
        };
      }
    };

    const testSupplierDetails = async () => {
      if (suppliers.value.length === 0) {
        apiError.value = { message: 'No suppliers available. Run suppliers API test first.' };
        return;
      }

      apiResult.value = null;
      apiError.value = null;
      
      try {
        const supplierId = suppliers.value[0].id;
        console.log('Testing supplier details API for ID:', supplierId);
        const response = await api.get(`/suppliers/${supplierId}`);
        console.log('Supplier details API response:', response);
        
        apiResult.value = {
          status: response.status,
          data: response.data
        };
        
      } catch (error) {
        console.error('Supplier details API error:', error);
        apiError.value = {
          status: error.response?.status,
          message: error.response?.data?.message || error.message,
          data: error.response?.data
        };
      }
    };

    const testViewModal = () => {
      if (suppliers.value.length > 0) {
        selectedSupplier.value = suppliers.value[0];
        showViewModal.value = true;
        console.log('Opening view modal with supplier:', selectedSupplier.value);
      }
    };

    const testEditModal = () => {
      if (suppliers.value.length > 0) {
        const supplier = suppliers.value[0];
        console.log('=== Testing Edit Modal ===');
        console.log('Original supplier:', supplier);
        console.log('Supplier keys:', Object.keys(supplier));

        selectedSupplier.value = { ...supplier };
        console.log('Copied supplier:', selectedSupplier.value);
        console.log('Copied supplier keys:', Object.keys(selectedSupplier.value));

        showEditModal.value = true;
        console.log('Edit modal opened');
      }
    };

    const closeViewModal = () => {
      showViewModal.value = false;
      selectedSupplier.value = null;
    };

    const closeEditModal = () => {
      showEditModal.value = false;
      selectedSupplier.value = null;
    };

    const handleSupplierSaved = () => {
      closeEditModal();
      testSuppliersAPI(); // Reload suppliers
    };

    onMounted(() => {
      console.log('SupplierDebug component mounted');
      console.log('Auth store:', authStore);
    });

    return {
      authStore,
      apiResult,
      apiError,
      suppliers,
      selectedSupplier,
      showViewModal,
      showEditModal,
      testSuppliersAPI,
      testSupplierDetails,
      testViewModal,
      testEditModal,
      closeViewModal,
      closeEditModal,
      handleSupplierSaved
    };
  }
};
</script>

<style scoped>
.supplier-debug {
  max-width: 1200px;
  margin: 0 auto;
}

pre {
  max-height: 300px;
  overflow-y: auto;
}
</style>
