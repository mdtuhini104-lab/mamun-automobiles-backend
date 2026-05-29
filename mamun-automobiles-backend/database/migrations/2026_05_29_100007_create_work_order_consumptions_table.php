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
        Schema::create('work_order_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->string('item_type'); // product, service
            
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
            $table->string('service_name')->nullable();
            
            $table->decimal('quantity', 8, 2)->default(1.00);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->string('source_type')->default('workshop_supplied'); // workshop_supplied, customer_supplied
            
            $table->foreignId('consumed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            
            $table->boolean('is_approved')->default(true); // can optionally require manual approval
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();

            // Indexes
            $table->index('work_order_id');
            $table->index('part_id');
            $table->index('item_type');
            $table->index('consumed_by_id');
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_consumptions');
    }
};
