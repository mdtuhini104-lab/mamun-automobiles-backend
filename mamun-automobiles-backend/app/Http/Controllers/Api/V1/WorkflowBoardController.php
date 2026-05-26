<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobWorkflowStage;
use App\Services\WorkflowBoardService;
use Illuminate\Http\Request;

class WorkflowBoardController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowBoardService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function index(Request $request)
    {
        $stages = JobWorkflowStage::orderBy('sort_order')->get();
        // Here we would fetch jobs and group by stage
        return response()->json(['stages' => $stages]);
    }

    public function move(Request $request)
    {
        $request->validate([
            'job_card_id' => 'required|integer',
            'new_stage_id' => 'required|exists:job_workflow_stages,id',
            'notes' => 'nullable|string'
        ]);

        try {
            $history = $this->workflowService->moveJob(
                $request->job_card_id,
                $request->new_stage_id,
                auth()->id() ?? 1,
                $request->notes
            );
            return response()->json($history, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
