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

    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * List all customers.
     */
    public function index(): JsonResponse
    {
        $customers = $this->customerService->listCustomers();
        return $this->successResponse(CustomerResource::collection($customers), 'Customers retrieved successfully');
    }

    /**
     * Create a new customer.
     */
    public function store(CreateCustomerRequest $request): JsonResponse
    {
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
        
        return $this->successResponse(new CustomerResource($customer), 'Customer details retrieved successfully');
    }
}
