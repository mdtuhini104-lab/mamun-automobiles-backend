<?php

namespace App\Repositories;

use App\Models\JobCardItem;
use Illuminate\Database\Eloquent\Collection;

class JobCardItemRepository extends BaseRepository
{
    /**
     * Create a new job card item.
     */
    public function create(array $data): JobCardItem
    {
        return JobCardItem::create($data);
    }

    /**
     * Get items by job card ID.
     */
    public function getByJobCardId(int $jobCardId): Collection
    {
        return JobCardItem::where('job_card_id', $jobCardId)->with('part')->get();
    }
}
