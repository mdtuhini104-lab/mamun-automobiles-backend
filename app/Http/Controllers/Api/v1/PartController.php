<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\PartService;
use App\Http\Requests\CreatePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Http\Resources\PartResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    use ApiResponseTrait;

    protected $partService;

    public function __construct(PartService $partService)
    {
        $this->partService = $partService;
    }

    /**
     * List all parts with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['brand', 'stock_availability']);
        $parts = $this->partService->listParts($filters);
        return $this->successResponse(PartResource::collection($parts), 'Parts retrieved successfully');
    }

    /**
     * Create a new part.
     */
    public function store(CreatePartRequest $request): JsonResponse
    {
        $part = $this->partService->createPart($request->validated());
        return $this->successResponse(new PartResource($part), 'Part created successfully', 201);
    }

    /**
     * Get part details.
     */
    public function show(int $id): JsonResponse
    {
        $part = $this->partService->getPart($id);
        
        if (!$part) {
            return $this->errorResponse('Part not found', 404);
        }
        
        return $this->successResponse(new PartResource($part), 'Part details retrieved successfully');
    }

    /**
     * Update a part.
     */
    public function update(UpdatePartRequest $request, int $id): JsonResponse
    {
        $updated = $this->partService->updatePart($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Part not found or update failed', 404);
        }
        
        return $this->successResponse(null, 'Part updated successfully');
    }

    /**
     * Delete a part.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->partService->deletePart($id);
        
        if (!$deleted) {
            return $this->errorResponse('Part not found or delete failed', 404);
        }
        
        return $this->successResponse(null, 'Part deleted successfully');
    }

    /**
     * Get low stock parts.
     */
    public function lowStock(): JsonResponse
    {
        $parts = $this->partService->getLowStockParts();
        return $this->successResponse(PartResource::collection($parts), 'Low stock parts retrieved successfully');
    }
}
