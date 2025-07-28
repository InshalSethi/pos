<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <!-- Enhanced Header -->
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="header-text">
            <h3>{{ isEdit ? 'Edit Purchase Order' : 'Create New Purchase Order' }}</h3>
            <p>{{ isEdit ? 'Update purchase order information' : 'Create a new purchase order' }}</p>
          </div>
        </div>
        <button @click="closeModal" class="btn-close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="savePurchaseOrder">
        <div class="modal-body">
          <!-- Basic Information -->
          <div class="form-section">
            <h4 class="section-title">Order Information</h4>
            <div class="form-grid">
              <div class="form-group">
                <label for="supplier_id" class="required">Supplier</label>
                <select
                  id="supplier_id"
                  v-model="form.supplier_id"
                  class="form-control"
                  :class="{ 'is-invalid': errors.supplier_id }"
                  required
                >
                  <option value="">Select Supplier</option>
                  <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                    {{ supplier.name }}
                  </option>
                </select>
                <div v-if="errors.supplier_id" class="invalid-feedback">{{ errors.supplier_id[0] }}</div>
              </div>

              <div class="form-group">
                <label for="order_date" class="required">Order Date</label>
                <input
                  id="order_date"
                  v-model="form.order_date"
                  type="date"
                  class="form-control"
                  :class="{ 'is-invalid': errors.order_date }"
                  required
                />
                <div v-if="errors.order_date" class="invalid-feedback">{{ errors.order_date[0] }}</div>
              </div>

              <div class="form-group">
                <label for="expected_delivery_date">Expected Delivery Date</label>
                <input
                  id="expected_delivery_date"
                  v-model="form.expected_delivery_date"
                  type="date"
                  class="form-control"
                  :class="{ 'is-invalid': errors.expected_delivery_date }"
                />
                <div v-if="errors.expected_delivery_date" class="invalid-feedback">{{ errors.expected_delivery_date[0] }}</div>
              </div>

              <div class="form-group">
                <label for="status">Status</label>
                <select
                  id="status"
                  v-model="form.status"
                  class="form-control"
                  :class="{ 'is-invalid': errors.status }"
                >
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="partially_received">Partially Received</option>
                  <option value="received">Received</option>
                  <option value="cancelled">Cancelled</option>
                </select>
                <div v-if="errors.status" class="invalid-feedback">{{ errors.status[0] }}</div>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="form-section">
            <div class="section-header">
              <h4 class="section-title">Order Items</h4>
              <button type="button" @click="addItem" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Item
              </button>
            </div>

            <div class="items-container">
              <div v-for="(item, index) in form.items" :key="index" class="item-row">
                <div class="item-grid">
                  <div class="form-group">
                    <label>Product</label>
                    <select
                      v-model="item.product_id"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`items.${index}.product_id`] }"
                      @change="updateItemPrice(index)"
                      required
                    >
                      <option value="">Select Product</option>
                      <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.name }} - ${{ product.cost_price }}
                      </option>
                    </select>
                    <div v-if="errors[`items.${index}.product_id`]" class="invalid-feedback">
                      {{ errors[`items.${index}.product_id`][0] }}
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Quantity Ordered</label>
                    <input
                      v-model.number="item.quantity_ordered"
                      type="number"
                      min="1"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`items.${index}.quantity_ordered`] }"
                      @input="calculateItemTotal(index)"
                      required
                    />
                    <div v-if="errors[`items.${index}.quantity_ordered`]" class="invalid-feedback">
                      {{ errors[`items.${index}.quantity_ordered`][0] }}
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Unit Cost</label>
                    <input
                      v-model.number="item.unit_cost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`items.${index}.unit_cost`] }"
                      @input="calculateItemTotal(index)"
                      required
                    />
                    <div v-if="errors[`items.${index}.unit_cost`]" class="invalid-feedback">
                      {{ errors[`items.${index}.unit_cost`][0] }}
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Total Cost</label>
                    <input
                      :value="formatCurrency(item.total_cost || 0)"
                      type="text"
                      class="form-control"
                      readonly
                    />
                  </div>

                  <div class="form-group">
                    <label>&nbsp;</label>
                    <button
                      type="button"
                      @click="removeItem(index)"
                      class="btn btn-danger btn-sm"
                      :disabled="form.items.length === 1"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Totals -->
          <div class="form-section">
            <div class="totals-container">
              <div class="totals-grid">
                <div class="form-group">
                  <label for="subtotal">Subtotal</label>
                  <input
                    id="subtotal"
                    :value="formatCurrency(calculateSubtotal())"
                    type="text"
                    class="form-control"
                    readonly
                  />
                </div>

                <div class="form-group">
                  <label for="tax_amount">Tax Amount</label>
                  <input
                    id="tax_amount"
                    v-model.number="form.tax_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-control"
                    @input="calculateTotal"
                  />
                </div>

                <div class="form-group">
                  <label for="shipping_cost">Shipping Cost</label>
                  <input
                    id="shipping_cost"
                    v-model.number="form.shipping_cost"
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-control"
                    @input="calculateTotal"
                  />
                </div>

                <div class="form-group">
                  <label for="total_amount">Total Amount</label>
                  <input
                    id="total_amount"
                    :value="formatCurrency(form.total_amount || 0)"
                    type="text"
                    class="form-control total-field"
                    readonly
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="form-section">
            <h4 class="section-title">Additional Information</h4>
            <div class="form-grid">
              <div class="form-group full-width">
                <label for="notes">Notes</label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  class="form-control"
                  rows="3"
                  placeholder="Add any additional notes..."
                ></textarea>
              </div>

              <div class="form-group full-width">
                <label for="terms_and_conditions">Terms and Conditions</label>
                <textarea
                  id="terms_and_conditions"
                  v-model="form.terms_and_conditions"
                  class="form-control"
                  rows="3"
                  placeholder="Enter terms and conditions..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <div class="footer-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="btn btn-primary">
              <svg v-if="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ saving ? 'Saving...' : (isEdit ? 'Update Order' : 'Create Order') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, watch, onMounted } from 'vue';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

