<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    use ApiResponseTrait;

    /**
     * Run database backup.
     */
    public function run(): JsonResponse
    {
        // Only admins should do this
        $this->authorize('viewAny', \App\Models\Setting::class);

        try {
            // Queue the backup to avoid timeout
            Artisan::queue('backup:run');

            return $this->successResponse(null, 'Backup job dispatched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * List backups.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Setting::class);

        $name = config('backup.backup.name', 'Laravel');
        $files = Storage::disk('local')->files($name);

        return $this->successResponse($files, 'Backups retrieved successfully');
    }

    /**
     * Delete a backup.
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Setting::class);

        $path = $request->input('path');
        
        if (empty($path)) {
            return $this->errorResponse('Path is required', 400);
        }

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            return $this->successResponse(null, 'Backup deleted successfully');
        }

        return $this->errorResponse('File not found', 404);
    }
}
