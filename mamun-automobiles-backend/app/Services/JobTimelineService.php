<?php

namespace App\Services;

use App\Models\JobWorkflowHistory;

class JobTimelineService
{
    public function getTimeline($jobCardId)
    {
        return JobWorkflowHistory::where('job_card_id', $jobCardId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
