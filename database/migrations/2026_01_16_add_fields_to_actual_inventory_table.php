<?php
// New Migration: database/migrations/2026_01_16_add_fields_to_actual_inventory_table.php (adjust timestamp)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actual_inventory', function (Blueprint $table) {
            $table->integer('min_stock')->default(0)->after('actual_quantity');
            $table->integer('max_stock')->default(0)->after('min_stock');
            $table->string('location')->nullable()->after('max_stock');
            $table->string('supplier')->nullable()->after('location');
            $table->decimal('unit_cost', 10, 2)->default(0.00)->after('supplier');
        });
    }

    public function down(): void
    {
        Schema::table('actual_inventory', function (Blueprint $table) {
            $table->dropColumn(['min_stock', 'max_stock', 'location', 'supplier', 'unit_cost']);
        });
    }
};