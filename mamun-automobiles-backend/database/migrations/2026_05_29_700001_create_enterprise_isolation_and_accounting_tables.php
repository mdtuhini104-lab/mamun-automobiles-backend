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
        // 1. Add tenant_id to tables that don't have it yet
        $tablesWithTenant = [
            'suppliers', 'purchases', 'stock_adjustments', 'workshop_bays',
            'employees', 'departments', 'designations', 'shifts',
            'quotations', 'work_orders', 'work_order_consumptions', 'customer_pricings'
        ];

        foreach ($tablesWithTenant as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'tenant_id')) {
                        $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                        $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                    }
                });
            }
        }

        // 2. Add branch_id to tables that don't have it yet
        $tablesWithBranch = [
            'customers', 'vehicles', 'job_cards', 'parts', 'appointments',
            'quotations', 'work_orders', 'work_order_consumptions', 'customer_pricings', 'suppliers'
        ];

        foreach ($tablesWithBranch as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'branch_id')) {
                        $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
                        $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                    }
                });
            }
        }

        // 3. Create double-entry accounting tables
        Schema::create('accounts_chart', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('entry_date');
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('journal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts_chart')->cascadeOnDelete();
            $table->decimal('debit', 12, 2)->default(0.00);
            $table->decimal('credit', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 4. Create communication retry queue table
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('recipient_phone');
            $table->text('message_body');
            $table->enum('channel', ['whatsapp', 'sms', 'email']);
            $table->enum('status', ['queued', 'dispatched', 'delivered', 'failed'])->default('queued');
            $table->integer('retry_count')->default(0);
            $table->text('error_log')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('journal_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts_chart');

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            // Drop branch_id columns
            $tablesWithBranch = [
                'customers', 'vehicles', 'job_cards', 'parts', 'appointments',
                'quotations', 'work_orders', 'work_order_consumptions', 'customer_pricings', 'suppliers'
            ];
            foreach ($tablesWithBranch as $tableName) {
                if (Schema::hasTable($tableName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        if (Schema::hasColumn($tableName, 'branch_id')) {
                            $table->dropForeign([$tableName . '_branch_id_foreign']);
                            $table->dropColumn('branch_id');
                        }
                    });
                }
            }

            // Drop tenant_id columns
            $tablesWithTenant = [
                'suppliers', 'purchases', 'stock_adjustments', 'workshop_bays',
                'employees', 'departments', 'designations', 'shifts',
                'quotations', 'work_orders', 'work_order_consumptions', 'customer_pricings'
            ];
            foreach ($tablesWithTenant as $tableName) {
                if (Schema::hasTable($tableName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        if (Schema::hasColumn($tableName, 'tenant_id')) {
                            $table->dropForeign([$tableName . '_tenant_id_foreign']);
                            $table->dropColumn('tenant_id');
                        }
                    });
                }
            }
        }
    }
};
