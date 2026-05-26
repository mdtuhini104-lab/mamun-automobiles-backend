<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'type')) {
                $table->string('type')->default('part')->after('name'); // part, expense, income
            }
        });
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'payment_method')) {
                $table->string('payment_method')->default('cash')->after('reference_id'); // cash, bank_transfer, check
            }
        });
    }
    public function down(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'payment_method']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
