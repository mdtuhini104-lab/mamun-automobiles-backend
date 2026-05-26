<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Category::class);

        $categories = $this->categoryService->listCategories($request->all());
        
        $meta = [
            'current_page' => $categories->currentPage(),
            'per_page' => $categories->perPage(),
            'total' => $categories->total(),
            'last_page' => $categories->lastPage(),
        ];
        
        return $this->successResponse(CategoryResource::collection($categories->items()), 'Categories retrieved successfully', 200, $meta);
    }

    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Category::class);

        $category = $this->categoryService->createCategory($request->validated());
        return $this->successResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    /**
     * Get category details.
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->categoryService->getCategory($id);
        
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        
        $this->authorize('view', $category);
        
        return $this->successResponse(new CategoryResource($category), 'Category details retrieved successfully');
    }

    /**
     * Update a category.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->categoryService->getCategory($id);
        
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $this->authorize('update', $category);

        $updated = $this->categoryService->updateCategory($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        $category = $this->categoryService->getCategory($id);
        return $this->successResponse(new CategoryResource($category), 'Category updated successfully');
    }

    /**
     * Delete a category.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = $this->categoryService->getCategory($id);
        
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $this->authorize('delete', $category);

        $deleted = $this->categoryService->deleteCategory($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Category deleted successfully');
    }
}
