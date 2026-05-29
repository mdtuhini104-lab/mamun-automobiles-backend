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
        Schema::create('removed_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('item_name');
            $table->foreignId('removed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('removal_reason');
            $table->decimal('previous_quantity', 8, 2);
            $table->decimal('previous_price', 10, 2);
            $table->timestamp('removed_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index('quotation_id');
            $table->index('removed_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('removed_quotation_items');
    }
};
