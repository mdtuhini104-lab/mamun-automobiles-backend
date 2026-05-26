<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MechanicAssignmentService;
use App\Services\JobTimelineService;
use Illuminate\Http\Request;

class JobAssignmentController extends Controller
{
    protected $assignmentService;
    protected $timelineService;

    public function __construct(MechanicAssignmentService $assignmentService, JobTimelineService $timelineService)
    {
        $this->assignmentService = $assignmentService;
        $this->timelineService = $timelineService;
    }

    public function assign(Request $request)
    {
        $request->validate([
            'job_card_id' => 'required|integer',
            'mechanic_id' => 'required|integer',
            'estimated_hours' => 'nullable|numeric'
        ]);

        $assignment = $this->assignmentService->assignMechanic(
            $request->job_card_id,
            $request->mechanic_id,
            auth()->id() ?? 1,
            $request->estimated_hours
        );

        return response()->json($assignment, 201);
    }

    public function timeline($jobCardId)
    {
        return response()->json($this->timelineService->getTimeline($jobCardId));
    }
}
