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
        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_mechanic_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('complaint');
            $table->text('diagnosis')->nullable();
            $table->enum('service_status', ['pending', 'in_progress', 'completed', 'delivered'])->default('pending');
            $table->decimal('estimated_cost', 10, 2)->default(0.00);
            $table->decimal('final_cost', 10, 2)->default(0.00);
            $table->date('service_date');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->text('notes')->nullable();
            
            $table->index('service_status');
            $table->index('service_date');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_cards');
    }
};
