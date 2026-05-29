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
        Schema::table('technician_metrics', function (Blueprint $table) {
            if (!Schema::hasColumn('technician_metrics', 'comebacks_count')) {
                $table->integer('comebacks_count')->default(0)->after('efficiency_percentage');
            }
            if (!Schema::hasColumn('technician_metrics', 'comeback_ratio')) {
                $table->decimal('comeback_ratio', 5, 2)->default(0.00)->after('comebacks_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_metrics', function (Blueprint $table) {
            $table->dropColumn(['comebacks_count', 'comeback_ratio']);
        });
    }
};
