<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('engine_number')->nullable()->after('vin');
            $table->string('color')->nullable()->after('engine_number');
            $table->string('fuel_type')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['engine_number', 'color', 'fuel_type']);
        });
    }
};
