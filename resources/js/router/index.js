import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useLicenseStore } from '@/stores/license';

// Import components
import Activation from '@/components/auth/Activation.vue';
import Landing from '@/components/Landing.vue';
import Plans from '@/components/Plans.vue';
import Login from '@/components/auth/Login.vue';
import Register from '@/components/auth/Register.vue';
import ForgotPassword from '@/components/auth/ForgotPassword.vue';
import ResetPassword from '@/components/auth/ResetPassword.vue';
import Welcome from '@/components/auth/Welcome.vue';
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
import SubscriptionPlan from '@/components/profile/SubscriptionPlan.vue';
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
import CreatePurchaseReturn from '@/components/purchase/CreatePurchaseReturn.vue';
import EditPurchaseReturn from '@/components/purchase/EditPurchaseReturn.vue';
import PurchaseReturnView from '@/components/purchase/PurchaseReturnView.vue';
import Expenses from '@/components/expenses/Expenses.vue';
import Employees from '@/components/employees/Employees.vue';
import HRAttendance from '@/components/hr/HRAttendance.vue';
import HRLeaves from '@/components/hr/HRLeaves.vue';
import HRPayroll from '@/components/hr/HRPayroll.vue';
import HRAdvances from '@/components/hr/HRAdvances.vue';
import HRExpenseClaims from '@/components/hr/HRExpenseClaims.vue';
import Customers from '@/components/customers/Customers.vue';
import Suppliers from '@/components/suppliers/Suppliers.vue';
import Payments from '@/components/payments/Payments.vue';
import PaymentReceipts from '@/components/payment-receipts/PaymentReceipts.vue';


const routes = [
  {
    path: '/activation',
    name: 'Activation',
    component: Activation
  },
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
    path: '/welcome',
    name: 'Welcome',
    component: Welcome,
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
        path: 'manufacturing/recipes',
        name: 'Recipes',
        component: () => import('@/components/manufacturing/Recipes.vue'),
        meta: { permission: 'products.view' }
      },
      {
        path: 'manufacturing/production',
        name: 'ProductionOrders',
        component: () => import('@/components/manufacturing/ProductionOrders.vue'),
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'assets',
        name: 'Assets',
        component: () => import('@/components/assets/Assets.vue'),
        meta: { permission: 'accounting.view' }
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
        path: 'subscription',
        name: 'SubscriptionPlan',
        component: SubscriptionPlan
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
        path: 'sales',
        redirect: '/sales/invoices'
      },
      {
        path: 'sales/create',
        redirect: '/sales/invoices/create'
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
        meta: { permission: 'sales.create' },
        alias: ['/sales/create']
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
        path: 'sales/:id',
        redirect: to => `/sales/invoices/${to.params.id}`
      },
      {
        path: 'sales/:id/edit',
        redirect: to => `/sales/invoices/${to.params.id}/edit`
      },
      {
        path: 'sales/:id/print',
        redirect: to => `/sales/invoices/${to.params.id}/print`
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
        path: 'purchase/returns/create',
        name: 'CreatePurchaseReturn',
        component: CreatePurchaseReturn,
        meta: { permission: 'purchases.create' }
      },
      {
        path: 'purchase/returns/:id',
        name: 'PurchaseReturnView',
        component: PurchaseReturnView,
        meta: { permission: 'purchases.view' }
      },
      {
        path: 'purchase/returns/:id/edit',
        name: 'EditPurchaseReturn',
        component: EditPurchaseReturn,
        meta: { permission: 'purchases.edit' }
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
        redirect: '/hr/employees'
      },
      {
        path: 'employees/create',
        redirect: '/hr/employees/create'
      },
      {
        path: 'hr/employees',
        name: 'HREmployees',
        component: Employees,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'hr/employees/create',
        name: 'CreateHREmployee',
        component: Employees,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'hr/attendance',
        name: 'HRAttendance',
        component: HRAttendance,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'hr/leaves',
        name: 'HRLeaves',
        component: HRLeaves,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'hr/payroll',
        name: 'HRPayroll',
        component: HRPayroll,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'hr/advances',
        name: 'HRAdvances',
        component: HRAdvances,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'hr/expense-claims',
        name: 'HRExpenseClaims',
        component: HRExpenseClaims,
        meta: { permission: 'employees.view' }
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
        path: 'payments-out',
        name: 'PaymentsOut',
        component: Payments,
        meta: { permission: 'payments.view' }
      },
      {
        path: 'payment-receipts',
        name: 'PaymentReceipts',
        component: PaymentReceipts,
        meta: { permission: 'payment_receipts.view' }
      },
      {
        path: 'calendar',
        name: 'Calendar',
        component: () => import('@/components/calendar/CalendarView.vue'),
        meta: { permission: 'calendar.view' }
      },
      {
        path: 'tasks',
        name: 'TaskBoard',
        component: () => import('@/components/tasks/TaskBoardView.vue'),
        meta: { permission: 'tasks.view' }
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
    component: () => import('@/components/companies/CompanySetup.vue'),
    beforeEnter: (to, from, next) => {
      // If we are already on the server-rendered company-setup page, do not trigger a reload
      if (window.location.pathname === '/company-setup' || window.location.pathname.startsWith('/company-setup')) {
        return next();
      }
      const token = localStorage.getItem('auth_token');
      let targetUrl = to.fullPath;
      if (token && !to.query.token) {
        const separator = targetUrl.includes('?') ? '&' : '?';
        targetUrl += `${separator}token=${encodeURIComponent(token)}`;
      }
      window.location.href = targetUrl;
    }
  },
  {
    path: '/initiate-new-company',
    name: 'InitiateNewCompany',
    beforeEnter: (to, from, next) => {
      if (window.location.pathname === '/initiate-new-company') {
        return next();
      }
      const token = localStorage.getItem('auth_token');
      let targetUrl = '/initiate-new-company';
      if (token) {
        targetUrl += `?token=${encodeURIComponent(token)}`;
      }
      window.location.href = targetUrl;
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
  const licenseStore = useLicenseStore();

  // Initialize auth if not already done
  if (!authStore.user && localStorage.getItem('auth_token')) {
    await authStore.initializeAuth();
  }

  // Run License Verification for authenticated routes
  if (to.meta.requiresAuth && to.path !== '/activation') {
    if (authStore.isAuthenticated) {
      licenseStore.isLicenseActive = true;
      licenseStore.licenseData = licenseStore.licenseData || { status: 'active', plan: 'enterprise' };
    } else if (!licenseStore.licenseData) {
      const licenseCheck = await licenseStore.checkLicenseStatus();
      if (!licenseCheck.valid && licenseCheck.status !== 'expired') {
        return next('/activation');
      }
    }
  }

  // Allow company setup, initiation, and activation routes to pass through cleanly
  if (to.path === '/company-setup' || to.path.startsWith('/company-setup') || to.path === '/initiate-new-company' || to.path === '/activation') {
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
    if (authStore.isDeactivated || !localStorage.getItem('auth_token')) {
      if (window.electronAPI?.isElectron) {
        return next('/welcome');
      }
      return next('/');
    }
    if (window.electronAPI?.isElectron) {
      return next('/welcome');
    }
    return next('/login');
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard');
  } else if (to.meta.permission && !authStore.hasPermission(to.meta.permission)) {
    next('/dashboard'); // Redirect to dashboard if no permission
  } else {
    // If electron and heading to standard landing/login, hijack it to welcome
    if (window.electronAPI?.isElectron && (to.path === '/' || to.path === '/login' || to.path === '/register')) {
      return next('/welcome');
    }
    next();
  }
});

export default router;
