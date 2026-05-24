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
        $this->authorize('create', \App\Models\Vehicle::class);

        $vehicle = $this->vehicleService->addVehicle($request->validated());
        return $this->successResponse(new VehicleResource($vehicle), 'Vehicle added successfully', 201);
    }

    /**
     * List vehicles by customer with optional filters.
     */
    public function index(\Illuminate\Http\Request $request, int $customerId): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Vehicle::class);

        $filters = $request->only(['search', 'page', 'per_page', 'sort_by', 'sort_order']);
        $filters['customer_id'] = $customerId;
        
        $vehicles = $this->vehicleService->listVehicles($filters);
        
        $meta = [
            'current_page' => $vehicles->currentPage(),
            'per_page' => $vehicles->perPage(),
            'total' => $vehicles->total(),
            'last_page' => $vehicles->lastPage(),
        ];
        
        return $this->successResponse(VehicleResource::collection($vehicles->items()), 'Vehicles retrieved successfully', 200, $meta);
    }

    /**
     * Get vehicle details.
     */
    public function show(int $id): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicle($id);
        
        if (!$vehicle) {
            return $this->errorResponse('Vehicle not found', 404);
        }
        
        $this->authorize('view', $vehicle);
        
        return $this->successResponse(new VehicleResource($vehicle), 'Vehicle details retrieved successfully');
    }

    /**
     * Update a vehicle.
     */
    public function update(\App\Http\Requests\UpdateVehicleRequest $request, int $id): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicle($id);
        
        if (!$vehicle) {
            return $this->errorResponse('Vehicle not found', 404);
        }

        $this->authorize('update', $vehicle);

        $updated = $this->vehicleService->updateVehicle($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        $vehicle = $this->vehicleService->getVehicle($id);
        return $this->successResponse(new VehicleResource($vehicle), 'Vehicle updated successfully');
    }

    /**
     * Delete a vehicle.
     */
    public function destroy(int $id): JsonResponse
    {
        $vehicle = $this->vehicleService->getVehicle($id);
        
        if (!$vehicle) {
            return $this->errorResponse('Vehicle not found', 404);
        }

        $this->authorize('delete', $vehicle);

        $deleted = $this->vehicleService->deleteVehicle($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Vehicle deleted successfully');
    }

    /**
     * List all vehicles with optional filters (global).
     */
    public function indexAll(\Illuminate\Http\Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Vehicle::class);

        $filters = $request->only(['search', 'page', 'per_page', 'sort_by', 'sort_order']);
        
        $vehicles = $this->vehicleService->listVehicles($filters);
        
        $meta = [
            'current_page' => $vehicles->currentPage(),
            'per_page' => $vehicles->perPage(),
            'total' => $vehicles->total(),
            'last_page' => $vehicles->lastPage(),
        ];
        
        return $this->successResponse(VehicleResource::collection($vehicles->items()), 'Vehicles retrieved successfully', 200, $meta);
    }
}
