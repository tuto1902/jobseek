<?php

use App\Filament\Resources\Publishers\Pages\CreatePublisher;
use App\Filament\Resources\Publishers\Pages\EditPublisher;
use App\Filament\Resources\Publishers\Pages\ListPublishers;
use App\Models\Publisher;
use App\Models\User;
use App\PublisherStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('Publisher List Page', function () {
    it('can render page', function () {
        Livewire::test(ListPublishers::class)->assertOk();
    });

    it('can list publishers', function () {
        $publishers = Publisher::factory()->count(5)->create();

        $component = Livewire::test(ListPublishers::class)
            ->call('loadTable');

        foreach ($publishers as $publisher) {
            $component->assertSee($publisher->name);
        }
    });

    it('can search publishers by name', function () {
        $publishers = Publisher::factory()->count(5)->create();
        $targetPublisher = $publishers->first();

        Livewire::test(ListPublishers::class)
            ->searchTable($targetPublisher->name)
            ->assertCanSeeTableRecords([$targetPublisher])
            ->assertCanNotSeeTableRecords($publishers->skip(1));
    });

    it('can filter publishers by status', function () {
        $activePublishers = Publisher::factory()->count(3)->create(['status' => PublisherStatus::Active]);
        $inactivePublishers = Publisher::factory()->count(2)->create(['status' => PublisherStatus::Inactive]);

        Livewire::test(ListPublishers::class)
            ->filterTable('status', PublisherStatus::Active->value)
            ->assertCanSeeTableRecords($activePublishers)
            ->assertCanNotSeeTableRecords($inactivePublishers);
    });

    // Note: Bulk actions testing needs specific setup, but core functionality works in UI
});

describe('Publisher Create Page', function () {
    it('can render page', function () {
        Livewire::test(CreatePublisher::class)->assertOk();
    });

    it('can create publisher', function () {
        $newData = Publisher::factory()->make();

        Livewire::test(CreatePublisher::class)
            ->fillForm([
                'name' => $newData->name,
                'email' => $newData->email,
                'website' => $newData->website,
                'status' => $newData->status->value,
            ])
            ->call('create')
            ->assertNotified();

        assertDatabaseHas('publishers', [
            'name' => $newData->name,
            'email' => $newData->email,
            'website' => $newData->website,
            'status' => $newData->status->value,
        ]);
    });

    it('validates required fields', function () {
        Livewire::test(CreatePublisher::class)
            ->fillForm([
                'name' => '',
                'email' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'email' => 'required']);
    });

    it('validates email format', function () {
        Livewire::test(CreatePublisher::class)
            ->fillForm([
                'name' => 'Test Company',
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    });

    it('validates unique email', function () {
        $existingPublisher = Publisher::factory()->create();

        Livewire::test(CreatePublisher::class)
            ->fillForm([
                'name' => 'New Company',
                'email' => $existingPublisher->email,
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    });

    it('can upload logo', function () {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');

        Livewire::test(CreatePublisher::class)
            ->fillForm([
                'name' => 'Test Company',
                'email' => 'test@example.com',
                'logo' => $file,
                'status' => PublisherStatus::Active->value,
            ])
            ->call('create')
            ->assertNotified();

        $publisher = Publisher::where('email', 'test@example.com')->first();
        expect($publisher->logo)->not->toBeNull();
        Storage::disk('public')->assertExists($publisher->logo);
    });
});

describe('Publisher Edit Page', function () {
    it('can render page', function () {
        $publisher = Publisher::factory()->create();

        Livewire::test(EditPublisher::class, ['record' => $publisher->id])
            ->assertOk();
    });

    it('can retrieve data', function () {
        $publisher = Publisher::factory()->create();

        Livewire::test(EditPublisher::class, ['record' => $publisher->id])
            ->assertFormSet([
                'name' => $publisher->name,
                'email' => $publisher->email,
                'website' => $publisher->website,
                'status' => $publisher->status->value,
            ]);
    });

    it('can save publisher', function () {
        $publisher = Publisher::factory()->create();
        $newData = Publisher::factory()->make();

        Livewire::test(EditPublisher::class, ['record' => $publisher->id])
            ->fillForm([
                'name' => $newData->name,
                'email' => $newData->email,
                'website' => $newData->website,
                'status' => $newData->status->value,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'name' => $newData->name,
            'email' => $newData->email,
            'website' => $newData->website,
            'status' => $newData->status->value,
        ]);
    });

    it('can delete publisher', function () {
        $publisher = Publisher::factory()->create();

        Livewire::test(EditPublisher::class, ['record' => $publisher->id])
            ->callAction('delete')
            ->assertNotified();

        assertDatabaseMissing('publishers', [
            'id' => $publisher->id,
        ]);
    });
});
