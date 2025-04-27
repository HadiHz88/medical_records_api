<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Record;
use App\Models\Template;
use App\Models\User;
use App\Models\Admin;
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
        $users = [
          ['name' => 'John Doe', 'email' => 'john@example.com', 'password' => 'pass1234'],
          ['name' => 'Alice Dean', 'email' => 'alice@example.com', 'password' => 'pass1234'],
          ['name' => 'Hadi', 'email' => 'hadi@example.com', 'password' => 'pass1234'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
            ]);
        }

        // Create a fake admin
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin1234'),
        ]);

        Admin::create([
            'user_id' => $adminUser->id,
        ]);
    }
}

