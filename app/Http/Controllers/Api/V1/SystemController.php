<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use App\Services\DisasterRecoveryService;
use App\Services\ServerMonitoringService;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    protected $backupService;
    protected $recoveryService;
    protected $monitoringService;

    public function __construct(BackupService $backupService, DisasterRecoveryService $recoveryService, ServerMonitoringService $monitoringService)
    {
        $this->backupService = $backupService;
        $this->recoveryService = $recoveryService;
        $this->monitoringService = $monitoringService;
    }

    public function getHealth()
    {
        return response()->json($this->monitoringService->getSystemHealth());
    }

    public function createBackup()
    {
        return response()->json($this->backupService->generateFullBackup());
    }

    public function restoreBackup(Request $request)
    {
        return response()->json($this->recoveryService->restoreFromBackup($request->input('file_id')));
    }
}
