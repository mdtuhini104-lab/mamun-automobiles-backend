<?php

namespace App\Http\Controllers\Api\v1;

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
        $user = $this->userService->createUser($request->validated());
        return $this->successResponse(new UserResource($user), 'User created successfully', 201);
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
        $updated = $this->userService->updateUser($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('User not found or update failed', 404);
        }
        
        $user = $this->userService->getUser($id);
        return $this->successResponse(new UserResource($user), 'User updated successfully');
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
