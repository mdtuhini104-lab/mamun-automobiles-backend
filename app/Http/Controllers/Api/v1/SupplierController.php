<?php

namespace App\Http\Controllers\Api\v1;

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

    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request): JsonResponse
    {
        $suppliers = $this->supplierService->listSuppliers($request->all());
        return $this->successResponse(SupplierResource::collection($suppliers), 'Suppliers retrieved successfully');
    }

    public function store(CreateSupplierRequest $request): JsonResponse
    {
        $supplier = $this->supplierService->createSupplier($request->validated());
        return $this->successResponse(new SupplierResource($supplier), 'Supplier created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $supplier = $this->supplierService->getSupplier($id);
        
        if (!$supplier) {
            return $this->errorResponse('Supplier not found', 404);
        }
        
        return $this->successResponse(new SupplierResource($supplier), 'Supplier details retrieved successfully');
    }

    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        $updated = $this->supplierService->updateSupplier($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Supplier not found or update failed', 404);
        }
        
        $supplier = $this->supplierService->getSupplier($id);
        return $this->successResponse(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->supplierService->deleteSupplier($id);
        
        if (!$deleted) {
            return $this->errorResponse('Supplier not found or delete failed', 404);
        }
        
        return $this->successResponse(null, 'Supplier deleted successfully');
    }
}
