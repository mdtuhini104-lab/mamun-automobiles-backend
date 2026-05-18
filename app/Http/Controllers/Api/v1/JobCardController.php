<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\JobCardService;
use App\Http\Requests\CreateJobCardRequest;
use App\Http\Requests\UpdateJobCardRequest;
use App\Http\Resources\JobCardResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class JobCardController extends Controller
{
    use ApiResponseTrait;

    protected $jobCardService;

    public function __construct(JobCardService $jobCardService)
    {
        $this->jobCardService = $jobCardService;
    }

    /**
     * Create a new job card.
     */
    public function store(CreateJobCardRequest $request): JsonResponse
    {
        $jobCard = $this->jobCardService->createJobCard($request->validated());
        return $this->successResponse(new JobCardResource($jobCard), 'Job Card created successfully', 201);
    }

    /**
     * List all job cards with optional filters.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $filters = $request->only(['status']);
        $jobCards = $this->jobCardService->listJobCards($filters);
        return $this->successResponse(JobCardResource::collection($jobCards), 'Job Cards retrieved successfully');
    }

    /**
     * Get job card details.
     */
    public function show(int $id): JsonResponse
    {
        $jobCard = $this->jobCardService->getJobCard($id);
        
        if (!$jobCard) {
            return $this->errorResponse('Job Card not found', 404);
        }
        
        return $this->successResponse(new JobCardResource($jobCard), 'Job Card details retrieved successfully');
    }

    /**
     * Get service history by vehicle.
     */
    public function vehicleHistory(int $vehicleId): JsonResponse
    {
        $history = $this->jobCardService->getVehicleServiceHistory($vehicleId);
        return $this->successResponse(JobCardResource::collection($history), 'Vehicle service history retrieved successfully');
    }

    /**
     * Get service history by customer.
     */
    public function customerHistory(int $customerId): JsonResponse
    {
        $history = $this->jobCardService->getCustomerServiceHistory($customerId);
        return $this->successResponse(JobCardResource::collection($history), 'Customer service history retrieved successfully');
    }

    /**
     * Update a job card.
     */
    public function update(UpdateJobCardRequest $request, int $id): JsonResponse
    {
        $updated = $this->jobCardService->updateJobCard($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Job Card not found or update failed', 404);
        }
        
        return $this->successResponse(null, 'Job Card updated successfully');
    }

    /**
     * Delete a job card.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->jobCardService->deleteJobCard($id);
        
        if (!$deleted) {
            return $this->errorResponse('Job Card not found or delete failed', 404);
        }
        
        return $this->successResponse(null, 'Job Card deleted successfully');
    }
}
