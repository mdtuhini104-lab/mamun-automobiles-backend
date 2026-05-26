<?php

namespace App\Services;

class LicenseService
{
    public function validateLicense($licenseKey)
    {
        return [
            'valid' => true,
            'features' => ['all_access']
        ];
    }
}
