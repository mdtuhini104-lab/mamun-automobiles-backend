<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('purchase_no')->unique();
            $table->date('purchase_date');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('due_amount', 10, 2)->default(0.00);
            $table->enum('payment_status', ['paid', 'partial', 'due'])->default('due');
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->string('invoice_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('purchase_no');
            $table->index('purchase_date');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
