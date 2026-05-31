<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

try {
    // Find the first user that is NOT Super Admin
    $user = User::whereDoesntHave('roles', function($q) {
        $q->where('name', 'Super Admin');
    })->first();

    if (!$user) {
        echo "No non-Super-Admin user found in DB. Creating a temporary Technician user...\n";
        // Create a temporary technician user
        $user = User::create([
            'name' => 'Temp Tech',
            'email' => 'temp.tech@example.com',
            'password' => bcrypt('password123'),
            'tenant_id' => 1,
            'branch_id' => 3
        ]);
        // Assign Technician role
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Technician', 'guard_name' => 'web']);
        $user->assignRole($role);
    }

    echo "Logging in as: " . $user->name . " (Email: " . $user->email . ", Roles: " . implode(', ', $user->getRoleNames()->toArray()) . ")\n";
    Auth::login($user);

    // Set current branch config just like middleware/request would
    if ($user->branch_id) {
        config(['app.current_branch_id' => $user->branch_id]);
    }

    echo "Calling getDashboardData()...\n";
    $service = app(App\Services\DashboardService::class);
    $data = $service->getDashboardData();
    echo "SUCCESS!\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

} catch (\Illuminate\Database\QueryException $e) {
    echo "DATABASE QUERY EXCEPTION CAUGHT:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "SQL: " . $e->getSql() . "\n";
    echo "Bindings: " . json_encode($e->getBindings()) . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (\Throwable $e) {
    echo "ERROR EXCEPTION CAUGHT:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
