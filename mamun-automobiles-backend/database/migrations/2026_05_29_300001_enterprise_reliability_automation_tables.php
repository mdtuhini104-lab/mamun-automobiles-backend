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
        // 1. Create supplier_ledgers table
        Schema::create('supplier_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('transaction_type'); // purchase, payment
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('transaction_type');
        });

        // 2. Create warranties table
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->date('warranty_expiry_date');
            $table->string('status')->default('active'); // active, claimed, expired
            $table->timestamps();

            $table->index('job_card_id');
            $table->index('invoice_id');
            $table->index('status');
        });

        // 3. Create comeback_jobs table
        Schema::create('comeback_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->foreignId('comeback_job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignId('technician_at_fault_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('original_job_card_id');
            $table->index('comeback_job_card_id');
            $table->index('technician_at_fault_id');
        });

        // 4. Create standard Laravel notifications table if it doesn't exist
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('comeback_jobs');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('supplier_ledgers');
    }
};
