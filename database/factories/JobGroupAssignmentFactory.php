<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobGroupAssignment>
 */
class JobGroupAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_posting_id' => \App\Models\JobPosting::factory(),
            'job_group_id' => \App\Models\JobGroup::factory(),
            'weight_percentage' => fake()->randomFloat(2, 0.01, 100),
        ];
    }
}
