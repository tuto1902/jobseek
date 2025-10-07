<?php

use App\Filament\Resources\JobGroups\Pages\CreateJobGroup;
use App\Filament\Resources\JobGroups\Pages\EditJobGroup;
use App\Filament\Resources\JobGroups\Pages\ListJobGroups;
use App\GroupStatus;
use App\Models\JobGroup;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Filament::setCurrentPanel('app');
});

it('can render job groups list page', function () {
    livewire(ListJobGroups::class)
        ->assertSuccessful();
});

it('can list job groups', function () {
    $groups = JobGroup::factory()->count(3)->create();

    livewire(ListJobGroups::class)
        ->assertCanSeeTableRecords($groups);
});

it('can create a job group', function () {
    livewire(CreateJobGroup::class)
        ->fillForm([
            'name' => 'Tech Jobs',
            'description' => 'Technology related positions',
            'status' => GroupStatus::Active,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(JobGroup::class, [
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

    livewire(EditJobGroup::class, [
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

it('can filter job groups by status', function () {
    $activeGroup = JobGroup::factory()->create(['status' => GroupStatus::Active]);
    $draftGroup = JobGroup::factory()->create(['status' => GroupStatus::Draft]);

    livewire(ListJobGroups::class)
        ->assertCanSeeTableRecords([$activeGroup, $draftGroup])
        ->filterTable('status', GroupStatus::Active->value)
        ->assertCanSeeTableRecords([$activeGroup])
        ->assertCanNotSeeTableRecords([$draftGroup]);
});

it('validates required fields when creating job group', function () {
    livewire(CreateJobGroup::class)
        ->fillForm([
            'name' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can search job groups by name', function () {
    $group1 = JobGroup::factory()->create(['name' => 'Technology Jobs']);
    $group2 = JobGroup::factory()->create(['name' => 'Marketing Positions']);

    livewire(ListJobGroups::class)
        ->searchTable('Technology')
        ->assertCanSeeTableRecords([$group1])
        ->assertCanNotSeeTableRecords([$group2]);
});

it('can delete a job group', function () {
    $group = JobGroup::factory()->create();

    livewire(ListJobGroups::class)
        ->callTableAction('delete', $group);

    assertModelMissing($group);
});
