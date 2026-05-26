<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header('X-Correlation-ID', (string) \Illuminate\Support\Str::uuid());
        
        // Add to request for downstream usage
        $request->headers->set('X-Correlation-ID', $correlationId);
        
        $response = $next($request);
        
        $response->headers->set('X-Correlation-ID', $correlationId);
        
        return $response;
    }
}
