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
        Schema::table('parts', function (Blueprint $table) {
            $table->index('part_code', 'parts_part_code_index');
            $table->index('name', 'parts_name_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone', 'customers_phone_index');
            $table->index('name', 'customers_name_index');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('plate_number', 'vehicles_plate_number_index');
        });

        Schema::table('job_cards', function (Blueprint $table) {
            $table->index('customer_id', 'job_cards_customer_id_index');
            $table->index('vehicle_id', 'job_cards_vehicle_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropIndex('parts_part_code_index');
            $table->dropIndex('parts_name_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_phone_index');
            $table->dropIndex('customers_name_index');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_plate_number_index');
        });

        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropIndex('job_cards_customer_id_index');
            $table->dropIndex('job_cards_vehicle_id_index');
        });
    }
};
