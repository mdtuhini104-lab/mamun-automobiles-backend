<?php

namespace App\Repositories;

use App\Models\JobCardAssignment;

class JobCardAssignmentRepository extends BaseRepository
{
    public function __construct(JobCardAssignment $model)
    {
        parent::__construct($model);
    }

    public function create(array $data): JobCardAssignment
    {
        return JobCardAssignment::create($data);
    }

    public function getActiveAssignmentsForJobCard(int $jobCardId)
    {
        return JobCardAssignment::where('job_card_id', $jobCardId)
            ->where('status', 'active')
            ->get();
    }

    public function getAssignmentsHistory(int $jobCardId)
    {
        return JobCardAssignment::where('job_card_id', $jobCardId)
            ->withTrashed()
            ->latest()
            ->get();
    }
}
