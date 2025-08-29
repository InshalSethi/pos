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
            <h3>{{ isEdit ? 'Edit Purchase Return' : 'Process Purchase Return' }}</h3>
            <p>{{ isEdit ? 'Update purchase return information' : 'Create a new purchase return' }}</p>
          </div>
        </div>
        <button @click="closeModal" class="btn-close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="savePurchaseReturn" class="modal-body">
        <div class="form-grid">
          <!-- Purchase Order Selection -->
          <div class="form-group">
            <label class="form-label">Original Purchase Order *</label>
            <select v-model="form.purchase_order_id" :class="['form-select', errors.purchase_order_id ? 'error' : '']" required @change="loadPurchaseOrderItems">
              <option value="">Select Purchase Order</option>
              <option v-for="order in purchaseOrders" :key="order.id" :value="order.id">
                {{ order.po_number }} - {{ order.supplier?.name }}
              </option>
            </select>
            <span v-if="errors.purchase_order_id" class="error-text">{{ errors.purchase_order_id[0] }}</span>
          </div>

          <!-- Supplier (Auto-filled) -->
          <div class="form-group">
            <label class="form-label">Supplier</label>
            <input
              v-model="selectedSupplierName"
              type="text"
              class="form-input"
              readonly
              placeholder="Will be auto-filled"
            />
          </div>

          <!-- Return Date -->
          <div class="form-group">
            <label class="form-label">Return Date *</label>
            <input
              v-model="form.return_date"
              type="date"
              :class="['form-input', errors.return_date ? 'error' : '']"
              required
            />
            <span v-if="errors.return_date" class="error-text">{{ errors.return_date[0] }}</span>
          </div>

          <!-- Reason -->
          <div class="form-group">
            <label class="form-label">Reason *</label>
            <select v-model="form.reason" :class="['form-select', errors.reason ? 'error' : '']" required>
              <option value="">Select Reason</option>
              <option value="damaged">Damaged</option>
              <option value="defective">Defective</option>
              <option value="wrong_item">Wrong Item</option>
              <option value="excess_quantity">Excess Quantity</option>
              <option value="quality_issue">Quality Issue</option>
              <option value="other">Other</option>
            </select>
            <span v-if="errors.reason" class="error-text">{{ errors.reason[0] }}</span>
          </div>
        </div>

        <!-- Items Section -->
        <div class="items-section">
          <div class="section-header">
            <h4>Return Items</h4>
            <button type="button" @click="addItem" class="btn btn-secondary" :disabled="!form.purchase_order_id">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Add Item
            </button>
          </div>

          <div class="items-table">
            <div class="table-header">
              <div class="col-product">Product</div>
              <div class="col-quantity">Return Qty</div>
              <div class="col-price">Unit Cost</div>
              <div class="col-total">Total</div>
              <div class="col-actions">Actions</div>
            </div>

            <div v-for="(item, index) in form.items" :key="index" class="table-row">
              <div class="col-product">
                <select v-model="item.product_id" @change="updateItemPrice(index)" class="form-select">
                  <option value="">Select Product</option>
                  <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                    {{ product.name }} (Available: {{ product.available_quantity }})
                  </option>
                </select>
              </div>
              <div class="col-quantity">
                <input
                  v-model.number="item.quantity"
                  type="number"
                  min="1"
                  :max="getMaxQuantity(item.product_id)"
                  @input="calculateItemTotal(index)"
                  class="form-input"
                />
              </div>
              <div class="col-price">
                <input
                  v-model.number="item.unit_cost"
                  type="number"
                  step="0.01"
                  min="0"
                  @input="calculateItemTotal(index)"
                  class="form-input"
                />
              </div>
              <div class="col-total">
                <span class="total-display">{{ formatCurrency(item.total_cost) }}</span>
              </div>
              <div class="col-actions">
                <button type="button" @click="removeItem(index)" class="btn-remove">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Amount -->
        <div class="totals-section">
          <div class="totals-grid">
            <div class="total-row total-final">
              <span><strong>Total Return Amount:</strong></span>
              <span><strong>{{ formatCurrency(totalAmount) }}</strong></span>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="form-textarea"
            placeholder="Additional notes about the return..."
          ></textarea>
        </div>

        <!-- Action Buttons -->
        <div class="modal-footer">
          <div class="footer-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">
              Cancel
            </button>
            <button type="submit" :disabled="saving || form.items.length === 0" class="btn btn-primary">
              <svg v-if="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ saving ? 'Processing...' : (isEdit ? 'Update Return' : 'Process Return') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, watch, onMounted, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

