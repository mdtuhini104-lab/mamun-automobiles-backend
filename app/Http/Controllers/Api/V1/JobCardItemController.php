<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\JobCardService;
use App\Http\Requests\AddJobCardItemRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class JobCardItemController extends Controller
{
    use ApiResponseTrait;

    protected $jobCardService;

    public function __construct(JobCardService $jobCardService)
    {
        $this->jobCardService = $jobCardService;
    }

    /**
     * Add item to job card.
     */
    public function store(AddJobCardItemRequest $request, int $jobCardId): JsonResponse
    {
        try {
            $this->jobCardService->addItemToJobCard($jobCardId, $request->validated());
            return $this->successResponse(null, 'Item added to Job Card successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
