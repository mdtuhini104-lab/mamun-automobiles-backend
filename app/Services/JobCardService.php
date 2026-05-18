<?php

namespace App\Services;

use App\Repositories\JobCardRepository;
use App\Repositories\PartRepository;
use App\Repositories\JobCardItemRepository;
use App\Models\JobCard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class JobCardService extends BaseService
{
    protected $jobCardRepository;
    protected $partRepository;
    protected $jobCardItemRepository;
    protected $invoiceService;

    public function __construct(
        JobCardRepository $jobCardRepository,
        PartRepository $partRepository,
        JobCardItemRepository $jobCardItemRepository,
        InvoiceService $invoiceService
    ) {
        $this->jobCardRepository = $jobCardRepository;
        $this->partRepository = $partRepository;
        $this->jobCardItemRepository = $jobCardItemRepository;
        $this->invoiceService = $invoiceService;
    }

    public function createJobCard(array $data): JobCard
    {
        return $this->jobCardRepository->create($data);
    }

    public function listJobCards(array $filters = []): Collection
    {
        return $this->jobCardRepository->getAll($filters);
    }

    public function getJobCard(int $id): ?JobCard
    {
        return $this->jobCardRepository->findById($id);
    }

    public function getVehicleServiceHistory(int $vehicleId): Collection
    {
        return $this->jobCardRepository->getByVehicleId($vehicleId);
    }

    public function getCustomerServiceHistory(int $customerId): Collection
    {
        return $this->jobCardRepository->getByCustomerId($customerId);
    }

    public function updateJobCard(int $id, array $data): bool
    {
        $updated = $this->jobCardRepository->update($id, $data);
        
        if ($updated && isset($data['service_status']) && $data['service_status'] === 'completed') {
            $this->invoiceService->generateInvoiceFromJobCard($id);
        }
        
        return $updated;
    }

    public function deleteJobCard(int $id): bool
    {
        return $this->jobCardRepository->delete($id);
    }

    /**
     * Add part to job card with stock deduction.
     */
    public function addItemToJobCard(int $jobCardId, array $data): bool
    {
        return DB::transaction(function () use ($jobCardId, $data) {
            $part = $this->partRepository->findById($data['part_id']);
            
            if (!$part) {
                throw new \Exception('Part not found');
            }
            
            if ($part->stock_quantity < $data['quantity']) {
                throw new \Exception('Insufficient stock');
            }
            
            // Deduct stock
            $part->stock_quantity -= $data['quantity'];
            $part->save();
            
            // Create item
            $this->jobCardItemRepository->create([
                'job_card_id' => $jobCardId,
                'part_id' => $data['part_id'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'] ?? $part->sale_price,
            ]);
            
            return true;
        });
    }
}
