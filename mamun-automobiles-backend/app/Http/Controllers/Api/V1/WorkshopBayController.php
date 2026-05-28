<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WorkshopBayService;
use App\Http\Resources\WorkshopBayResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkshopBayController extends Controller
{
    use ApiResponseTrait;

    protected $bayService;

    public function __construct(WorkshopBayService $bayService)
    {
        $this->bayService = $bayService;
    }

    public function index(Request $request): JsonResponse
    {
        $bays = $this->bayService->listBays($request->only(['status', 'branch_id']));
        return $this->successResponse(
            WorkshopBayResource::collection($bays),
            'Workshop Bays retrieved successfully'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:workshop_bays,code|max:255',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'max_vehicle_capacity' => 'required|integer|min:1',
            'status' => 'nullable|string|in:active,inactive,maintenance',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            $bay = $this->bayService->createBay($request->all());
            return $this->successResponse(
                new WorkshopBayResource($bay),
                'Workshop Bay created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function getUtilization(int $id): JsonResponse
    {
        try {
            $metrics = resolve(\App\Services\WorkforceAssignmentService::class)->getBayUtilization($id);
            return $this->successResponse($metrics, 'Workshop Bay utilization retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
