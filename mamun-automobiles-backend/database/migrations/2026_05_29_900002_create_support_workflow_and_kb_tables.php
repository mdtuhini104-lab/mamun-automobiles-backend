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
        Schema::dropIfExists('resolution_workflows');
        Schema::dropIfExists('support_incidents');
        Schema::dropIfExists('knowledge_base_articles');

        // 1. Support Incidents Table
        Schema::create('support_incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('ticket_id');
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('investigating'); // investigating, identified, resolving, resolved
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('ticket_id')->references('id')->on('support_tickets')->cascadeOnDelete();
        });

        // 2. Resolution Workflows Table
        Schema::create('resolution_workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('incident_id');
            $table->json('steps')->nullable(); // array of resolution steps
            $table->text('solution');
            $table->unsignedBigInteger('kb_article_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('incident_id')->references('id')->on('support_incidents')->cascadeOnDelete();
        });

        // 3. Knowledge Base Articles Table (Dynamic database-backed KB)
        Schema::create('knowledge_base_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable(); // Null means global article, otherwise tenant-scoped
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('general'); // onboarding, technical, billing, general
            $table->text('content'); // Markdown support
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_articles');
        Schema::dropIfExists('resolution_workflows');
        Schema::dropIfExists('support_incidents');
    }
};
