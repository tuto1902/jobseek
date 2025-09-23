<?php

use App\EmploymentType;
use App\Filament\Resources\JobPostings\Pages\CreateJobPosting;
use App\Filament\Resources\JobPostings\Pages\EditJobPosting;
use App\Filament\Resources\JobPostings\Pages\ListJobPostings;
use App\JobStatus;
use App\Models\JobPosting;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('JobPosting List Page', function () {
    it('can render page', function () {
        Livewire::test(ListJobPostings::class)->assertOk();
    });

    it('can list job postings', function () {
        $jobPostings = JobPosting::factory()->count(5)->create();

        $component = Livewire::test(ListJobPostings::class)
            ->call('loadTable');

        foreach ($jobPostings as $jobPosting) {
            $component->assertSee($jobPosting->title);
        }
    });

    it('can search job postings by title', function () {
        $targetJobPosting = JobPosting::factory()->create(['title' => 'Unique Developer Role']);
        $otherJobPostings = JobPosting::factory()->count(3)->create(['title' => 'Other Job Title']);

        Livewire::test(ListJobPostings::class)
            ->searchTable('Unique Developer Role')
            ->assertCanSeeTableRecords([$targetJobPosting])
            ->assertCanNotSeeTableRecords($otherJobPostings);
    });

    it('can filter job postings by status', function () {
        $activeJobs = JobPosting::factory()->count(3)->create(['status' => JobStatus::Active]);
        $draftJobs = JobPosting::factory()->count(2)->create(['status' => JobStatus::Draft]);

        Livewire::test(ListJobPostings::class)
            ->filterTable('status', JobStatus::Active->value)
            ->assertCanSeeTableRecords($activeJobs)
            ->assertCanNotSeeTableRecords($draftJobs);
    });

    it('can filter job postings by employment type', function () {
        $fullTimeJobs = JobPosting::factory()->count(3)->create(['employment_type' => EmploymentType::FullTime]);
        $partTimeJobs = JobPosting::factory()->count(2)->create(['employment_type' => EmploymentType::PartTime]);

        Livewire::test(ListJobPostings::class)
            ->filterTable('employment_type', EmploymentType::FullTime->value)
            ->assertCanSeeTableRecords($fullTimeJobs)
            ->assertCanNotSeeTableRecords($partTimeJobs);
    });
});

