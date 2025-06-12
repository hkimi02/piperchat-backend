<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3), // Generate a short name (e.g., "Project Management System")
            'description' => fake()->optional()->paragraph(), // Nullable description
            'organisation_id' => Organisation::factory(), // Create or reference an Organisation
        ];
    }
}
