<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        $db = false;
        $redis = false;
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $db = true;
        } catch (\Exception $e) {}
        
        try {
            \Illuminate\Support\Facades\Redis::connection()->ping();
            $redis = true;
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'API v1 is running',
            'services' => [
                'database' => $db ? 'connected' : 'disconnected',
                'redis' => $redis ? 'connected' : 'disconnected',
            ]
        ], 200);
    });

    Route::get('/login', function () {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    })->name('login');

    // TEMPORARY: Deploy migrate and debug routes
    Route::get('/deploy-migrate', function () {
        try {
            $output = '';
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return response()->json([
                'success' => true,
                'message' => 'Migrations run successfully',
                'output' => \Illuminate\Support\Facades\Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    });

    Route::get('/debug-dash', [App\Http\Controllers\Api\V1\DashboardController::class, 'index']);

    Route::get('/debug-db', function () {
        try {
            $steps = [];
            
            $steps['connection'] = 'starting';
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            $steps['connection'] = 'connected';
            
            $steps['select_1'] = \Illuminate\Support\Facades\DB::select('SELECT 1');
            
            // Check driver name
            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
            $steps['driver'] = $driver;
            
            if ($driver === 'mysql') {
                $steps['tables'] = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            } elseif ($driver === 'sqlite') {
                $steps['tables'] = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            } else {
                $steps['tables'] = 'unknown driver: ' . $driver;
            }
            
            return response()->json([
                'success' => true,
                'steps' => $steps
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    });

    Route::get('/debug-users', function () {
        try {
            $users = \App\Models\User::all()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                ];
            });
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    });

    Route::get('/debug-dashboard-auth/{userId}', function ($userId) {
        try {
            $user = \App\Models\User::findOrFail($userId);
            auth()->login($user);
            
            $data = app(App\Services\DashboardService::class)->getDashboardData();
            return response()->json([
                'success' => true,
                'message' => 'Dashboard loaded successfully for user ' . $user->email,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    });

    // Public Verification Route
    Route::get('/verify/invoice/{invoice_no}', [App\Http\Controllers\Api\VerificationController::class, 'verifyInvoice']);
    Route::get('/portal/access/{uuid}', [App\Http\Controllers\Api\V1\CustomerPortalController::class, 'accessViaUuid']);
    Route::post('/portal/analytics/log-event', [App\Http\Controllers\Api\V1\CustomerPortalAnalyticsController::class, 'logEvent']);

    // Auth routes (Public)
    Route::post('/auth/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/auth/forgot-password', [App\Http\Controllers\Api\V1\AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/auth/reset-password', [App\Http\Controllers\Api\V1\AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    // Protected routes
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/auth/user', function (Illuminate\Http\Request $request) {
            return response()->json([
                'data' => new \App\Http\Resources\UserResource($request->user())
            ]);
        });
        Route::post('/auth/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/auth/me', [App\Http\Controllers\Api\V1\AuthController::class, 'me']);

        // Global Search
        Route::get('/search', [App\Http\Controllers\Api\V1\GlobalSearchController::class, 'search']);

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Api\V1\DashboardController::class, 'index']);

        // User Management
        Route::get('/users', [App\Http\Controllers\Api\V1\UserController::class, 'index']);
        Route::post('/users', [App\Http\Controllers\Api\V1\UserController::class, 'store']);
        Route::get('/users/{id}', [App\Http\Controllers\Api\V1\UserController::class, 'show']);
        Route::put('/users/{id}', [App\Http\Controllers\Api\V1\UserController::class, 'update']);
        Route::delete('/users/{id}', [App\Http\Controllers\Api\V1\UserController::class, 'destroy']);
        
        // Roles & Permissions (Granular RBAC)
        Route::get('/permissions', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'getPermissions']);
        Route::get('/roles', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'getRoles']);
        Route::post('/roles', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'createRole']);
        Route::put('/roles/{id}', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'updateRole']);
        Route::delete('/roles/{id}', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'deleteRole']);
        Route::post('/roles/{id}/clone', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'cloneRole']);
        Route::post('/users/{userId}/permissions', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'assignUserPermissions']);
        Route::get('/permission-audits', [App\Http\Controllers\Api\V1\RolePermissionController::class, 'getAuditLogs']);

        
        // HR & Payroll
        Route::get('/hr/attendances', [App\Http\Controllers\Api\AttendanceController::class, 'index']);
        Route::post('/hr/attendances/check-in', [App\Http\Controllers\Api\AttendanceController::class, 'checkIn']);
        Route::post('/hr/attendances/check-out', [App\Http\Controllers\Api\AttendanceController::class, 'checkOut']);
        Route::post('/hr/attendances/manual', [App\Http\Controllers\Api\AttendanceController::class, 'manualEntry']);
        
        Route::get('/hr/payrolls', [App\Http\Controllers\Api\PayrollController::class, 'index']);
        Route::post('/hr/payrolls/generate', [App\Http\Controllers\Api\PayrollController::class, 'generate']);
        Route::get('/hr/payrolls/{id}', [App\Http\Controllers\Api\PayrollController::class, 'show']);
        Route::post('/hr/payrolls/{id}/approve', [App\Http\Controllers\Api\PayrollController::class, 'approve']);
        Route::post('/hr/payrolls/{id}/mark-paid', [App\Http\Controllers\Api\PayrollController::class, 'markPaid']);
        
        Route::get('/hr/leaves', [App\Http\Controllers\Api\V1\HrController::class, 'leaves']);
        
        // Analytics
        Route::get('/analytics/dashboard', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'dashboard']);

        // CRM & Appointments
        Route::get('/crm/appointments', [App\Http\Controllers\Api\V1\CrmController::class, 'getAppointments']);
        Route::post('/crm/appointments', [App\Http\Controllers\Api\V1\CrmController::class, 'storeAppointment']);
        Route::patch('/crm/appointments/{id}', [App\Http\Controllers\Api\V1\CrmController::class, 'updateAppointmentStatus']);
        Route::get('/crm/customers/{id}/timeline', [App\Http\Controllers\Api\V1\CrmController::class, 'getCustomerTimeline']);
        Route::patch('/crm/customers/{id}/tags', [App\Http\Controllers\Api\V1\CrmController::class, 'updateCustomerTags']);

        // Mobile APIs
        Route::post('/mobile/customer/login', [App\Http\Controllers\Api\V1\MobileApiController::class, 'loginCustomer'])->withoutMiddleware('auth:sanctum');
        Route::post('/mobile/staff/login', [App\Http\Controllers\Api\V1\MobileApiController::class, 'loginStaff'])->withoutMiddleware('auth:sanctum');
        Route::get('/mobile/customer/tracking', [App\Http\Controllers\Api\V1\MobileApiController::class, 'getCustomerServiceTracking']);
        Route::get('/mobile/staff/work-orders', [App\Http\Controllers\Api\V1\MobileApiController::class, 'getStaffWorkOrders']);

        // SaaS Super Admin
        Route::get('/saas/tenants', [App\Http\Controllers\Api\V1\SuperAdminController::class, 'getTenants']);
        Route::patch('/saas/tenants/{id}/status', [App\Http\Controllers\Api\V1\SuperAdminController::class, 'updateTenantStatus']);
        Route::get('/saas/stats', [App\Http\Controllers\Api\V1\SuperAdminController::class, 'getSystemStats']);
        Route::get('/saas/portal-analytics', [App\Http\Controllers\Api\V1\CustomerPortalAnalyticsController::class, 'getAnalyticsSummary']);

        // AI & Automations
        Route::get('/ai/insights', [App\Http\Controllers\Api\V1\AiAutomationController::class, 'getInsights']);
        Route::get('/ai/automations', [App\Http\Controllers\Api\V1\AiAutomationController::class, 'getAutomations']);
        Route::post('/ai/trigger', [App\Http\Controllers\Api\V1\AiAutomationController::class, 'triggerEvent']);
        Route::get('/ai/workshop-activity', [App\Http\Controllers\Api\V1\AiAutomationController::class, 'getLiveWorkshopActivity']);

        // Customer routes
        Route::get('/customers', [App\Http\Controllers\Api\V1\CustomerController::class, 'index'])->middleware('permission:customers.view');
        Route::post('/customers', [App\Http\Controllers\Api\V1\CustomerController::class, 'store'])->middleware('permission:customers.create');
        Route::get('/customers/{id}', [App\Http\Controllers\Api\V1\CustomerController::class, 'show'])->middleware('permission:customers.view');
        Route::put('/customers/{id}', [App\Http\Controllers\Api\V1\CustomerController::class, 'update']);
        Route::delete('/customers/{id}', [App\Http\Controllers\Api\V1\CustomerController::class, 'destroy']);
        
        // Customer Ledger
        Route::get('/customer-ledgers', [App\Http\Controllers\Api\CustomerLedgerController::class, 'index']);
        Route::get('/customer-ledgers/{customer}', [App\Http\Controllers\Api\CustomerLedgerController::class, 'show']);
        Route::get('/customer-ledgers/{customer}/statement', [App\Http\Controllers\Api\CustomerLedgerController::class, 'statement']);
        Route::post('/customer-payments', [App\Http\Controllers\Api\CustomerLedgerController::class, 'recordPayment']);
        
        // Vehicle routes
        Route::get('/vehicles', [App\Http\Controllers\Api\V1\VehicleController::class, 'indexAll']);
        Route::post('/vehicles', [App\Http\Controllers\Api\V1\VehicleController::class, 'store']);
        Route::get('/customers/{customerId}/vehicles', [App\Http\Controllers\Api\V1\VehicleController::class, 'index']);
        Route::get('/vehicles/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'show']);
        Route::get('/vehicles/{id}/health-prediction', [App\Http\Controllers\Api\V1\VehicleController::class, 'getHealthPrediction']);
        Route::put('/vehicles/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'update']);
        Route::delete('/vehicles/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'destroy']);
        
        // Vehicle History
        Route::get('/vehicle-history', [App\Http\Controllers\Api\VehicleHistoryController::class, 'index']);
        Route::get('/vehicle-history/{vehicle}', [App\Http\Controllers\Api\VehicleHistoryController::class, 'show']);
        Route::get('/vehicle-history/{vehicle}/timeline', [App\Http\Controllers\Api\VehicleHistoryController::class, 'timeline']);
        
        // Job Card routes
        Route::get('/job-cards', [App\Http\Controllers\Api\V1\JobCardController::class, 'index']);
        Route::post('/job-cards', [App\Http\Controllers\Api\V1\JobCardController::class, 'store']);
        Route::get('/job-cards/{id}', [App\Http\Controllers\Api\V1\JobCardController::class, 'show']);
        Route::get('/vehicles/{vehicleId}/job-cards', [App\Http\Controllers\Api\V1\JobCardController::class, 'vehicleHistory']);
        Route::get('/customers/{customerId}/job-cards', [App\Http\Controllers\Api\V1\JobCardController::class, 'customerHistory']);
        Route::put('/job-cards/{id}', [App\Http\Controllers\Api\V1\JobCardController::class, 'update']);
        Route::delete('/job-cards/{id}', [App\Http\Controllers\Api\V1\JobCardController::class, 'destroy']);
        
        // Quotation routes
        Route::get('/quotations', [App\Http\Controllers\Api\V1\QuotationController::class, 'index'])->middleware('permission:quotations.view');
        Route::post('/quotations', [App\Http\Controllers\Api\V1\QuotationController::class, 'store'])->middleware(['permission:quotations.create', 'idempotent']);
        Route::get('/quotations/{id}', [App\Http\Controllers\Api\V1\QuotationController::class, 'show'])->middleware('permission:quotations.view');
        Route::put('/quotations/{id}/revise', [App\Http\Controllers\Api\V1\QuotationController::class, 'revise'])->middleware(['permission:quotations.revise', 'idempotent']);
        Route::post('/quotations/{id}/approve', [App\Http\Controllers\Api\V1\QuotationController::class, 'approve'])->middleware(['permission:quotations.approve', 'idempotent']);
        Route::delete('/quotation-items/{itemId}', [App\Http\Controllers\Api\V1\QuotationController::class, 'destroyItem'])->middleware('permission:quotations.edit');

        // Work Order routes
        Route::get('/work-orders', [App\Http\Controllers\Api\V1\WorkOrderController::class, 'index'])->middleware('permission:work_orders.view');
        Route::get('/live-workshop-board', [App\Http\Controllers\Api\V1\WorkOrderController::class, 'liveBoard'])->middleware('permission:work_orders.view');
        Route::get('/work-orders/{id}', [App\Http\Controllers\Api\V1\WorkOrderController::class, 'show'])->middleware('permission:work_orders.view');
        Route::put('/work-orders/{id}/status', [App\Http\Controllers\Api\V1\WorkOrderController::class, 'updateStatus'])->middleware('permission:work_orders.edit');
        Route::post('/work-orders/{id}/additional-consumption', [App\Http\Controllers\Api\V1\WorkOrderController::class, 'addConsumption'])->middleware(['permission:work_orders.edit', 'idempotent']);

        // Customer Pricing routes
        Route::get('/customers/{customerId}/pricing', [App\Http\Controllers\Api\V1\CustomerPricingController::class, 'index']);
        Route::get('/customers/{customerId}/pricing/calculate', [App\Http\Controllers\Api\V1\CustomerPricingController::class, 'getRate']);
        Route::post('/customers/pricing', [App\Http\Controllers\Api\V1\CustomerPricingController::class, 'storeRate']);

        
        // Workshop Bay routes
        Route::get('/workshop-bays', [App\Http\Controllers\Api\V1\WorkshopBayController::class, 'index']);
        Route::post('/workshop-bays', [App\Http\Controllers\Api\V1\WorkshopBayController::class, 'store']);
        Route::get('/workshop-bays/{id}/utilization', [App\Http\Controllers\Api\V1\WorkshopBayController::class, 'getUtilization']);
        Route::get('/workshop/suggestions', [App\Http\Controllers\Api\V1\WorkshopSuggestionsController::class, 'getSuggestions']);

        // Workforce Assignment routes
        Route::get('/workforce/employees', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'listEmployees']);
        Route::get('/workforce/skills', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'listSkills']);
        Route::get('/workforce/departments', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'listDepartments']);
        Route::get('/workforce/designations', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'listDesignations']);
        Route::get('/workforce/shifts', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'listShifts']);

        Route::post('/job-cards/{id}/assign', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'assignWorkforce']);
        Route::post('/job-cards/{id}/tasks', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'createTask']);
        Route::post('/job-cards/{id}/tasks/{taskId}/assign', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'assignTask']);
        Route::post('/job-cards/assignments/{assignmentId}/complete', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'completeAssignment']);
        Route::post('/job-cards/task-assignments/{assignmentId}/complete', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'completeTaskAssignment']);
        Route::get('/workforce/employees/{id}/workload', [App\Http\Controllers\Api\V1\WorkforceAssignmentController::class, 'getEmployeeWorkload']);

        // Employee Availability logs
        Route::get('/workforce/employees/{id}/availability', [App\Http\Controllers\Api\V1\EmployeeAvailabilityController::class, 'getHistory']);
        Route::post('/workforce/employees/{id}/availability', [App\Http\Controllers\Api\V1\EmployeeAvailabilityController::class, 'updateAvailability']);

        // Employee Shift Assignments
        Route::get('/workforce/employees/{id}/shifts', [App\Http\Controllers\Api\V1\EmployeeShiftAssignmentController::class, 'getAssignments']);
        Route::post('/workforce/employees/{id}/shifts', [App\Http\Controllers\Api\V1\EmployeeShiftAssignmentController::class, 'assignShift']);
        
        // Settings Routes
        Route::get('/settings', [App\Http\Controllers\Api\V1\SettingsController::class, 'index'])->middleware('permission:settings.view');
        Route::post('/settings', [App\Http\Controllers\Api\V1\SettingsController::class, 'update'])->middleware('permission:settings.edit');

        // QC & Delivery Routes
        Route::post('/quality-control', [App\Http\Controllers\Api\V1\QcAndDeliveryController::class, 'submitQc'])->middleware('permission:quality_controls.manage');
        Route::post('/vehicle-delivery', [App\Http\Controllers\Api\V1\QcAndDeliveryController::class, 'submitDelivery'])->middleware('permission:vehicle_deliveries.manage');

        // Activity Logs Routes
        Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->middleware('permission:activity_logs.view');
        Route::get('/activity-logs/stats', [App\Http\Controllers\ActivityLogController::class, 'stats'])->middleware('permission:activity_logs.view');
        Route::get('/activity-logs/export', [App\Http\Controllers\ActivityLogController::class, 'export'])->middleware('permission:activity_logs.view');
        
        // Parts routes
        Route::get('/parts/low-stock', [App\Http\Controllers\Api\V1\PartController::class, 'lowStock']);
        Route::get('/parts', [App\Http\Controllers\Api\V1\PartController::class, 'index']);
        Route::post('/parts', [App\Http\Controllers\Api\V1\PartController::class, 'store']);
        Route::get('/parts/{id}', [App\Http\Controllers\Api\V1\PartController::class, 'show']);
        Route::put('/parts/{id}', [App\Http\Controllers\Api\V1\PartController::class, 'update']);
        Route::post('/parts/{id}/image', [App\Http\Controllers\Api\V1\PartController::class, 'uploadImage']);
        Route::delete('/parts/{id}', [App\Http\Controllers\Api\V1\PartController::class, 'destroy']);

        // Category routes
        Route::get('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
        Route::post('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'store']);
        Route::get('/categories/{id}', [App\Http\Controllers\Api\V1\CategoryController::class, 'show']);
        Route::put('/categories/{id}', [App\Http\Controllers\Api\V1\CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [App\Http\Controllers\Api\V1\CategoryController::class, 'destroy']);

        // Stock Adjustments
        Route::get('/stock-adjustments', [App\Http\Controllers\Api\V1\StockAdjustmentController::class, 'index']);
        Route::post('/stock-adjustments', [App\Http\Controllers\Api\V1\StockAdjustmentController::class, 'store']);
        Route::get('/stock-adjustments/{id}', [App\Http\Controllers\Api\V1\StockAdjustmentController::class, 'show']);
        Route::put('/stock-adjustments/{id}', [App\Http\Controllers\Api\V1\StockAdjustmentController::class, 'update']);
        Route::delete('/stock-adjustments/{id}', [App\Http\Controllers\Api\V1\StockAdjustmentController::class, 'destroy']);
        
        // Job Card Items routes
        Route::post('/job-cards/{id}/items', [App\Http\Controllers\Api\V1\JobCardItemController::class, 'store']);
        
        // Invoice routes
        Route::post('/invoices/generate/{jobCardId}', [App\Http\Controllers\Api\V1\InvoiceController::class, 'generate']);
        Route::get('/invoices', [App\Http\Controllers\Api\V1\InvoiceController::class, 'index']);
        Route::get('/invoices/{id}', [App\Http\Controllers\Api\V1\InvoiceController::class, 'show']);
        Route::get('/invoices/{id}/pdf', [App\Http\Controllers\Api\V1\InvoiceController::class, 'downloadPdf']);
        Route::post('/invoices/{id}/pay', [App\Http\Controllers\Api\V1\InvoiceController::class, 'pay']);
        Route::get('/customers/{id}/due-invoices', [App\Http\Controllers\Api\V1\InvoiceController::class, 'customerDueInvoices']);
        Route::delete('/invoices/{id}', [App\Http\Controllers\Api\V1\InvoiceController::class, 'destroy']);

        // Supplier routes
        Route::get('/suppliers', [App\Http\Controllers\Api\V1\SupplierController::class, 'index']);
        Route::post('/suppliers', [App\Http\Controllers\Api\V1\SupplierController::class, 'store']);
        Route::get('/suppliers/{id}', [App\Http\Controllers\Api\V1\SupplierController::class, 'show']);
        Route::put('/suppliers/{id}', [App\Http\Controllers\Api\V1\SupplierController::class, 'update']);
        Route::delete('/suppliers/{id}', [App\Http\Controllers\Api\V1\SupplierController::class, 'destroy']);
        Route::post('/suppliers/{id}/payments', [App\Http\Controllers\Api\V1\SupplierController::class, 'recordPayment']);
        Route::get('/suppliers/{id}/ledgers', [App\Http\Controllers\Api\V1\SupplierController::class, 'ledgerHistory']);

        // Purchase routes
        Route::get('/purchases', [App\Http\Controllers\Api\V1\PurchaseController::class, 'index']);
        Route::post('/purchases', [App\Http\Controllers\Api\V1\PurchaseController::class, 'store']);
        Route::get('/purchases/{id}', [App\Http\Controllers\Api\V1\PurchaseController::class, 'show']);
        Route::put('/purchases/{id}/status', [App\Http\Controllers\Api\V1\PurchaseController::class, 'updateStatus']);
        Route::get('/purchases/low-stock-parts', [App\Http\Controllers\Api\V1\PurchaseController::class, 'lowStockParts']);
        Route::delete('/purchases/{id}', [App\Http\Controllers\Api\V1\PurchaseController::class, 'destroy']);

        // Cashbook & Daily Closing
        Route::get('/cashbooks', [App\Http\Controllers\Api\V1\CashbookController::class, 'index']);
        Route::post('/cashbooks', [App\Http\Controllers\Api\V1\CashbookController::class, 'store']);
        Route::get('/cashbook-transactions', [App\Http\Controllers\Api\V1\CashbookController::class, 'transactions']);
        Route::post('/cashbook-transfer', [App\Http\Controllers\Api\V1\CashbookController::class, 'transfer']);
        
        Route::get('/daily-closing', [App\Http\Controllers\Api\V1\DailyClosingController::class, 'index']);
        Route::post('/daily-closing/close', [App\Http\Controllers\Api\V1\DailyClosingController::class, 'close']);
        
        Route::get('/cash-reconciliation', [App\Http\Controllers\Api\V1\CashReconciliationController::class, 'index']);
        Route::post('/cash-reconciliation', [App\Http\Controllers\Api\V1\CashReconciliationController::class, 'store']);

        // Kanban Board & Workflow
        Route::get('/workflow-board', [App\Http\Controllers\Api\V1\WorkflowBoardController::class, 'index']);
        Route::post('/workflow-board/move', [App\Http\Controllers\Api\V1\WorkflowBoardController::class, 'move']);
        
        Route::post('/job-assignments', [App\Http\Controllers\Api\V1\JobAssignmentController::class, 'assign']);
        Route::get('/job-timeline/{job}', [App\Http\Controllers\Api\V1\JobAssignmentController::class, 'timeline']);
        
        Route::post('/job-comments', [App\Http\Controllers\Api\V1\JobCommentController::class, 'store']);

        // Notifications
        Route::get('/notifications/templates', [App\Http\Controllers\Api\V1\NotificationController::class, 'templates']);
        Route::get('/notifications/logs', [App\Http\Controllers\Api\V1\NotificationController::class, 'logs']);
        Route::post('/notifications/send', [App\Http\Controllers\Api\V1\NotificationController::class, 'send']);

        // Reports and Analytics
        Route::get('/analytics/summary', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'summary']);
        Route::get('/analytics/sales', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'sales']);

        // Multi-Branch
        Route::get('/branches', [App\Http\Controllers\Api\V1\BranchController::class, 'index']);
        Route::get('/branches/{id}/analytics', [App\Http\Controllers\Api\V1\BranchController::class, 'analytics']);

        // AI Automation
        Route::get('/ai/dashboard', [App\Http\Controllers\Api\V1\AiController::class, 'dashboard']);
        Route::post('/ai/run-automation', [App\Http\Controllers\Api\V1\AiController::class, 'runAutomation']);
        Route::get('/ai/recommendations', [App\Http\Controllers\Api\V1\AiRecommendationController::class, 'index']);
        Route::post('/ai/recommendations/{id}/approve', [App\Http\Controllers\Api\V1\AiRecommendationController::class, 'approve']);
        Route::post('/ai/recommendations/{id}/reject', [App\Http\Controllers\Api\V1\AiRecommendationController::class, 'reject']);
        Route::post('/ai/anomalies/{id}/override', [App\Http\Controllers\Api\V1\AiRecommendationController::class, 'overrideAnomaly']);

        // Mobile App APIs
        Route::post('/mobile/login', [App\Http\Controllers\Api\V1\MobileApiController::class, 'login']);
        Route::post('/mobile/sync', [App\Http\Controllers\Api\V1\MobileApiController::class, 'sync']);
        Route::get('/mobile/dashboard', [App\Http\Controllers\Api\V1\MobileApiController::class, 'dashboard']);
        Route::get('/mobile/tasks', [App\Http\Controllers\Api\V1\MobileApiController::class, 'getTasks']);
        Route::put('/mobile/tasks/{id}/status', [App\Http\Controllers\Api\V1\MobileApiController::class, 'updateTaskStatus'])->middleware('idempotent');

        // System & Monitoring APIs
        Route::get('/system/health', [App\Http\Controllers\Api\V1\SystemController::class, 'getHealth']);
        Route::post('/system/backup', [App\Http\Controllers\Api\V1\SystemController::class, 'createBackup']);
        Route::post('/system/restore', [App\Http\Controllers\Api\V1\SystemController::class, 'restoreBackup']);
        Route::get('/system/production-health', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getHealth']);
        Route::post('/system/maintenance', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'toggleMaintenance']);
        Route::post('/system/failed-jobs/clear', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'clearFailedJobs']);
        Route::get('/system/failed-jobs', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getFailedJobs']);
        Route::post('/system/failed-jobs/{id}/retry', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'retryJob']);
        Route::post('/system/failed-jobs/retry-all', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'bulkRetryJobs']);
        Route::delete('/system/failed-jobs/{id}', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'deleteFailedJob']);
        Route::get('/system/performance/telemetry', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getPerformanceTelemetry']);
        Route::get('/system/operations/predictive-metrics', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getPredictiveMetrics']);
        Route::post('/system/operations/trigger-escalations', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'triggerEscalations']);
        Route::get('/system/operations/adaptive-analytics', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getAdaptiveAnalytics']);
        Route::get('/system/operations/load-balancing', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getLoadBalancingRecommendations']);
        Route::get('/system/operations/anomalies', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getAnomalyDetections']);
        Route::post('/system/operations/recommendations/{id}/action', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'actionRecommendation']);
        Route::post('/system/operations/simulate', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'simulateCoordination']);
        Route::get('/system/operations/learning-metrics', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getLearningMetrics']);
        Route::get('/system/operations/customer-quick-load', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'quickLoadCustomer']);
        Route::get('/system/operations/supervisor-dashboard', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getSupervisorDashboard']);
        Route::post('/system/operations/webhook-receiver', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'webhookCallback']);
        Route::get('/system/operations/excellence-kpis', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getExcellenceKpis']);
        Route::get('/system/operations/continuous-optimization', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getContinuousOptimization']);
        Route::get('/system/operations/executive-observation', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getExecutiveObservation']);
        Route::get('/system/operations/executive-governance', [App\Http\Controllers\Api\V1\ProductionOperationsController::class, 'getExecutiveGovernance']);
        Route::post('/system/stress/slow-query', [App\Http\Controllers\Api\V1\ReliabilityStressController::class, 'simulateSlowQuery'])->middleware('throttle:5,1,stress');
        Route::post('/system/stress/failed-job', [App\Http\Controllers\Api\V1\ReliabilityStressController::class, 'simulateFailedJob'])->middleware('throttle:5,1,stress');
        Route::post('/system/stress/websocket-disconnect', [App\Http\Controllers\Api\V1\ReliabilityStressController::class, 'simulateWebsocketDisconnect'])->middleware('throttle:5,1,stress');
        Route::get('/security/incidents', [App\Http\Controllers\Api\V1\SecurityIncidentController::class, 'index']);
        Route::post('/security/incidents/{id}/resolve', [App\Http\Controllers\Api\V1\SecurityIncidentController::class, 'resolve']);
        Route::post('/security/ip-block', [App\Http\Controllers\Api\V1\SecurityIncidentController::class, 'blockIp']);

        // Onboarding APIs
        Route::post('/onboarding/import-customers', [App\Http\Controllers\Api\V1\OnboardingController::class, 'importCustomers']);
        Route::post('/onboarding/import-parts', [App\Http\Controllers\Api\V1\OnboardingController::class, 'importParts']);
        Route::post('/onboarding/generate-demo', [App\Http\Controllers\Api\V1\OnboardingController::class, 'generateDemoData']);

        // SaaS & Billing APIs
        Route::post('/saas/register', [App\Http\Controllers\Api\V1\SaasController::class, 'register']);
        Route::post('/saas/activate', [App\Http\Controllers\Api\V1\SaasController::class, 'activate']);
        Route::get('/saas/plugins', [App\Http\Controllers\Api\V1\SaasController::class, 'getPlugins']);
        Route::post('/saas/plugins/toggle', [App\Http\Controllers\Api\V1\SaasController::class, 'togglePlugin']);
        Route::get('/saas/subscription', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'index']);
        Route::post('/saas/subscription/checkout', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'checkout']);
        Route::post('/saas/subscription/cancel', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'cancel']);
        Route::post('/saas/subscription/mock-webhook', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'mockWebhook']);
        Route::get('/saas/success-metrics', [App\Http\Controllers\Api\V1\CustomerSuccessController::class, 'getMetrics']);
        Route::get('/saas/global-success', [App\Http\Controllers\Api\V1\CustomerSuccessController::class, 'getGlobalSuccessDashboard']);
        Route::get('/saas/success-history', [App\Http\Controllers\Api\V1\CustomerSuccessController::class, 'getHistory']);
        Route::get('/saas/success-history/{id}', [App\Http\Controllers\Api\V1\CustomerSuccessController::class, 'getHistory']);

        // Support & Ticketing APIs
        Route::get('/support/tickets', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'index']);
        Route::post('/support/tickets', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'store']);
        Route::post('/support/tickets/{id}/resolve', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'resolve']);
        Route::post('/support/tickets/{id}/feedback', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'submitFeedback']);
        Route::get('/support/diagnostics', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'exportDiagnostics']);
        Route::get('/support/tickets/{id}/suggestions', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'getSuggestions']);
        Route::post('/support/tickets/{id}/incident', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'createIncident']);
        Route::get('/support/incidents', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'getIncidents']);
        Route::post('/support/incidents/{id}/workflow', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'createResolutionWorkflow']);
        Route::post('/support/workflows/{id}/publish-kb', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'publishWorkflowToKb']);
        Route::get('/support/kb', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'getKbArticles']);
        Route::get('/support/kb/{slug}', [App\Http\Controllers\Api\V1\SupportTicketController::class, 'getKbArticleBySlug']);

        // Payment Webhooks
        Route::post('/webhooks/sslcommerz', [App\Http\Controllers\Api\V1\WebhookController::class, 'sslcommerzIPN']);
        Route::post('/webhooks/stripe', [App\Http\Controllers\Api\V1\WebhookController::class, 'stripeWebhook']);

        // Fleet Management routes
        Route::get('/fleet/contracts', [App\Http\Controllers\Api\V1\FleetController::class, 'index']);
        Route::post('/fleet/contracts', [App\Http\Controllers\Api\V1\FleetController::class, 'store']);
        Route::get('/fleet/metrics', [App\Http\Controllers\Api\V1\FleetController::class, 'metrics']);
        Route::post('/fleet/quotations/bulk-approve', [App\Http\Controllers\Api\V1\FleetController::class, 'bulkApprove']);

        // Finance routes
        Route::get('/accounts', [App\Http\Controllers\Api\V1\AccountController::class, 'index']);
        Route::post('/accounts', [App\Http\Controllers\Api\V1\AccountController::class, 'store']);
        Route::get('/accounts/{id}', [App\Http\Controllers\Api\V1\AccountController::class, 'show']);
        Route::put('/accounts/{id}', [App\Http\Controllers\Api\V1\AccountController::class, 'update']);
        Route::delete('/accounts/{id}', [App\Http\Controllers\Api\V1\AccountController::class, 'destroy']);

        Route::get('/transactions', [App\Http\Controllers\Api\V1\TransactionController::class, 'index']);
        Route::post('/transactions', [App\Http\Controllers\Api\V1\TransactionController::class, 'store']);
        Route::get('/transactions/{id}', [App\Http\Controllers\Api\V1\TransactionController::class, 'show']);
        Route::put('/transactions/{id}', [App\Http\Controllers\Api\V1\TransactionController::class, 'update']);
        Route::delete('/transactions/{id}', [App\Http\Controllers\Api\V1\TransactionController::class, 'destroy']);

        // Audit Logs
        Route::get('/audit-logs', [App\Http\Controllers\Api\V1\AuditLogController::class, 'index']);

        // Report routes
        Route::get('/reports/financial', [App\Http\Controllers\Api\V1\ReportController::class, 'financialReport']);
        Route::get('/reports/sales', [App\Http\Controllers\Api\V1\ReportController::class, 'salesReport']);
        Route::get('/reports/purchases', [App\Http\Controllers\Api\V1\ReportController::class, 'purchaseReport']);
        Route::get('/reports/stock', [App\Http\Controllers\Api\V1\ReportController::class, 'stockReport']);

        // Notification routes
        Route::get('/notifications', [App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
        Route::delete('/notifications/clear', [App\Http\Controllers\Api\V1\NotificationController::class, 'clear']);

        // Warranty & Comeback routes
        Route::get('/warranties', [App\Http\Controllers\Api\V1\WarrantyAndComebackController::class, 'warranties']);
        Route::get('/comebacks', [App\Http\Controllers\Api\V1\WarrantyAndComebackController::class, 'comebacks']);
        Route::post('/comebacks', [App\Http\Controllers\Api\V1\WarrantyAndComebackController::class, 'storeComeback']);

        // Settings
        Route::get('/settings', [App\Http\Controllers\Api\V1\SettingController::class, 'index']);
        Route::put('/settings', [App\Http\Controllers\Api\V1\SettingController::class, 'update']);

        // Backups
        Route::get('/backups', [App\Http\Controllers\Api\V1\BackupController::class, 'index']);
        Route::post('/backups', [App\Http\Controllers\Api\V1\BackupController::class, 'run']);
        Route::delete('/backups', [App\Http\Controllers\Api\V1\BackupController::class, 'destroy']);
        // Print APIs
        Route::get('/print/invoice/{id}', [App\Http\Controllers\Api\V1\PrintController::class, 'invoice']);
        Route::get('/print/invoice/{id}/thermal', [App\Http\Controllers\Api\V1\PrintController::class, 'invoiceThermal']);
        Route::get('/print/job-card/{id}', [App\Http\Controllers\Api\V1\PrintController::class, 'jobCard']);
        Route::get('/print/purchase/{id}', [App\Http\Controllers\Api\V1\PrintController::class, 'purchase']);
        Route::get('/print/payroll/{id}', [App\Http\Controllers\Api\V1\PrintController::class, 'payroll']);

        // Customer Self-Service Portal Routes
        Route::get('/portal/repair-status/{job_card_id}', [App\Http\Controllers\Api\V1\CustomerPortalController::class, 'getRepairStatus']);
        Route::get('/portal/quotations/{id}', [App\Http\Controllers\Api\V1\CustomerPortalController::class, 'getQuotation']);
        Route::post('/portal/quotations/{id}/approve', [App\Http\Controllers\Api\V1\CustomerPortalController::class, 'approveQuotation']);
        Route::get('/portal/vehicles/{id}/history', [App\Http\Controllers\Api\V1\CustomerPortalController::class, 'getVehicleHistory']);
    });
});