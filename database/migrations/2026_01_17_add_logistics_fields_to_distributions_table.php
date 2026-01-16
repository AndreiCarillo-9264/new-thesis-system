<?php
// New Migration: database/migrations/2026_01_17_add_logistics_fields_to_distributions_table.php (adjust timestamp)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('destination');
            $table->string('driver')->nullable()->after('customer_name');
            $table->string('vehicle')->nullable()->after('driver');
            $table->enum('status', ['pending', 'in_transit', 'delivered'])->default('pending')->after('vehicle');
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'driver', 'vehicle', 'status', 'notes']);
        });
    }
};