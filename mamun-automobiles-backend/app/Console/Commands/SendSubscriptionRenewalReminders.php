<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendSubscriptionRenewalReminders extends Command
{
    protected $signature = 'saas:send-renewal-reminders';
    protected $description = 'Scan active subscriptions expiring within 7 days and send renewal alerts with protections.';

    public function handle()
    {
        $expiringSubscriptions = DB::table('tenant_subscriptions')
            ->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->get();

        $this->info("Found " . $expiringSubscriptions->count() . " active subscriptions expiring within 7 days.");

        foreach ($expiringSubscriptions as $sub) {
            $tenantId = $sub->tenant_id;
            if (!$tenantId) continue;

            // 1. Unsubscribe-safe communication check
            $isUnsubscribed = DB::table('tenant_feature_limits')
                ->where('tenant_id', $tenantId)
                ->where('feature_name', 'unsubscribe_renewal_reminders')
                ->exists() || Cache::get("tenant_unsubscribed_reminders_{$tenantId}", false);

            if ($isUnsubscribed) {
                $this->info("Tenant #{$tenantId} has unsubscribed from renewal communications. Skipping.");
                continue;
            }

            // 2. Cooldown check: Maximum 1 reminder per 24 hours
            $hasRecentReminder = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'subscription_renewal_reminder')
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($hasRecentReminder) {
                $this->info("Tenant #{$tenantId} already received a renewal reminder in the last 24 hours. Skipping.");
                continue;
            }

            // 3. Send notification (log audit and dispatch message)
            $tenantName = DB::table('tenants')->where('id', $tenantId)->value('company_name') ?? 'Valued Tenant';
            $daysRemaining = max(0, intval(Carbon::parse($sub->ends_at)->diffInDays(now())));

            Log::info("Renewal Alert: Subscription for '{$tenantName}' (Tenant #{$tenantId}) ends in {$daysRemaining} days (ends_at: {$sub->ends_at}).");

            // Create system alert
            DB::table('system_health_alerts')->insert([
                'alert_type' => 'subscription_expiring',
                'severity' => 'warning',
                'message' => "Your subscription plan '{$sub->plan_name}' is expiring in {$daysRemaining} days. Please renew to prevent service disruption.",
                'metrics' => json_encode([
                    'ends_at' => $sub->ends_at,
                    'plan' => $sub->plan_name,
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Record reminder history tracking in audit logs
            DB::table('audit_logs')->insert([
                'tenant_id' => $tenantId,
                'user_id' => 1, // System admin
                'action' => 'subscription_renewal_reminder',
                'module' => 'billing',
                'details' => json_encode([
                    'plan_name' => $sub->plan_name,
                    'ends_at' => $sub->ends_at,
                    'days_remaining' => $daysRemaining,
                    'message' => 'Subscription renewal alert dispatched to tenant.'
                ]),
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->info("Renewal reminder sent successfully to Tenant #{$tenantId}.");
        }

        return 0;
    }
}
