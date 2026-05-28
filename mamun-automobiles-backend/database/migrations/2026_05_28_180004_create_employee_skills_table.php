<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('proficiency_level')->default('junior'); // junior, mid, senior, expert
            
            $table->primary(['employee_id', 'skill_id']);
            $table->index('employee_id');
            $table->index('skill_id');
            $table->index(['skill_id', 'proficiency_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
    }
};
