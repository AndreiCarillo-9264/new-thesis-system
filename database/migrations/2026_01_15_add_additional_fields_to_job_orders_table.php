<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('jo_number');
            $table->decimal('unit_price', 10, 2)->default(0.00)->after('ordered_quantity');
            $table->date('due_date')->nullable()->after('jo_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->after('status');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium')->after('user_id');
            $table->text('notes')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'unit_price', 'due_date', 'user_id', 'priority', 'notes']);
        });
    }
};