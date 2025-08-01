import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Import components
import Login from '@/components/auth/Login.vue';
import MainLayout from '@/components/layout/MainLayout.vue';
import Dashboard from '@/components/Dashboard.vue';
import POS from '@/components/pos/POS.vue';
import Products from '@/components/products/Products.vue';
import Users from '@/components/users/Users.vue';
import Roles from '@/components/roles/Roles.vue';
import Inventory from '@/components/inventory/Inventory.vue';
import Accounting from '@/components/accounting/Accounting.vue';
import Reports from '@/components/reports/Reports.vue';
import UserProfile from '@/components/profile/UserProfile.vue';
import Settings from '@/components/settings/Settings.vue';
import SalesInvoices from '@/components/sales/SalesInvoices.vue';
import SalesReturns from '@/components/sales/SalesReturns.vue';
import PurchaseOrders from '@/components/purchase/PurchaseOrders.vue';
import PurchaseReturns from '@/components/purchase/PurchaseReturns.vue';
import Expenses from '@/components/expenses/Expenses.vue';
import Employees from '@/components/employees/Employees.vue';
import Customers from '@/components/customers/Customers.vue';
import Suppliers from '@/components/suppliers/Suppliers.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'pos',
        name: 'POS',
        component: POS,
        meta: { permission: 'pos.access' }
      },
      {
        path: 'products',
        name: 'Products',
        component: Products,
        meta: { permission: 'products.view' }
      },
      {
        path: 'users',
        name: 'Users',
        component: Users,
        meta: { permission: 'users.view' }
      },
      {
        path: 'roles',
        name: 'Roles',
        component: Roles,
        meta: { permission: 'users.view' }
      },
      {
        path: 'inventory',
        name: 'Inventory',
        component: Inventory,
        meta: { permission: 'inventory.view' }
      },
      {
        path: 'accounting',
        name: 'Accounting',
        component: Accounting,
        meta: { permission: 'accounting.view' }
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
        path: 'sales/invoices',
        name: 'SalesInvoices',
        component: SalesInvoices,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'sales/returns',
        name: 'SalesReturns',
        component: SalesReturns,
        meta: { permission: 'sales.view' }
      },
      {
        path: 'purchase/orders',
        name: 'PurchaseOrders',
        component: PurchaseOrders,
        meta: { permission: 'purchases.view' }
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
        path: 'employees',
        name: 'Employees',
        component: Employees,
        meta: { permission: 'employees.view' }
      },
      {
        path: 'customers',
        name: 'Customers',
        component: Customers,
        meta: { permission: 'customers.view' }
      },
      {
        path: 'suppliers',
        name: 'Suppliers',
        component: Suppliers,
        meta: { permission: 'suppliers.view' }
      }
    ]
  },
  {
    path: '/debug/suppliers',
    name: 'SupplierDebug',
    component: () => import('@/components/debug/SupplierDebug.vue'),
    meta: { requiresAuth: true }
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

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/');
  } else if (to.meta.permission && !authStore.hasPermission(to.meta.permission)) {
    next('/'); // Redirect to dashboard if no permission
  } else {
    next();
  }
});

export default router;
