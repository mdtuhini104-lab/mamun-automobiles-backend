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
        // 1. Extend parts table with reserved stock
        Schema::table('parts', function (Blueprint $table) {
            if (!Schema::hasColumn('parts', 'reserved_quantity')) {
                $table->decimal('reserved_quantity', 8, 2)->default(0.00)->after('stock_quantity');
            }
        });

        // 2. Extend quotation_items table with immutable snapshot columns
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('part_name_snapshot')->nullable()->after('part_id');
            $table->string('sku_snapshot')->nullable()->after('part_name_snapshot');
            $table->string('customer_name_snapshot')->nullable()->after('sku_snapshot');
            $table->decimal('price_snapshot', 10, 2)->nullable()->after('unit_price');
            $table->decimal('tax_snapshot', 10, 2)->default(0.00)->after('tax');
            $table->decimal('labor_snapshot', 10, 2)->default(0.00)->after('labor_cost');
        });

        // 3. Extend customer_approvals table with multi-level approval columns
        Schema::table('customer_approvals', function (Blueprint $table) {
            $table->string('approval_stage')->default('customer')->after('status'); // supervisor, manager, finance, insurance, customer
            $table->integer('approval_order')->default(1)->after('approval_stage');
            $table->string('approval_type')->default('final')->after('approval_order'); // partial, final
        });

        // 4. Extend quotations and work_orders with AI-Ready Metrics Layer
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('ai_priority_score', 5, 2)->nullable();
            $table->decimal('ai_customer_acceptance_probability', 5, 2)->nullable();
            $table->decimal('ai_estimated_completion_hours', 5, 2)->nullable();
            $table->decimal('ai_technician_efficiency_score', 5, 2)->nullable();
            $table->decimal('ai_inventory_prediction_score', 5, 2)->nullable();
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->decimal('ai_priority_score', 5, 2)->nullable();
            $table->decimal('ai_customer_acceptance_probability', 5, 2)->nullable();
            $table->decimal('ai_technician_efficiency_score', 5, 2)->nullable();
            $table->decimal('ai_inventory_prediction_score', 5, 2)->nullable();
        });

        // 5. Extend work_order_consumptions with separation metrics
        Schema::table('work_order_consumptions', function (Blueprint $table) {
            $table->decimal('actual_consumed_quantity', 8, 2)->default(0.00)->after('quantity');
            $table->decimal('wasted_quantity', 8, 2)->default(0.00)->after('actual_consumed_quantity');
            $table->decimal('returned_quantity', 8, 2)->default(0.00)->after('wasted_quantity');
        });

        // 6. Create workflow_actions table
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transition_id')->constrained('workflow_transitions')->cascadeOnDelete();
            $table->string('action_type'); // send_notification, generate_invoice, reserve_stock, verify_qc
            $table->json('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Create quality_controls table
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->string('status'); // pending, passed, failed
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('checklist')->nullable(); // oil level, electricals, tyre pressure, brakes
            $table->boolean('road_test_performed')->default(false);
            $table->text('road_test_notes')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();

            $table->index('work_order_id');
            $table->index('status');
        });

        // 8. Create vehicle_deliveries table
        Schema::create('vehicle_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->string('delivered_to');
            $table->string('signature_path')->nullable();
            $table->json('delivery_photos')->nullable();
            $table->foreignId('delivered_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('job_card_id');
        });

        // 9. Create Reporting Warehouse Tables
        Schema::create('quotation_conversion_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('total_quotations')->default(0);
            $table->integer('total_approved')->default(0);
            $table->integer('total_rejected')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('technician_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->integer('completed_jobs')->default(0);
            $table->integer('total_estimated_minutes')->default(0);
            $table->integer('total_actual_minutes')->default(0);
            $table->decimal('efficiency_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['date', 'employee_id']);
        });

        Schema::create('inventory_turnover_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->decimal('opening_stock', 8, 2)->default(0.00);
            $table->decimal('closing_stock', 8, 2)->default(0.00);
            $table->decimal('consumed_stock', 8, 2)->default(0.00);
            $table->decimal('turnover_rate', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['date', 'part_id']);
        });

        Schema::create('bay_utilization_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('workshop_bay_id')->constrained('workshop_bays')->cascadeOnDelete();
            $table->integer('total_occupied_minutes')->default(0);
            $table->decimal('utilization_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['date', 'workshop_bay_id']);
        });

        Schema::create('customer_lifetime_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->integer('total_invoices')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->decimal('avg_invoice_value', 10, 2)->default(0.00);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_lifetime_reports');
        Schema::dropIfExists('bay_utilization_reports');
        Schema::dropIfExists('inventory_turnover_reports');
        Schema::dropIfExists('technician_metrics');
        Schema::dropIfExists('quotation_conversion_reports');
        Schema::dropIfExists('vehicle_deliveries');
        Schema::dropIfExists('quality_controls');
        Schema::dropIfExists('workflow_actions');

        Schema::table('work_order_consumptions', function (Blueprint $table) {
            $table->dropColumn(['actual_consumed_quantity', 'wasted_quantity', 'returned_quantity']);
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['ai_priority_score', 'ai_customer_acceptance_probability', 'ai_technician_efficiency_score', 'ai_inventory_prediction_score']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['ai_priority_score', 'ai_customer_acceptance_probability', 'ai_estimated_completion_hours', 'ai_technician_efficiency_score', 'ai_inventory_prediction_score']);
        });

        Schema::table('customer_approvals', function (Blueprint $table) {
            $table->dropColumn(['approval_stage', 'approval_order', 'approval_type']);
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['part_name_snapshot', 'sku_snapshot', 'customer_name_snapshot', 'price_snapshot', 'tax_snapshot', 'labor_snapshot']);
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn(['reserved_quantity']);
        });
    }
};
