<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 10 random users
        User::factory(10)->create();

        // Create a specific user so we have a known email and password to log in with
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            // The password is automatically 'password' thanks to the UserFactory
        ]);

        // Now, let's create 20 dummy notes for our test user
        Note::factory(20)->create([
            'user_id' => $testUser->id
        ]);

        // And let's sprinkle 50 random notes across the other 10 random users
        Note::factory(50)->create();
    }
}
