<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to attendances
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('overtime_minutes')->default(0)->after('late_minutes');
            $table->decimal('work_hours', 5, 2)->default(0)->after('overtime_minutes');
            $table->string('device_info')->nullable()->after('work_hours');
            $table->string('ip_address')->nullable()->after('device_info');
            $table->boolean('is_manual_entry')->default(false)->after('ip_address');
        });

        // Add columns to payrolls
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('payroll_cycle')->nullable()->after('year'); // e.g., 'monthly', 'weekly'
            $table->decimal('late_deduction', 10, 2)->default(0)->after('deductions');
            $table->decimal('absent_deduction', 10, 2)->default(0)->after('late_deduction');
            $table->decimal('advances', 10, 2)->default(0)->after('absent_deduction');
            $table->decimal('tax', 10, 2)->default(0)->after('advances');
        });

        // Employee Schedules
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('effective_date');
            $table->timestamps();
        });

        // Holidays
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('type', ['public', 'company', 'other'])->default('public');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Salary Structures
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('house_rent', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('transport_allowance', 10, 2)->default(0);
            $table->decimal('other_allowance', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->timestamps();
        });

        // Salary Advances
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->boolean('is_deducted')->default(false);
            $table->foreignId('payroll_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('employee_schedules');

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['payroll_cycle', 'late_deduction', 'absent_deduction', 'advances', 'tax']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['overtime_minutes', 'work_hours', 'device_info', 'ip_address', 'is_manual_entry']);
        });
    }
};
