<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    $users = User::all();
    foreach ($users as $user) {
        echo "User ID: " . $user->id . "\n";
        echo "Name: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Tenant ID: " . $user->tenant_id . "\n";
        echo "Branch ID: " . $user->branch_id . "\n";
        echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
        echo "Direct Permissions: " . implode(', ', $user->getPermissionNames()->toArray()) . "\n";
        echo "-----------------------------------------\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
