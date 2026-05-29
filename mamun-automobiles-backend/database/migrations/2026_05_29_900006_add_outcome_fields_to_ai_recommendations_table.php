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
        Schema::table('ai_recommendations', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_recommendations', 'outcome')) {
                $table->string('outcome')->nullable()->after('status'); // succeeded, failed
            }
            if (!Schema::hasColumn('ai_recommendations', 'effectiveness_score')) {
                $table->decimal('effectiveness_score', 5, 2)->nullable()->after('outcome');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_recommendations', function (Blueprint $table) {
            $table->dropColumn(['outcome', 'effectiveness_score']);
        });
    }
};
