<?php

namespace App\Repositories;

use App\Models\JobCardTask;

class JobCardTaskRepository extends BaseRepository
{
    public function __construct(JobCardTask $model)
    {
        parent::__construct($model);
    }

    public function create(array $data): JobCardTask
    {
        return JobCardTask::create($data);
    }

    public function getTasksForJobCard(int $jobCardId)
    {
        return JobCardTask::where('job_card_id', $jobCardId)->get();
    }

    public function findById(int $id): ?JobCardTask
    {
        return JobCardTask::find($id);
    }
}
