<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->index()->after('name');
            $table->foreignId('head_user_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('head_user_id')->constrained('branches')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('branch_id');
            
            // Explicitly index foreign keys
            $table->index('head_user_id');
            $table->index('branch_id');
        });

        Schema::table('designations', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->index()->after('name');
            $table->foreignId('parent_designation_id')->nullable()->after('department_id')->constrained('designations')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('parent_designation_id');
            
            // Explicitly index foreign keys
            $table->index('parent_designation_id');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Drop and recreate tables to their original state to bypass SQLite alter limitations
            Schema::dropIfExists('designations');
            Schema::dropIfExists('departments');

            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });

            Schema::create('designations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('designations', function (Blueprint $table) {
                $table->dropForeign(['parent_designation_id']);
                $table->dropUnique(['code']);
            });

            Schema::table('designations', function (Blueprint $table) {
                $table->dropColumn(['code', 'parent_designation_id', 'is_active']);
            });

            Schema::table('departments', function (Blueprint $table) {
                $table->dropForeign(['head_user_id']);
                $table->dropForeign(['branch_id']);
                $table->dropUnique(['code']);
            });

            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn(['code', 'head_user_id', 'branch_id', 'is_active']);
            });
        }
    }
};
