<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;

class ActivityLogController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user:id,name,email')
            ->orderBy('created_at', 'desc');

        if ($request->has('module')) {
            $query->where('module', $request->module);
        }
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate($request->per_page ?? 15);

        return $this->successResponse($logs, 'Activity logs retrieved successfully');
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'danger' => ActivityLog::where('severity', 'danger')->count(),
            'warning' => ActivityLog::where('severity', 'warning')->count(),
            'recent' => ActivityLog::with('user:id,name')->orderBy('created_at', 'desc')->take(5)->get()
        ];

        return $this->successResponse($stats, 'Activity log stats retrieved successfully');
    }

    public function export(Request $request)
    {
        // For simplicity, just returning the full list. In production, return CSV download.
        $logs = ActivityLog::with('user:id,name,email')->orderBy('created_at', 'desc')->get();
        return $this->successResponse($logs, 'Export data ready');
    }
}
