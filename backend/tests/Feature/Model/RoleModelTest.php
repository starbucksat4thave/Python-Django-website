<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('role can be created with a name and gets default guard_name', function () {
    // Create a new role by name
    $role = Role::create(['name' => 'editor']);

    // Assert the record exists in the roles table
    $this->assertDatabaseHas('roles', [
        'name'       => 'editor',
        'guard_name' => 'web',          // default from protected $attributes :contentReference[oaicite:2]{index=2}
    ]);

    // Instance is of the correct class
    $this->assertInstanceOf(Role::class, $role);
});

test('role has correct fillable attributes', function () {
    $expected = ['name'];

    expect((new Role)->getFillable())->toBe($expected);
});

test('users() returns a BelongsToMany relationship to User via model_has_roles', function () {
    $relation = (new Role)->users();

    // It should be a BelongsToMany relation
    $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relation);

    // The pivot table is model_has_roles
    $this->assertEquals('model_has_roles', $relation->getTable());

    // The foreign key on the pivot pointing to roles is role_id
    $this->assertEquals('role_id', $relation->getForeignPivotKeyName());

    // The related pivot key for users is model_id
    $this->assertEquals('model_id', $relation->getRelatedPivotKeyName());
});

test('role can attach users through users() relationship', function () {
    $role = Role::create(['name' => 'manager']);
    $user = User::factory()->create();

    // Assign the role to the user using Spatie's method
    $user->assignRole($role);

    // Verify the pivot entry in model_has_roles
    $this->assertDatabaseHas('model_has_roles', [
        'role_id'    => $role->id,
        'model_id'   => $user->id,
        'model_type' => User::class,
    ]);

    // Assert that the user has the role
    $this->assertTrue($user->hasRole('manager'));
});
