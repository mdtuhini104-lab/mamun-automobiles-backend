<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $modules = ['Invoice', 'User', 'Settings', 'Customer', 'Vehicle', 'JobCard', 'Inventory', 'Payroll'];
        $actions = ['created', 'updated', 'deleted', 'login', 'logout', 'failed_login', 'exported'];
        $severities = ['info', 'warning', 'danger'];

        $logs = [];
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $action = $actions[array_rand($actions)];
            $module = $action === 'login' || $action === 'failed_login' || $action === 'logout' ? 'Auth' : $modules[array_rand($modules)];
            $severity = $action === 'failed_login' || $action === 'deleted' ? 'danger' : 'info';

            $logs[] = [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'branch_id' => $user->branch_id,
                'module' => $module,
                'action' => $action,
                'description' => "User {$user->name} {$action} {$module}.",
                'old_values' => $action === 'updated' ? json_encode(['status' => 'pending']) : null,
                'new_values' => $action === 'updated' || $action === 'created' ? json_encode(['status' => 'completed']) : null,
                'ip_address' => '192.168.1.' . rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'url' => "https://erp.mamunautomobiles.com/api/{$module}",
                'method' => $action === 'created' ? 'POST' : ($action === 'updated' ? 'PUT' : 'GET'),
                'severity' => $severity,
                'created_at' => now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440)),
                'updated_at' => now(),
            ];
        }

        ActivityLog::insert($logs);
    }
}
