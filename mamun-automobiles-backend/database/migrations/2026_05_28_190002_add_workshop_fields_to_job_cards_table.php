<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('job_cards', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('vehicle_id')->constrained('departments')->nullOnDelete();
                $table->index('department_id');
            }
            if (!Schema::hasColumn('job_cards', 'workshop_bay_id')) {
                $table->foreignId('workshop_bay_id')->nullable()->after('department_id')->constrained('workshop_bays')->nullOnDelete();
                $table->index('workshop_bay_id');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('job_cards', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropForeign(['workshop_bay_id']);
                $table->dropColumn(['department_id', 'workshop_bay_id']);
            });
        }
    }
};
