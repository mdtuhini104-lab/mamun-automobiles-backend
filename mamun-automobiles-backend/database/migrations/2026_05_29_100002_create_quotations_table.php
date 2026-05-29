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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->string('quotation_number')->unique();
            $table->integer('version')->default(1);
            $table->string('status')->default('draft'); // draft, sent, revised, approved, partially_approved, rejected, expired
            
            $table->decimal('total_product_cost', 10, 2)->default(0.00);
            $table->decimal('total_labor_cost', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2)->default(0.00);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Explicit indexes
            $table->index('job_card_id');
            $table->index('status');
            $table->index('quotation_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
