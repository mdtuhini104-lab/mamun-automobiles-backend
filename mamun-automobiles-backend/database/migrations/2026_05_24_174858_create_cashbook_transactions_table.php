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
        Schema::create('cashbook_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashbook_id')->constrained('cashbooks')->onDelete('cascade');
            $table->string('reference_type')->nullable(); // e.g. Invoice, Expense, Transfer
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('transaction_type', ['income', 'expense', 'transfer', 'adjustment']);
            $table->string('category')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbook_transactions');
    }
};
