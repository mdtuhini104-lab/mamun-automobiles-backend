<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\VehicleService;
use App\Http\Requests\CreateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    use ApiResponseTrait;

    protected $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Add a vehicle to a customer.
     */
    public function store(CreateVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->addVehicle($request->validated());
        return $this->successResponse(new VehicleResource($vehicle), 'Vehicle added successfully', 201);
    }

    /**
     * List vehicles by customer.
     */
    public function index(int $customerId): JsonResponse
    {
        $vehicles = $this->vehicleService->listVehiclesByCustomer($customerId);
        return $this->successResponse(VehicleResource::collection($vehicles), 'Vehicles retrieved successfully');
    }
}