describe('JobPosting Create Page', function () {
    it('can render page', function () {
        Livewire::test(CreateJobPosting::class)->assertOk();
    });

    it('can create job posting', function () {
        $publisher = Publisher::factory()->create();
        $newData = JobPosting::factory()->make(['publisher_id' => $publisher->id]);

        Livewire::test(CreateJobPosting::class)
            ->fillForm([
                'publisher_id' => $publisher->id,
                'title' => $newData->title,
                'description' => $newData->description,
                'location' => $newData->location,
                'employment_type' => $newData->employment_type->value,
                'application_url' => $newData->application_url,
                'expiration_date' => $newData->expiration_date->format('Y-m-d'),
                'status' => $newData->status->value,
            ])
            ->call('create')
            ->assertNotified();

        assertDatabaseHas('job_postings', [
            'publisher_id' => $publisher->id,
            'title' => $newData->title,
            'location' => $newData->location,
            'employment_type' => $newData->employment_type->value,
            'application_url' => $newData->application_url,
            'status' => $newData->status->value,
        ]);
    });

    it('validates required fields', function () {
        Livewire::test(CreateJobPosting::class)
            ->fillForm([
                'title' => '',
                'location' => '',
                'employment_type' => '',
                'application_url' => '',
                'expiration_date' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'publisher_id' => 'required',
                'title' => 'required',
                'location' => 'required',
                'employment_type' => 'required',
                'application_url' => 'required',
                'expiration_date' => 'required',
            ]);
    });

    it('validates URL format', function () {
        $publisher = Publisher::factory()->create();

        Livewire::test(CreateJobPosting::class)
            ->fillForm([
                'publisher_id' => $publisher->id,
                'title' => 'Test Job',
                'description' => 'Test description',
                'location' => 'Test Location',
                'employment_type' => EmploymentType::FullTime->value,
                'application_url' => 'invalid-url',
                'expiration_date' => now()->addMonth()->format('Y-m-d'),
            ])
            ->call('create')
            ->assertHasFormErrors(['application_url']);
    });

    it('can create job posting with optional coordinates', function () {
        $publisher = Publisher::factory()->create();

        Livewire::test(CreateJobPosting::class)
            ->fillForm([
                'publisher_id' => $publisher->id,
                'title' => 'Remote Software Engineer',
                'description' => 'Remote position available...',
                'location' => 'Remote',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'employment_type' => EmploymentType::FullTime->value,
                'application_url' => 'https://company.com/apply',
                'expiration_date' => now()->addMonth()->format('Y-m-d'),
                'remote_work_option' => true,
                'status' => JobStatus::Active->value,
            ])
            ->call('create')
            ->assertNotified();

        assertDatabaseHas('job_postings', [
            'title' => 'Remote Software Engineer',
            'latitude' => '40.71280000',
            'longitude' => '-74.00600000',
            'remote_work_option' => true,
        ]);
    });

    it('can create featured job posting', function () {
        $publisher = Publisher::factory()->create();

        Livewire::test(CreateJobPosting::class)
            ->fillForm([
                'publisher_id' => $publisher->id,
                'title' => 'Featured Position',
                'description' => 'This is a featured job...',
                'location' => 'San Francisco, CA, USA',
                'employment_type' => EmploymentType::FullTime->value,
                'application_url' => 'https://company.com/apply',
                'expiration_date' => now()->addMonth()->format('Y-m-d'),
                'featured' => true,
                'category' => 'Technology',
                'rpa' => 25.50,
            ])
            ->call('create')
            ->assertNotified();

        assertDatabaseHas('job_postings', [
            'title' => 'Featured Position',
            'featured' => true,
            'category' => 'Technology',
            'rpa' => '25.50',
        ]);
    });
});

describe('JobPosting Edit Page', function () {
    it('can render page', function () {
        $jobPosting = JobPosting::factory()->create();

        Livewire::test(EditJobPosting::class, ['record' => $jobPosting->id])
            ->assertOk();
    });

    it('can retrieve data', function () {
        $jobPosting = JobPosting::factory()->create();

        Livewire::test(EditJobPosting::class, ['record' => $jobPosting->id])
            ->assertFormSet([
                'publisher_id' => $jobPosting->publisher_id,
                'title' => $jobPosting->title,
                'location' => $jobPosting->location,
                'employment_type' => $jobPosting->employment_type->value,
                'application_url' => $jobPosting->application_url,
                'status' => $jobPosting->status->value,
            ]);
    });

    it('can save job posting', function () {
        $jobPosting = JobPosting::factory()->create();
        $newData = JobPosting::factory()->make(['publisher_id' => $jobPosting->publisher_id]);

        Livewire::test(EditJobPosting::class, ['record' => $jobPosting->id])
            ->fillForm([
                'title' => $newData->title,
                'location' => $newData->location,
                'employment_type' => $newData->employment_type->value,
                'application_url' => $newData->application_url,
                'status' => $newData->status->value,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas('job_postings', [
            'id' => $jobPosting->id,
            'title' => $newData->title,
            'location' => $newData->location,
            'employment_type' => $newData->employment_type->value,
            'application_url' => $newData->application_url,
            'status' => $newData->status->value,
        ]);
    });

    it('can delete job posting', function () {
        $jobPosting = JobPosting::factory()->create();

        Livewire::test(EditJobPosting::class, ['record' => $jobPosting->id])
            ->callAction('delete')
            ->assertNotified();

        assertDatabaseMissing('job_postings', [
            'id' => $jobPosting->id,
        ]);
    });
});
