<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <!-- Enhanced Header -->
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="header-text">
            <h3>{{ isEdit ? 'Edit Supplier' : 'Add New Supplier' }}</h3>
            <p>{{ isEdit ? 'Update supplier information' : 'Create a new supplier profile' }}</p>
            <small v-if="isEdit" style="color: red;">
              DEBUG: Supplier ID: {{ supplier?.id }}, Name: {{ supplier?.name }}<br>
              Form Name: "{{ form?.name }}", Form Email: "{{ form?.email }}"<br>
              Form Object: {{ JSON.stringify(form) }}
            </small>
          </div>
        </div>
        <button @click="closeModal" class="btn-close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="saveSupplier">
        <div class="modal-body">
          <!-- Tab Navigation -->
          <div class="tab-navigation">
            <button
              type="button"
              :class="['tab-button', { active: activeTab === 'basic' }]"
              @click="activeTab = 'basic'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Basic Info
            </button>
            <button
              type="button"
              :class="['tab-button', { active: activeTab === 'contact' }]"
              @click="activeTab = 'contact'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Contact
            </button>
            <button
              type="button"
              :class="['tab-button', { active: activeTab === 'address' }]"
              @click="activeTab = 'address'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Address
            </button>
            <button
              type="button"
              :class="['tab-button', { active: activeTab === 'business' }]"
              @click="activeTab = 'business'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Business
            </button>
          </div>

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Basic Information Tab -->
            <div v-if="activeTab === 'basic'" class="tab-pane">
              <div class="form-grid">
                <div class="form-group">
                  <label for="name" class="required">Supplier Name</label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.name }"
                    placeholder="Enter supplier name"
                    required
                  />
                  <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="company_name">Company Name</label>
                  <input
                    id="company_name"
                    v-model="form.company_name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.company_name }"
                    placeholder="Enter company name"
                  />
                  <div v-if="errors.company_name" class="invalid-feedback">{{ errors.company_name[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="tax_number">Tax Number</label>
                  <input
                    id="tax_number"
                    v-model="form.tax_number"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.tax_number }"
                    placeholder="Enter tax number"
                  />
                  <div v-if="errors.tax_number" class="invalid-feedback">{{ errors.tax_number[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="is_active">Status</label>
                  <select
                    id="is_active"
                    v-model="form.is_active"
                    class="form-control"
                    :class="{ 'is-invalid': errors.is_active }"
                  >
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
                  <div v-if="errors.is_active" class="invalid-feedback">{{ errors.is_active[0] }}</div>
                </div>
              </div>

              <div class="form-group full-width">
                <label for="notes">Notes</label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  class="form-control"
                  :class="{ 'is-invalid': errors.notes }"
                  rows="3"
                  placeholder="Add any additional notes about this supplier..."
                ></textarea>
                <div v-if="errors.notes" class="invalid-feedback">{{ errors.notes[0] }}</div>
              </div>
            </div>

            <!-- Contact Information Tab -->
            <div v-if="activeTab === 'contact'" class="tab-pane">
              <div class="form-grid">
                <div class="form-group">
                  <label for="email">Email Address</label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': errors.email }"
                    placeholder="supplier@example.com"
                  />
                  <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="phone">Phone Number</label>
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': errors.phone }"
                    placeholder="+1 (555) 123-4567"
                  />
                  <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="mobile">Mobile Number</label>
                  <input
                    id="mobile"
                    v-model="form.mobile"
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': errors.mobile }"
                    placeholder="+1 (555) 987-6543"
                  />
                  <div v-if="errors.mobile" class="invalid-feedback">{{ errors.mobile[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="website">Website</label>
                  <input
                    id="website"
                    v-model="form.website"
                    type="url"
                    class="form-control"
                    :class="{ 'is-invalid': errors.website }"
                    placeholder="https://www.supplier.com"
                  />
                  <div v-if="errors.website" class="invalid-feedback">{{ errors.website[0] }}</div>
                </div>
              </div>
            </div>

            <!-- Address Information Tab -->
            <div v-if="activeTab === 'address'" class="tab-pane">
              <div class="form-group full-width">
                <label for="address">Street Address</label>
                <textarea
                  id="address"
                  v-model="form.address"
                  class="form-control"
                  :class="{ 'is-invalid': errors.address }"
                  rows="3"
                  placeholder="Enter full street address..."
                ></textarea>
                <div v-if="errors.address" class="invalid-feedback">{{ errors.address[0] }}</div>
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label for="city">City</label>
                  <input
                    id="city"
                    v-model="form.city"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.city }"
                    placeholder="Enter city"
                  />
                  <div v-if="errors.city" class="invalid-feedback">{{ errors.city[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="state">State/Province</label>
                  <input
                    id="state"
                    v-model="form.state"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.state }"
                    placeholder="Enter state or province"
                  />
                  <div v-if="errors.state" class="invalid-feedback">{{ errors.state[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="postal_code">Postal Code</label>
                  <input
                    id="postal_code"
                    v-model="form.postal_code"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.postal_code }"
                    placeholder="Enter postal code"
                  />
                  <div v-if="errors.postal_code" class="invalid-feedback">{{ errors.postal_code[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="country">Country</label>
                  <input
                    id="country"
                    v-model="form.country"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.country }"
                    placeholder="Enter country"
                  />
                  <div v-if="errors.country" class="invalid-feedback">{{ errors.country[0] }}</div>
                </div>
              </div>
            </div>

            <!-- Business Information Tab -->
            <div v-if="activeTab === 'business'" class="tab-pane">
              <div class="form-grid">
                <div class="form-group">
                  <label for="credit_limit">Credit Limit</label>
                  <div class="input-group">
                    <span class="input-prefix">$</span>
                    <input
                      id="credit_limit"
                      v-model="form.credit_limit"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-control"
                      :class="{ 'is-invalid': errors.credit_limit }"
                      placeholder="0.00"
                    />
                  </div>
                  <div v-if="errors.credit_limit" class="invalid-feedback">{{ errors.credit_limit[0] }}</div>
                </div>

                <div class="form-group">
                  <label for="payment_terms_days">Payment Terms</label>
                  <div class="input-group">
                    <input
                      id="payment_terms_days"
                      v-model="form.payment_terms_days"
                      type="number"
                      min="0"
                      class="form-control"
                      :class="{ 'is-invalid': errors.payment_terms_days }"
                      placeholder="30"
                    />
                    <span class="input-suffix">days</span>
                  </div>
                  <div v-if="errors.payment_terms_days" class="invalid-feedback">{{ errors.payment_terms_days[0] }}</div>
                </div>
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
              {{ saving ? 'Saving...' : (isEdit ? 'Update Supplier' : 'Create Supplier') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, watch, nextTick } from 'vue';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

export default {
  name: 'SupplierModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    supplier: {
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
    const activeTab = ref('basic');

    const form = reactive({
      name: '',
      company_name: '',
      email: '',
      phone: '',
      mobile: '',
      address: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      tax_number: '',
      website: '',
      notes: '',
      credit_limit: 0,
      payment_terms_days: 30,
      is_active: true
    });

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'credit_limit') {
          form[key] = 0;
        } else if (key === 'payment_terms_days') {
          form[key] = 30;
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
    };

    const loadSupplierData = () => {
      console.log('=== loadSupplierData DEBUG ===');
      console.log('props.supplier:', props.supplier);
      console.log('props.isEdit:', props.isEdit);

      if (!props.supplier || !props.isEdit) {
        console.log('Conditions not met, returning');
        return;
      }

      console.log('Form before loading:', JSON.stringify(form, null, 2));

      try {
        // Method 1: Object.assign approach
        console.log('Trying Object.assign approach...');
        const supplierData = {
          name: props.supplier.name || '',
          company_name: props.supplier.company_name || '',
          email: props.supplier.email || '',
          phone: props.supplier.phone || '',
          mobile: props.supplier.mobile || '',
          address: props.supplier.address || '',
          city: props.supplier.city || '',
          state: props.supplier.state || '',
          postal_code: props.supplier.postal_code || '',
          country: props.supplier.country || '',
          tax_number: props.supplier.tax_number || '',
          website: props.supplier.website || '',
          notes: props.supplier.notes || '',
          credit_limit: parseFloat(props.supplier.credit_limit) || 0,
          payment_terms_days: parseInt(props.supplier.payment_terms_days) || 30,
          is_active: Boolean(props.supplier.is_active)
        };

        console.log('Prepared supplier data:', supplierData);
        Object.assign(form, supplierData);
        console.log('After Object.assign - form:', form);

        console.log('Form after loading:', JSON.stringify(form, null, 2));
        console.log('Individual field check:');
        console.log('- form.name:', form.name);
        console.log('- form.email:', form.email);
        console.log('- form.phone:', form.phone);
        console.log('Supplier data loaded successfully');
      } catch (error) {
        console.error('Error loading supplier data:', error);
      }
    };

    const saveSupplier = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const url = props.isEdit ? `/suppliers/${props.supplier.id}` : '/suppliers';
        const method = props.isEdit ? 'put' : 'post';
        
        await api[method](url, form);
        
        showToast(
          props.isEdit ? 'Supplier updated successfully' : 'Supplier created successfully',
          'success'
        );
        
        emit('saved');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error saving supplier', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    const closeModal = () => {
      resetForm();
      emit('close');
    };

    watch(() => props.show, async (newVal) => {
      console.log('=== MODAL SHOW WATCHER ===');
      console.log('newVal (show):', newVal);
      console.log('props.isEdit:', props.isEdit);
      console.log('props.supplier:', props.supplier);

      if (newVal) {
        console.log('Modal is opening...');
        activeTab.value = 'basic';
        resetForm();
        console.log('Form reset completed');

        // Load supplier data after resetting form for edit mode
        if (props.isEdit && props.supplier) {
          console.log('Conditions met for loading supplier data');
          // Use nextTick to ensure DOM is updated before loading data
          await nextTick();
          console.log('After nextTick, calling loadSupplierData in 100ms');
          setTimeout(() => {
            console.log('Timeout reached, calling loadSupplierData now');
            loadSupplierData();
          }, 100); // Small delay to ensure everything is ready
        } else {
          console.log('Conditions NOT met for loading supplier data:', {
            isEdit: props.isEdit,
            hasSupplier: !!props.supplier
          });
        }
      }
    });

    // Watch for supplier prop changes to reload data
    watch(() => props.supplier, (newSupplier) => {
      if (props.show && props.isEdit && newSupplier) {
        loadSupplierData();
      }
    }, { deep: true });

    return {
      form,
      errors,
      saving,
      activeTab,
      saveSupplier,
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
  max-width: 900px;
  max-height: 95vh;
  min-height: 500px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.modal-content form {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
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
  max-height: calc(95vh - 200px);
}

.modal-footer {
  padding: 24px;
  border-top: 3px solid #667eea;
  background: #f0f4ff;
  flex-shrink: 0;
  position: relative;
  z-index: 10;
  min-height: 80px;
  margin-top: auto;
  border-bottom-left-radius: 16px;
  border-bottom-right-radius: 16px;
}

.footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  position: relative;
  z-index: 10;
}

/* Tab Navigation */
.tab-navigation {
  display: flex;
  border-bottom: 2px solid #e5e7eb;
  margin-bottom: 32px;
  gap: 4px;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: none;
  border: none;
  border-radius: 8px 8px 0 0;
  cursor: pointer;
  font-weight: 500;
  color: #6b7280;
  transition: all 0.2s ease;
  position: relative;
}

.tab-button:hover {
  color: #374151;
  background: #f3f4f6;
}

.tab-button.active {
  color: #667eea;
  background: #f0f4ff;
}

.tab-button.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: #667eea;
}

/* Form Styling */
.tab-content {
  min-height: 400px;
}

.tab-pane {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
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

/* Input Groups */
.input-group {
  display: flex;
  align-items: center;
}

.input-prefix,
.input-suffix {
  background: #f3f4f6;
  border: 2px solid #e5e7eb;
  padding: 12px 16px;
  font-weight: 600;
  color: #6b7280;
  font-size: 14px;
}

.input-prefix {
  border-right: none;
  border-radius: 8px 0 0 8px;
}

.input-suffix {
  border-left: none;
  border-radius: 0 8px 8px 0;
}

.input-group .form-control {
  border-radius: 0;
}

.input-group .form-control:first-child {
  border-radius: 8px 0 0 8px;
}

.input-group .form-control:last-child {
  border-radius: 0 8px 8px 0;
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

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  position: relative;
  z-index: 1;
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

  .tab-navigation {
    flex-wrap: wrap;
    gap: 2px;
  }

  .tab-button {
    padding: 10px 16px;
    font-size: 13px;
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
