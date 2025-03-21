<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Record;
use App\Models\Template;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Value;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Template::factory()->count(5)->create();

        Record::factory()->count(10)->create();

        Field::factory()->count(20)->create();

        Value::factory()->count(50)->create();
    }
}
