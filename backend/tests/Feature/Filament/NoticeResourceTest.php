<?php

use App\Filament\Resources\NoticeResource;
use App\Filament\Resources\NoticeResource\Pages\CreateNotice;
use App\Filament\Resources\NoticeResource\Pages\EditNotice;
use App\Filament\Resources\NoticeResource\Pages\ListNotices;
use App\Models\Department;
use App\Models\Notice;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    actingAs($admin);

    $this->department = Department::factory()->create();
    $this->publisher = User::factory()->create();
    $this->approvers = User::factory()->count(2)->create()->each->assignRole('teacher');
});

it('can list notices', function () {
    $notices = Notice::factory()->count(3)->create();

    Livewire::test(ListNotices::class)
        ->assertCanSeeTableRecords($notices);
});

// In NoticeResourceTest.php
it('can create a notice', function () {
    $file = UploadedFile::fake()->create('document.pdf');

    Livewire::test(CreateNotice::class)
        ->fillForm([
            'title' => 'Important Notice',
            'content' => 'This is a test notice',
            'department_id' => $this->department->id,
            'archived_on' => now()->addWeek(),
            'file' => [$file], // Wrap in array for file uploads
            'approvedBy' => $this->approvers->pluck('id')->toArray(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    // Check storage using the correct path format
    Storage::disk('public')->assertExists(
        'notices/'
    );
});

it('requires title when creating', function () {
    Livewire::test(CreateNotice::class)
        ->fillForm([
            'title' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can edit a notice', function () {
    $notice = Notice::factory()->create();

    Livewire::test(EditNotice::class, ['record' => $notice->id])
        ->fillForm([
            'title' => 'Updated Title',
            'archived_on' => now()->addMonth(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $notice->refresh();
    expect($notice->title)->toBe('Updated Title');
});

it('can filter by department', function () {
    $notice1 = Notice::factory()->create(['department_id' => $this->department->id]);
    $notice2 = Notice::factory()->create(['department_id' => Department::factory()->create()->id]);

    Livewire::test(ListNotices::class)
        ->filterTable('department', $this->department->id)
        ->assertCanSeeTableRecords([$notice1])
        ->assertCanNotSeeTableRecords([$notice2]);
});

// In NoticeResourceTest.php
it('can filter by approval status', function () {
    $approvedNotice = Notice::factory()
        ->hasAttached(
            $this->approvers,
            [],
            'approvedBy' // Specify the relationship name
        )->create();

    $unapprovedNotice = Notice::factory()->create();

    Livewire::test(ListNotices::class)
        ->filterTable('approved', 'true')
        ->assertCanSeeTableRecords([$approvedNotice])
        ->assertCanNotSeeTableRecords([$unapprovedNotice]);
});

it('can filter by archived status', function () {
    $archivedNotice = Notice::factory()->create(['archived_on' => now()]);
    $activeNotice = Notice::factory()->create(['archived_on' => null]);

    Livewire::test(ListNotices::class)
        ->filterTable('archived', 'true')
        ->assertCanSeeTableRecords([$archivedNotice])
        ->assertCanNotSeeTableRecords([$activeNotice]);
});

it('can filter by file attachment', function () {
    $withFile = Notice::factory()->create(['file' => 'test.pdf']);
    $withoutFile = Notice::factory()->create(['file' => null]);

    Livewire::test(ListNotices::class)
        ->filterTable('has_file', 'true')
        ->assertCanSeeTableRecords([$withFile])
        ->assertCanNotSeeTableRecords([$withoutFile]);
});

it('shows correct columns in table', function () {
    Notice::factory()->create();

    Livewire::test(ListNotices::class)
        ->assertTableColumnExists('title')
        ->assertTableColumnExists('publisher.name')
        ->assertTableColumnExists('department.name')
        ->assertTableColumnExists('published_at')
        ->assertTableColumnExists('archived_on');
});

it('uses correct model and navigation icon', function () {
    $resource = new NoticeResource();

    expect($resource->getModel())->toBe(Notice::class)
        ->and($resource->getNavigationIcon())->toBe('heroicon-o-rectangle-stack');
});
