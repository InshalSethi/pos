import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Import components
import Landing from '@/components/Landing.vue';
import Plans from '@/components/Plans.vue';
import Login from '@/components/auth/Login.vue';
import Register from '@/components/auth/Register.vue';
import ForgotPassword from '@/components/auth/ForgotPassword.vue';
import ResetPassword from '@/components/auth/ResetPassword.vue';
import MainLayout from '@/components/layout/MainLayout.vue';
import Dashboard from '@/components/Dashboard.vue';

import Products from '@/components/products/Products.vue';
import CreateProduct from '@/components/products/CreateProduct.vue';
import EditProduct from '@/components/products/EditProduct.vue';


import Inventory from '@/components/inventory/Inventory.vue';
import ProductVariations from '@/components/inventory/ProductVariations.vue';
import Accounting from '@/components/accounting/Accounting.vue';
import Transactions from '@/components/transactions/Transactions.vue';
import Reports from '@/components/reports/Reports.vue';
import UserProfile from '@/components/profile/UserProfile.vue';
import Settings from '@/components/settings/Settings.vue';
import ManageCompanies from '@/components/companies/ManageCompanies.vue';
import EditCompany from '@/components/companies/EditCompany.vue';
import SalesInvoices from '@/components/sales/SalesInvoices.vue';
import CreateInvoice from '@/components/sales/CreateInvoice.vue';
import SalesInvoiceView from '@/components/sales/SalesInvoiceView.vue';
import SalesInvoicePrint from '@/components/sales/SalesInvoicePrint.vue';
import EditInvoice from '@/components/sales/EditInvoice.vue';
import SalesReturns from '@/components/sales/SalesReturns.vue';
import CreateReturn from '@/components/sales/CreateReturn.vue';
import SalesReturnView from '@/components/sales/SalesReturnView.vue';
import EditReturn from '@/components/sales/EditReturn.vue';
import SalesReturnPrint from '@/components/sales/SalesReturnPrint.vue';
import PurchaseOrders from '@/components/purchase/PurchaseOrders.vue';
import CreatePurchaseOrder from '@/components/purchase/CreatePurchaseOrder.vue';
import EditPurchaseOrder from '@/components/purchase/EditPurchaseOrder.vue';
import PurchaseOrderView from '@/components/purchase/PurchaseOrderView.vue';
import ReceivePurchaseOrder from '@/components/purchase/ReceivePurchaseOrder.vue';
import PurchaseReturns from '@/components/purchase/PurchaseReturns.vue';
import Expenses from '@/components/expenses/Expenses.vue';
import Employees from '@/components/employees/Employees.vue';
import Customers from '@/components/customers/Customers.vue';
import Suppliers from '@/components/suppliers/Suppliers.vue';
import Payments from '@/components/payments/Payments.vue';
import PaymentReceipts from '@/components/payment-receipts/PaymentReceipts.vue';


