<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class AiAutomationController extends Controller
{
    use ApiResponseTrait;

    public function getInsights(): JsonResponse
    {
        $insights = DB::table('ai_insights')->orderBy('created_at', 'desc')->get();
        return $this->successResponse($insights, 'AI Insights fetched successfully');
    }

    public function getAutomations(): JsonResponse
    {
        $automations = DB::table('automations')->get();
        return $this->successResponse($automations, 'Automations fetched successfully');
    }

    public function triggerEvent(Request $request): JsonResponse
    {
        $request->validate(['event' => 'required|string']);
        
        // Mock triggering an AI logic
        DB::table('ai_insights')->insert([
            'tenant_id' => 1,
            'type' => 'workflow_execution',
            'title' => 'Event Triggered: ' . $request->event,
            'description' => 'System automatically routed tasks based on event constraints.',
            'severity' => 'info',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(null, 'Event triggered and automation processed');
    }

    public function getLiveWorkshopActivity(): JsonResponse
    {
        // Mock live tracking data for Workshop Board
        $activities = [
            ['id' => 1, 'mechanic' => 'John Doe', 'status' => 'Working', 'task' => 'Engine Repair - V-1029', 'progress' => 45],
            ['id' => 2, 'mechanic' => 'Smith', 'status' => 'Idle', 'task' => 'Available for Assignment', 'progress' => 0],
            ['id' => 3, 'mechanic' => 'Alex', 'status' => 'Testing', 'task' => 'Brake Inspection - V-9932', 'progress' => 90],
        ];

        return $this->successResponse($activities, 'Live workshop activity fetched');
    }
}

