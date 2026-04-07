<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // If no user is passed, create a new one to own this note
            'user_id' => User::factory(),
            'title' => fake()->sentence(4), // Generates a random title (e.g. "Dolores et alias ut.")
            'content' => fake()->paragraph(3), // Generates a random paragraph
            'notes' => fake()->optional(0.7)->sentence(3), // 70% chance to have a note, 30% chance to be null
        ];
    }
}
