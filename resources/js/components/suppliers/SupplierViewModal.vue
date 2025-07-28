<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h4>Supplier Details</h4>
        <button @click="closeModal" class="btn-close">&times;</button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>

        <div v-else-if="!loading && supplierData">
          <!-- Supplier Summary -->
          <div class="supplier-summary">
            <div class="row">
              <div class="col-md-8">
                <h3>{{ supplierData.name }}</h3>
                <p class="text-muted">{{ supplierData.company_name || 'No company name' }}</p>
                <p class="text-muted">Supplier ID: #{{ supplierData.id }}</p>
              </div>
              <div class="col-md-4 text-right">
                <span :class="supplierData.is_active ? 'badge badge-success' : 'badge badge-danger'">
                  {{ supplierData.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Supplier Stats -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-value">${{ formatNumber(supplierData.credit_limit) }}</div>
                <div class="stat-label">Credit Limit</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-value">{{ supplierData.payment_terms_days || 0 }} days</div>
                <div class="stat-label">Payment Terms</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-value">{{ supplierData.purchase_orders ? supplierData.purchase_orders.length : 0 }}</div>
                <div class="stat-label">Total Orders</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-value">${{ calculateTotalValue() }}</div>
                <div class="stat-label">Total Value</div>
              </div>
            </div>
          </div>

          <!-- Supplier Information Tabs -->
          <div class="tabs">
            <div class="tab-headers">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                :class="['tab-header', { active: activeTab === tab.id }]"
                @click="activeTab = tab.id"
              >
                {{ tab.label }}
              </button>
            </div>

            <div class="tab-content">
              <!-- Contact Information -->
              <div v-if="activeTab === 'contact'" class="tab-pane">
                <div class="row">
                  <div class="col-md-6">
                    <div class="info-group">
                      <label>Email</label>
                      <p>{{ supplierData.email || '-' }}</p>
                    </div>
                    <div class="info-group">
                      <label>Phone</label>
                      <p>{{ supplierData.phone || '-' }}</p>
                    </div>
                    <div class="info-group">
                      <label>Mobile</label>
                      <p>{{ supplierData.mobile || '-' }}</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="info-group">
                      <label>Website</label>
                      <p>
                        <a v-if="supplierData.website" :href="supplierData.website" target="_blank">
                          {{ supplierData.website }}
                        </a>
                        <span v-else>-</span>
                      </p>
                    </div>
                    <div class="info-group">
                      <label>Tax Number</label>
                      <p>{{ supplierData.tax_number || '-' }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Address Information -->
              <div v-if="activeTab === 'address'" class="tab-pane">
                <div class="info-group">
                  <label>Address</label>
                  <p>{{ supplierData.address || '-' }}</p>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="info-group">
                      <label>City</label>
                      <p>{{ supplierData.city || '-' }}</p>
                    </div>
                    <div class="info-group">
                      <label>State</label>
                      <p>{{ supplierData.state || '-' }}</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="info-group">
                      <label>Postal Code</label>
                      <p>{{ supplierData.postal_code || '-' }}</p>
                    </div>
                    <div class="info-group">
                      <label>Country</label>
                      <p>{{ supplierData.country || '-' }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Purchase Orders -->
              <div v-if="activeTab === 'orders'" class="tab-pane">
                <div v-if="supplierData.purchase_orders && supplierData.purchase_orders.length > 0">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>PO #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="order in supplierData.purchase_orders.slice(0, 10)" :key="order.id">
                        <td>{{ order.po_number }}</td>
                        <td>{{ formatDate(order.order_date) }}</td>
                        <td>${{ formatNumber(order.total_amount) }}</td>
                        <td>
                          <span :class="getStatusBadgeClass(order.status)">
                            {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <p v-if="supplierData.purchase_orders.length > 10" class="text-muted">
                    Showing latest 10 orders. Total: {{ supplierData.purchase_orders.length }}
                  </p>
                </div>
                <div v-else class="text-center py-4">
                  <p class="text-muted">No purchase orders found.</p>
                </div>
              </div>

              <!-- Notes -->
              <div v-if="activeTab === 'notes'" class="tab-pane">
                <div class="info-group">
                  <label>Notes</label>
                  <p>{{ supplierData.notes || 'No notes available.' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="!loading && !supplierData" class="text-center py-8">
          <div class="text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-lg font-medium">Unable to load supplier details</p>
            <p class="text-sm">Please try again or contact support if the problem persists.</p>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="closeModal" class="btn btn-secondary">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
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
    const loading = ref(false);
    const supplierData = ref(null);
    const activeTab = ref('contact');

    const tabs = [
      { id: 'contact', label: 'Contact Info' },
      { id: 'address', label: 'Address' },
      { id: 'orders', label: 'Purchase Orders' },
      { id: 'notes', label: 'Notes' }
    ];

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

    const formatDate = (date) => {
      if (!date) return null;
      return new Date(date).toLocaleDateString();
    };

    const calculateTotalValue = () => {
      if (!supplierData.value?.purchase_orders) return '0';
      const total = supplierData.value.purchase_orders.reduce((sum, order) => sum + parseFloat(order.total_amount || 0), 0);
      return formatNumber(total);
    };

    const getStatusBadgeClass = (status) => {
      const classes = {
        draft: 'badge badge-secondary',
        sent: 'badge badge-info',
        confirmed: 'badge badge-warning',
        partially_received: 'badge badge-warning',
        received: 'badge badge-success',
        cancelled: 'badge badge-danger'
      };
      return classes[status] || 'badge badge-secondary';
    };

    const closeModal = () => {
      emit('close');
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        activeTab.value = 'contact';
        loadSupplierDetails();
      }
    });

    // Watch for supplier prop changes to reload data
    watch(() => props.supplier, (newSupplier) => {
      if (props.show && newSupplier) {
        loadSupplierDetails();
      }
    }, { deep: true });

    return {
      loading,
      supplierData,
      activeTab,
      tabs,
      formatNumber,
      formatDate,
      calculateTotalValue,
      getStatusBadgeClass,
      closeModal
    };
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h4 {
  margin: 0;
  color: #333;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #999;
}

.btn-close:hover {
  color: #333;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
}

.supplier-summary {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.supplier-summary h3 {
  margin: 0;
  color: #333;
}

.stat-card {
  background: white;
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
  text-align: center;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
}

.tabs {
  margin-top: 20px;
}

.tab-headers {
  display: flex;
  border-bottom: 1px solid #eee;
  margin-bottom: 20px;
}

.tab-header {
  background: none;
  border: none;
  padding: 10px 20px;
  cursor: pointer;
  color: #666;
  border-bottom: 2px solid transparent;
}

.tab-header.active {
  color: #007bff;
  border-bottom-color: #007bff;
}

.tab-header:hover {
  color: #007bff;
}

.info-group {
  margin-bottom: 15px;
}

.info-group label {
  display: block;
  font-weight: 500;
  color: #333;
  margin-bottom: 5px;
}

.info-group p {
  margin: 0;
  color: #666;
}

.table {
  margin: 0;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.badge-secondary {
  background-color: #6c757d;
  color: white;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #545b62;
}
</style>
