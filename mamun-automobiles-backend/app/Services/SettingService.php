<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    /**
     * Get a setting by key.
     */
    public function get(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting.
     */
    public function set(string $key, $value): Setting
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings.
     */
    public function all(): array
    {
        return Setting::pluck('value', 'key')->toArray();
    }
}
