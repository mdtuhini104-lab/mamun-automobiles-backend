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
        Schema::create('customer_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('part_id')->nullable()->constrained('parts')->cascadeOnDelete();
            $table->string('labor_service_name')->nullable();
            
            $table->decimal('custom_price', 10, 2)->nullable(); // customer-specific product rate
            $table->decimal('custom_labor_rate', 10, 2)->nullable(); // customer-specific service rate
            
            $table->date('effective_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('customer_id');
            $table->index('part_id');
            $table->index('labor_service_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_pricings');
    }
};
