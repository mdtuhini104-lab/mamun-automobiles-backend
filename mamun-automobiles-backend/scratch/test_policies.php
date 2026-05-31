<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

try {
    // Find the first user who is a Technician
    $user = User::whereHas('roles', function($q) {
        $q->where('name', 'Technician');
    })->first();

    if (!$user) {
        // Create a temporary technician user
        $user = User::create([
            'name' => 'Temp Tech',
            'email' => 'temp.tech@example.com',
            'password' => bcrypt('password123'),
            'tenant_id' => 1,
            'branch_id' => 3
        ]);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Technician', 'guard_name' => 'web']);
        $user->assignRole($role);
    }

    echo "Logging in as: " . $user->name . " (Roles: " . implode(', ', $user->getRoleNames()->toArray()) . ")\n";
    Auth::login($user);

    echo "Testing Invoice viewAny policy...\n";
    $result = $user->can('viewAny', \App\Models\Invoice::class);
    echo "Can view any invoices: " . ($result ? "YES" : "NO") . "\n";

} catch (\Throwable $e) {
    echo "ERROR EXCEPTION CAUGHT:\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
