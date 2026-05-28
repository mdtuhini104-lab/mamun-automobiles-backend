<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_card_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('estimated_minutes')->default(0);
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->timestamps();

            // Explicitly index foreign keys and status
            $table->index('job_card_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_card_tasks');
    }
};
