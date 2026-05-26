<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('domain')->unique();
            $table->string('status')->default('trial');
            $table->date('subscription_ends_at')->nullable();
            $table->string('subscription_plan')->default('basic');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        $tables = ['users', 'customers', 'vehicles', 'job_cards', 'invoices', 'parts', 'transactions', 'appointments'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                    $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['users', 'customers', 'vehicles', 'job_cards', 'invoices', 'parts', 'transactions', 'appointments'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
        
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::dropIfExists('tenants');
    }
};

