<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Adjust if your User model is in a different namespace
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'          => 'goku',
            'username'      => 'son goku',
            'email'         => 'goku@example.com',
            'password'      => Hash::make('songoku123'),
            'department'    => 'admin',
            'profile_photo_path' => null // If you have a field for admin status; otherwise, use roles
        ]);
    }
}