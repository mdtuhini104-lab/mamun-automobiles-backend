<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\StockAdjustmentService;
use App\Http\Requests\CreateStockAdjustmentRequest;
use App\Http\Resources\StockAdjustmentResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    use ApiResponseTrait;

    protected $stockAdjustmentService;

    public function __construct(StockAdjustmentService $stockAdjustmentService)
    {
        $this->stockAdjustmentService = $stockAdjustmentService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\StockAdjustment::class);

        $adjustments = $this->stockAdjustmentService->listAdjustments($request->all());
        
        $meta = [
            'current_page' => $adjustments->currentPage(),
            'per_page' => $adjustments->perPage(),
            'total' => $adjustments->total(),
            'last_page' => $adjustments->lastPage(),
        ];
        
        return $this->successResponse(StockAdjustmentResource::collection($adjustments->items()), 'Stock adjustments retrieved successfully', 200, $meta);
    }

    public function store(CreateStockAdjustmentRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\StockAdjustment::class);

        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id(); // Set authenticated user
            
            $adjustment = $this->stockAdjustmentService->createAdjustment($data);
            return $this->successResponse(new StockAdjustmentResource($adjustment), 'Stock adjustment created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $adjustment = $this->stockAdjustmentService->getAdjustment($id);
        
        if (!$adjustment) {
            return $this->errorResponse('Stock adjustment not found', 404);
        }
        
        $this->authorize('view', $adjustment);
        
        return $this->successResponse(new StockAdjustmentResource($adjustment), 'Stock adjustment details retrieved successfully');
    }

    public function update(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        $adjustment = $this->stockAdjustmentService->getAdjustment($id);
        
        if (!$adjustment) {
            return $this->errorResponse('Stock adjustment not found', 404);
        }

        $this->authorize('update', $adjustment);

        $updated = $this->stockAdjustmentService->updateAdjustment($id, $request->all());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        $adjustment = $this->stockAdjustmentService->getAdjustment($id);
        return $this->successResponse(new StockAdjustmentResource($adjustment), 'Stock adjustment updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $adjustment = $this->stockAdjustmentService->getAdjustment($id);
        
        if (!$adjustment) {
            return $this->errorResponse('Stock adjustment not found', 404);
        }

        $this->authorize('delete', $adjustment);

        try {
            $deleted = $this->stockAdjustmentService->deleteAdjustment($id);
            
            if (!$deleted) {
                return $this->errorResponse('Delete failed', 400);
            }
            
            return $this->successResponse(null, 'Stock adjustment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
