<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemHealthAlert;
use App\Models\User;
use App\Notifications\SystemNotification;

class CheckSystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Mamun:CheckSystemHealth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan server system health, including SQLite disk footprint, queue backlog, and log alerts for any operational anomalies.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting enterprise system health scan...");

        $anomaliesDetected = [];

        // 1. Scan SQLite Footprint
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            $fileSizeBytes = filesize($dbPath);
            $fileSizeMB = round($fileSizeBytes / (1024 * 1024), 2);
            $this->info("SQLite Database size: {$fileSizeMB} MB");

            if ($fileSizeMB > 100.00) {
                $anomaliesDetected[] = [
                    'type' => 'database_size',
                    'severity' => 'critical',
                    'message' => "SQLite footprint has reached a critical size of {$fileSizeMB} MB. Consider vacuuming or archiving old audit tables.",
                    'metrics' => ['size_mb' => $fileSizeMB]
                ];
            } elseif ($fileSizeMB > 50.00) {
                $anomaliesDetected[] = [
                    'type' => 'database_size',
                    'severity' => 'warning',
                    'message' => "SQLite footprint is elevated at {$fileSizeMB} MB. Vacuuming is recommended soon.",
                    'metrics' => ['size_mb' => $fileSizeMB]
                ];
            }
        } else {
            $this->warn("SQLite file not found at default path. Skipping footprint check.");
        }

        // 2. Scan Queue Backlog
        if (Schema::hasTable('jobs')) {
            $pendingJobsCount = DB::table('jobs')->count();
            $this->info("Pending jobs in queue: {$pendingJobsCount}");

            if ($pendingJobsCount > 50) {
                $anomaliesDetected[] = [
                    'type' => 'queue_lag',
                    'severity' => 'critical',
                    'message' => "Queue lag is critical. There are {$pendingJobsCount} pending jobs in the queue.",
                    'metrics' => ['pending_jobs' => $pendingJobsCount]
                ];
            } elseif ($pendingJobsCount > 20) {
                $anomaliesDetected[] = [
                    'type' => 'queue_lag',
                    'severity' => 'warning',
                    'message' => "Queue is experiencing mild latency. There are {$pendingJobsCount} pending jobs in the queue.",
                    'metrics' => ['pending_jobs' => $pendingJobsCount]
                ];
            }
        }

        // 3. Scan Failed Jobs
        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = DB::table('failed_jobs')->count();
            $this->info("Failed jobs in queue: {$failedJobsCount}");

            if ($failedJobsCount > 5) {
                $anomaliesDetected[] = [
                    'type' => 'failed_jobs_count',
                    'severity' => 'warning',
                    'message' => "There are {$failedJobsCount} failed jobs in the queue. Manual retry/recovery action is needed.",
                    'metrics' => ['failed_jobs' => $failedJobsCount]
                ];
            }
        }

        // 4. Record Anomalies and Send Notifications
        if (empty($anomaliesDetected)) {
            $this->info("SUCCESS: All system health indicators are normal.");
            return Command::SUCCESS;
        }

        foreach ($anomaliesDetected as $anomaly) {
            // Write to system_health_alerts database
            $alert = SystemHealthAlert::create([
                'alert_type' => $anomaly['type'],
                'severity' => $anomaly['severity'],
                'message' => $anomaly['message'],
                'metrics' => $anomaly['metrics']
            ]);

            $this->error("ALERT: [{$anomaly['severity']}] {$anomaly['message']}");

            // Notify administrative users
            $admins = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Super Admin', 'Admin', 'Manager']);
            })->get();

            \Illuminate\Support\Facades\Notification::send($admins, new SystemNotification(
                "System Health Alert: " . ucfirst($anomaly['type']),
                $anomaly['message'],
                "system_health_" . $anomaly['severity'],
                ['alert_id' => $alert->id]
            ));
        }

        $this->warn("System health check completed with warning(s) or critical alert(s) logged.");
        return Command::FAILURE;
    }
}
