<?php

namespace Database\Factories;

use App\EmploymentType;
use App\JobStatus;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPosting>
 */
class JobPostingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jobTitles = [
            'Software Engineer',
            'Frontend Developer',
            'Backend Developer',
            'Full Stack Developer',
            'DevOps Engineer',
            'Data Scientist',
            'Product Manager',
            'UX Designer',
            'Marketing Manager',
            'Sales Representative',
        ];

        $categories = [
            'Technology',
            'Marketing',
            'Sales',
            'Design',
            'Engineering',
            'Data Science',
            'Product Management',
            'Customer Support',
            'Human Resources',
            'Finance',
        ];

        $coordinates = $this->faker->boolean(70) ? [
            'latitude' => $this->faker->latitude(-85, 85),
            'longitude' => $this->faker->longitude(-180, 180),
        ] : [
            'latitude' => null,
            'longitude' => null,
        ];

        return [
            'publisher_id' => Publisher::factory(),
            'title' => $this->faker->randomElement($jobTitles),
            'description' => $this->faker->paragraphs(3, true),
            'location' => $this->faker->city().', '.$this->faker->stateAbbr().', '.$this->faker->countryCode(),
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'employment_type' => $this->faker->randomElement(EmploymentType::cases()),
            'application_url' => $this->faker->url(),
            'expiration_date' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'category' => $this->faker->randomElement($categories),
            'remote_work_option' => $this->faker->boolean(30),
            'status' => $this->faker->randomElement(JobStatus::cases()),
            'featured' => $this->faker->boolean(10),
            'rpa' => $this->faker->randomFloat(2, 5, 100),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobStatus::Active,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    public function remote(): static
    {
        return $this->state(fn (array $attributes) => [
            'remote_work_option' => true,
        ]);
    }

    public function withCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(-85, 85),
            'longitude' => $this->faker->longitude(-180, 180),
        ]);
    }
}
