<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EmergencyMaintenanceMiddleware
{
    /**
     * Intercept requests to verify system maintenance status.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if Emergency Maintenance Mode is enabled
        $maintenanceActive = Cache::get('emergency_maintenance_mode', false);

        if ($maintenanceActive) {
            // Bypass maintenance constraints for diagnostic/maintenance endpoints
            $bypassUrls = [
                'api/v1/system/production-health',
                'api/v1/system/maintenance',
                'api/v1/auth/login'
            ];

            foreach ($bypassUrls as $url) {
                if ($request->is($url)) {
                    return $next($request);
                }
            }

            // Bypass for Super Admin users
            if (auth()->check() && auth()->user()->hasRole('Super Admin')) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'System is currently undergoing emergency maintenance. Normal operations will resume shortly.'
            ], 503);
        }

        return $next($request);
    }
}
