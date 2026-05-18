<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\PurchaseService;
use App\Http\Requests\CreatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Http\Resources\PartResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    use ApiResponseTrait;

    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(Request $request): JsonResponse
    {
        $purchases = $this->purchaseService->listPurchases($request->all());
        return $this->successResponse(PurchaseResource::collection($purchases), 'Purchases retrieved successfully');
    }

    public function store(CreatePurchaseRequest $request): JsonResponse
    {
        try {
            $purchase = $this->purchaseService->createPurchase($request->validated());
            return $this->successResponse(new PurchaseResource($purchase), 'Purchase created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $purchase = $this->purchaseService->getPurchase($id);
        
        if (!$purchase) {
            return $this->errorResponse('Purchase not found', 404);
        }
        
        return $this->successResponse(new PurchaseResource($purchase), 'Purchase details retrieved successfully');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,received,cancelled',
        ]);

        try {
            $updated = $this->purchaseService->updatePurchaseStatus($id, $request->status);
            
            if (!$updated) {
                return $this->errorResponse('Purchase not found or update failed', 404);
            }
            
            $purchase = $this->purchaseService->getPurchase($id);
            return $this->successResponse(new PurchaseResource($purchase), 'Purchase status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function lowStockParts(): JsonResponse
    {
        $parts = $this->purchaseService->getLowStockParts();
        return $this->successResponse(PartResource::collection($parts), 'Low stock parts retrieved successfully');
    }
}
