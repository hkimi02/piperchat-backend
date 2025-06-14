<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chatroom>
 */
class ChatroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => 'organisation', // Default to 'organisation' as per migration
            'organisation_id' => Organisation::factory(), // Create or reference an Organisation
            'project_id' => null, // Nullable, can be overridden for project chatrooms
        ];
    }
}
