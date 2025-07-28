import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

// Create axios instance
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    const token = authStore.token || localStorage.getItem('auth_token');

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    const authStore = useAuthStore();

    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      authStore.logout();
      window.location.href = '/login';
      return Promise.reject(error);
    }

    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      console.error('Access forbidden:', error.response.data.message);
    }

    // Handle 422 Validation Errors
    if (error.response?.status === 422) {
      console.error('Validation errors:', error.response.data.errors);
    }

    // Handle 500 Server Errors
    if (error.response?.status >= 500) {
      console.error('Server error:', error.response.data.message || 'Internal server error');
    }

    return Promise.reject(error);
  }
);

// API methods
const apiMethods = {
  // Generic HTTP methods
  get: (url, config = {}) => api.get(url, config),
  post: (url, data = {}, config = {}) => api.post(url, data, config),
  put: (url, data = {}, config = {}) => api.put(url, data, config),
  patch: (url, data = {}, config = {}) => api.patch(url, data, config),
  delete: (url, config = {}) => api.delete(url, config),

  // Auth methods
  auth: {
    login: (credentials) => api.post('/auth/login', credentials),
    logout: () => api.post('/auth/logout'),
    register: (userData) => api.post('/auth/register', userData),
    me: () => api.get('/auth/me'),
    refresh: () => api.post('/auth/refresh'),
    forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
    resetPassword: (data) => api.post('/auth/reset-password', data),
  },

  // Customer methods
  customers: {
    list: (params = {}) => api.get('/customers', { params }),
    get: (id) => api.get(`/customers/${id}`),
    create: (data) => api.post('/customers', data),
    update: (id, data) => api.put(`/customers/${id}`, data),
    delete: (id) => api.delete(`/customers/${id}`),
    statistics: () => api.get('/customers/statistics'),
  },

  // Supplier methods
  suppliers: {
    list: (params = {}) => api.get('/suppliers', { params }),
    get: (id) => api.get(`/suppliers/${id}`),
    create: (data) => api.post('/suppliers', data),
    update: (id, data) => api.put(`/suppliers/${id}`, data),
    delete: (id) => api.delete(`/suppliers/${id}`),
    statistics: () => api.get('/suppliers/statistics'),
  },

  // Product methods
  products: {
    list: (params = {}) => api.get('/products', { params }),
    get: (id) => api.get(`/products/${id}`),
    create: (data) => api.post('/products', data),
    update: (id, data) => api.put(`/products/${id}`, data),
    delete: (id) => api.delete(`/products/${id}`),
    categories: () => api.get('/products/categories'),
    bulkImport: (file) => {
      const formData = new FormData();
      formData.append('file', file);
      return api.post('/products/bulk-import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    },
    bulkExport: () => api.get('/products/bulk-export', { responseType: 'blob' }),
  },

  // Sales methods
  sales: {
    list: (params = {}) => api.get('/sales', { params }),
    get: (id) => api.get(`/sales/${id}`),
    create: (data) => api.post('/sales', data),
    update: (id, data) => api.put(`/sales/${id}`, data),
    delete: (id) => api.delete(`/sales/${id}`),
    statistics: () => api.get('/sales/statistics'),
  },

  // Purchase methods
  purchases: {
    list: (params = {}) => api.get('/purchase-orders', { params }),
    get: (id) => api.get(`/purchase-orders/${id}`),
    create: (data) => api.post('/purchase-orders', data),
    update: (id, data) => api.put(`/purchase-orders/${id}`, data),
    delete: (id) => api.delete(`/purchase-orders/${id}`),
    receive: (id, data) => api.post(`/purchase-orders/${id}/receive`, data),
  },

  // Employee methods
  employees: {
    list: (params = {}) => api.get('/employees', { params }),
    get: (id) => api.get(`/employees/${id}`),
    create: (data) => api.post('/employees', data),
    update: (id, data) => api.put(`/employees/${id}`, data),
    delete: (id) => api.delete(`/employees/${id}`),
    departments: () => api.get('/employees/departments'),
    positions: () => api.get('/employees/positions'),
  },

  // Payroll methods
  payroll: {
    list: (params = {}) => api.get('/payroll', { params }),
    get: (id) => api.get(`/payroll/${id}`),
    create: (data) => api.post('/payroll', data),
    update: (id, data) => api.put(`/payroll/${id}`, data),
    process: (data) => api.post('/payroll/process', data),
    approve: (id) => api.post(`/payroll/${id}/approve`),
    pay: (id) => api.post(`/payroll/${id}/pay`),
  },

  // Accounting methods
  accounting: {
    accounts: (params = {}) => api.get('/accounting/accounts', { params }),
    journalEntries: (params = {}) => api.get('/accounting/journal-entries', { params }),
    trialBalance: (params = {}) => api.get('/accounting/trial-balance', { params }),
    balanceSheet: (params = {}) => api.get('/accounting/balance-sheet', { params }),
    incomeStatement: (params = {}) => api.get('/accounting/income-statement', { params }),
    cashFlow: (params = {}) => api.get('/accounting/cash-flow', { params }),
  },

  // Reports methods
  reports: {
    sales: (params = {}) => api.get('/reports/sales', { params }),
    inventory: (params = {}) => api.get('/reports/inventory', { params }),
    customers: (params = {}) => api.get('/reports/customers', { params }),
    suppliers: (params = {}) => api.get('/reports/suppliers', { params }),
    financial: (params = {}) => api.get('/reports/financial', { params }),
    export: (type, params = {}) => api.get(`/reports/export/${type}`, { 
      params, 
      responseType: 'blob' 
    }),
  },

  // User management methods
  users: {
    list: (params = {}) => api.get('/users', { params }),
    get: (id) => api.get(`/users/${id}`),
    create: (data) => api.post('/users', data),
    update: (id, data) => api.put(`/users/${id}`, data),
    delete: (id) => api.delete(`/users/${id}`),
    roles: () => api.get('/users/roles'),
    permissions: () => api.get('/users/permissions'),
  },

  // Settings methods
  settings: {
    get: () => api.get('/settings'),
    update: (data) => api.put('/settings', data),
    paymentGateways: () => api.get('/settings/payment-gateways'),
    updatePaymentGateway: (gateway, data) => api.put(`/settings/payment-gateways/${gateway}`, data),
  },
};

export default apiMethods;
export { api };
