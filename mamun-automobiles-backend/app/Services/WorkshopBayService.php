<?php

namespace App\Services;

use App\Repositories\WorkshopBayRepository;
use App\Models\WorkshopBay;
use App\Models\JobCard;
use App\Events\WorkshopBayAllocated;
use Illuminate\Support\Facades\DB;

class WorkshopBayService
{
    protected $workshopBayRepository;

    public function __construct(WorkshopBayRepository $workshopBayRepository)
    {
        $this->workshopBayRepository = $workshopBayRepository;
    }

    public function listBays(array $filters = [])
    {
        return $this->workshopBayRepository->getAll($filters);
    }

    public function createBay(array $data): WorkshopBay
    {
        return $this->workshopBayRepository->create($data);
    }

    /**
     * Allocate a workshop bay to a job card.
     */
    public function allocateBay(int $jobCardId, int $bayId): bool
    {
        return DB::transaction(function () use ($jobCardId, $bayId) {
            $jobCard = JobCard::findOrFail($jobCardId);
            
            // Pessimistic lock on the bay to prevent race conditions on capacity checks
            $bay = WorkshopBay::where('id', $bayId)->lockForUpdate()->first();
            if (!$bay) {
                throw new \Exception('Workshop Bay not found.');
            }

            if ($bay->status !== 'active') {
                throw new \Exception('Selected Workshop Bay is not active.');
            }

            // Recalculate capacity load check
            if ($bay->current_load >= $bay->max_vehicle_capacity) {
                throw new \Exception('Selected Workshop Bay is at maximum capacity.');
            }

            // If job card was already in another bay, release it
            if ($jobCard->workshop_bay_id) {
                $oldBay = WorkshopBay::where('id', $jobCard->workshop_bay_id)->lockForUpdate()->first();
                if ($oldBay) {
                    $oldBay->current_load = max(0, $oldBay->current_load - 1);
                    $oldBay->save();
                }
            }

            // Update Job Card and Bay
            $jobCard->workshop_bay_id = $bay->id;
            $jobCard->save();

            $bay->current_load += 1;
            $bay->save();

            event(new WorkshopBayAllocated($jobCard, $bay));

            return true;
        });
    }

    /**
     * Release a job card from a workshop bay.
     */
    public function releaseBay(int $jobCardId): bool
    {
        return DB::transaction(function () use ($jobCardId) {
            $jobCard = JobCard::findOrFail($jobCardId);
            if (!$jobCard->workshop_bay_id) {
                return true;
            }

            $bay = WorkshopBay::where('id', $jobCard->workshop_bay_id)->lockForUpdate()->first();
            if ($bay) {
                $bay->current_load = max(0, $bay->current_load - 1);
                $bay->save();
            }

            $jobCard->workshop_bay_id = null;
            $jobCard->save();

            event(new WorkshopBayAllocated($jobCard, null));

            return true;
        });
    }
}
