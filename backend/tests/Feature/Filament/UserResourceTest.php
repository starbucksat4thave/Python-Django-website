<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use App\Models\Department;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use function Pest\Livewire\livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(RolesAndPermissionsSeeder::class);

    // Ensure 'admin' role exists and assign all permissions
    $role = Role::firstOrCreate(['name' => 'super-admin']);
    $permissions = Permission::all();
    $role->syncPermissions($permissions);

    // Seed a department for the User factory
    Department::factory()->create();

    // Create and assign the admin user
    $this->admin = User::factory()->create()->assignRole('super-admin');
    Filament::auth()->login($this->admin);
});

it('lists users on the Filament index page', function () {
    User::factory()->count(5)->create();

    livewire(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords(User::all());
});

it('edits a user via the Filament edit page', function () {
    $user = User::factory()->create(['name' => 'Jane Smith']);

    livewire(\App\Filament\Resources\UserResource\Pages\EditUser::class, [
        'record' => $user->getKey(),
    ])
        ->assertSuccessful()
        ->fillForm([
            'name' => 'Jane Doe',
            'image' => UploadedFile::fake()->image('avatar.jpg'), // added image
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id'   => $user->id,
        'name' => 'Jane Doe',
    ]);
});

it('deletes a user via the Filament delete action', function () {
    $user = User::factory()->create();

    livewire(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$user])
        ->callTableAction('delete', $user);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});


it('restricts access to user management for non-admin users', function () {
    $this->actingAs(User::factory()->create());

    livewire(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
        ->assertForbidden();
});


