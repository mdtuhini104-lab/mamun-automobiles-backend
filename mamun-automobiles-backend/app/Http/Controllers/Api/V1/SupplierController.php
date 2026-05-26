<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SupplierService;
use App\Http\Requests\CreateSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponseTrait;

    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Supplier::class);

        $suppliers = $this->supplierService->listSuppliers($request->all());
        
        $meta = [
            'current_page' => $suppliers->currentPage(),
            'per_page' => $suppliers->perPage(),
            'total' => $suppliers->total(),
            'last_page' => $suppliers->lastPage(),
        ];
        
        return $this->successResponse(SupplierResource::collection($suppliers->items()), 'Suppliers retrieved successfully', 200, $meta);
    }

    public function store(CreateSupplierRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Supplier::class);

        $supplier = $this->supplierService->createSupplier($request->validated());
        return $this->successResponse(new SupplierResource($supplier), 'Supplier created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $supplier = $this->supplierService->getSupplier($id);
        
        if (!$supplier) {
            return $this->errorResponse('Supplier not found', 404);
        }
        
        $this->authorize('view', $supplier);
        
        return $this->successResponse(new SupplierResource($supplier), 'Supplier details retrieved successfully');
    }

    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        $supplier = $this->supplierService->getSupplier($id);
        
        if (!$supplier) {
            return $this->errorResponse('Supplier not found', 404);
        }

        $this->authorize('update', $supplier);

        $updated = $this->supplierService->updateSupplier($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        $supplier = $this->supplierService->getSupplier($id);
        return $this->successResponse(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $supplier = $this->supplierService->getSupplier($id);
        
        if (!$supplier) {
            return $this->errorResponse('Supplier not found', 404);
        }

        $this->authorize('delete', $supplier);

        $deleted = $this->supplierService->deleteSupplier($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Supplier deleted successfully');
    }
}
