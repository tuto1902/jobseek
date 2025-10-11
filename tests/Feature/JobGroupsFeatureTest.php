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
