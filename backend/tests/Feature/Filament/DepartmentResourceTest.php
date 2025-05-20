<?php

use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\DepartmentResource\Pages\CreateDepartment;
use App\Filament\Resources\DepartmentResource\Pages\EditDepartment;
use App\Filament\Resources\DepartmentResource\Pages\ListDepartments;
use App\Filament\Resources\DepartmentResource\RelationManagers\CoursesRelationManager;
use App\Filament\Resources\DepartmentResource\RelationManagers\UsersRelationManager;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->seed(RolesAndPermissionsSeeder::class);

    // Ensure 'admin' role exists
    $role = Role::firstOrCreate(['name' => 'super-admin']);
    $permissions = Permission::all();
    $role->syncPermissions($permissions);


    // Create and assign the admin user
    $this->user = User::factory()->create()->assignRole('super-admin');
    Filament::auth()->login($this->user);
});

// Test if departments can be listed
it('can list departments', function () {
    $departments = Department::factory()->count(3)->create();

    Livewire::test(ListDepartments::class)
        ->assertCanSeeTableRecords($departments);
});

// Test department creation with valid data
it('can create a department', function () {
    Livewire::test(CreateDepartment::class)
        ->fillForm([
            'name' => 'Computer Science',
            'faculty' => 'Engineering',
            'short_name' => 'CS',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Department::class, [
        'name' => 'Computer Science',
        'faculty' => 'Engineering',
        'short_name' => 'CS',
    ]);
});

// Test validation for required fields when creating
it('requires name and short name when creating a department', function () {
    Livewire::test(CreateDepartment::class)
        ->fillForm([
            'name' => '',
            'short_name' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'short_name' => 'required',
        ]);
});

// Test editing an existing department
it('can edit a department', function () {
    $department = Department::factory()->create();

    Livewire::test(EditDepartment::class, ['record' => $department->id])
        ->fillForm([
            'name' => 'Updated Department',
            'faculty' => 'Science',
            'short_name' => 'UD',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Department::class, [
        'id' => $department->id,
        'name' => 'Updated Department',
        'faculty' => 'Science',
        'short_name' => 'UD',
    ]);
});

it('can filter departments by faculty', function () {
    $engineeringDept = Department::factory()->create(['faculty' => 'Engineering']);
    $scienceDept     = Department::factory()->create(['faculty' => 'Science']);

    livewire(ListDepartments::class)
        // first assert both rows are visible
        ->assertCanSeeTableRecords([$engineeringDept, $scienceDept])

        // correctly apply the “faculty” filter
        ->filterTable('faculty', 'Engineering')

        // now only the Engineering row remains
        ->assertCanSeeTableRecords([$engineeringDept])
        ->assertCanNotSeeTableRecords([$scienceDept]);
});

// Test presence of relation managers
it('includes courses and users relation managers', function () {
    $managers = DepartmentResource::getRelations();

    expect($managers)->toContain(CoursesRelationManager::class)
        ->and($managers)->toContain(UsersRelationManager::class);
});

// Test correct model and navigation icon
it('uses correct model and navigation icon', function () {
    $resource = new DepartmentResource();

    expect($resource->getModel())->toBe(Department::class)
        ->and($resource->getNavigationIcon())->toBe('heroicon-o-rectangle-stack');
});