const routes = [
  {
    path: '/',
    name: 'Landing',
    component: Landing
  },
  {
    path: '/plans',
    name: 'Plans',
    component: Plans
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresGuest: true }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: { requiresGuest: true }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'companies',
        name: 'ManageCompanies',
        component: ManageCompanies
      },
      {
        path: 'companies/edit',
        name: 'EditCompany',
        component: EditCompany
      },
      {
        path: 'products',
        name: 'Products',
        component: Products,
        meta: { permission: 'products.view' }
      },
      {
        path: 'products/create',
        name: 'CreateProduct',
        component: CreateProduct,
        meta: { permission: 'products.create' }
      },
      {
        path: 'products/:id/edit',
        name: 'EditProduct',
        component: EditProduct,
        meta: { permission: 'products.edit' }
      },
      {
        path: 'inventory/groups',
        redirect: '/inventory/adjustments'
      },
      {
        path: 'inventory/categories-brands',
        name: 'CategoriesBrands',
        component: () => import('@/components/inventory/CategoriesBrands.vue'),
        meta: { permission: 'inventory.view' }
      },


      {
        path: 'inventory/adjustments/create',
        name: 'CreateAdjustment',
        component: () => import('@/components/inventory/CreateAdjustment.vue'),
        meta: { permission: 'inventory.adjust' }
      },
      {
        path: 'inventory/adjustments',
        name: 'AdjustmentsIndex',
        component: Inventory,
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'inventory',
        name: 'Inventory',
        component: Inventory,
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'inventory/product-variations',
        name: 'ProductVariations',
        component: ProductVariations,
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'inventory/warehouses',
        name: 'Warehouses',
        component: () => import('@/components/inventory/Warehouses.vue'),
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'inventory/transfer-orders',
        name: 'TransferOrders',
        component: () => import('@/components/inventory/TransferOrders.vue'),
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'inventory/transfer-orders/create',
        name: 'CreateTransferOrder',
        component: () => import('@/components/inventory/CreateTransferOrder.vue'),
        meta: { permission: 'inventory.edit' }
      },
      {
        path: 'inventory/transfer-orders/:id',
        name: 'TransferOrderView',
        component: () => import('@/components/inventory/TransferOrderView.vue'),
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'inventory/histories',
        name: 'Histories',
        component: () => import('@/components/inventory/Histories.vue'),
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'accounting',
        redirect: '/accounting/chart-of-accounts'
      },
      {
        path: 'accounting/chart-of-accounts',
        name: 'ChartOfAccounts',
        component: () => import('@/components/banking/BankingAccounts.vue'),
        meta: { permission: 'accounting.view' }
      },
      {
        path: 'accounting/journal-entries',
        name: 'AccountingJournalEntries',
        component: () => import('@/components/banking/BankingManualJournals.vue'),
        meta: { permission: 'accounting.view' }
      },
      {
        path: 'banking/accounts',
        name: 'BankAccounts',
        component: () => import('@/components/banking/BankAccounts.vue'),
        meta: { permission: 'accounting.view' }
      },
      {
        path: 'banking/accounts/create',
        name: 'CreateBankAccount',
        component: () => import('@/components/banking/CreateBankAccount.vue'),
        meta: { permission: 'accounting.create' }
      },
      {
        path: 'banking/accounts/:id/edit',
        name: 'EditBankAccount',
        component: () => import('@/components/banking/CreateBankAccount.vue'),
        meta: { permission: 'accounting.edit' }
      },
      {
        path: 'banking/manual-journals',
        name: 'BankingManualJournals',
        component: () => import('@/components/banking/BankingManualJournals.vue'),
        meta: { permission: 'accounting.view' }
      },
      {
        path: 'banking/manual-journals/create',
        name: 'CreateManualJournal',
        component: () => import('@/components/banking/CreateManualJournal.vue'),
        meta: { permission: 'accounting.create' }
      },
      {
        path: 'banking/transactions',
        name: 'BankingTransactions',
        component: () => import('@/components/banking/BankingTransactions.vue'),
        meta: { permission: 'accounting.view' }
      },
      {
        path: 'banking/transactions/create-income',
        name: 'CreateIncomeTransaction',
        component: () => import('@/components/banking/CreateTransaction.vue'),
        meta: { permission: 'accounting.create' }
      },
      {
        path: 'banking/transactions/create-expense',
        name: 'CreateExpenseTransaction',
        component: () => import('@/components/banking/CreateTransaction.vue'),
        meta: { permission: 'accounting.create' }
      },
      {
        path: 'banking/transfers',
        name: 'BankingTransfers',
        component: () => import('@/components/banking/BankingTransfers.vue'),
        meta: { permission: 'accounting.view' }
      },
      {
        path: 'banking/reconciliations',
        name: 'BankingReconciliations',
        component: () => import('@/components/banking/BankingReconciliations.vue'),
        meta: { permission: 'accounting.view' }
      },
      {
        path: 'transactions',
        redirect: '/banking/transactions'
      },
      {
        path: 'reports',
        name: 'Reports',
        component: Reports,
        meta: { permission: 'reports.view' }
      },
      {
        path: 'profile',
        name: 'Profile',
        component: UserProfile
      },
      {
        path: 'settings',
        name: 'Settings',
        component: Settings
      },
      {
        path: 'settings/tax-tags',
        name: 'TaxTags',
        component: () => import('@/components/settings/TaxTags.vue')
      },
      {
        path: 'sales/invoices',
        name: 'SalesInvoices',
        component: SalesInvoices,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'sales/invoices/create',
        name: 'CreateInvoice',
        component: CreateInvoice,
        meta: { permission: 'sales.create' }
      },
      {
        path: 'sales/invoices/:id',
        name: 'SalesInvoiceView',
        component: SalesInvoiceView,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'sales/invoices/:id/edit',
        name: 'EditInvoice',
        component: EditInvoice,
        meta: { permission: 'sales.edit' }
      },
      {
        path: 'sales/invoices/:id/print',
        name: 'SalesInvoicePrint',
        component: SalesInvoicePrint,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'sales/returns',
        name: 'SalesReturns',
        component: SalesReturns,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'sales/returns/create',
        name: 'CreateReturn',
        component: CreateReturn,
        meta: { permission: 'sales.create' }
      },
      {
        path: 'sales/returns/:id',
        name: 'SalesReturnView',
        component: SalesReturnView,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'sales/returns/:id/edit',
        name: 'EditReturn',
        component: EditReturn,
        meta: { permission: 'sales.edit' }
      },
      {
        path: 'sales/returns/:id/print',
        name: 'SalesReturnPrint',
        component: SalesReturnPrint,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'purchase/orders',
        name: 'PurchaseOrders',
        component: PurchaseOrders,
        meta: { permission: 'purchases.view' }
      },
      {
        path: 'purchase/orders/create',
        name: 'CreatePurchaseOrder',
        component: CreatePurchaseOrder,
        meta: { permission: 'purchases.create' }
      },
      {
        path: 'purchase/orders/:id',
        name: 'PurchaseOrderView',
        component: PurchaseOrderView,
        meta: { permission: 'purchases.view' }
      },
      {
        path: 'purchase/orders/:id/edit',
        name: 'EditPurchaseOrder',
        component: EditPurchaseOrder,
        meta: { permission: 'purchases.edit' }
      },
      {
        path: 'purchase/orders/:id/receive',
        name: 'ReceivePurchaseOrder',
        component: ReceivePurchaseOrder,
        meta: { permission: 'purchases.edit' }
      },
      {
        path: 'purchase/returns',
        name: 'PurchaseReturns',
        component: PurchaseReturns,
        meta: { permission: 'purchases.view' }
      },
      {
        path: 'expenses',
        name: 'Expenses',
        component: Expenses,
        meta: { permission: 'expenses.view' }
      },
      {
        path: 'expenses/create',
        name: 'CreateExpense',
        component: Expenses,
        meta: { permission: 'expenses.create' }
      },
      {
        path: 'employees',
        name: 'Employees',
        component: Employees,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'employees/create',
        name: 'CreateEmployee',
        component: Employees,
        meta: { permission: 'employees.create' }
      },
      {
        path: 'customers',
        name: 'Customers',
        component: Customers,
        meta: { permission: 'customers.view' }
      },
      {
        path: 'customers/create',
        name: 'CreateCustomer',
        component: Customers,
        meta: { permission: 'customers.create' }
      },
      {
        path: 'suppliers',
        name: 'Suppliers',
        component: Suppliers,
        meta: { permission: 'suppliers.view' }
      },
      {
        path: 'suppliers/create',
        name: 'CreateSupplier',
        component: Suppliers,
        meta: { permission: 'suppliers.create' }
      },
      {
        path: 'payments',
        name: 'Payments',
        component: Payments,
        meta: { permission: 'payments.view' }
      },
      {
        path: 'payment-receipts',
        name: 'PaymentReceipts',
        component: PaymentReceipts,
        meta: { permission: 'payment_receipts.view' }
      },

    ]
  },
  {
    path: '/debug/suppliers',
    name: 'SupplierDebug',
    component: () => import('@/components/debug/SupplierDebug.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/company-setup',
    name: 'CompanySetup',
    beforeEnter: (to, from, next) => {
      // If we are already on the server-rendered company-setup page, do not trigger a reload
      if (window.location.pathname === '/company-setup' || window.location.pathname.startsWith('/company-setup')) {
        return next();
      }
      window.location.href = to.fullPath;
    }
  },
  {
    path: '/initiate-new-company',
    name: 'InitiateNewCompany',
    beforeEnter: (to, from, next) => {
      if (window.location.pathname === '/initiate-new-company') {
        return next();
      }
      window.location.href = '/initiate-new-company';
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Initialize auth if not already done
  if (!authStore.user && localStorage.getItem('auth_token')) {
    await authStore.initializeAuth();
  }

  // Allow company setup and initiation routes to pass through cleanly
  if (to.path === '/company-setup' || to.path.startsWith('/company-setup') || to.path === '/initiate-new-company') {
    return next();
  }

  // Redirect to company setup if setup is not complete
  if (authStore.isAuthenticated && (authStore.user.company_id === null || !authStore.user.is_setup_completed)) {
    if (to.path !== '/login' && to.path !== '/register' && to.path !== '/company-setup') {
      window.location.href = '/company-setup';
      return;
    }
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard');
  } else if (to.meta.permission && !authStore.hasPermission(to.meta.permission)) {
    next('/dashboard'); // Redirect to dashboard if no permission
  } else {
    next();
  }
});

export default router;
