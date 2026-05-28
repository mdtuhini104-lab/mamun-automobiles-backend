<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            
            $table->string('employee_code')->unique()->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('nid')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('joining_date')->nullable();
            
            $table->string('status')->default('active'); // active, inactive, suspended, resigned, terminated, on_leave
            $table->string('availability_status')->default('available'); // available, busy, assigned, on_leave, offline
            
            $table->softDeletes();
            $table->timestamps();
            
            // Explicitly index all foreign keys and composite queries for scalability
            $table->index('user_id');
            $table->index('department_id');
            $table->index('designation_id');
            $table->index('branch_id');
            $table->index(['department_id', 'designation_id', 'status'], 'idx_emp_dept_desg_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
