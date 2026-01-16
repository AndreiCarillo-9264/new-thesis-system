<?php
// New Migration: database/migrations/2026_01_16_add_assigned_team_to_job_orders_table.php (adjust timestamp as needed)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->string('assigned_team')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn('assigned_team');
        });
    }
};