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
            <h3>{{ isEdit ? 'Edit Sales Invoice' : 'Create New Sales Invoice' }}</h3>
            <p>{{ isEdit ? 'Update invoice information' : 'Create a new sales invoice' }}</p>
          </div>
        </div>
        <button @click="closeModal" class="btn-close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="saveSalesInvoice">
        <div class="modal-body">
          <!-- Basic Information -->
          <div class="form-section">
            <h4 class="section-title">Invoice Information</h4>
            <div class="form-grid">
              <div class="form-group">
                <label for="customer_id">Customer</label>
                <select
                  id="customer_id"
                  v-model="form.customer_id"
                  class="form-control"
                  :class="{ 'is-invalid': errors.customer_id }"
                >
                  <option value="">Walk-in Customer</option>
                  <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                    {{ customer.name }}
                  </option>
                </select>
                <div v-if="errors.customer_id" class="invalid-feedback">{{ errors.customer_id[0] }}</div>
              </div>

              <div class="form-group">
                <label for="sale_date" class="required">Sale Date</label>
                <input
                  id="sale_date"
                  v-model="form.sale_date"
                  type="date"
                  class="form-control"
                  :class="{ 'is-invalid': errors.sale_date }"
                  required
                />
                <div v-if="errors.sale_date" class="invalid-feedback">{{ errors.sale_date[0] }}</div>
              </div>

              <div class="form-group">
                <label for="payment_method" class="required">Payment Method</label>
                <select
                  id="payment_method"
                  v-model="form.payment_method"
                  class="form-control"
                  :class="{ 'is-invalid': errors.payment_method }"
                  required
                >
                  <option value="">Select Payment Method</option>
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="mobile_payment">Mobile Payment</option>
                  <option value="mixed">Mixed</option>
                </select>
                <div v-if="errors.payment_method" class="invalid-feedback">{{ errors.payment_method[0] }}</div>
              </div>

              <div class="form-group">
                <label for="status">Status</label>
                <select
                  id="status"
                  v-model="form.status"
                  class="form-control"
                  :class="{ 'is-invalid': errors.status }"
                >
                  <option value="pending">Pending</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
                <div v-if="errors.status" class="invalid-feedback">{{ errors.status[0] }}</div>
              </div>
            </div>
          </div>

          <!-- Invoice Items -->
          <div class="form-section">
            <div class="section-header">
              <h4 class="section-title">Invoice Items</h4>
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
                        {{ product.name }} - ${{ product.selling_price }}
                      </option>
                    </select>
                    <div v-if="errors[`items.${index}.product_id`]" class="invalid-feedback">
                      {{ errors[`items.${index}.product_id`][0] }}
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Quantity</label>
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`items.${index}.quantity`] }"
                      @input="calculateItemTotal(index)"
                      required
                    />
                    <div v-if="errors[`items.${index}.quantity`]" class="invalid-feedback">
                      {{ errors[`items.${index}.quantity`][0] }}
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Unit Price</label>
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-control"
                      :class="{ 'is-invalid': errors[`items.${index}.unit_price`] }"
                      @input="calculateItemTotal(index)"
                      required
                    />
                    <div v-if="errors[`items.${index}.unit_price`]" class="invalid-feedback">
                      {{ errors[`items.${index}.unit_price`][0] }}
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Discount</label>
                    <input
                      v-model.number="item.discount_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-control"
                      @input="calculateItemTotal(index)"
                    />
                  </div>

                  <div class="form-group">
                    <label>Total</label>
                    <input
                      :value="formatCurrency(item.total || 0)"
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
                  <label for="discount_amount">Discount</label>
                  <input
                    id="discount_amount"
                    v-model.number="form.discount_amount"
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

          <!-- Payment Information -->
          <div class="form-section">
            <h4 class="section-title">Payment Information</h4>
            <div class="form-grid">
              <div class="form-group">
                <label for="paid_amount" class="required">Paid Amount</label>
                <input
                  id="paid_amount"
                  v-model.number="form.paid_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-control"
                  :class="{ 'is-invalid': errors.paid_amount }"
                  required
                />
                <div v-if="errors.paid_amount" class="invalid-feedback">{{ errors.paid_amount[0] }}</div>
              </div>

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
              {{ saving ? 'Saving...' : (isEdit ? 'Update Invoice' : 'Create Invoice') }}
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
  name: 'SalesInvoiceModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    invoice: {
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
    const customers = ref([]);
    const products = ref([]);

    const form = reactive({
      customer_id: '',
      sale_date: new Date().toISOString().split('T')[0],
      payment_method: 'cash',
      status: 'completed',
      subtotal: 0,
      tax_amount: 0,
      discount_amount: 0,
      total_amount: 0,
      paid_amount: 0,
      notes: '',
      items: [
        {
          product_id: '',
          quantity: 1,
          unit_price: 0,
          discount_amount: 0,
          total: 0
        }
      ]
    });

    const resetForm = () => {
      form.customer_id = '';
      form.sale_date = new Date().toISOString().split('T')[0];
      form.payment_method = 'cash';
      form.status = 'completed';
      form.subtotal = 0;
      form.tax_amount = 0;
      form.discount_amount = 0;
      form.total_amount = 0;
      form.paid_amount = 0;
      form.notes = '';
      form.items = [
        {
          product_id: '',
          quantity: 1,
          unit_price: 0,
          discount_amount: 0,
          total: 0
        }
      ];
      errors.value = {};
    };

    const loadInvoiceData = () => {
      if (props.isEdit && props.invoice) {
        Object.keys(form).forEach(key => {
          if (props.invoice[key] !== undefined) {
            form[key] = props.invoice[key];
          }
        });
        
        if (props.invoice.sale_items && props.invoice.sale_items.length > 0) {
          form.items = props.invoice.sale_items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            discount_amount: item.discount_amount || 0,
            total: item.total_price
          }));
        }
      }
    };

    const loadCustomers = async () => {
      try {
        const response = await api.get('/customers');
        customers.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error loading customers:', error);
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
        quantity: 1,
        unit_price: 0,
        discount_amount: 0,
        total: 0
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
        item.unit_price = parseFloat(product.selling_price) || 0;
        calculateItemTotal(index);
      }
    };

    const calculateItemTotal = (index) => {
      const item = form.items[index];
      const subtotal = (item.quantity || 0) * (item.unit_price || 0);
      item.total = subtotal - (item.discount_amount || 0);
      calculateTotal();
    };

    const calculateSubtotal = () => {
      return form.items.reduce((sum, item) => sum + (item.total || 0), 0);
    };

    const calculateTotal = () => {
      form.subtotal = calculateSubtotal();
      form.total_amount = form.subtotal + (form.tax_amount || 0) - (form.discount_amount || 0);
      if (!form.paid_amount) {
        form.paid_amount = form.total_amount;
      }
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount || 0);
    };

    const saveSalesInvoice = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const url = props.isEdit ? `/sales/${props.invoice.id}` : '/sales';
        const method = props.isEdit ? 'put' : 'post';
        
        await api[method](url, form);
        
        showToast(
          props.isEdit ? 'Invoice updated successfully' : 'Invoice created successfully',
          'success'
        );
        
        emit('saved');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error saving invoice', 'error');
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
        loadInvoiceData();
        loadCustomers();
        loadProducts();
      }
    });

    onMounted(() => {
      loadCustomers();
      loadProducts();
    });

    return {
      form,
      errors,
      saving,
      customers,
      products,
      addItem,
      removeItem,
      updateItemPrice,
      calculateItemTotal,
      calculateSubtotal,
      calculateTotal,
      formatCurrency,
      saveSalesInvoice,
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
  grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
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
