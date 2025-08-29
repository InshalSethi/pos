<template>
  <div class="print-container">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Print Content -->
    <div v-else-if="invoice" class="print-content">
      <!-- Header -->
      <div class="print-header">
        <div class="company-info">
          <h1 class="company-name">Your Company Name</h1>
          <div class="company-details">
            <p>123 Business Street</p>
            <p>City, State 12345</p>
            <p>Phone: (555) 123-4567</p>
            <p>Email: info@company.com</p>
          </div>
        </div>
        <div class="invoice-info">
          <h2 class="invoice-title">SALES INVOICE</h2>
          <div class="invoice-details">
            <p><strong>Invoice #:</strong> {{ invoice.sale_number }}</p>
            <p><strong>Date:</strong> {{ formatDate(invoice.sale_date) }}</p>
            <p><strong>Status:</strong> {{ invoice.status.toUpperCase() }}</p>
          </div>
        </div>
      </div>

      <!-- Customer Information -->
      <div class="customer-section">
        <h3>Bill To:</h3>
        <div class="customer-info">
          <p v-if="invoice.customer">
            <strong>{{ invoice.customer.name }}</strong><br>
            <span v-if="invoice.customer.email">{{ invoice.customer.email }}<br></span>
            <span v-if="invoice.customer.phone">{{ invoice.customer.phone }}<br></span>
            <span v-if="invoice.customer.address">
              {{ invoice.customer.address }}<br>
              <span v-if="invoice.customer.city || invoice.customer.state">
                {{ invoice.customer.city }}{{ invoice.customer.city && invoice.customer.state ? ', ' : '' }}{{ invoice.customer.state }}
              </span>
            </span>
          </p>
          <p v-else><strong>Walk-in Customer</strong></p>
        </div>
      </div>

      <!-- Items Table -->
      <div class="items-section">
        <table class="items-table">
          <thead>
            <tr>
              <th>Description</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Discount</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in invoice.sale_items" :key="item.id">
              <td>
                <div class="item-description">
                  <strong>{{ item.product?.name || 'Unknown Product' }}</strong>
                  <div v-if="item.product?.sku" class="item-sku">SKU: {{ item.product.sku }}</div>
                </div>
              </td>
              <td class="text-center">{{ item.quantity }}</td>
              <td class="text-right">{{ formatCurrency(item.unit_price) }}</td>
              <td class="text-right">{{ formatCurrency(item.discount_amount || 0) }}</td>
              <td class="text-right">{{ formatCurrency(item.total_price) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totals Section -->
      <div class="totals-section">
        <div class="totals-table">
          <div class="total-row">
            <span class="total-label">Subtotal:</span>
            <span class="total-value">{{ formatCurrency(invoice.subtotal) }}</span>
          </div>
          <div v-if="invoice.discount_amount > 0" class="total-row">
            <span class="total-label">Discount:</span>
            <span class="total-value discount">-{{ formatCurrency(invoice.discount_amount) }}</span>
          </div>
          <div v-if="invoice.tax_amount > 0" class="total-row">
            <span class="total-label">Tax:</span>
            <span class="total-value">{{ formatCurrency(invoice.tax_amount) }}</span>
          </div>
          <div class="total-row grand-total">
            <span class="total-label">Total:</span>
            <span class="total-value">{{ formatCurrency(invoice.total_amount) }}</span>
          </div>
          <div class="total-row">
            <span class="total-label">Paid:</span>
            <span class="total-value paid">{{ formatCurrency(invoice.paid_amount) }}</span>
          </div>
          <div v-if="invoice.change_amount > 0" class="total-row">
            <span class="total-label">Change:</span>
            <span class="total-value">{{ formatCurrency(invoice.change_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Payment Information -->
      <div class="payment-section">
        <p><strong>Payment Method:</strong> {{ formatPaymentMethod(invoice.payment_method) }}</p>
        <p><strong>Cashier:</strong> {{ invoice.user?.name || 'N/A' }}</p>
      </div>

      <!-- Notes -->
      <div v-if="invoice.notes" class="notes-section">
        <h4>Notes:</h4>
        <p>{{ invoice.notes }}</p>
      </div>

      <!-- Footer -->
      <div class="print-footer">
        <p>Thank you for your business!</p>
        <p class="footer-note">This is a computer-generated invoice.</p>
      </div>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="print-actions no-print">
      <button @click="printInvoice" class="btn btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Print Invoice
      </button>
      <button @click="goBack" class="btn btn-secondary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const { showToast } = useToast();

const invoice = ref(null);
const loading = ref(true);

const fetchInvoice = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/sales/${route.params.id}`);
    invoice.value = response.data;
  } catch (err) {
    showToast('Error loading invoice', 'error');
    router.push('/sales/invoices');
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0);
};

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    card: 'Card',
    bank_transfer: 'Bank Transfer',
    mobile_payment: 'Mobile Payment',
    mixed: 'Mixed Payment'
  };
  return methods[method] || method;
};

const printInvoice = () => {
  window.print();
};

const goBack = () => {
  router.push('/sales/invoices');
};

onMounted(() => {
  fetchInvoice();
});
</script>

<style scoped>
.print-container {
  max-width: 8.5in;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Arial', sans-serif;
  background: white;
}

.print-content {
  width: 100%;
}

.print-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #333;
}

.company-name {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin: 0 0 10px 0;
}

.company-details {
  font-size: 12px;
  color: #666;
  line-height: 1.4;
}

.invoice-title {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin: 0 0 10px 0;
  text-align: right;
}

.invoice-details {
  font-size: 12px;
  text-align: right;
  line-height: 1.6;
}

.customer-section {
  margin-bottom: 30px;
}

.customer-section h3 {
  font-size: 14px;
  font-weight: bold;
  margin: 0 0 10px 0;
  color: #333;
}

.customer-info {
  font-size: 12px;
  line-height: 1.6;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 30px;
}

.items-table th,
.items-table td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
  font-size: 12px;
}

.items-table th {
  background-color: #f8f9fa;
  font-weight: bold;
  text-align: left;
  border-bottom: 2px solid #333;
}

.item-description {
  line-height: 1.4;
}

.item-sku {
  font-size: 10px;
  color: #666;
  margin-top: 2px;
}

.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}

.totals-section {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 30px;
}

.totals-table {
  width: 300px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
  font-size: 12px;
}

.grand-total {
  border-top: 2px solid #333;
  border-bottom: 2px solid #333;
  font-weight: bold;
  font-size: 14px;
  padding: 10px 0;
}

.discount {
  color: #dc3545;
}

.paid {
  color: #28a745;
}

.payment-section {
  margin-bottom: 20px;
  font-size: 12px;
  line-height: 1.6;
}

.notes-section {
  margin-bottom: 30px;
  font-size: 12px;
}

.notes-section h4 {
  margin: 0 0 10px 0;
  font-size: 14px;
}

.print-footer {
  text-align: center;
  font-size: 12px;
  color: #666;
  border-top: 1px solid #ddd;
  padding-top: 20px;
}

.footer-note {
  font-size: 10px;
  margin-top: 10px;
}

.print-actions {
  margin-top: 30px;
  text-align: center;
  display: flex;
  gap: 10px;
  justify-content: center;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #4f46e5;
  color: white;
}

.btn-primary:hover {
  background: #4338ca;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

/* Print Styles */
@media print {
  .no-print {
    display: none !important;
  }
  
  .print-container {
    padding: 0;
    margin: 0;
    max-width: none;
  }
  
  .print-content {
    page-break-inside: avoid;
  }
  
  .items-table {
    page-break-inside: auto;
  }
  
  .items-table tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }
  
  .totals-section {
    page-break-inside: avoid;
  }
  
  body {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}

@page {
  margin: 0.5in;
  size: letter;
}
</style>
