<?php

namespace App\Http\Controllers\Api\V1;

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
        $this->authorize('create', \App\Models\JobCard::class);

        $jobCard = $this->jobCardService->createJobCard($request->validated());
        return $this->successResponse(new JobCardResource($jobCard), 'Job Card created successfully', 201);
    }

    /**
     * List all job cards with optional filters.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\JobCard::class);

        $filters = $request->only(['status', 'customer_id', 'vehicle_id', 'search', 'page', 'per_page', 'sort_by', 'sort_order']);
        $jobCards = $this->jobCardService->listJobCards($filters);
        
        $meta = [
            'current_page' => $jobCards->currentPage(),
            'per_page' => $jobCards->perPage(),
            'total' => $jobCards->total(),
            'last_page' => $jobCards->lastPage(),
        ];
        
        return $this->successResponse(JobCardResource::collection($jobCards->items()), 'Job Cards retrieved successfully', 200, $meta);
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
        
        $this->authorize('view', $jobCard);
        
        return $this->successResponse(new JobCardResource($jobCard), 'Job Card details retrieved successfully');
    }

    /**
     * Get service history by vehicle.
     */
    public function vehicleHistory(int $vehicleId): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\JobCard::class);

        $history = $this->jobCardService->getVehicleServiceHistory($vehicleId);
        return $this->successResponse(JobCardResource::collection($history), 'Vehicle service history retrieved successfully');
    }

    /**
     * Get service history by customer.
     */
    public function customerHistory(int $customerId): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\JobCard::class);

        $history = $this->jobCardService->getCustomerServiceHistory($customerId);
        return $this->successResponse(JobCardResource::collection($history), 'Customer service history retrieved successfully');
    }

    /**
     * Update a job card.
     */
    public function update(UpdateJobCardRequest $request, int $id): JsonResponse
    {
        $jobCard = $this->jobCardService->getJobCard($id);
        
        if (!$jobCard) {
            return $this->errorResponse('Job Card not found', 404);
        }

        $this->authorize('update', $jobCard);

        $updated = $this->jobCardService->updateJobCard($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        return $this->successResponse(null, 'Job Card updated successfully');
    }

    /**
     * Delete a job card.
     */
    public function destroy(int $id): JsonResponse
    {
        $jobCard = $this->jobCardService->getJobCard($id);
        
        if (!$jobCard) {
            return $this->errorResponse('Job Card not found', 404);
        }

        $this->authorize('delete', $jobCard);

        $deleted = $this->jobCardService->deleteJobCard($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Job Card deleted successfully');
    }
}
