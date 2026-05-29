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
        if (!Schema::hasTable('system_health_alerts')) {
            Schema::create('system_health_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('alert_type'); // queue_lag, disk_space, database_size, slow_query
                $table->string('severity'); // info, warning, critical
                $table->text('message');
                $table->json('metrics')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index('alert_type');
                $table->index('severity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_health_alerts');
    }
};
