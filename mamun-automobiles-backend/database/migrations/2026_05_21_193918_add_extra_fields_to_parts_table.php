<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('parts', function (Blueprint $table) {
            if (!Schema::hasColumn('parts', 'barcode')) { $table->string('barcode')->nullable()->after('sku'); }
            if (!Schema::hasColumn('parts', 'category_id')) { $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete()->after('brand'); }
            if (!Schema::hasColumn('parts', 'rack_location')) { $table->string('rack_location')->nullable()->after('low_stock_threshold'); }
            if (!Schema::hasColumn('parts', 'unit_type')) { $table->string('unit_type')->default('pcs')->after('rack_location'); }
            if (!Schema::hasColumn('parts', 'deleted_at')) { $table->softDeletes(); }
        });
    }
    public function down(): void {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['barcode', 'category_id', 'rack_location', 'unit_type', 'deleted_at']);
        });
    }
};
