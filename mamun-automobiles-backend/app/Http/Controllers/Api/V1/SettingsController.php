<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $settings = Cache::rememberForever('app_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $this->successResponse($settings, 'Settings retrieved successfully');
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->except(['logo', 'favicon']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Handle File Uploads
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'company_logo'], ['value' => '/storage/' . $path]);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'company_favicon'], ['value' => '/storage/' . $path]);
            $data['company_favicon'] = '/storage/' . $path; // for audit log
        }

        \App\Models\AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'updated_settings',
            'model_type' => Setting::class,
            'model_id' => null,
            'changes' => $data,
            'ip_address' => $request->ip(),
        ]);

        Cache::forget('app_settings');

        return $this->successResponse(null, 'Settings updated successfully');
    }
}
