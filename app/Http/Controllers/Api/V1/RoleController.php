<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponseTrait;

class RoleController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        return $this->successResponse($roles, 'Roles retrieved successfully');
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::all();
        return $this->successResponse($permissions, 'Permissions retrieved successfully');
    }
}

