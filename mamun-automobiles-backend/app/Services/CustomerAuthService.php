<?php

namespace App\Services;

class CustomerAuthService
{
    public function login($credentials)
    {
        // Issue token
        return ['token' => 'dummy-portal-token', 'customer' => []];
    }
}
