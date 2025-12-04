<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConversionEvent>
 */
class ConversionEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = ['registration', 'application', 'click', 'save_job', 'profile_update'];
        $eventType = $this->faker->randomElement($eventTypes);

        $eventData = match ($eventType) {
            'registration' => ['source' => $this->faker->randomElement(['organic', 'referral', 'social'])],
            'application' => ['job_id' => $this->faker->numberBetween(1, 100)],
            'click' => ['target' => $this->faker->url()],
            'save_job' => ['job_id' => $this->faker->numberBetween(1, 100)],
            'profile_update' => ['fields_updated' => $this->faker->randomElements(['name', 'email', 'location'], 2)],
            default => [],
        };

        return [
            'user_id' => User::factory(),
            'event_type' => $eventType,
            'event_data' => $eventData,
            'session_id' => $this->faker->uuid(),
        ];
    }

    public function registration(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'registration',
            'event_data' => ['source' => $this->faker->randomElement(['organic', 'referral', 'social'])],
        ]);
    }
}
