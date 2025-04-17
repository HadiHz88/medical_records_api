<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Record;
use App\Models\Template;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Value;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = [
            'name' => 'admin1234',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('pass1234'),
            'role' => 'admin'
        ];

        User::create($admin);

        $user = [
            'name' => 'user1234',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('pass1234'),
            'role' => 'user'
        ];

        User::create($user);
    }
}