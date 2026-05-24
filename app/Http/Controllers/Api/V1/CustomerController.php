<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use ApiResponseTrait;

    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * List all customers with optional filters.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Customer::class);

        $filters = $request->only(['search', 'page', 'per_page', 'sort_by', 'sort_order']);
        $customers = $this->customerService->listCustomers($filters);
        
        $meta = [
            'current_page' => $customers->currentPage(),
            'per_page' => $customers->perPage(),
            'total' => $customers->total(),
            'last_page' => $customers->lastPage(),
        ];
        
        return $this->successResponse(CustomerResource::collection($customers->items()), 'Customers retrieved successfully', 200, $meta);
    }

    /**
     * Create a new customer.
     */
    public function store(CreateCustomerRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Customer::class);

        $customer = $this->customerService->createCustomer($request->validated());
        return $this->successResponse(new CustomerResource($customer), 'Customer created successfully', 201);
    }

    /**
     * Get customer details.
     */
    public function show(int $id): JsonResponse
    {
        $customer = $this->customerService->getCustomer($id);
        
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }
        
        $this->authorize('view', $customer);
        
        return $this->successResponse(new CustomerResource($customer), 'Customer details retrieved successfully');
    }

    /**
     * Update a customer.
     */
    public function update(\App\Http\Requests\UpdateCustomerRequest $request, int $id): JsonResponse
    {
        $customer = $this->customerService->getCustomer($id);
        
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }

        $this->authorize('update', $customer);

        $updated = $this->customerService->updateCustomer($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        $customer = $this->customerService->getCustomer($id);
        return $this->successResponse(new CustomerResource($customer), 'Customer updated successfully');
    }

    /**
     * Delete a customer.
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = $this->customerService->getCustomer($id);
        
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }

        $this->authorize('delete', $customer);

        $deleted = $this->customerService->deleteCustomer($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Customer deleted successfully');
    }
}
