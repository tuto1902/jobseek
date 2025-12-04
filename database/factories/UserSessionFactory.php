<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSession>
 */
class UserSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-30 days', 'now');
        $endedAt = $this->faker->optional(0.8)->dateTimeBetween($startedAt, '+2 hours');
        $duration = $endedAt ? $endedAt->getTimestamp() - $startedAt->getTimestamp() : null;

        $deviceTypes = ['mobile', 'desktop', 'tablet'];

        return [
            'user_id' => User::factory(),
            'session_id' => $this->faker->uuid(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration' => $duration,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'device_type' => $this->faker->randomElement($deviceTypes),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => null,
            'duration' => null,
        ]);
    }
}
