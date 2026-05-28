<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponseTrait;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->listUsers($request->all());
        return $this->successResponse(UserResource::collection($users), 'Users retrieved successfully');
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());
            return $this->successResponse(new UserResource($user), 'User created successfully', 201);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('User creation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input' => $request->safe()->except(['password'])
            ]);
            throw $e;
        }
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);
        
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        
        return $this->successResponse(new UserResource($user), 'User details retrieved successfully');
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->userService->updateUser($id, $request->validated());
            
            if (!$updated) {
                \Illuminate\Support\Facades\Log::warning('User update failed: user not found or no changes made', [
                    'user_id' => $id,
                    'input' => $request->safe()->except(['password'])
                ]);
                return $this->errorResponse('User not found or update failed', 404);
            }
            
            $user = $this->userService->getUser($id);
            return $this->successResponse(new UserResource($user), 'User updated successfully');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('User update failed with exception', [
                'user_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input' => $request->safe()->except(['password'])
            ]);
            throw $e;
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->userService->deleteUser($id);
        
        if (!$deleted) {
            return $this->errorResponse('User not found or delete failed', 404);
        }
        
        return $this->successResponse(null, 'User deleted successfully');
    }
}
