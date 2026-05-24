<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MobileAuthService;
use App\Services\OfflineSyncService;
use Illuminate\Http\Request;

class MobileApiController extends Controller
{
    protected $authService;
    protected $syncService;

    public function __construct(MobileAuthService $authService, OfflineSyncService $syncService)
    {
        $this->authService = $authService;
        $this->syncService = $syncService;
    }

    public function login(Request $request)
    {
        return response()->json($this->authService->login($request->all(), $request->header('User-Agent')));
    }

    public function sync(Request $request)
    {
        return response()->json($this->syncService->sync($request->header('Device-Token'), $request->all()));
    }

    public function dashboard()
    {
        return response()->json([
            'pending_jobs' => 12,
            'today_revenue' => 15000,
            'notifications' => 3
        ]);
    }
}
