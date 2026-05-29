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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('item_type'); // product, service
            
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
            $table->string('service_name')->nullable();
            
            $table->decimal('quantity', 8, 2)->default(1.00);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('labor_cost', 10, 2)->default(0.00); // for service types
            $table->decimal('estimated_hours', 5, 2)->default(0.00); // for service types
            
            $table->string('source_type')->default('workshop_supplied'); // workshop_supplied, customer_supplied
            $table->string('status')->default('pending'); // pending, approved, rejected
            
            // AI hooks
            $table->decimal('ai_price_recommendation', 10, 2)->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Explicit indexes
            $table->index('quotation_id');
            $table->index('part_id');
            $table->index('item_type');
            $table->index('source_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
