<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safely drop empty placeholders or duplicate SQLite records to avoid conflict
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('ai_anomaly_logs');
        Schema::dropIfExists('kpi_monthly_aggregates');

        // Centralized AI Recommendations Inbox Queue Table
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('recommendation_type'); // price_markup, technician_allocation, inventory_reorder, workload_balancing, customer_risk
            $table->unsignedBigInteger('source_id'); // maps to quotation_id, job_card_id, part_id etc.
            $table->json('suggestion_data'); // holds old vs suggested parameters
            $table->decimal('confidence_score', 5, 2)->default(100.00); // 0-100% score
            $table->text('explanation'); // plain-English AI decision justification
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('actioned_by_id')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();

            $table->index('recommendation_type');
            $table->index('status');
        });

        // Suspicious Activity & Financial Discount Override Table
        Schema::create('ai_anomaly_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // quotation, work_order, inventory, customer
            $table->unsignedBigInteger('entity_id');
            $table->string('severity')->default('low'); // low, medium, critical
            $table->string('rule_name'); // item_discount_limit, grand_discount_limit, excessive_labor, suspicious_stock
            $table->text('description');
            $table->boolean('is_resolved')->default(false);
            $table->unsignedBigInteger('resolved_by_id')->nullable();
            $table->text('override_notes')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('is_resolved');
        });

        // Monthly Executives Reporting KPI aggregates table
        Schema::create('kpi_monthly_aggregates', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('revenue', 12, 2)->default(0.00);
            $table->decimal('quotation_conversion_ratio', 5, 2)->default(0.00);
            $table->decimal('bay_utilization_ratio', 5, 2)->default(0.00);
            $table->decimal('technician_efficiency_score', 5, 2)->default(0.00);
            $table->decimal('inventory_turnover', 5, 2)->default(0.00);
            $table->decimal('comeback_rate', 5, 2)->default(0.00);
            $table->decimal('customer_retention_rate', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('ai_anomaly_logs');
        Schema::dropIfExists('kpi_monthly_aggregates');
    }
};
