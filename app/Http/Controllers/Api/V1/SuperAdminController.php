<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class SuperAdminController extends Controller
{
    use ApiResponseTrait;

    public function getTenants(): JsonResponse
    {
        $tenants = DB::table('tenants')->get();
        return $this->successResponse($tenants, 'Tenants fetched successfully');
    }

    public function updateTenantStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:trial,active,suspended']);
        DB::table('tenants')->where('id', $id)->update(['status' => $request->status]);
        return $this->successResponse(null, 'Tenant status updated');
    }

    public function getSystemStats(): JsonResponse
    {
        $stats = [
            'total_tenants' => DB::table('tenants')->count(),
            'total_users' => DB::table('users')->count(),
            'total_branches' => DB::table('branches')->count(),
            'total_invoices' => DB::table('invoices')->count(),
        ];
        return $this->successResponse($stats, 'System stats fetched');
    }
}

