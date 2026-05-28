<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_bays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->index();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->integer('max_vehicle_capacity')->default(1);
            $table->integer('current_load')->default(0);
            $table->string('status')->default('active'); // active, inactive, maintenance
            $table->timestamps();

            // Explicitly index foreign keys and status
            $table->index('branch_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_bays');
    }
};
