<?php

namespace App\Services;

use App\Models\TenantHealthSnapshot;
use App\Models\SupportTicket;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Log;

class CustomerSuccessService
{
    /**
     * Get aggregate success telemetry indicators for a tenant.
     */
    public function getTenantHealthScore(int $tenantId): array
    {
        // 1. Daily Activity Score (30% weight)
        // Ratio of active job cards created in the last 30 days
        $activeJobCards = DB::table('job_cards')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $activityScore = min(($activeJobCards / 20) * 100, 100); // benchmark 20 job cards/mo = 100% activity

        // 2. Invoice Collection Efficiency (25% weight)
        // Ratio of paid invoices vs total invoices created
        $totalInvoices = DB::table('invoices')->where('tenant_id', $tenantId)->count();
        $paidInvoices = DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->count();
        $collectionScore = $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 100;

        // 3. Workflow Completion Ratio (20% weight)
        // Ratio of completed jobs vs open jobs in last 30 days
        $totalJobs = DB::table('job_cards')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $completedJobs = DB::table('job_cards')
            ->where('tenant_id', $tenantId)
            ->where('service_status', 'completed')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();
        $workflowScore = $totalJobs > 0 ? ($completedJobs / $totalJobs) * 100 : 100;

        // 4. Feature Adoption Rate (15% weight)
        // Count of toggled premium plugins in tenant limits
        $pluginsCount = DB::table('tenant_feature_limits')
            ->where('tenant_id', $tenantId)
            ->where('feature_name', 'like', 'plugin_%')
            ->count();
        $featureScore = min(($pluginsCount / 3) * 100, 100); // 3 premium plugins = 100% adoption

        // 5. Login Frequency Rate (10% weight)
        // Number of active unique logs in the last 7 days
        $loginsCount = DB::table('audit_logs')
            ->where('tenant_id', $tenantId)
            ->where('action', 'login')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $loginScore = min(($loginsCount / 10) * 100, 100); // 10 logins/week = 100%

        // Calculate final weighted success score
        $finalScore = round(
            ($activityScore * 0.30) +
            ($collectionScore * 0.25) +
            ($workflowScore * 0.20) +
            ($featureScore * 0.15) +
            ($loginScore * 0.10),
            1
        );

        // 6. Inactivity Benchmarking
        $lastActivity = DB::table('audit_logs')
            ->where('tenant_id', $tenantId)
            ->latest('created_at')
            ->value('created_at');

        $daysInactive = $lastActivity ? now()->diffInDays(Carbon::parse($lastActivity)) : 30; // default to 30 if no activity

        $riskLevel = 'Active';
        if ($daysInactive >= 30) {
            $riskLevel = 'Churn Risk';
        } elseif ($daysInactive >= 14) {
            $riskLevel = 'High Risk';
        } elseif ($daysInactive >= 7) {
            $riskLevel = 'Inactive';
        } elseif ($daysInactive >= 3) {
            $riskLevel = 'Warning';
        }

        // Fetch Support Response Metrics
        $support = $this->getSupportResponseMetrics($tenantId);

        // Fetch Operational Usage Analytics
        $usage = $this->getOperationalUsageAnalytics($tenantId);

        return [
            'tenant_id' => $tenantId,
            'health_score' => $finalScore,
            'days_inactive' => $daysInactive,
            'risk_level' => $riskLevel,
            'activity_score' => round($activityScore, 1),
            'collection_score' => round($collectionScore, 1),
            'workflow_score' => round($workflowScore, 1),
            'feature_score' => round($featureScore, 1),
            'login_score' => round($loginScore, 1),
            'support_response_metrics' => $support,
            'operational_usage_analytics' => $usage,
        ];
    }

