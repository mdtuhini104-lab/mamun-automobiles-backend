<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Shift;

return new class extends Migration
{
    public function up(): void
    {
        Shift::firstOrCreate([
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_threshold_minutes' => 15
        ]);

        Shift::firstOrCreate([
            'name' => 'Evening Shift',
            'start_time' => '14:00:00',
            'end_time' => '22:00:00',
            'late_threshold_minutes' => 15
        ]);

        Shift::firstOrCreate([
            'name' => 'Night Shift',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'late_threshold_minutes' => 15
        ]);
    }

    public function down(): void
    {
        // Keep shifts intact on rollback to avoid breaking historical records
    }
};
