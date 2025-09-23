<?php

namespace Database\Seeders;

use App\Models\JobPosting;
use App\Models\Publisher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobPostingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = Publisher::all();

        if ($publishers->isEmpty()) {
            $publishers = Publisher::factory()->count(5)->create();
        }

        $publishers->each(function (Publisher $publisher) {
            JobPosting::factory()
                ->count(rand(3, 8))
                ->for($publisher)
                ->create();

            JobPosting::factory()
                ->count(rand(1, 3))
                ->for($publisher)
                ->active()
                ->featured()
                ->create();

            JobPosting::factory()
                ->count(rand(2, 5))
                ->for($publisher)
                ->active()
                ->remote()
                ->withCoordinates()
                ->create();
        });
    }
}
