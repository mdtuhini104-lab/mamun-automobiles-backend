<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable();
            $table->string('tag')->default('Regular'); // VIP, Corporate, Regular
            $table->integer('loyalty_points')->default(0);
            $table->text('notes')->nullable();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('mechanic_id')->nullable();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('service_type');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->foreign('mechanic_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // e.g., 'invoice_created', 'service_completed', 'appointment_booked'
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_activities');
        Schema::dropIfExists('appointments');
        
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['password', 'tag', 'loyalty_points', 'notes']);
        });
    }
};