export default {
  name: 'PurchaseReturnModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    returnItem: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    originalPurchaseOrderId: {
      type: [String, Number],
      default: null
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    
    const saving = ref(false);
    const errors = ref({});
    const purchaseOrders = ref([]);
    const availableProducts = ref([]);
    const selectedPurchaseOrder = ref(null);

    const form = reactive({
      purchase_order_id: '',
      supplier_id: '',
      return_date: new Date().toISOString().split('T')[0],
      reason: '',
      notes: '',
      items: [
        {
          product_id: '',
          quantity: 1,
          unit_cost: 0,
          total_cost: 0
        }
      ]
    });

    const selectedSupplierName = computed(() => {
      return selectedPurchaseOrder.value?.supplier?.name || '';
    });

    const totalAmount = computed(() => {
      return form.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
    });

    const resetForm = () => {
      Object.assign(form, {
        purchase_order_id: props.originalPurchaseOrderId || '',
        supplier_id: '',
        return_date: new Date().toISOString().split('T')[0],
        reason: '',
        notes: '',
        items: [
          {
            product_id: '',
            quantity: 1,
            unit_cost: 0,
            total_cost: 0
          }
        ]
      });
      errors.value = {};
      availableProducts.value = [];
      selectedPurchaseOrder.value = null;
    };

    const loadReturnData = () => {
      if (props.isEdit && props.returnItem) {
        Object.assign(form, {
          purchase_order_id: props.returnItem.purchase_order_id,
          supplier_id: props.returnItem.supplier_id,
          return_date: props.returnItem.return_date,
          reason: props.returnItem.reason,
          notes: props.returnItem.notes,
          items: props.returnItem.items || [
            {
              product_id: '',
              quantity: 1,
              unit_cost: 0,
              total_cost: 0
            }
          ]
        });
      }
    };

    const loadPurchaseOrders = async () => {
      try {
        const response = await api.get('/purchase-orders?status=received');
        purchaseOrders.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error loading purchase orders:', error);
      }
    };

    const loadPurchaseOrderItems = async () => {
      if (!form.purchase_order_id) {
        availableProducts.value = [];
        selectedPurchaseOrder.value = null;
        return;
      }

      try {
        const response = await api.get(`/purchase-orders/${form.purchase_order_id}`);
        selectedPurchaseOrder.value = response.data;
        form.supplier_id = response.data.supplier_id;
        
        // Set available products from purchase order items
        availableProducts.value = response.data.purchase_order_items.map(item => ({
          id: item.product_id,
          name: item.product.name,
          unit_cost: item.unit_cost,
          available_quantity: item.quantity_received
        }));
      } catch (error) {
        console.error('Error loading purchase order items:', error);
      }
    };

    const addItem = () => {
      form.items.push({
        product_id: '',
        quantity: 1,
        unit_cost: 0,
        total_cost: 0
      });
    };

    const removeItem = (index) => {
      if (form.items.length > 1) {
        form.items.splice(index, 1);
      }
    };

    const updateItemPrice = (index) => {
      const item = form.items[index];
      const product = availableProducts.value.find(p => p.id == item.product_id);
      if (product) {
        item.unit_cost = parseFloat(product.unit_cost || 0);
        calculateItemTotal(index);
      }
    };

    const calculateItemTotal = (index) => {
      const item = form.items[index];
      item.total_cost = item.quantity * item.unit_cost;
    };

    const getMaxQuantity = (productId) => {
      const product = availableProducts.value.find(p => p.id == productId);
      return product ? product.available_quantity : 999;
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount || 0);
    };

    const savePurchaseReturn = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const returnData = {
          ...form,
          total_amount: totalAmount.value
        };

        const url = props.isEdit ? `/purchase-returns/${props.returnItem.id}` : '/purchase-returns';
        const method = props.isEdit ? 'put' : 'post';
        
        await api[method](url, returnData);
        
        showToast(
          props.isEdit ? 'Purchase return updated successfully' : 'Purchase return processed successfully',
          'success'
        );
        
        emit('saved');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error processing purchase return', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    const closeModal = () => {
      resetForm();
      emit('close');
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        resetForm();
        loadReturnData();
        loadPurchaseOrders();
        if (props.originalPurchaseOrderId) {
          form.purchase_order_id = props.originalPurchaseOrderId;
          loadPurchaseOrderItems();
        }
      }
    });

    onMounted(() => {
      loadPurchaseOrders();
    });

    return {
      form,
      errors,
      saving,
      purchaseOrders,
      availableProducts,
      selectedSupplierName,
      totalAmount,
      addItem,
      removeItem,
      updateItemPrice,
      calculateItemTotal,
      getMaxQuantity,
      formatCurrency,
      loadPurchaseOrderItems,
      savePurchaseReturn,
      closeModal
    };
  }
};
</script>

<style scoped>
/* Reuse the same styles from PurchaseInvoiceModal */
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
  padding: 20px;
  overflow-y: auto;
}

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  width: 100%;
  max-width: 1200px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: white;
  border-radius: 12px 12px 0 0;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  background: rgba(255, 255, 255, 0.2);
  padding: 12px;
  border-radius: 8px;
}

.header-text h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.header-text p {
  font-size: 0.875rem;
  opacity: 0.9;
  margin: 4px 0 0 0;
}

.btn-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.modal-body {
  padding: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
  font-size: 0.875rem;
}

.form-input, .form-select, .form-textarea {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.error, .form-select.error {
  border-color: #ef4444;
}

.error-text {
  color: #ef4444;
  font-size: 0.75rem;
  margin-top: 4px;
}

.items-section {
  margin-bottom: 24px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-header h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.items-table {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.table-header, .table-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 12px;
}

.table-header {
  background: #f9fafb;
  font-weight: 600;
  font-size: 0.875rem;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.table-row {
  border-bottom: 1px solid #f3f4f6;
}

.table-row:last-child {
  border-bottom: none;
}

.total-display {
  font-weight: 500;
  color: #374151;
}

.btn-remove {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  padding: 6px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-remove:hover {
  background: #fecaca;
}

.totals-section {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.totals-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 300px;
  margin-left: auto;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;
}

.total-final {
  font-weight: 600;
  font-size: 1rem;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.modal-footer {
  border-top: 1px solid #e5e7eb;
  padding: 20px 24px;
  background: #f9fafb;
  border-radius: 0 0 12px 12px;
}

.footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn {
  display: inline-flex;
  align-items: center;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background: #dc2626;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #e5e7eb;
}
</style>
