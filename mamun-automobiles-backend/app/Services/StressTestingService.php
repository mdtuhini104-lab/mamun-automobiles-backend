<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Events\SystemReliabilityIncident;
use Exception;

class StressTestingService
{
    /**
     * Simulate a slow database query.
     */
    public function simulateSlowQuery(int $milliseconds = 150): array
    {
        $driver = DB::connection()->getDriverName();
        $startTime = microtime(true);

        if ($driver === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            try {
                $pdo->sqliteCreateFunction('sleep', function ($ms) {
                    usleep($ms * 1000);
                    return 1;
                }, 1);
            } catch (\Exception $e) {
                // Already registered
            }

            DB::select('SELECT sleep(?);', [$milliseconds]);
        } else {
            $seconds = $milliseconds / 1000;
            DB::select('SELECT SLEEP(?);', [$seconds]);
        }

        $duration = (microtime(true) - $startTime) * 1000;

        return [
            'success' => true,
            'simulated_ms' => $milliseconds,
            'actual_duration_ms' => round($duration, 2)
        ];
    }

    /**
     * Dispatch a mock failed background job.
     */
    public function simulateFailedJob(string $type = 'safe', string $reason = 'Simulated reliability stress incident'): array
    {
        $queue = $type === 'restricted' ? 'billing' : 'notifications';
        
        dispatch(function () use ($reason) {
            throw new Exception("StressTestException: " . $reason);
        })->onQueue($queue);

        return [
            'success' => true,
            'queue' => $queue,
            'message' => "Mock job of classification '{$type}' successfully dispatched to queue '{$queue}'."
        ];
    }

    /**
     * Broadcast a simulated websocket disconnect notification event to client.
     */
    public function simulateWebsocketDisconnect(): array
    {
        event(new SystemReliabilityIncident('simulate_disconnect'));

        return [
            'success' => true,
            'message' => 'Simulated websocket disconnect broadcast event dispatched to client.'
        ];
    }
}
