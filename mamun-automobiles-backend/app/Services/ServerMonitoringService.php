<?php

namespace App\Services;

class ServerMonitoringService
{
    public function getSystemHealth()
    {
        return [
            'cpu_usage' => '12%',
            'ram_usage' => '45%',
            'disk_usage' => '65%',
            'database_status' => 'Healthy',
            'api_response_time' => '120ms'
        ];
    }
}
