<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CustomerPricingEngine;
use App\Http\Requests\CreateCustomerPricingRequest;
use App\Http\Resources\CustomerPricingResource;
use App\Models\CustomerPricing;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerPricingController extends Controller
{
    use ApiResponseTrait;

    protected $pricingEngine;

    public function __construct(CustomerPricingEngine $pricingEngine)
    {
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Get calculated negotiated or historical price rate for a customer.
     */
    public function getRate(Request $request, int $customerId): JsonResponse
    {
        $partId = $request->input('part_id') ? (int) $request->input('part_id') : null;
        $serviceName = $request->input('labor_service_name');

        try {
            $price = $this->pricingEngine->getRateForCustomer($customerId, $partId, $serviceName);
            return $this->successResponse([
                'price' => $price,
                'customer_id' => $customerId,
                'part_id' => $partId,
                'labor_service_name' => $serviceName,
            ], 'Customer pricing rate calculated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Store a negotiated pricing contract rate.
     */
    public function storeRate(CreateCustomerPricingRequest $request): JsonResponse
    {
        try {
            $pricing = $this->pricingEngine->storeContractRate($request->validated());
            $pricing->load(['customer', 'part']);
            
            return $this->successResponse(
                new CustomerPricingResource($pricing),
                'Customer negotiated contract rate saved successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * List all negotiated rates for a customer.
     */
    public function index(Request $request, int $customerId): JsonResponse
    {
        $rates = CustomerPricing::where('customer_id', $customerId)
            ->with(['part'])
            ->orderBy('effective_date', 'desc')
            ->get();

        return $this->successResponse(
            CustomerPricingResource::collection($rates),
            'Customer negotiated contract rates retrieved successfully'
        );
    }
}
