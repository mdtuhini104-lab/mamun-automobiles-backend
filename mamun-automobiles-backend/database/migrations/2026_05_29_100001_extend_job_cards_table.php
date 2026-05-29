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
        Schema::table('job_cards', function (Blueprint $table) {
            $table->string('fuel_level')->nullable()->after('notes');
            $table->integer('odometer_reading')->nullable()->after('fuel_level');
            $table->string('emergency_level')->default('medium')->after('odometer_reading');
            $table->dateTime('expected_delivery_date')->nullable()->after('delivery_date');
            
            $table->text('inspection_notes')->nullable()->after('diagnosis');
            $table->string('priority_level')->default('normal')->after('emergency_level');
            $table->text('safety_warnings')->nullable()->after('inspection_notes');
            
            $table->string('voice_notes_path')->nullable()->after('notes');
            $table->text('images_paths')->nullable()->after('voice_notes_path'); // text for sqlite json compat
            $table->text('documents_paths')->nullable()->after('images_paths'); // text for sqlite json compat
            
            // AI hooks
            $table->decimal('ai_priority_score', 5, 2)->nullable()->after('documents_paths');
            $table->decimal('ai_failure_probability', 5, 2)->nullable()->after('ai_priority_score');

            // Indexes
            $table->index('emergency_level');
            $table->index('priority_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropIndex(['emergency_level']);
            $table->dropIndex(['priority_level']);
            
            $table->dropColumn([
                'fuel_level',
                'odometer_reading',
                'emergency_level',
                'expected_delivery_date',
                'inspection_notes',
                'priority_level',
                'safety_warnings',
                'voice_notes_path',
                'images_paths',
                'documents_paths',
                'ai_priority_score',
                'ai_failure_probability'
            ]);
        });
    }
};
