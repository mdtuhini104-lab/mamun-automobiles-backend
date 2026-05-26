<?php

namespace App\Services;

class TenantService
{
    public function provisionTenant($data)
    {
        // Setup new tenant, create databases/schemas, provision storage
        return [
            'status' => 'success',
            'tenant_id' => 'TNT-' . uniqid(),
            'domain' => $data['subdomain'] . '.mamunerp.com'
        ];
    }
}
