<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SearchQuery>
 */
class SearchQueryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $searchTerms = [
            'software engineer',
            'frontend developer',
            'backend developer',
            'data scientist',
            'product manager',
            'ux designer',
            'marketing manager',
            'remote jobs',
            'entry level',
            'senior developer',
        ];

        $filters = $this->faker->optional(0.6)->passthrough([
            'location' => $this->faker->city(),
            'employment_type' => $this->faker->randomElement(['full-time', 'part-time', 'contract']),
            'remote' => $this->faker->boolean(),
        ]);

        return [
            'user_id' => User::factory(),
            'query' => $this->faker->randomElement($searchTerms),
            'filters' => $filters,
            'results_count' => $this->faker->numberBetween(0, 100),
            'session_id' => $this->faker->uuid(),
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
