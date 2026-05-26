<?php

namespace App\Services;

class MobileAuthService
{
    public function login($credentials, $deviceInfo)
    {
        // JWT Authentication with device tracking
        return [
            'token' => 'jwt-token-example',
            'user' => ['id' => 1, 'name' => 'Admin'],
            'device_id' => 'device-123'
        ];
    }
}
