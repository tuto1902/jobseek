<?php

use App\GroupStatus;
use App\Models\JobGroup;
use App\Models\JobGroupAssignment;
use App\Models\JobPosting;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->publisher = Publisher::factory()->create();
    $this->jobPosting = JobPosting::factory()->create(['publisher_id' => $this->publisher->id]);
    $this->jobGroup = JobGroup::factory()->create();
});

it('can create a job group', function () {
    $group = JobGroup::create([
        'name' => 'Test Group',
        'description' => 'Test description',
        'status' => GroupStatus::Draft,
    ]);

    expect($group->name)->toBe('Test Group');
    expect($group->description)->toBe('Test description');
    expect($group->status)->toBe(GroupStatus::Draft);
});

it('can assign jobs to groups with weights', function () {
    $assignment = JobGroupAssignment::create([
        'job_posting_id' => $this->jobPosting->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 50.0,
    ]);

    expect($assignment->weight_percentage)->toBe('50.00');
    expect($assignment->jobPosting->id)->toBe($this->jobPosting->id);
    expect($assignment->jobGroup->id)->toBe($this->jobGroup->id);
});

it('validates weight percentage is between 0 and 100', function () {
    expect(function () {
        JobGroupAssignment::create([
            'job_posting_id' => $this->jobPosting->id,
            'job_group_id' => $this->jobGroup->id,
            'weight_percentage' => 150.0, // Invalid
        ]);
    })->toThrow(InvalidArgumentException::class);

    expect(function () {
        JobGroupAssignment::create([
            'job_posting_id' => $this->jobPosting->id,
            'job_group_id' => $this->jobGroup->id,
            'weight_percentage' => -10.0, // Invalid
        ]);
    })->toThrow(InvalidArgumentException::class);
});

it('calculates total weight correctly', function () {
    JobGroupAssignment::create([
        'job_posting_id' => $this->jobPosting->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 30.0,
    ]);

    $jobPosting2 = JobPosting::factory()->create(['publisher_id' => $this->publisher->id]);
    JobGroupAssignment::create([
        'job_posting_id' => $jobPosting2->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 70.0,
    ]);

    expect($this->jobGroup->refresh()->total_weight)->toBe(100.0);
});

it('validates weight correctly when total is 100', function () {
    JobGroupAssignment::create([
        'job_posting_id' => $this->jobPosting->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 50.0,
    ]);

    $jobPosting2 = JobPosting::factory()->create(['publisher_id' => $this->publisher->id]);
    JobGroupAssignment::create([
        'job_posting_id' => $jobPosting2->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 50.0,
    ]);

    expect($this->jobGroup->refresh()->isWeightValid())->toBeTrue();
});

it('validates weight incorrectly when total is not 100', function () {
    JobGroupAssignment::create([
        'job_posting_id' => $this->jobPosting->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 30.0,
    ]);

    $jobPosting2 = JobPosting::factory()->create(['publisher_id' => $this->publisher->id]);
    JobGroupAssignment::create([
        'job_posting_id' => $jobPosting2->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 50.0,
    ]);

    expect($this->jobGroup->refresh()->isWeightValid())->toBeFalse();
});

it('counts jobs correctly', function () {
    expect($this->jobGroup->job_count)->toBe(0);

    JobGroupAssignment::create([
        'job_posting_id' => $this->jobPosting->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 50.0,
    ]);

    expect($this->jobGroup->refresh()->job_count)->toBe(1);

    $jobPosting2 = JobPosting::factory()->create(['publisher_id' => $this->publisher->id]);
    JobGroupAssignment::create([
        'job_posting_id' => $jobPosting2->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 50.0,
    ]);

    expect($this->jobGroup->refresh()->job_count)->toBe(2);
});

it('prevents duplicate job assignments to same group', function () {
    JobGroupAssignment::create([
        'job_posting_id' => $this->jobPosting->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 50.0,
    ]);

    expect(function () {
        JobGroupAssignment::create([
            'job_posting_id' => $this->jobPosting->id,
            'job_group_id' => $this->jobGroup->id,
            'weight_percentage' => 30.0, // Duplicate assignment
        ]);
    })->toThrow(Exception::class);
});

it('has proper relationships', function () {
    $assignment = JobGroupAssignment::create([
        'job_posting_id' => $this->jobPosting->id,
        'job_group_id' => $this->jobGroup->id,
        'weight_percentage' => 100.0,
    ]);

    expect($this->jobGroup->assignments)->toHaveCount(1);
    expect($this->jobGroup->jobPostings)->toHaveCount(1);
    expect($this->jobPosting->groupAssignments)->toHaveCount(1);
    expect($this->jobPosting->jobGroups)->toHaveCount(1);
});
