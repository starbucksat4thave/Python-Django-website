<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Notice;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Ensure 'admin' role exists
    $role = Role::firstOrCreate(['name' => 'super-admin']);
    $permissions = Permission::all();
    $role->syncPermissions($permissions);
    // Create roles
    $this->admin = User::factory()->create()->assignRole('super-admin');
    $this->publisher = User::factory()->create()->assignRole('teacher');

    // Create department
    $this->department = Department::factory()->create();

    // Create notice with approvedBy
    $this->notice = Notice::factory()->create([
        'department_id' => $this->department->id,
        'title' => 'Important Notice',
        'content' => 'Curriculum changes',
        'created_at' => now()->subDay()
    ]);

    // Add approvedBy
    $this->approvedBy = User::factory()->count(2)->create();
    $this->notice->approvedBy()->attach($this->approvedBy->pluck('id'));
});

/*************************
 * Public Show-Notice Tests *
 *************************/

it('returns approved notices to public', function () {
    // Approve all assigned approvers
    $this->notice->approvedBy->each(function ($user) {
        $this->notice->approvedBy()->updateExistingPivot($user->id, ['is_approved' => true]);
    });

    $response = $this->getJson('/api/show-notice');

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'notices' => [
                '*' => ['id', 'title', 'content', 'department_name', 'created_at']
            ]
        ])
        ->assertJsonPath('notices.0.title', 'Important Notice');
});

it('shows individual notice details', function () {
    $this->notice->approvedBy()->updateExistingPivot($this->approvedBy->first()->id, ['is_approved' => true]);

    $response = $this->getJson("/api/show-notice/{$this->notice->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'notice' => [
                'notice' => ['id', 'title', 'content', 'department_id'],
                'department_name'
            ]
        ])
        ->assertJsonPath('notice.notice.title', 'Important Notice');
});

/********************
 * Negative Tests *
 ********************/

it('excludes unapproved notices from public', function () {
    // Create unapproved notice
    $unapproved = Notice::factory()->create();
    $unapproved->approvedBy()->attach(User::factory()->create(), ['is_approved' => false]);

    $response = $this->getJson('/api/show-notice');

    $response->assertOk()
        ->assertJsonCount(0, 'notices');
});

it('handles missing notice', function () {
    $response = $this->getJson('/api/show-notice/9999');
    $response->assertNotFound();
});
