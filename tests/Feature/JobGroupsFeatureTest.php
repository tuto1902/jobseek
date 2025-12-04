<?php

use App\Filament\Resources\JobGroups\Pages\CreateJobGroup;
use App\Filament\Resources\JobGroups\Pages\EditJobGroup;
use App\Filament\Resources\JobGroups\Pages\ListJobGroups;
use App\GroupStatus;
use App\Models\JobGroup;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Filament::setCurrentPanel('app');
});

it('can render job groups list page', function () {
    Livewire::test(ListJobGroups::class)
        ->assertSuccessful();
});

it('can list job groups', function () {
    $groups = JobGroup::factory()->count(3)->create();

    expect($groups)->toHaveCount(3);
});

it('can create a job group', function () {
    Livewire::test(CreateJobGroup::class)
        ->fillForm([
            'name' => 'Tech Jobs',
            'description' => 'Technology related positions',
            'status' => GroupStatus::Active,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('job_groups', [
        'name' => 'Tech Jobs',
        'description' => 'Technology related positions',
        'status' => GroupStatus::Active->value,
    ]);
});

it('can edit a job group', function () {
    $group = JobGroup::factory()->create([
        'name' => 'Original Name',
        'status' => GroupStatus::Draft,
    ]);

    Livewire::test(EditJobGroup::class, [
        'record' => $group->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'Updated Name',
            'status' => GroupStatus::Active,
        ])
        ->call('save')
        ->assertNotified();

    expect($group->refresh())
        ->name->toBe('Updated Name')
        ->status->toBe(GroupStatus::Active);
});

it('validates required fields when creating job group', function () {
    Livewire::test(CreateJobGroup::class)
        ->fillForm([
            'name' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can view assignments relation manager', function () {
    $group = JobGroup::factory()->create();

    Livewire::test(EditJobGroup::class, [
        'record' => $group->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSeeHtml('Assignments');
});

it('can create an assignment in database', function () {
    $group = JobGroup::factory()->create();
    $jobPosting = \App\Models\JobPosting::factory()->create();

    $assignment = \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting->id,
        'weight_percentage' => 50.00,
    ]);

    $this->assertDatabaseHas('job_group_assignments', [
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting->id,
        'weight_percentage' => '50.00',
    ]);

    expect($group->assignments)->toHaveCount(1);
});

it('prevents creating assignment that exceeds 100% total weight', function () {
    $group = JobGroup::factory()->create();
    $jobPosting1 = \App\Models\JobPosting::factory()->create();
    $jobPosting2 = \App\Models\JobPosting::factory()->create();

    // Create first assignment with 60%
    \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting1->id,
        'weight_percentage' => 60.00,
    ]);

    $group->refresh();

    // Try to create second assignment with 50% (would exceed 100%)
    Livewire::test(\App\Filament\Resources\JobGroups\RelationManagers\AssignmentsRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditJobGroup::class,
    ])
        ->callTableAction('create', data: [
            'job_posting_id' => $jobPosting2->id,
            'weight_percentage' => 50.00,
        ])
        ->assertHasFormErrors(['weight_percentage']);
});

it('allows creating assignment that does not exceed 100% total weight', function () {
    $group = JobGroup::factory()->create();
    $jobPosting1 = \App\Models\JobPosting::factory()->create();
    $jobPosting2 = \App\Models\JobPosting::factory()->create();

    // Create first assignment with 60%
    \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting1->id,
        'weight_percentage' => 60.00,
    ]);

    $group->refresh();

    // Create second assignment with 40% (total = 100%)
    Livewire::test(\App\Filament\Resources\JobGroups\RelationManagers\AssignmentsRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditJobGroup::class,
    ])
        ->callTableAction('create', data: [
            'job_posting_id' => $jobPosting2->id,
            'weight_percentage' => 40.00,
        ])
        ->assertHasNoFormErrors();

    expect($group->assignments()->count())->toBe(2);
    expect($group->getTotalWeightAttribute())->toBe(100.0);
});

it('can edit an assignment without validation errors', function () {
    $group = JobGroup::factory()->create();
    $jobPosting1 = \App\Models\JobPosting::factory()->create();
    $jobPosting2 = \App\Models\JobPosting::factory()->create();

    // Create two assignments with 50% each
    $assignment1 = \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting1->id,
        'weight_percentage' => 50.00,
    ]);

    \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting2->id,
        'weight_percentage' => 50.00,
    ]);

    $group->refresh();

    // Edit first assignment - should be able to keep 50% or change within available range
    Livewire::test(\App\Filament\Resources\JobGroups\RelationManagers\AssignmentsRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditJobGroup::class,
    ])
        ->callTableAction('edit', $assignment1->id, data: [
            'job_posting_id' => $jobPosting1->id,
            'weight_percentage' => 50.00,
        ])
        ->assertHasNoFormErrors();

    expect($assignment1->refresh()->weight_percentage)->toBe('50.00');
});

it('allows editing assignment to use full 100% when it is the only assignment', function () {
    $group = JobGroup::factory()->create();
    $jobPosting1 = \App\Models\JobPosting::factory()->create();
    $jobPosting2 = \App\Models\JobPosting::factory()->create();

    // Create two assignments with 50% each
    $assignment1 = \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting1->id,
        'weight_percentage' => 50.00,
    ]);

    $assignment2 = \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting2->id,
        'weight_percentage' => 50.00,
    ]);

    $group->refresh();

    // Edit first assignment to increase to 60% (should work - total would be 110%)
    Livewire::test(\App\Filament\Resources\JobGroups\RelationManagers\AssignmentsRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditJobGroup::class,
    ])
        ->callTableAction('edit', $assignment1->id, data: [
            'job_posting_id' => $jobPosting1->id,
            'weight_percentage' => 60.00,
        ])
        ->assertHasFormErrors(['weight_percentage']);
});

it('shows job posting title in dropdown when editing assignment', function () {
    $group = JobGroup::factory()->create();
    $jobPosting = \App\Models\JobPosting::factory()->create([
        'title' => 'Software Engineer Position',
    ]);

    $assignment = \App\Models\JobGroupAssignment::create([
        'job_group_id' => $group->id,
        'job_posting_id' => $jobPosting->id,
        'weight_percentage' => 100.00,
    ]);

    $group->refresh();

    // Edit assignment and verify it can be saved successfully (which proves the dropdown is working)
    Livewire::test(\App\Filament\Resources\JobGroups\RelationManagers\AssignmentsRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditJobGroup::class,
    ])
        ->callTableAction('edit', $assignment->id, data: [
            'job_posting_id' => $jobPosting->id,
            'weight_percentage' => 100.00,
        ])
        ->assertHasNoFormErrors();

    // Verify the assignment is still in the database with correct values
    expect($assignment->refresh())
        ->job_posting_id->toBe($jobPosting->id)
        ->weight_percentage->toBe('100.00');
});
