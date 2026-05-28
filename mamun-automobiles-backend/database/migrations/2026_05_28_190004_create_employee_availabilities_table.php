<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('available'); // available, busy, assigned, on_leave, offline
            $table->boolean('is_available')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            // Explicitly index foreign keys, date, and status for fast lookups
            $table->index('employee_id');
            $table->index('date');
            $table->index('status');
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_availabilities');
    }
};
