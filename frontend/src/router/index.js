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
      path: '/verify/invoice/:invoice_no',
      name: 'verify.invoice',
      component: () => import('../views/verification/InvoiceVerificationView.vue'),
      meta: { public: true }
    },
    {
      path: '/portal/:uuid',
      name: 'customer-portal',
      component: () => import('../views/customers/CustomerPortalDashboard.vue'),
      meta: { public: true }
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
          path: 'live-board',
          name: 'workshop.live-board',
          component: () => import('../views/dashboard/WorkshopLiveBoard.vue'),
          meta: { permission: 'work_orders.view' }
        },
        {
          path: 'qc-delivery',
          name: 'workshop.qc-delivery',
          component: () => import('../views/dashboard/QcDeliveryDashboard.vue'),
          meta: { permission: 'quality_controls.manage' }
        },
        {
          path: 'executive',
          name: 'dashboard.executive',
          component: () => import('../views/dashboard/ExecutiveCommandCenter.vue'),
          meta: { permission: 'analytics.view' }
        },
        {
          path: 'ai-inbox',
          name: 'dashboard.ai-inbox',
          component: () => import('../views/dashboard/AiRecommendationInbox.vue'),
          meta: { permission: 'ai_operations.view' }
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
          path: 'crm/customer-ledger',
          name: 'crm.customer-ledger',
          component: () => import('../views/crm/CustomerLedgerView.vue'),
          meta: { permission: 'customers.view' }
        },
        {
          path: 'crm/customer-pricing',
          name: 'crm.customer-pricing',
          component: () => import('../views/crm/CustomerPricingView.vue'),
          meta: { permission: 'customers.view' }
        },
        {
          path: 'crm/customer-statement/:id',
          name: 'crm.customer-statement',
          component: () => import('../views/crm/CustomerStatementView.vue'),
          meta: { permission: 'customers.view' }
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
          path: 'vehicles/history',
          name: 'vehicles.history.index',
          component: () => import('../views/vehicles/VehicleHistoryView.vue'),
          meta: { permission: 'vehicles.view' }
        },
        {
          path: 'vehicles/history/:id',
          name: 'vehicles.history.timeline',
          component: () => import('../views/vehicles/VehicleTimelineView.vue'),
          meta: { permission: 'vehicles.view' }
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
          path: 'workshop/hub',
          name: 'workshop.hub',
          component: () => import('../views/workshop/WorkshopOperationsHub.vue'),
          meta: { permission: 'job_cards.view' }
        },
        {
          path: 'workshop/intake',
          name: 'workshop.intake',
          component: () => import('../views/workshop/FrontdeskIntake.vue'),
          meta: { permission: 'job_cards.create' }
        },
        {
          path: 'workshop/inspection/:id?',
          name: 'workshop.inspection',
          component: () => import('../views/workshop/InspectionWorkspace.vue'),
          meta: { permission: 'job_cards.edit' }
        },
        {
          path: 'workshop/diagnosis/:id?',
          name: 'workshop.diagnosis',
          component: () => import('../views/workshop/DiagnosisCenter.vue'),
          meta: { permission: 'job_cards.edit' }
        },
        {
          path: 'workshop/quotation/:id?',
          name: 'workshop.quotation',
          component: () => import('../views/workshop/QuotationWorkspace.vue'),
          meta: { permission: 'quotations.create' }
        },
        {
          path: 'workshop/approvals',
          name: 'workshop.approvals',
          component: () => import('../views/workshop/ApprovalTrackingWorkspace.vue'),
          meta: { permission: 'quotations.approve' }
        },
        {
          path: 'workshop/work-orders',
          name: 'workshop.work-orders',
          component: () => import('../views/workshop/WorkOrderCommandCenter.vue'),
          meta: { permission: 'work_orders.view' }
        },
        {
          path: 'workshop/bays',
          name: 'workshop.bays',
          component: () => import('../views/workshop/BayOperationsBoard.vue'),
          meta: { permission: 'work_orders.view' }
        },
        {
          path: 'workshop/technician-tasks',
          name: 'workshop.technician-tasks',
          component: () => import('../views/workshop/TechnicianMobileTaskScreen.vue'),
          meta: { permission: 'work_orders.edit' }
        },
        {
          path: 'workshop/parts-consumption/:id?',
          name: 'workshop.parts-consumption',
          component: () => import('../views/workshop/PartsConsumptionWorkspace.vue'),
          meta: { permission: 'work_orders.edit' }
        },
        {
          path: 'workshop/qc/:id?',
          name: 'workshop.qc',
          component: () => import('../views/workshop/QcVerificationWorkspace.vue'),
          meta: { permission: 'quality_controls.manage' }
        },
        {
          path: 'workshop/delivery/:id?',
          name: 'workshop.delivery',
          component: () => import('../views/workshop/DeliveryHandoverWorkspace.vue'),
          meta: { permission: 'vehicle_deliveries.manage' }
        },
        {
          path: 'workshop/settlement/:id?',
          name: 'workshop.settlement',
          component: () => import('../views/workshop/InvoiceSettlementWorkspace.vue'),
          meta: { permission: 'invoices.view' }
        },
        {
          path: 'workshop/warranty-comeback',
          name: 'workshop.warranty-comeback',
          component: () => import('../views/workshop/WarrantyComebackWorkspace.vue'),
          meta: { permission: 'job_cards.view' }
        },
        {
          path: 'workshop/tracker/:id?',
          name: 'workshop.tracker',
          component: () => import('../views/workshop/CustomerWorkflowTracker.vue'),
          meta: { permission: 'job_cards.view' }
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
          path: 'attendance-dashboard',
          name: 'attendance.dashboard',
          component: () => import('../views/hr/AttendanceDashboard.vue'),
          meta: { permission: 'attendances.view' }
        },
        {
          path: 'daily-attendance',
          name: 'attendance.daily',
          component: () => import('../views/hr/DailyAttendance.vue'),
          meta: { permission: 'attendances.view' }
        },
        {
          path: 'monthly-attendance',
          name: 'attendance.monthly',
          component: () => import('../views/hr/MonthlyAttendance.vue'),
          meta: { permission: 'attendances.view' }
        },
        {
          path: 'leave-management',
          name: 'attendance.leave',
          component: () => import('../views/hr/LeaveManagement.vue'),
          meta: { permission: 'attendances.view' }
        },
        {
          path: 'payroll-dashboard',
          name: 'payroll.dashboard',
          component: () => import('../views/hr/PayrollDashboard.vue'),
          meta: { permission: 'payrolls.view' }
        },
        {
          path: 'generate-payroll',
          name: 'payroll.generate',
          component: () => import('../views/hr/GeneratePayroll.vue'),
          meta: { permission: 'payrolls.create' }
        },
        {
          path: 'payslip-preview',
          name: 'payroll.payslip',
          component: () => import('../views/hr/PayslipPreview.vue'),
          meta: { permission: 'payrolls.view' }
        },
        {
          path: 'salary-structure',
          name: 'payroll.salary',
          component: () => import('../views/hr/SalaryStructure.vue'),
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
          path: 'saas/billing',
          name: 'saas.billing',
          component: () => import('../views/saas/BillingCenter.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'saas/marketplace',
          name: 'saas.marketplace',
          component: () => import('../views/saas/PluginMarketplace.vue'),
          meta: { permission: 'settings.view' }
        },
        {
          path: 'fleet',
          name: 'fleet.dashboard',
          component: () => import('../views/fleet/FleetDashboard.vue'),
          meta: { permission: 'vehicles.view' }
        },
        {
          path: 'developer',
          name: 'developer.portal',
          component: () => import('../views/developer/DeveloperPortal.vue'),
          meta: { permission: 'settings.view' }
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
        },
        {
          path: 'incident-center',
          name: 'settings.incident-center',
          component: () => import('../views/settings/IncidentCenter.vue'),
          meta: { permission: 'settings.view' }
        },
        {
          path: 'production-operations',
          name: 'settings.production-operations',
          component: () => import('../views/settings/ProductionOperationsDashboard.vue'),
          meta: { permission: 'settings.view' }
        },
        {
          path: 'security-audit',
          name: 'settings.security-audit',
          component: () => import('../views/settings/SecurityAuditCenter.vue'),
          meta: { permission: 'settings.view' }
        },
        {
          path: 'onboarding',
          name: 'settings.onboarding',
          component: () => import('../views/onboarding/OnboardingWizard.vue'),
          meta: { permission: 'settings.view' }
        },
        {
          path: 'support',
          name: 'settings.support-center',
          component: () => import('../views/support/SupportCenter.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'support/incidents',
          name: 'support.incident-center',
          component: () => import('../views/support/IncidentCenter.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'saas/success',
          name: 'saas.customer-success',
          component: () => import('../views/saas/CustomerSuccessDashboard.vue'),
          meta: { permission: 'saas_admin.view' }
        },
        {
          path: 'backup',
          name: 'backup.dashboard',
          component: () => import('../views/BackupDashboard.vue'),
          meta: { permission: 'settings.view' }
        },
        {
          path: 'notifications',
          name: 'notifications.dashboard',
          component: () => import('../views/NotificationDashboard.vue'),
          meta: { permission: 'settings.view' }
        }
        // Dashboard
        ,{ path: 'business-kpi', name: 'dashboard.business-kpi', component: () => import('../views/dashboard/BusinessKpiCenter.vue'), meta: { permission: 'dashboard.view' } },
        
        // CRM
        { path: 'crm/segmentation', name: 'crm.segmentation', component: () => import('../views/crm/CustomerSegmentation.vue'), meta: { permission: 'customers.view' } },
        { path: 'crm/loyalty', name: 'crm.loyalty', component: () => import('../views/crm/LoyaltyProgram.vue'), meta: { permission: 'customers.view' } },
        { path: 'crm/memberships', name: 'crm.memberships', component: () => import('../views/crm/MembershipTiers.vue'), meta: { permission: 'customers.view' } },
        { path: 'crm/followups', name: 'crm.followups', component: () => import('../views/crm/FollowUps.vue'), meta: { permission: 'customers.view' } },
        { path: 'crm/complaints', name: 'crm.complaints', component: () => import('../views/crm/Complaints.vue'), meta: { permission: 'customers.view' } },
        { path: 'crm/feedback', name: 'crm.feedback', component: () => import('../views/crm/FeedbackManagement.vue'), meta: { permission: 'customers.view' } },
        { path: 'crm/reminders', name: 'crm.reminders', component: () => import('../views/crm/ServiceReminders.vue'), meta: { permission: 'customers.view' } },
        { path: 'crm/vehicle-health', name: 'crm.vehicle-health', component: () => import('../views/crm/VehicleHealthScore.vue'), meta: { permission: 'customers.view' } },
        
        // Workshop & Front Desk
        { path: 'workshop/reception-queue', name: 'workshop.reception-queue', component: () => import('../views/workshop/InspectionWorkspace.vue'), meta: { permission: 'job_cards.create' } },
        { path: 'workshop/service-calendar', name: 'workshop.service-calendar', component: () => import('../views/workshop/ServiceCalendar.vue'), meta: { permission: 'job_cards.create' } },
        { path: 'workshop/appointment-analytics', name: 'workshop.appointment-analytics', component: () => import('../views/workshop/AppointmentAnalytics.vue'), meta: { permission: 'analytics.view' } },
        { path: 'workshop/customer-acceptance', name: 'workshop.customer-acceptance', component: () => import('../views/workshop/CustomerAcceptance.vue'), meta: { permission: 'job_cards.create' } },
        { path: 'workshop/service-packages', name: 'workshop.service-packages', component: () => import('../views/workshop/ServicePackages.vue'), meta: { permission: 'job_cards.create' } },
        { path: 'workshop/labor-tracking', name: 'workshop.labor-tracking', component: () => import('../views/workshop/LaborTracking.vue'), meta: { permission: 'job_cards.view' } },
        { path: 'workshop/workshop-scheduling', name: 'workshop.workshop-scheduling', component: () => import('../views/workshop/WorkshopScheduling.vue'), meta: { permission: 'work_orders.view' } },
        { path: 'workshop/digital-checklists', name: 'workshop.digital-checklists', component: () => import('../views/workshop/DigitalChecklists.vue'), meta: { permission: 'quality_controls.manage' } },

        // Inventory
        { path: 'inventory/stock-levels', name: 'inventory.stock-levels', component: () => import('../views/inventory/StockLevels.vue'), meta: { permission: 'inventory.view' } },
        { path: 'inventory/stock-transfers', name: 'inventory.stock-transfers', component: () => import('../views/inventory/StockTransfers.vue'), meta: { permission: 'inventory.edit' } },
        { path: 'inventory/stock-adjustments', name: 'inventory.stock-adjustments', component: () => import('../views/inventory/StockAdjustments.vue'), meta: { permission: 'inventory.edit' } },
        { path: 'inventory/goods-receiving', name: 'inventory.goods-receiving', component: () => import('../views/inventory/GoodsReceiving.vue'), meta: { permission: 'purchases.edit' } },
        { path: 'inventory/purchase-returns', name: 'inventory.purchase-returns', component: () => import('../views/inventory/PurchaseReturns.vue'), meta: { permission: 'purchases.edit' } },
        { path: 'inventory/intelligence', name: 'inventory.intelligence', component: () => import('../views/inventory/InventoryIntelligence.vue'), meta: { permission: 'analytics.view' } },
        
        // Finance
        { path: 'finance/chart-of-accounts', name: 'finance.chart-of-accounts', component: () => import('../views/finance/ChartOfAccounts.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/journal-entries', name: 'finance.journal-entries', component: () => import('../views/finance/JournalEntries.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/vouchers', name: 'finance.vouchers', component: () => import('../views/finance/Vouchers.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/cost-centers', name: 'finance.cost-centers', component: () => import('../views/finance/CostCenters.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/cash-management', name: 'finance.cash-management', component: () => import('../views/finance/CashManagement.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/bank-accounts', name: 'finance.bank-accounts', component: () => import('../views/finance/BankAccounts.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/bank-reconciliation', name: 'finance.bank-reconciliation', component: () => import('../views/finance/BankReconciliation.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/profit-loss', name: 'finance.profit-loss', component: () => import('../views/finance/ProfitLoss.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/balance-sheet', name: 'finance.balance-sheet', component: () => import('../views/finance/BalanceSheet.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/cash-flow', name: 'finance.cash-flow', component: () => import('../views/finance/CashFlow.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/trial-balance', name: 'finance.trial-balance', component: () => import('../views/finance/TrialBalance.vue'), meta: { permission: 'accounts.view' } },
        { path: 'finance/tax-reports', name: 'finance.tax-reports', component: () => import('../views/finance/TaxReports.vue'), meta: { permission: 'accounts.view' } },
        
        // HR
        { path: 'hr/departments', name: 'hr.departments', component: () => import('../views/hr/Departments.vue'), meta: { permission: 'staff.view' } },
        { path: 'hr/designations', name: 'hr.designations', component: () => import('../views/hr/Designations.vue'), meta: { permission: 'staff.view' } },
        { path: 'hr/shifts', name: 'hr.shifts', component: () => import('../views/hr/ShiftManagement.vue'), meta: { permission: 'staff.view' } },
        { path: 'hr/bonuses', name: 'hr.bonuses', component: () => import('../views/hr/Bonuses.vue'), meta: { permission: 'payrolls.view' } },
        { path: 'hr/overtime', name: 'hr.overtime', component: () => import('../views/hr/Overtime.vue'), meta: { permission: 'payrolls.view' } },
        { path: 'hr/loans', name: 'hr.loans', component: () => import('../views/hr/LoansAdvances.vue'), meta: { permission: 'payrolls.view' } },
        { path: 'hr/analytics', name: 'hr.analytics', component: () => import('../views/hr/HrAnalytics.vue'), meta: { permission: 'analytics.view' } },
        { path: 'attendance/leave', name: 'attendance.leave', component: () => import('../views/attendances/AttendanceList.vue'), meta: { permission: 'attendances.view' } },
        
        // AI & Analytics
        { path: 'ai/revenue-forecast', name: 'ai.revenue-forecast', component: () => import('../views/ai/RevenueForecasting.vue'), meta: { permission: 'analytics.view' } },
        { path: 'ai/customer-churn', name: 'ai.customer-churn', component: () => import('../views/ai/CustomerChurn.vue'), meta: { permission: 'analytics.view' } },
        { path: 'ai/inventory-prediction', name: 'ai.inventory-prediction', component: () => import('../views/ai/InventoryPrediction.vue'), meta: { permission: 'analytics.view' } },
        { path: 'ai/workshop-analytics', name: 'ai.workshop-analytics', component: () => import('../views/ai/WorkshopAnalytics.vue'), meta: { permission: 'analytics.view' } },
        { path: 'ai/workflow-automation', name: 'ai.workflow-automation', component: () => import('../views/ai/WorkflowAutomation.vue'), meta: { permission: 'settings.manage' } },
        { path: 'ai/index', name: 'ai.index', component: () => import('../views/dashboard/AiRecommendationInbox.vue'), meta: { permission: 'ai_operations.view' } },
        
        // Reports
        { path: 'reports/executive', name: 'reports.executive', component: () => import('../views/reports/ExecutiveReports.vue'), meta: { permission: 'analytics.view' } },

        // Settings / Admin
        { path: 'settings/company', name: 'settings.company', component: () => import('../views/settings/CompanySettings.vue'), meta: { permission: 'settings.manage' } },
        { path: 'settings/api', name: 'settings.api', component: () => import('../views/settings/ApiSettings.vue'), meta: { permission: 'settings.manage' } },
        
        // Notification & Backup
        { path: 'backup/dashboard', name: 'backup.dashboard', component: () => import('../views/settings/CompanySettings.vue'), meta: { permission: 'settings.manage' } }
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
      if (to.name === 'dashboard-home') {
        next({ name: 'login' });
      } else {
        next({ name: 'dashboard-home' });
      }
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
