<?php

namespace App\Services;

use App\Models\JobWorkflowHistory;
use App\Models\JobWorkflowStage;
use Illuminate\Support\Facades\DB;

class WorkflowBoardService
{
    /**
     * Move a job to a new workflow stage.
     */
    public function moveJob($jobCardId, $newStageId, $changedBy, $notes = '')
    {
        return DB::transaction(function () use ($jobCardId, $newStageId, $changedBy, $notes) {
            // Find current stage (from history or default)
            $lastHistory = JobWorkflowHistory::where('job_card_id', $jobCardId)->latest()->first();
            $oldStage = $lastHistory ? $lastHistory->new_stage : null;
            
            $newStage = JobWorkflowStage::findOrFail($newStageId);

            if ($oldStage == $newStage->slug) {
                return false; // Already in this stage
            }

            // Create history record
            $history = JobWorkflowHistory::create([
                'job_card_id' => $jobCardId,
                'old_stage' => $oldStage,
                'new_stage' => $newStage->slug,
                'changed_by' => $changedBy,
                'notes' => $notes,
            ]);

            // Dispatch real-time websocket broadcast
            event(new \App\Events\JobMoved($jobCardId, $oldStage, $newStage->slug, $changedBy, $notes));

            // If new stage is 'completed_stage', we would trigger verification
            // and activity logging
            
            return $history;
        });
    }
}
