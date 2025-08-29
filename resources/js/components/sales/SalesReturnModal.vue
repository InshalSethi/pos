<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <!-- Enhanced Header -->
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
          </div>
          <div class="header-text">
            <h3>Process Sales Return</h3>
            <p>Create a return for a previous sale</p>
          </div>
        </div>
        <button @click="closeModal" class="btn-close" type="button">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="processSalesReturn" novalidate>
        <div class="modal-body">
          <!-- Original Sale Selection -->
          <div class="form-section">
            <h4 class="section-title">Original Sale Information</h4>
            <div class="form-grid">
              <div class="form-group">
                <label for="original_sale_search" class="required">Search Original Sale</label>
                <div class="search-container">
                  <input
                    id="original_sale_search"
                    v-model="saleSearchQuery"
                    type="text"
                    placeholder="Enter sale number or customer name..."
                    class="form-control"
                    :class="{ 'is-invalid': errors.original_sale_id }"
                    @input="searchSales"
                  />
                  <div v-if="searchResults.length > 0" class="search-results">
                    <div
                      v-for="sale in searchResults"
                      :key="sale.id"
                      @click="selectOriginalSale(sale)"
                      class="search-result-item"
                    >
                      <div class="result-main">
                        <span class="sale-number">{{ sale.sale_number }}</span>
                        <span class="sale-amount">{{ formatCurrency(sale.total_amount) }}</span>
                      </div>
                      <div class="result-details">
                        <span class="customer">{{ sale.customer?.name || 'Walk-in Customer' }}</span>
                        <span class="date">{{ formatDate(sale.sale_date) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="errors.original_sale_id" class="invalid-feedback">{{ errors.original_sale_id[0] }}</div>
              </div>

              <div v-if="originalSale" class="form-group">
                <label>Selected Sale</label>
                <div class="selected-sale">
                  <div class="sale-info">
                    <h5>{{ originalSale.sale_number }}</h5>
                    <p>{{ originalSale.customer?.name || 'Walk-in Customer' }}</p>
                    <p>{{ formatDate(originalSale.sale_date) }} - {{ formatCurrency(originalSale.total_amount) }}</p>
                  </div>
                  <button type="button" @click="clearOriginalSale" class="btn btn-sm btn-secondary">
                    Clear
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Return Information -->
          <div v-if="originalSale" class="form-section">
            <h4 class="section-title">Return Details</h4>
            <div class="form-grid">
              <div class="form-group">
                <label for="return_date" class="required">Return Date</label>
                <input
                  id="return_date"
                  v-model="form.return_date"
                  type="date"
                  class="form-control"
                  :class="{ 'is-invalid': errors.return_date }"
                />
                <div v-if="errors.return_date" class="invalid-feedback">{{ errors.return_date[0] }}</div>
              </div>

              <div class="form-group">
                <label for="return_reason" class="required">Return Reason</label>
                <select
                  id="return_reason"
                  v-model="form.return_reason"
                  class="form-control"
                  :class="{ 'is-invalid': errors.return_reason }"
                >
                  <option value="">Select reason</option>
                  <option value="defective">Defective Product</option>
                  <option value="wrong_item">Wrong Item</option>
                  <option value="customer_change_mind">Customer Changed Mind</option>
                  <option value="damaged_shipping">Damaged in Shipping</option>
                  <option value="not_as_described">Not as Described</option>
                  <option value="other">Other</option>
                </select>
                <div v-if="errors.return_reason" class="invalid-feedback">{{ errors.return_reason[0] }}</div>
              </div>

              <div class="form-group full-width">
                <label for="return_notes">Return Notes</label>
                <textarea
                  id="return_notes"
                  v-model="form.return_notes"
                  class="form-control"
                  rows="3"
                  placeholder="Additional notes about the return..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Return Items -->
          <div v-if="originalSale && originalSale.sale_items" class="form-section">
            <div class="section-header">
              <h4 class="section-title">Items to Return</h4>
              <div class="section-actions">
                <button type="button" @click="selectAllItems" class="btn btn-sm btn-secondary">
                  Select All
                </button>
                <button type="button" @click="clearAllItems" class="btn btn-sm btn-secondary">
                  Clear All
                </button>
              </div>
            </div>
            
            <div class="items-container">
              <div v-for="(item, index) in originalSale.sale_items" :key="item.id" class="item-row">
                <div class="item-grid">
                  <div class="item-info">
                    <div class="item-checkbox">
                      <input
                        :id="`item_${item.id}`"
                        v-model="form.return_items[item.id].selected"
                        type="checkbox"
                        @change="updateItemSelection(item.id)"
                      />
                      <label :for="`item_${item.id}`" class="checkbox-label">
                        <strong>{{ item.product?.name || 'Unknown Product' }}</strong>
                        <div class="item-details">
                          SKU: {{ item.product?.sku || 'N/A' }} | 
                          Original Qty: {{ item.quantity }} | 
                          Unit Price: {{ formatCurrency(item.unit_price) }}
                        </div>
                      </label>
                    </div>
                  </div>
                  
                  <div v-if="form.return_items[item.id].selected" class="return-quantity">
                    <label :for="`qty_${item.id}`">Return Qty</label>
                    <input
                      :id="`qty_${item.id}`"
                      v-model.number="form.return_items[item.id].quantity"
                      type="number"
                      :min="1"
                      :max="item.quantity"
                      class="form-control"
                      @input="calculateReturnTotal"
                    />
                  </div>
                  
                  <div v-if="form.return_items[item.id].selected" class="return-total">
                    <label>Return Amount</label>
                    <div class="total-display">
                      {{ formatCurrency(form.return_items[item.id].return_amount) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Return Summary -->
          <div v-if="originalSale" class="form-section">
            <h4 class="section-title">Return Summary</h4>
            <div class="totals-container">
              <div class="totals-grid">
                <div class="form-group">
                  <label>Total Return Amount</label>
                  <input
                    v-model="form.total_return_amount"
                    type="text"
                    class="form-control total-field"
                    readonly
                  />
                </div>
                <div class="form-group">
                  <label for="refund_method" class="required">Refund Method</label>
                  <select
                    id="refund_method"
                    v-model="form.refund_method"
                    class="form-control"
                    :class="{ 'is-invalid': errors.refund_method }"
                  >
                    <option value="">Select refund method</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card Refund</option>
                    <option value="store_credit">Store Credit</option>
                    <option value="exchange">Exchange</option>
                  </select>
                  <div v-if="errors.refund_method" class="invalid-feedback">{{ errors.refund_method[0] }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <div class="footer-summary">
            <div class="summary-item">
              <span class="summary-label">Return Amount:</span>
              <span class="summary-value">{{ formatCurrency(form.total_return_amount) }}</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Items:</span>
              <span class="summary-value">{{ selectedItemsCount }}</span>
            </div>
          </div>
          <div class="footer-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary" :disabled="processing">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Cancel
            </button>
            <button type="submit" :disabled="processing || !canProcessReturn" class="btn btn-primary">
              <svg v-if="processing" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
              </svg>
              {{ processing ? 'Processing...' : 'Process Return' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

export default {
  name: 'SalesReturnModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    originalSaleId: {
      type: [String, Number],
      default: null
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    
    const processing = ref(false);
    const errors = ref({});
    const saleSearchQuery = ref('');
    const searchResults = ref([]);
    const originalSale = ref(null);
    const searchTimeout = ref(null);

    const form = reactive({
      original_sale_id: '',
      return_date: new Date().toISOString().split('T')[0],
      return_reason: '',
      return_notes: '',
      refund_method: '',
      total_return_amount: 0,
      return_items: {}
    });

    const selectedItemsCount = computed(() => {
      return Object.values(form.return_items).filter(item => item.selected).length;
    });

    const canProcessReturn = computed(() => {
      return originalSale.value && 
             selectedItemsCount.value > 0 && 
             form.return_reason && 
             form.refund_method &&
             form.total_return_amount > 0;
    });

    const resetForm = () => {
      form.original_sale_id = '';
      form.return_date = new Date().toISOString().split('T')[0];
      form.return_reason = '';
      form.return_notes = '';
      form.refund_method = '';
      form.total_return_amount = 0;
      form.return_items = {};
      originalSale.value = null;
      saleSearchQuery.value = '';
      searchResults.value = [];
      errors.value = {};
    };

    const searchSales = () => {
      if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
      }

      searchTimeout.value = setTimeout(async () => {
        if (saleSearchQuery.value.length < 2) {
          searchResults.value = [];
          return;
        }

        try {
          const response = await api.get('/sales', {
            params: {
              search: saleSearchQuery.value,
              status: 'completed',
              per_page: 10
            }
          });
          searchResults.value = response.data.data || [];
        } catch (error) {
          console.error('Error searching sales:', error);
        }
      }, 300);
    };

    const selectOriginalSale = async (sale) => {
      try {
        const response = await api.get(`/sales/${sale.id}`);
        originalSale.value = response.data;
        form.original_sale_id = sale.id;
        saleSearchQuery.value = sale.sale_number;
        searchResults.value = [];

        // Initialize return items
        form.return_items = {};
        if (originalSale.value.sale_items) {
          originalSale.value.sale_items.forEach(item => {
            form.return_items[item.id] = {
              selected: false,
              quantity: 1,
              return_amount: 0,
              unit_price: item.unit_price
            };
          });
        }
      } catch (error) {
        showToast('Error loading sale details', 'error');
      }
    };

    const clearOriginalSale = () => {
      originalSale.value = null;
      form.original_sale_id = '';
      form.return_items = {};
      saleSearchQuery.value = '';
      searchResults.value = [];
      calculateReturnTotal();
    };

    const updateItemSelection = (itemId) => {
      if (!form.return_items[itemId].selected) {
        form.return_items[itemId].quantity = 1;
        form.return_items[itemId].return_amount = 0;
      } else {
        const item = originalSale.value.sale_items.find(i => i.id === itemId);
        if (item) {
          form.return_items[itemId].quantity = 1;
          form.return_items[itemId].return_amount = item.unit_price;
        }
      }
      calculateReturnTotal();
    };

    const selectAllItems = () => {
      if (originalSale.value && originalSale.value.sale_items) {
        originalSale.value.sale_items.forEach(item => {
          form.return_items[item.id].selected = true;
          form.return_items[item.id].quantity = item.quantity;
          form.return_items[item.id].return_amount = item.unit_price * item.quantity;
        });
        calculateReturnTotal();
      }
    };

    const clearAllItems = () => {
      Object.keys(form.return_items).forEach(itemId => {
        form.return_items[itemId].selected = false;
        form.return_items[itemId].quantity = 1;
        form.return_items[itemId].return_amount = 0;
      });
      calculateReturnTotal();
    };

    const calculateReturnTotal = () => {
      let total = 0;
      Object.values(form.return_items).forEach(item => {
        if (item.selected) {
          item.return_amount = item.unit_price * item.quantity;
          total += item.return_amount;
        }
      });
      form.total_return_amount = total;
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString();
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount || 0);
    };

    const validateForm = () => {
      const newErrors = {};

      if (!form.original_sale_id) {
        newErrors.original_sale_id = ['Please select an original sale'];
      }

      if (!form.return_reason) {
        newErrors.return_reason = ['Return reason is required'];
      }

      if (!form.refund_method) {
        newErrors.refund_method = ['Refund method is required'];
      }

      if (selectedItemsCount.value === 0) {
        newErrors.items = ['Please select at least one item to return'];
      }

      errors.value = newErrors;
      return Object.keys(newErrors).length === 0;
    };

    const processSalesReturn = async () => {
      if (!validateForm()) {
        showToast('Please fix the validation errors', 'error');
        return;
      }

      processing.value = true;
      errors.value = {};

      try {
        const returnData = {
          original_sale_id: form.original_sale_id,
          return_date: form.return_date,
          return_reason: form.return_reason,
          return_notes: form.return_notes,
          refund_method: form.refund_method,
          total_return_amount: form.total_return_amount,
          return_items: Object.entries(form.return_items)
            .filter(([_, item]) => item.selected)
            .map(([itemId, item]) => ({
              original_item_id: parseInt(itemId),
              quantity: item.quantity,
              return_amount: item.return_amount
            }))
        };

        const response = await api.sales.processReturn(returnData);

        showToast('Return processed successfully', 'success');
        emit('saved', response.data);
        closeModal();
      } catch (error) {
        console.error('Error processing return:', error);
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors || {};
          showToast('Please fix the validation errors', 'error');
        } else {
          showToast(
            error.response?.data?.message || 'Error processing return. Please try again.',
            'error'
          );
        }
      } finally {
        processing.value = false;
      }
    };

    const closeModal = () => {
      resetForm();
      emit('close');
    };

    // Load original sale if provided
    watch(() => props.originalSaleId, async (newId) => {
      if (newId && props.show) {
        try {
          const response = await api.get(`/sales/${newId}`);
          selectOriginalSale(response.data);
        } catch (error) {
          console.error('Error loading original sale:', error);
        }
      }
    });

    watch(() => props.show, (newVal) => {
      if (newVal) {
        resetForm();
        if (props.originalSaleId) {
          // Will be handled by the originalSaleId watcher
        }
      }
    });

    return {
      processing,
      errors,
      saleSearchQuery,
      searchResults,
      originalSale,
      form,
      selectedItemsCount,
      canProcessReturn,
      resetForm,
      searchSales,
      selectOriginalSale,
      clearOriginalSale,
      updateItemSelection,
      selectAllItems,
      clearAllItems,
      calculateReturnTotal,
      formatDate,
      formatCurrency,
      validateForm,
      processSalesReturn,
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
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 95%;
  max-width: 1200px;
  max-height: 95vh;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.modal-header {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: white;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.header-text h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.header-text p {
  margin: 4px 0 0 0;
  opacity: 0.9;
  font-size: 14px;
}

.btn-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 8px;
  width: 40px;
  height: 40px;
  cursor: pointer;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.modal-body {
  padding: 24px;
  flex: 1;
  overflow-y: auto;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.footer-summary {
  display: flex;
  gap: 24px;
  align-items: center;
}

.summary-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.summary-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-value {
  font-size: 16px;
  font-weight: 700;
  color: #374151;
}

.footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Form Sections */
.form-section {
  margin-bottom: 32px;
  padding: 24px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fafbfc;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 20px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #e5e7eb;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-actions {
  display: flex;
  gap: 8px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
  font-size: 14px;
}

.form-group label.required::after {
  content: ' *';
  color: #ef4444;
}

.form-control {
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
  background: #ffffff;
}

.form-control:focus {
  outline: none;
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-control:hover {
  border-color: #d1d5db;
}

.form-control.is-invalid {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.invalid-feedback {
  color: #ef4444;
  font-size: 12px;
  margin-top: 6px;
  font-weight: 500;
}

/* Search Container */
.search-container {
  position: relative;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-top: none;
  border-radius: 0 0 8px 8px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 10;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.search-result-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.2s ease;
}

.search-result-item:hover {
  background-color: #f9fafb;
}

.search-result-item:last-child {
  border-bottom: none;
}

.result-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.sale-number {
  font-weight: 600;
  color: #374151;
}

.sale-amount {
  font-weight: 600;
  color: #059669;
}

.result-details {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #6b7280;
}

/* Selected Sale */
.selected-sale {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #f0f9ff;
  border: 2px solid #0ea5e9;
  border-radius: 8px;
}

.sale-info h5 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 600;
  color: #0c4a6e;
}

.sale-info p {
  margin: 0;
  font-size: 12px;
  color: #0369a1;
}

/* Items Container */
.items-container {
  background: white;
  border-radius: 8px;
  padding: 16px;
}

.item-row {
  margin-bottom: 16px;
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f9fafb;
}

.item-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 16px;
  align-items: center;
}

.item-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.item-checkbox input[type="checkbox"] {
  margin-top: 4px;
  width: 16px;
  height: 16px;
}

.checkbox-label {
  flex: 1;
  cursor: pointer;
}

.item-details {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
}

.return-quantity label,
.return-total label {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 4px;
}

.total-display {
  padding: 8px 12px;
  background: #f3f4f6;
  border-radius: 6px;
  font-weight: 600;
  color: #374151;
  text-align: center;
}

/* Totals */
.totals-container {
  background: white;
  border-radius: 8px;
  padding: 20px;
}

.totals-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.total-field {
  font-weight: 600;
  font-size: 16px;
  background: #fef2f2 !important;
  border-color: #dc2626 !important;
}

/* Button Styling */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
  min-width: 120px;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 12px;
  min-width: auto;
}

.btn-primary {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
}

.btn-secondary {
  background: #f3f4f6;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #e5e7eb;
  color: #374151;
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.btn:disabled:hover {
  transform: none !important;
  box-shadow: none !important;
}

/* Animations */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-content {
    width: 98%;
    max-height: 98vh;
    margin: 1vh;
  }

  .modal-header {
    padding: 16px;
  }

  .header-text h3 {
    font-size: 20px;
  }

  .modal-body {
    padding: 16px;
  }

  .form-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .item-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .totals-grid {
    grid-template-columns: 1fr;
  }

  .modal-footer {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .footer-summary {
    justify-content: center;
    gap: 32px;
  }

  .footer-actions {
    flex-direction: column-reverse;
    gap: 8px;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .section-actions {
    justify-content: center;
  }
}
</style>
