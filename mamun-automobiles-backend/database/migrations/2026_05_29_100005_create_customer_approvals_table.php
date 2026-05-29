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
        Schema::dropIfExists('customer_approvals');
        Schema::create('customer_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('status'); // approved, rejected, partially_approved, changes_requested
            $table->string('approved_by'); // authorized person
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // internal recorder
            $table->string('signature_path')->nullable();
            $table->text('notes')->nullable();
            $table->text('approved_items')->nullable(); // json text
            $table->text('rejected_items')->nullable(); // json text
            $table->timestamps();

            // Indexes
            $table->index('quotation_id');
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_approvals');
        
        // Recreate the stub for absolute rollback integrity
        Schema::create('customer_approvals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
