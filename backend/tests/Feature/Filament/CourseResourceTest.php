<?php

namespace Tests\Feature\Filament;

use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Ensure 'admin' role exists
    $role = Role::firstOrCreate(['name' => 'super-admin']);
    $permissions = Permission::all();
    $role->syncPermissions($permissions);

    // Seed a department for the Course factory
    Department::factory()->create();

    // Create and assign the admin user
    $this->admin = User::factory()->create()->assignRole('super-admin');
    Filament::auth()->login($this->admin);
});

it('lists courses on the Filament index page', function () {
    Course::factory()->count(5)->create();

    $this->actingAs($this->admin);
    livewire(\App\Filament\Resources\CourseResource\Pages\ListCourses::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords(Course::all());
});

it('creates a course via the Filament create page', function () {
    $department = Department::first();

    $this->actingAs($this->admin);
    $livewire = livewire(\App\Filament\Resources\CourseResource\Pages\CreateCourse::class)
        ->assertSuccessful()
        ->fillForm([
            'code'          => 'CSE1001',
            'name'          => 'Intro to Testing',
            'credit'        => 3,
            'year'          => 2,
            'semester'      => 1,
            'department_id' => $department->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = Course::latest()->first();
    $livewire->assertRedirect(
        \App\Filament\Resources\CourseResource::getUrl('view', ['record' => $created->id])
    );

    $this->assertDatabaseHas('courses', [
        'id'   => $created->id,
        'code' => 'CSE1001',
        'name' => 'Intro to Testing',
    ]);
});

it('edits a course via the Filament edit page', function () {
    $course = Course::factory()->create(['name' => 'Original Name']);

    $this->actingAs($this->admin);
    livewire(\App\Filament\Resources\CourseResource\Pages\EditCourse::class, [
        'record' => $course->getKey(),
    ])
        ->assertSuccessful()
        ->fillForm(['name' => 'Updated Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('courses', [
        'id'   => $course->id,
        'name' => 'Updated Name',
    ]);
});
