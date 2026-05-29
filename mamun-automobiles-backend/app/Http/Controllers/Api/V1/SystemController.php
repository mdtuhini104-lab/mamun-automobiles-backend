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

    public function createBackup(\App\Services\BackupRecoveryEngine $engine)
    {
        try {
            $archivePath = $engine->createArchive();
            $encryptedPath = $engine->gpgEncrypt($archivePath);
            $isViable = $engine->verifyRestore($encryptedPath);

            if ($isViable) {
                return response()->json([
                    'success' => true,
                    'message' => 'Encrypted system database backup completed and restore integrity verified successfully.',
                    'file' => basename($encryptedPath)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity verification failed on backup archive.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function restoreBackup(Request $request, \App\Services\BackupRecoveryEngine $engine)
    {
        $filePath = storage_path('app/backups/' . $request->input('file_id'));
        try {
            $isValid = $engine->verifyRestore($filePath);
            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup archive restore capability successfully verified.'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid backup encryption signature or corrupted file payload.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
