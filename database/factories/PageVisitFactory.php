<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageVisit>
 */
class PageVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pages = ['/', '/jobs', '/search', '/about', '/contact'];
        $referrers = [
            'https://google.com',
            'https://linkedin.com',
            'https://facebook.com',
            'https://twitter.com',
            null,
        ];

        return [
            'session_id' => $this->faker->uuid(),
            'page_url' => $this->faker->randomElement($pages),
            'referrer' => $this->faker->randomElement($referrers),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'visited_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
