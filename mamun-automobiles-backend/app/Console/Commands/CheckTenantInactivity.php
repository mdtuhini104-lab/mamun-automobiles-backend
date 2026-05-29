<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tenant;
use App\Services\CustomerSuccessService;
use App\Services\SmsWhatsappNotificationCenter;
use App\Notifications\SystemNotification;

class CheckTenantInactivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:check-tenant-inactivity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan tenant engagement analytics, identify inactive accounts (>= 7 days of inactivity), and dispatch alerts via SMS and Email.';

    protected $successService;
    protected $notificationCenter;

    /**
     * Create a new command instance.
     */
    public function __construct(CustomerSuccessService $successService, SmsWhatsappNotificationCenter $notificationCenter)
    {
        parent::__construct();
        $this->successService = $successService;
        $this->notificationCenter = $notificationCenter;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning SaaS tenant health and activity logs...");

        $tenants = Tenant::all();
        $notifiedCount = 0;

        foreach ($tenants as $tenant) {
            $health = $this->successService->getTenantHealthScore($tenant->id);
            $daysInactive = $health['days_inactive'];
            $riskLevel = $health['risk_level'];

            $this->info("Tenant ID #{$tenant->id} ({$tenant->company_name}): {$daysInactive} days inactive (Risk: {$riskLevel})");

            // We alert when the tenant has crossed the 7-day inactivity mark (risk level >= Inactive)
            if ($daysInactive >= 7) {
                // Check if they were already notified about inactivity in the last 7 days to prevent spamming
                $recentNotification = DB::table('communication_logs')
                    ->where('tenant_id', $tenant->id)
                    ->where('message_body', 'like', '%inactivity alert%')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if ($recentNotification) {
                    $this->warn("Skipping notification for Tenant #{$tenant->id}: Already alerted in the last 7 days.");
                    continue;
                }

                // Notify all Admin and Manager users for this tenant
                $admins = User::where('tenant_id', $tenant->id)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['Admin', 'Manager', 'Super Admin']);
                    })
                    ->get();

                if ($admins->isEmpty()) {
                    $this->error("No administrators found to notify for Tenant #{$tenant->id}.");
                    continue;
                }

                $message = "SaaS Inactivity Alert: Your Mamun Automobiles ERP instance ({$tenant->company_name}) has been inactive for {$daysInactive} days. Please log back in to verify your workshop status and review operational analytics.";

                foreach ($admins as $admin) {
                    $this->info("Dispatching alerts to admin {$admin->name} ({$admin->email} / {$admin->phone})");

                    // 1. In-app database notification
                    $admin->notify(new SystemNotification(
                        "Tenant Inactivity Warning",
                        $message,
                        "tenant_inactivity_alert",
                        ['days_inactive' => $daysInactive, 'tenant_id' => $tenant->id]
                    ));

                    // 2. Queue & Dispatch SMS/WhatsApp alert with fallback
                    if ($admin->phone) {
                        try {
                            $logId = $this->notificationCenter->queueAlert(
                                $admin->phone,
                                $admin->email,
                                $message,
                                $tenant->id
                            );

                            $this->notificationCenter->processFallbackDispatch($logId, $admin->email);
                        } catch (\Exception $e) {
                            $this->error("Failed to dispatch phone notification: " . $e->getMessage());
                        }
                    }
                }

                $notifiedCount++;
            }
        }

        $this->info("Tenant inactivity scan complete. Alerted {$notifiedCount} tenant(s).");
        return Command::SUCCESS;
    }
}
