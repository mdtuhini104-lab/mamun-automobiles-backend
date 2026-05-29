<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('job_task_assignments');
        Schema::create('job_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_task_id')->constrained('job_card_tasks')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamp('allocated_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            
            $table->softDeletes();
            $table->timestamps();

            // Explicitly index foreign keys and status
            $table->index('job_card_task_id');
            $table->index('employee_id');
            $table->index('status');
        });

        // Add partial unique index to prevent duplicate ACTIVE task assignments for the same employee
        if (DB::getDriverName() === 'sqlite' || DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX job_task_assignments_active_unique ON job_task_assignments (job_card_task_id, employee_id) WHERE status = 'active' AND deleted_at IS NULL;");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS job_task_assignments_active_unique;');
        }
        Schema::dropIfExists('job_task_assignments');
    }
};
