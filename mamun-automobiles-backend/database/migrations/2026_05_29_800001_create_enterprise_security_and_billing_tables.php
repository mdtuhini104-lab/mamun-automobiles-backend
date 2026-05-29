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
        Schema::dropIfExists('fleet_contracts');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('security_incidents');
        Schema::dropIfExists('user_mfa_secrets');

        // 1. MFA Secrets Table
        Schema::create('user_mfa_secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('secret_key');
            $table->text('backup_codes'); // stored encrypted
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('grace_started_at')->nullable();
            $table->timestamps();
        });

        // 2. Security Incidents Table
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('incident_type'); // brute_force, sql_injection, mfa_bypass, ip_anomaly
            $table->enum('severity', ['low', 'medium', 'critical'])->default('low');
            $table->json('details')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 3. SaaS Subscriptions
        Schema::dropIfExists('tenant_subscriptions');
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('plan_name');
            $table->string('payment_gateway');
            $table->string('gateway_subscription_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('grace_period_until')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 4. Fleet Contracts
        Schema::create('fleet_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('company_name');
            $table->string('contact_email');
            $table->text('billing_terms')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_contracts');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('security_incidents');
        Schema::dropIfExists('user_mfa_secrets');
    }
};