    /**
     * Record tenant health score trends snapshot and trigger automated retention flows.
     */
    public function recordHealthSnapshot(int $tenantId): TenantHealthSnapshot
    {
        $health = $this->getTenantHealthScore($tenantId);
        $tenant = Tenant::find($tenantId);
        $companyName = $tenant ? $tenant->company_name : 'Unknown Tenant';

        $snapshot = TenantHealthSnapshot::create([
            'tenant_id' => $tenantId,
            'health_score' => $health['health_score'],
            'days_inactive' => $health['days_inactive'],
            'risk_level' => $health['risk_level'],
            'metrics' => [
                'activity' => $health['activity_score'],
                'collection' => $health['collection_score'],
                'workflow' => $health['workflow_score'],
                'feature' => $health['feature_score'],
                'login' => $health['login_score'],
            ],
            'recorded_at' => now(),
        ]);

        // Trigger Automated Retention Flow if tenant falls to High Risk or Churn Risk
        if (in_array($health['risk_level'], ['High Risk', 'Churn Risk'])) {
            $category = 'onboarding';
            $retentionTitlePattern = 'Automated Retention Alert: Low Health Warning';

            // Duplication Guard: Check for similar open ticket created within last 7 days
            $existingTicket = SupportTicket::where('tenant_id', $tenantId)
                ->where('category', $category)
                ->whereIn('status', ['open', 'in_progress'])
                ->where('title', 'like', "%{$retentionTitlePattern}%")
                ->where('created_at', '>=', now()->subDays(7))
                ->first();

            if ($existingTicket) {
                // Do not create duplicate; append note to existing ticket
                $newDescription = $existingTicket->description . "\n" . 
                                  "[Retention Check " . now()->toDateTimeString() . "]: Health re-evaluated at " . $health['health_score'] . "% (Risk: " . $health['risk_level'] . ", Inactive: " . $health['days_inactive'] . " days). Status remains degraded.";
                
                $existingTicket->update([
                    'description' => $newDescription
                ]);

                Log::info("Retention Flow: Appended metrics update to existing ticket #{$existingTicket->id} for Tenant #{$tenantId}");
            } else {
                // Create a new high-priority retention ticket
                $adminUser = DB::table('users')->where('tenant_id', $tenantId)->first();
                $userId = $adminUser ? $adminUser->id : 1; // Fallback to superuser ID

                $newTicket = SupportTicket::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'title' => "{$retentionTitlePattern} - {$companyName}",
                    'description' => "Automated system scan detected critical customer health degradation.\n" .
                                     "Health Score: {$health['health_score']}%\n" .
                                     "Risk Assessment Level: {$health['risk_level']}\n" .
                                     "Days Inactive: {$health['days_inactive']} days.\n" .
                                     "Please initiate customer success customer-retention outreach workflow immediately.",
                    'priority' => 'high',
                    'category' => $category,
                    'status' => 'open'
                ]);

                Log::info("Retention Flow: Created new automated retention ticket #{$newTicket->id} for Tenant #{$tenantId}");
            }
        }

        return $snapshot;
    }

    /**
     * Retrieve health trend snapshots history.
     */
    public function getHealthHistory(int $tenantId, int $days = 30): array
    {
        return TenantHealthSnapshot::where('tenant_id', $tenantId)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get support response metrics for a tenant.
     */
    public function getSupportResponseMetrics(int $tenantId): array
    {
        $resolvedTickets = DB::table('support_tickets')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('resolved_at')
            ->get();

        $respondedTickets = DB::table('support_tickets')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('first_response_at')
            ->get();

        $avgResponseHours = 0.0;
        if ($respondedTickets->count() > 0) {
            $totalHours = 0;
            foreach ($respondedTickets as $ticket) {
                $created = Carbon::parse($ticket->created_at);
                $responded = Carbon::parse($ticket->first_response_at);
                $totalHours += $created->diffInHours($responded);
            }
            $avgResponseHours = round($totalHours / $respondedTickets->count(), 1);
        }

        $avgResolutionHours = 0.0;
        if ($resolvedTickets->count() > 0) {
            $totalHours = 0;
            foreach ($resolvedTickets as $ticket) {
                $created = Carbon::parse($ticket->created_at);
                $resolved = Carbon::parse($ticket->resolved_at);
                $totalHours += $created->diffInHours($resolved);
            }
            $avgResolutionHours = round($totalHours / $resolvedTickets->count(), 1);
        }

        $csat = DB::table('support_tickets')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('satisfaction_score')
            ->avg('satisfaction_score');

        return [
            'total_tickets' => DB::table('support_tickets')->where('tenant_id', $tenantId)->count(),
            'resolved_tickets' => $resolvedTickets->count(),
            'avg_response_hours' => $avgResponseHours,
            'avg_resolution_hours' => $avgResolutionHours,
            'avg_csat' => $csat ? round($csat, 1) : null
        ];
    }

    /**
     * Get operational usage analytics for a tenant.
     */
    public function getOperationalUsageAnalytics(int $tenantId): array
    {
        return [
            'total_active_job_cards' => DB::table('job_cards')
                ->where('tenant_id', $tenantId)
                ->where('service_status', '!=', 'completed')
                ->count(),
            'monthly_transactions' => DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'monthly_logins' => DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'login')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'workforce_count' => DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->count()
        ];
    }

    /**
     * Get aggregate onboarding checklist metrics for a tenant.
     */
    public function getOnboardingCompletionRate(int $tenantId): float
    {
        $checkpoints = [
            'has_branch' => DB::table('branches')->where('tenant_id', $tenantId)->exists(),
            'has_user' => DB::table('users')->where('tenant_id', $tenantId)->exists(),
            'has_customer' => DB::table('customers')->where('tenant_id', $tenantId)->exists(),
            'has_parts' => DB::table('parts')->where('tenant_id', $tenantId)->exists(),
            'has_accounts' => DB::table('accounts_chart')->where('tenant_id', $tenantId)->exists(),
        ];

        $completed = array_filter($checkpoints);
        return round((count($completed) / count($checkpoints)) * 100, 1);
    }
}
