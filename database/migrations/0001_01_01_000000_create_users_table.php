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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();           // Added as per your requirements
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('department', [
                'admin',
                'sales',
                'production',
                'inventory',
                'logistics'
            ]);
            $table->string('profile_photo_path')->nullable(); // For navbar profile picture
            $table->rememberToken();                           // Keep this → allows "Remember Me" on login
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};