export default {
  name: 'PurchaseOrderModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    order: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    
    const saving = ref(false);
    const errors = ref({});
    const suppliers = ref([]);
    const products = ref([]);

    const form = reactive({
      supplier_id: '',
      order_date: new Date().toISOString().split('T')[0],
      expected_delivery_date: '',
      status: 'draft',
      subtotal: 0,
      tax_amount: 0,
      shipping_cost: 0,
      total_amount: 0,
      notes: '',
      terms_and_conditions: '',
      items: [
        {
          product_id: '',
          quantity_ordered: 1,
          unit_cost: 0,
          total_cost: 0
        }
      ]
    });

    const resetForm = () => {
      form.supplier_id = '';
      form.order_date = new Date().toISOString().split('T')[0];
      form.expected_delivery_date = '';
      form.status = 'draft';
      form.subtotal = 0;
      form.tax_amount = 0;
      form.shipping_cost = 0;
      form.total_amount = 0;
      form.notes = '';
      form.terms_and_conditions = '';
      form.items = [
        {
          product_id: '',
          quantity_ordered: 1,
          unit_cost: 0,
          total_cost: 0
        }
      ];
      errors.value = {};
    };

    const loadOrderData = () => {
      if (props.isEdit && props.order) {
        Object.keys(form).forEach(key => {
          if (props.order[key] !== undefined) {
            form[key] = props.order[key];
          }
        });
        
        if (props.order.purchase_order_items && props.order.purchase_order_items.length > 0) {
          form.items = props.order.purchase_order_items.map(item => ({
            product_id: item.product_id,
            quantity_ordered: item.quantity_ordered,
            unit_cost: item.unit_cost,
            total_cost: item.total_cost
          }));
        }
      }
    };

    const loadSuppliers = async () => {
      try {
        const response = await api.get('/suppliers');
        suppliers.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error loading suppliers:', error);
      }
    };

    const loadProducts = async () => {
      try {
        const response = await api.get('/products');
        products.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error loading products:', error);
      }
    };

    const addItem = () => {
      form.items.push({
        product_id: '',
        quantity_ordered: 1,
        unit_cost: 0,
        total_cost: 0
      });
    };

    const removeItem = (index) => {
      if (form.items.length > 1) {
        form.items.splice(index, 1);
        calculateTotal();
      }
    };

    const updateItemPrice = (index) => {
      const item = form.items[index];
      const product = products.value.find(p => p.id == item.product_id);
      if (product) {
        item.unit_cost = parseFloat(product.cost_price) || 0;
        calculateItemTotal(index);
      }
    };

    const calculateItemTotal = (index) => {
      const item = form.items[index];
      item.total_cost = (item.quantity_ordered || 0) * (item.unit_cost || 0);
      calculateTotal();
    };

    const calculateSubtotal = () => {
      return form.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
    };

    const calculateTotal = () => {
      form.subtotal = calculateSubtotal();
      form.total_amount = form.subtotal + (form.tax_amount || 0) + (form.shipping_cost || 0);
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount || 0);
    };

    const savePurchaseOrder = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const url = props.isEdit ? `/purchase-orders/${props.order.id}` : '/purchase-orders';
        const method = props.isEdit ? 'put' : 'post';
        
        await api[method](url, form);
        
        showToast(
          props.isEdit ? 'Purchase order updated successfully' : 'Purchase order created successfully',
          'success'
        );
        
        emit('saved');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error saving purchase order', 'error');
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
        loadOrderData();
        loadSuppliers();
        loadProducts();
      }
    });

    onMounted(() => {
      loadSuppliers();
      loadProducts();
    });

    return {
      form,
      errors,
      saving,
      suppliers,
      products,
      addItem,
      removeItem,
      updateItemPrice,
      calculateItemTotal,
      calculateSubtotal,
      calculateTotal,
      formatCurrency,
      savePurchaseOrder,
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  gap: 12px;
  align-items: end;
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
  background: #f0f4ff !important;
  border-color: #667eea !important;
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
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

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
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

  .footer-actions {
    flex-direction: column-reverse;
    gap: 8px;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
