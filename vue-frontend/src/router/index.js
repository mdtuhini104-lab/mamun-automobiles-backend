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
      path: '/',
      name: 'dashboard',
      component: () => import('../views/dashboard/DashboardLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard-home',
          component: () => import('../views/dashboard/DashboardHome.vue')
        },
        {
          path: 'inventory',
          name: 'inventory-list',
          component: () => import('../views/inventory/InventoryList.vue')
        },
        {
          path: 'pos',
          name: 'pos-home',
          component: () => import('../views/pos/PosHome.vue')
        },
        {
          path: 'purchases',
          name: 'purchases-list',
          component: () => import('../views/purchases/PurchaseList.vue')
        },
        {
          path: 'invoices',
          name: 'invoices-list',
          component: () => import('../views/invoices/InvoiceList.vue')
        },
        {
          path: 'reports',
          name: 'reports-home',
          component: () => import('../views/reports/ReportHome.vue')
        }
      ]
    }
  ]
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token');
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login' });
  } else if (to.meta.guest && isAuthenticated) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
});

export default router;
