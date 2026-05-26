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
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreignId('cashbook_id')->constrained('cashbooks')->onDelete('cascade');
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('total_income', 15, 2)->default(0.00);
            $table->decimal('total_expense', 15, 2)->default(0.00);
            $table->decimal('total_due_collected', 15, 2)->default(0.00);
            $table->decimal('total_invoice_sales', 15, 2)->default(0.00);
            $table->decimal('manual_adjustment', 15, 2)->default(0.00);
            $table->decimal('closing_balance', 15, 2)->default(0.00);
            $table->decimal('system_balance', 15, 2)->default(0.00);
            $table->decimal('difference_amount', 15, 2)->default(0.00);
            $table->text('closing_notes')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
