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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('work_order_number')->unique();
            $table->string('status')->default('pending'); // pending, in_progress, paused, completed, cancelled
            
            $table->text('department_allocations')->nullable(); // json text of departments
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            
            // AI hooks
            $table->decimal('ai_estimated_completion_hours', 5, 2)->nullable();
            $table->decimal('ai_efficiency_score', 5, 2)->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('job_card_id');
            $table->index('quotation_id');
            $table->index('status');
            $table->index('work_order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
