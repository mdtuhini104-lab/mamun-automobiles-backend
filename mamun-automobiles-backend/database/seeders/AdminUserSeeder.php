<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@mamunerp.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('admin123')]
        );
        $superAdmin->assignRole('Super Admin');

        $manager = User::updateOrCreate(
            ['email' => 'manager@mamunerp.com'],
            ['name' => 'Workshop Manager', 'password' => Hash::make('manager123')]
        );
        $manager->assignRole('Manager');

        $technician = User::updateOrCreate(
            ['email' => 'tech@mamunerp.com'],
            ['name' => 'Senior Technician', 'password' => Hash::make('tech123')]
        );
        $technician->assignRole('Technician');

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@mamunerp.com'],
            ['name' => 'Front Desk Cashier', 'password' => Hash::make('cashier123')]
        );
        $cashier->assignRole('Cashier');

        $storeManager = User::updateOrCreate(
            ['email' => 'store@mamunerp.com'],
            ['name' => 'Inventory Manager', 'password' => Hash::make('store123')]
        );
        $storeManager->assignRole('Store Manager');
    }
}
