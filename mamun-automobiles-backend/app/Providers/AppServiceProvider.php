<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
        
        \App\Models\Part::observe(\App\Observers\PartObserver::class);

        // Listen for Authentication events
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \App\Services\ActivityLogService::log('Auth', 'login', "User {$event->user->email} logged in successfully.", null, null, 'info');
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            $email = $event->credentials['email'] ?? 'unknown';
            \App\Services\ActivityLogService::log('Auth', 'failed_login', "Failed login attempt for {$email}.", null, null, 'danger');
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                \App\Services\ActivityLogService::log('Auth', 'logout', "User {$event->user->email} logged out.", null, null, 'info');
            }
        });

        // Domain Event listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\QuotationApproved::class,
            [\App\Listeners\DomainEventListener::class, 'handleQuotationApproved']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\WorkOrderCreated::class,
            [\App\Listeners\DomainEventListener::class, 'handleWorkOrderCreated']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\TechnicianAssigned::class,
            [\App\Listeners\DomainEventListener::class, 'handleTechnicianAssigned']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\TaskStarted::class,
            [\App\Listeners\DomainEventListener::class, 'handleTaskStarted']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\TaskCompleted::class,
            [\App\Listeners\DomainEventListener::class, 'handleTaskCompleted']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AdditionalConsumptionAdded::class,
            [\App\Listeners\DomainEventListener::class, 'handleAdditionalConsumptionAdded']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\InvoiceGenerated::class,
            [\App\Listeners\DomainEventListener::class, 'handleInvoiceGenerated']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\VehicleDelivered::class,
            [\App\Listeners\DomainEventListener::class, 'handleVehicleDelivered']
        );

        // Register Queue latency profiling hooks
        \Illuminate\Support\Facades\Queue::before(function (\Illuminate\Queue\Events\JobProcessing $event) {
            \Illuminate\Support\Facades\Cache::put('job_start_' . $event->job->getJobId(), microtime(true), 600);
        });

        \Illuminate\Support\Facades\Queue::after(function (\Illuminate\Queue\Events\JobProcessed $event) {
            $jobId = $event->job->getJobId();
            $startTime = \Illuminate\Support\Facades\Cache::pull('job_start_' . $jobId);
            if ($startTime) {
                $duration = microtime(true) - $startTime;
                
                $queueStats = \Illuminate\Support\Facades\Cache::get('queue_performance_telemetry', [
                    'total_processed' => 0,
                    'avg_duration_seconds' => 0.0,
                    'latency_history' => []
                ]);
                
                $total = $queueStats['total_processed'] + 1;
                $avg = (($queueStats['avg_duration_seconds'] * $queueStats['total_processed']) + $duration) / $total;
                
                $queueStats['total_processed'] = $total;
                $queueStats['avg_duration_seconds'] = round($avg, 3);
                $queueStats['latency_history'][] = [
                    'job' => $event->job->resolveName(),
                    'duration' => round($duration, 3),
                    'timestamp' => now()->toDateTimeString()
                ];
                
                if (count($queueStats['latency_history']) > 50) {
                    array_shift($queueStats['latency_history']);
                }
                
                \Illuminate\Support\Facades\Cache::put('queue_performance_telemetry', $queueStats, now()->addDays(30));
            }
        });

        // Listen for queries and profile performance
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            $queryTimeMs = $query->time;
            
            // 1. Profile queries > 100ms for rolling telemetry log
            if ($queryTimeMs > 100) {
                if (strpos($query->sql, 'system_health_alerts') === false && strpos($query->sql, 'audit_logs') === false) {
                    try {
                        $slowQueries = \Illuminate\Support\Facades\Cache::get('slow_queries', []);
                        $slowQueries[] = [
                            'sql' => $query->sql,
                            'time' => round($queryTimeMs, 1),
                            'connection' => $query->connectionName,
                            'triggered_at' => now()->toDateTimeString()
                        ];
                        
                        // Cap at last 50 queries
                        if (count($slowQueries) > 50) {
                            array_shift($slowQueries);
                        }
                        \Illuminate\Support\Facades\Cache::put('slow_queries', $slowQueries, now()->addDays(30));
                    } catch (\Throwable $e) {}
                }
            }

            // 2. Alert on severe queries > 1000ms
            if ($queryTimeMs > 1000) {
                if (strpos($query->sql, 'system_health_alerts') === false) {
                    try {
                        \App\Models\SystemHealthAlert::create([
                            'alert_type' => 'slow_query',
                            'severity' => 'warning',
                            'message' => "Slow database query detected: execution took " . round($queryTimeMs / 1000, 2) . "s. SQL: {$query->sql}",
                            'metrics' => [
                                'time_ms' => $queryTimeMs,
                                'sql' => $query->sql,
                                'bindings' => $query->bindings
                            ]
                        ]);
                    } catch (\Throwable $e) {}
                }
            }
        });
    }
}
