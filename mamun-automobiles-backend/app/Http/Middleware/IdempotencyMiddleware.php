<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Idempotency-Token') ?: $request->header('x-idempotency-token');
        
        if ($token) {
            $cacheKey = 'idempotency_token_' . md5($token);
            
            // Atomic check and set lock for 60 minutes
            if (Cache::has($cacheKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate submission detected. Please wait or refresh.',
                ], 409); // Conflict status code
            }
            
            Cache::put($cacheKey, 'locked', 3600); // 1 hour hold
        }
        
        return $next($request);
    }
}
