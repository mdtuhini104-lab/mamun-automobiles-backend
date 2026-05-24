<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\PartService;
use App\Http\Requests\CreatePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Http\Resources\PartResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    use ApiResponseTrait;

    protected $partService;
    protected $mediaService;

    public function __construct(PartService $partService, \App\Services\MediaService $mediaService)
    {
        $this->partService = $partService;
        $this->mediaService = $mediaService;
    }

    /**
     * List all parts with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Part::class);

        $filters = $request->only(['brand', 'stock_availability', 'search', 'page', 'per_page', 'sort_by', 'sort_order']);
        $parts = $this->partService->listParts($filters);
        
        $meta = [
            'current_page' => $parts->currentPage(),
            'per_page' => $parts->perPage(),
            'total' => $parts->total(),
            'last_page' => $parts->lastPage(),
        ];
        
        return $this->successResponse(PartResource::collection($parts->items()), 'Parts retrieved successfully', 200, $meta);
    }

    /**
     * Create a new part.
     */
    public function store(CreatePartRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Part::class);

        $part = $this->partService->createPart($request->validated());
        return $this->successResponse(new PartResource($part), 'Part created successfully', 201);
    }

    /**
     * Get part details.
     */
    public function show(int $id): JsonResponse
    {
        $part = $this->partService->getPart($id);
        
        if (!$part) {
            return $this->errorResponse('Part not found', 404);
        }
        
        $this->authorize('view', $part);
        
        return $this->successResponse(new PartResource($part), 'Part details retrieved successfully');
    }

    /**
     * Update a part.
     */
    public function update(UpdatePartRequest $request, int $id): JsonResponse
    {
        $part = $this->partService->getPart($id);
        
        if (!$part) {
            return $this->errorResponse('Part not found', 404);
        }

        $this->authorize('update', $part);

        $updated = $this->partService->updatePart($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        return $this->successResponse(null, 'Part updated successfully');
    }

    /**
     * Delete a part.
     */
    public function destroy(int $id): JsonResponse
    {
        $part = $this->partService->getPart($id);
        
        if (!$part) {
            return $this->errorResponse('Part not found', 404);
        }

        $this->authorize('delete', $part);

        $deleted = $this->partService->deletePart($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Part deleted successfully');
    }

    /**
     * Upload image for a part.
     */
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $part = $this->partService->getPart($id);
        
        if (!$part) {
            return $this->errorResponse('Part not found', 404);
        }

        $this->authorize('update', $part);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($part->image_path) {
                $this->mediaService->delete($part->image_path);
            }

            $path = $this->mediaService->upload($request->file('image'), 'parts');
            
            $this->partService->updatePart($id, ['image_path' => $path]);

            return $this->successResponse(['image_path' => $path, 'url' => $this->mediaService->getUrl($path)], 'Image uploaded successfully');
        }

        return $this->errorResponse('No image uploaded', 400);
    }

    /**
     * Get low stock parts.
     */
    public function lowStock(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Part::class);

        $parts = $this->partService->getLowStockParts();
        return $this->successResponse(PartResource::collection($parts), 'Low stock parts retrieved successfully');
    }
}
