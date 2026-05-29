<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create workflow_states table
        Schema::create('workflow_states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // pending_inspection, inspected, quotation_draft, work_order_active, etc.
            $table->string('label'); // Pending Inspection, Inspected, etc.
            $table->string('entity_type')->default('job_card');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Create workflow_transitions table
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_state_id')->constrained('workflow_states')->cascadeOnDelete();
            $table->foreignId('to_state_id')->constrained('workflow_states')->cascadeOnDelete();
            $table->string('role_required')->nullable(); // Spatie role prefix required for transition
            $table->string('trigger_event')->nullable(); // Event name triggered upon transition
            $table->timestamps();

            $table->index('from_state_id');
            $table->index('to_state_id');
        });

        // 3. Create workflow_histories table
        Schema::create('workflow_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->string('from_state')->nullable();
            $table->string('to_state');
            
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('job_card_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_states');
    }
};
