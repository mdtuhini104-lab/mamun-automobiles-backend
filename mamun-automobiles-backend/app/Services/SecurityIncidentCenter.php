<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class SecurityIncidentCenter
{
    /**
     * Log a security incident in the registry.
     */
    public function logIncident(string $type, string $severity, string $message, array $details = []): void
    {
        $tenantId = auth()->check() ? auth()->user()->tenant_id : null;
        $ip = Request::ip();
        $userAgent = Request::header('User-Agent');

        DB::table('security_incidents')->insert([
            'tenant_id' => $tenantId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'incident_type' => $type,
            'severity' => $severity,
            'details' => json_encode(array_merge($details, ['message' => $message])),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // If brute force login, increment IP failure counters to block automatically
        if ($type === 'brute_force') {
            $key = 'login_fail_count_' . $ip;
            $fails = (int) Cache::get($key, 0) + 1;
            Cache::put($key, $fails, now()->addMinutes(15));

            if ($fails >= 5) {
                $this->blockIp($ip, 15);
            }
        }
    }

    /**
     * Set a block on an IP address in the cache.
     */
    public function blockIp(string $ip, int $minutes = 15): void
    {
        Cache::put('blocked_ip_' . $ip, true, now()->addMinutes($minutes));
    }

    /**
     * Check if an IP address is blocked.
     */
    public function isIpBlocked(string $ip): bool
    {
        return (bool) Cache::get('blocked_ip_' . $ip, false);
    }

    /**
     * Resolve an incident record.
     */
    public function resolveIncident(int $id): bool
    {
        return DB::table('security_incidents')
            ->where('id', $id)
            ->update([
                'resolved_at' => now(),
                'updated_at' => now()
            ]) > 0;
    }
}
