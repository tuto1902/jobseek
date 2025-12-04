<?php

namespace Database\Seeders;

use App\Models\ConversionEvent;
use App\Models\JobClick;
use App\Models\JobPosting;
use App\Models\PageVisit;
use App\Models\Publisher;
use App\Models\SearchQuery;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Seeder;

class AnalyticsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating publishers and job postings...');
        $publishers = Publisher::factory(5)->create();
        $jobs = JobPosting::factory(50)->create();

        $this->command->info('Creating users...');
        $users = User::factory(30)->create();

        $this->command->info('Creating user sessions...');
        foreach ($users as $user) {
            UserSession::factory(rand(1, 5))->create(['user_id' => $user->id]);
        }

        $this->command->info('Creating job clicks...');
        foreach ($jobs->random(30) as $job) {
            JobClick::factory(rand(1, 10))->create(['job_posting_id' => $job->id]);
        }

        $this->command->info('Creating search queries...');
        SearchQuery::factory(100)->create();

        $this->command->info('Creating page visits...');
        PageVisit::factory(200)->create();

        $this->command->info('Creating conversion events...');
        foreach ($users->random(20) as $user) {
            ConversionEvent::factory()->registration()->create(['user_id' => $user->id]);
        }

        $this->command->info('Analytics demo data created successfully!');
    }
}
