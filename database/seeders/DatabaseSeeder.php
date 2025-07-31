<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user first
        User::factory()->create([
            'name' => 'John Smith',
            'email' => 'test@example.com',
        ]);

        // Seed tasks for the test user
        $this->call([
            TaskSeeder::class,
        ]);
    }
}
