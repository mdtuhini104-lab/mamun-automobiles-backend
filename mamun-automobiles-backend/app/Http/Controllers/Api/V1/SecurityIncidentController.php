<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\SecurityIncidentCenter;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class SecurityIncidentController extends Controller
{
    use ApiResponseTrait;

    protected $incidentCenter;

    public function __construct(SecurityIncidentCenter $incidentCenter)
    {
        $this->incidentCenter = $incidentCenter;
    }

    /**
     * List all logged security incidents.
     */
    public function index(Request $request): JsonResponse
    {
        // Restrict to Super Admin / Admin
        if (auth()->check() && !auth()->user()->hasRole('Super Admin')) {
            return $this->errorResponse('Unauthorized access.', 403);
        }

        $incidents = DB::table('security_incidents')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 25));

        return $this->successResponse($incidents, 'Security incidents retrieved.');
    }

    /**
     * Resolve a specific logged incident.
     */
    public function resolve(int $id): JsonResponse
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Admin')) {
            return $this->errorResponse('Unauthorized access.', 403);
        }

        $resolved = $this->incidentCenter->resolveIncident($id);
        if ($resolved) {
            return $this->successResponse(null, 'Incident resolved.');
        }

        return $this->errorResponse('Incident not found or already resolved.', 404);
    }

    /**
     * Manually block an IP address.
     */
    public function blockIp(Request $request): JsonResponse
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Admin')) {
            return $this->errorResponse('Unauthorized access.', 403);
        }

        $request->validate([
            'ip_address' => 'required|ip',
            'duration_minutes' => 'nullable|integer|min:1'
        ]);

        $ip = $request->input('ip_address');
        $duration = $request->input('duration_minutes', 15);

        $this->incidentCenter->blockIp($ip, $duration);

        return $this->successResponse(null, "IP {$ip} blocked successfully for {$duration} minutes.");
    }
}
