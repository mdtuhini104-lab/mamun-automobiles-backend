import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('../views/auth/ForgotPasswordView.vue'),
      meta: { guest: true }
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('../views/auth/ResetPasswordView.vue'),
      meta: { guest: true }
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('../views/dashboard/DashboardLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard-home',
          component: () => import('../views/dashboard/DashboardHome.vue'),
          meta: { permission: 'dashboard.view' }
        },
        {
          path: 'customers',
          name: 'customers.index',
          component: () => import('../views/customers/CustomerList.vue'),
          meta: { permission: 'customers.view' }
        },
        {
          path: 'customers/create',
          name: 'customers.create',
          component: () => import('../views/customers/CustomerCreate.vue'),
          meta: { permission: 'customers.create' }
        },
        {
          path: 'customers/:id',
          name: 'customers.show',
          component: () => import('../views/customers/CustomerDetails.vue'),
          meta: { permission: 'customers.view' }
        },
        {
          path: 'customers/:id/edit',
          name: 'customers.edit',
          component: () => import('../views/customers/CustomerEdit.vue'),
          meta: { permission: 'customers.edit' }
        },
        {
          path: 'vehicles',
          name: 'vehicles.index',
          component: () => import('../views/vehicles/VehicleList.vue'),
          meta: { permission: 'vehicles.view' }
        },
        {
          path: 'vehicles/create',
          name: 'vehicles.create',
          component: () => import('../views/vehicles/VehicleForm.vue'),
          meta: { permission: 'vehicles.create' }
        },
        {
          path: 'vehicles/:id',
          name: 'vehicles.show',
          component: () => import('../views/vehicles/VehicleDetails.vue'),
          meta: { permission: 'vehicles.view' }
        },
        {
          path: 'vehicles/:id/edit',
          name: 'vehicles.edit',
          component: () => import('../views/vehicles/VehicleForm.vue'),
          meta: { permission: 'vehicles.edit' }
        },
        {
          path: 'inventory',
          name: 'inventory-list',
          component: () => import('../views/inventory/InventoryList.vue'),
          meta: { permission: 'inventory.view' }
        },
        {
          path: 'pos',
          name: 'pos-home',
          component: () => import('../views/pos/PosHome.vue'),
          meta: { permission: 'invoices.create' }
        },
        {
          path: 'purchases',
          name: 'purchases-list',
          component: () => import('../views/purchases/PurchaseList.vue'),
          meta: { permission: 'purchases.view' }
        },
        {
          path: 'invoices',
          name: 'invoices.index',
          component: () => import('../views/invoices/InvoiceList.vue'),
          meta: { permission: 'invoices.view' }
        },
        {
          path: 'invoices/:id',
          name: 'invoices.show',
          component: () => import('../views/invoices/InvoiceDetails.vue'),
          meta: { permission: 'invoices.view' }
        },
        {
          path: 'job-cards',
          name: 'job-cards.index',
          component: () => import('../views/job-cards/JobCardList.vue'),
          meta: { permission: 'job_cards.view' }
        },
        {
          path: 'job-cards/create',
          name: 'job-cards.create',
          component: () => import('../views/job-cards/JobCardForm.vue'),
          meta: { permission: 'job_cards.create' }
        },
        {
          path: 'job-cards/:id',
          name: 'job-cards.show',
          component: () => import('../views/job-cards/JobCardDetails.vue'),
          meta: { permission: 'job_cards.view' }
        },
        {
          path: 'job-cards/:id/edit',
          name: 'job-cards.edit',
          component: () => import('../views/job-cards/JobCardForm.vue'),
          meta: { permission: 'job_cards.edit' }
        },
        {
          path: 'accounts',
          name: 'accounts.index',
          component: () => import('../views/accounts/AccountList.vue'),
          meta: { permission: 'accounts.view' }
        },
        {
          path: 'staff',
          name: 'staff.index',
          component: () => import('../views/staff/StaffList.vue'),
          meta: { permission: 'staff.view' }
        },
        {
          path: 'roles',
          name: 'roles.index',
          component: () => import('../views/staff/RolesList.vue'),
          meta: { permission: 'staff.view' }
        },
        {
          path: 'attendances',
          name: 'attendances.index',
          component: () => import('../views/hr/AttendanceList.vue'),
          meta: { permission: 'attendances.view' }
        },
        {
          path: 'payrolls',
          name: 'payrolls.index',
          component: () => import('../views/hr/PayrollList.vue'),
          meta: { permission: 'payrolls.view' }
        },
        {
          path: 'analytics',
          name: 'analytics.index',
          component: () => import('../views/analytics/AnalyticsHome.vue'),
          meta: { permission: 'analytics.view' }
        },
        {
          path: 'appointments',
          name: 'appointments.index',
          component: () => import('../views/crm/AppointmentCalendar.vue'),
          meta: { permission: 'appointments.view' }
        },
        {
          path: 'saas',
          name: 'saas.index',
          component: () => import('../views/saas/SaasDashboard.vue'),
          meta: { permission: 'saas_admin.view' }
        },
        {
          path: 'ai-operations',
          name: 'ai.index',
          component: () => import('../views/ai/AiDashboard.vue'),
          meta: { permission: 'ai_operations.view' }
        },
        {
          path: 'transactions',
          name: 'transactions.index',
          component: () => import('../views/accounts/TransactionList.vue'),
          meta: { permission: 'accounts.view' }
        },
        {
          path: 'reports',
          name: 'reports-home',
          component: () => import('../views/reports/ReportHome.vue'),
          meta: { permission: 'analytics.view' }
        },
        {
          path: 'settings',
          name: 'settings.index',
          component: () => import('../views/settings/Settings.vue'),
          meta: { permission: 'settings.view' }
        },
        {
          path: 'activity-logs',
          name: 'activity-logs.index',
          component: () => import('../views/settings/ActivityLogs.vue'),
          meta: { permission: 'activity_logs.view' }
        }
      ]
    }
  ]
});

import { useAuthStore } from '../stores/auth';

let isAuthHydrated = false;

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  const token = localStorage.getItem('token');
  
  if (token && !isAuthHydrated) {
    try {
      await authStore.fetchUser();
    } catch (e) {
      console.error('Initial auth hydration failed', e);
    }
    isAuthHydrated = true;
  } else if (!token) {
    isAuthHydrated = true;
  }

  const isAuthenticated = !!authStore.token && !!authStore.user;
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login' });
  } else if (to.meta.guest && isAuthenticated) {
    next({ name: 'dashboard-home' });
  } else if (to.meta.permission && isAuthenticated) {
    if (authStore.hasRole('Super Admin') || authStore.hasPermission(to.meta.permission)) {
      next();
    } else {
      console.warn('Access denied to route:', to.path);
      next({ name: 'dashboard-home' }); // or an unauthorized page
    }
  } else {
    next();
  }
});

export default router;

router.onError((error, to) => {
  console.error('Router error caught globally:', error);
  if (error.message.includes('Failed to fetch dynamically imported module') || error.name === 'ChunkLoadError') {
    if (!to.query.reloaded) {
      const url = new URL(window.location.href);
      url.searchParams.set('reloaded', 'true');
      window.location.assign(url.href);
    }
  }
});
