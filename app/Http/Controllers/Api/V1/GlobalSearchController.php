<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    use ApiResponseTrait;

    protected $service;

    public function __construct(GlobalSearchService $service)
    {
        $this->service = $service;
    }

    /**
     * Perform a global search.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return $this->successResponse([], 'Query cannot be empty');
        }

        $results = $this->service->search($query);
        
        return $this->successResponse($results, 'Search results retrieved successfully');
    }
}
