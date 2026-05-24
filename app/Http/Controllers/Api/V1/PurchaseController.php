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
        $this->authorize('viewAny', \App\Models\Purchase::class);

        $purchases = $this->purchaseService->listPurchases($request->all());
        
        $meta = [
            'current_page' => $purchases->currentPage(),
            'per_page' => $purchases->perPage(),
            'total' => $purchases->total(),
            'last_page' => $purchases->lastPage(),
        ];
        
        return $this->successResponse(PurchaseResource::collection($purchases->items()), 'Purchases retrieved successfully', 200, $meta);
    }

    public function store(CreatePurchaseRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Purchase::class);

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
        
        $this->authorize('view', $purchase);
        
        return $this->successResponse(new PurchaseResource($purchase), 'Purchase details retrieved successfully');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $purchase = $this->purchaseService->getPurchase($id);
        
        if (!$purchase) {
            return $this->errorResponse('Purchase not found', 404);
        }

        $this->authorize('update', $purchase);

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,received,cancelled',
        ]);

        try {
            $updated = $this->purchaseService->updatePurchaseStatus($id, $request->status);
            
            if (!$updated) {
                return $this->errorResponse('Update failed', 400);
            }
            
            $purchase = $this->purchaseService->getPurchase($id);
            return $this->successResponse(new PurchaseResource($purchase), 'Purchase status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function lowStockParts(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Purchase::class);

        $parts = $this->purchaseService->getLowStockParts();
        return $this->successResponse(PartResource::collection($parts), 'Low stock parts retrieved successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $purchase = $this->purchaseService->getPurchase($id);
        
        if (!$purchase) {
            return $this->errorResponse('Purchase not found', 404);
        }

        $this->authorize('delete', $purchase);

        $deleted = $this->purchaseService->deletePurchase($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Purchase deleted successfully');
    }
}
