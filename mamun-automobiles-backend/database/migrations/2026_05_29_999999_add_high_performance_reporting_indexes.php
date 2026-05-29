<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add required columns to audit_logs if not present
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('audit_logs', 'module')) {
                $table->string('module')->nullable()->after('action');
            }
            if (!Schema::hasColumn('audit_logs', 'details')) {
                $table->text('details')->nullable()->after('changes');
            }
        });

        Schema::table('job_cards', function (Blueprint $table) {
            $table->index(['tenant_id', 'service_status', 'created_at'], 'jc_tenant_status_created_index');
            $table->index(['tenant_id', 'branch_id', 'created_at'], 'jc_tenant_branch_created_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['tenant_id', 'status', 'created_at'], 'inv_tenant_status_created_index');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['tenant_id', 'action', 'created_at'], 'al_tenant_action_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropIndex('jc_tenant_status_created_index');
            $table->dropIndex('jc_tenant_branch_created_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('inv_tenant_status_created_index');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('al_tenant_action_created_index');
            
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn(['tenant_id', 'module', 'details']);
            }
        });
    }
};
