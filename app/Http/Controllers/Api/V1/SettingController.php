<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponseTrait;

    protected $service;

    public function __construct(SettingService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all settings.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Setting::class);

        return $this->successResponse($this->service->all(), 'Settings retrieved successfully');
    }

    /**
     * Update settings.
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('update', \App\Models\Setting::class);

        $data = $request->all();
        
        foreach ($data as $key => $value) {
            $this->service->set($key, $value);
        }

        return $this->successResponse($this->service->all(), 'Settings updated successfully');
    }
}
