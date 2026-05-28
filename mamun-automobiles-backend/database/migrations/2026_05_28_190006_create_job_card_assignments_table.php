<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_card_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('assignment_type'); // lead_technician, assistant_technician, helper
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->decimal('labor_hours', 8, 2)->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled, reassigned
            $table->foreignId('reassigned_to_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();

            // Explicitly index fields
            $table->index('job_card_id');
            $table->index('employee_id');
            $table->index('assignment_type');
            $table->index('status');
            $table->index('reassigned_to_id');
        });

        // Add a partial unique index to prevent duplicate ACTIVE assignments for the same employee on the same job card
        DB::statement("CREATE UNIQUE INDEX job_card_assignments_active_unique ON job_card_assignments (job_card_id, employee_id) WHERE status = 'active' AND deleted_at IS NULL;");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS job_card_assignments_active_unique;');
        }
        Schema::dropIfExists('job_card_assignments');
    }
};
