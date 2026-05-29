<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StressTestingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Exception;

class ReliabilityStressController extends Controller
{
    protected $stressService;

    public function __construct(StressTestingService $stressService)
    {
        $this->stressService = $stressService;
    }

    /**
     * Helper to enforce environment, authorization, and optional security gates.
     */
    protected function authorizeStressAction(Request $request, string $action)
    {
        // 1. Gated environment check
        if (app()->environment('production') || config('app.env') === 'production') {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Unauthorized. Reliability stress testing is strictly disabled in production environments.'
            ], 403));
        }

        // 2. Gate check: Super Admin only
        try {
            if (!$request->user()->hasRole('Super Admin')) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin operators are permitted to trigger stress-testing simulations.'
                ], 403));
            }
        } catch (\Throwable $e) {
            // Fallback fail-safe if Super Admin role doesn't exist in the database
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super Admin authorization validation failed.'
            ], 403));
        }

        // 3. Optional IP Allowlist check
        $allowlist = config('system.stress_ip_allowlist', []);
        if (!empty($allowlist) && !in_array($request->ip(), $allowlist)) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Unauthorized. Operator IP address is not present in the allowed stress-testing registry.'
            ], 403));
        }

        // 4. Optional Signature check (signed request support)
        if (config('system.stress_require_signature', false)) {
            $sig = $request->header('X-Stress-Signature');
            $secret = config('app.key');
            $expectedSig = hash_hmac('sha256', $action . '|' . $request->ip(), $secret);
            if ($sig !== $expectedSig) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Signed signature header validation failed.'
                ], 403));
            }
        }

        // 5. Audit Logging
        DB::table('audit_logs')->insert([
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $request->user()->id,
            'action' => 'stress_test_' . $action,
            'module' => 'system',
            'details' => json_encode([
                'operator' => $request->user()->email,
                'ip' => $request->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]),
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Simulate a slow database query.
     */
    public function simulateSlowQuery(Request $request)
    {
        $this->authorizeStressAction($request, 'slow_query');

        $request->validate([
            'milliseconds' => 'integer|min:50|max:5000'
        ]);

        $ms = $request->input('milliseconds', 150);
        $result = $this->stressService->simulateSlowQuery($ms);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => "Successfully simulated a slow DB query of {$ms}ms."
        ]);
    }

    /**
     * Trigger a mock failed background job.
     */
    public function simulateFailedJob(Request $request)
    {
        $this->authorizeStressAction($request, 'failed_job');

        $request->validate([
            'classification' => 'required|string|in:safe,restricted',
            'reason' => 'string|min:5|max:255'
        ]);

        $classification = $request->input('classification');
        $reason = $request->input('reason', 'Simulated queue stress test failed job');

        $result = $this->stressService->simulateFailedJob($classification, $reason);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => "Mock failed job of type '{$classification}' dispatched successfully."
        ]);
    }

    /**
     * Trigger a mock websocket connection drop event.
     */
    public function simulateWebsocketDisconnect(Request $request)
    {
        $this->authorizeStressAction($request, 'websocket_disconnect');

        $result = $this->stressService->simulateWebsocketDisconnect();

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => "Simulated websocket disconnect command broadcasted to system channels."
        ]);
    }
}
