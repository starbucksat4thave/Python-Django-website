<?php

use App\Models\User;
use App\Models\Department;
use App\Models\Notice;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Publication;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\{seed};

uses(RefreshDatabase::class);
beforeEach(function () {
    // Fresh DB each time, so seeding here won't duplicate
    seed(RolesAndPermissionsSeeder::class);
});

test('user can be created with fillable attributes', function () {
    $user = User::create([
        'name' => 'John Doe',
        'image' => 'hashed_password',
        'email' => 'john@example.com',
        'password' => 'password',
        'university_id' => '12345',
        'department_id' => Department::factory()->create()->id,
        'phone' => '123-456-7890',
        'dob' => '2000-01-01',
        'address' => '123 Main St',
        'city' => 'New York',
        'status' => 'active',
        'designation' => 'Student',
        'publication_count' => 0,
        'year' => 2,
        'semester' => 1,
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'university_id' => '12345'
    ]);
});

test('password is hashed when set', function () {
    $user = User::create([
        'name' => 'Test User',
        'image' => 'hashed_password',
        'email' => 'test@example.com',
        'password' => 'secret',
        'university_id' => '12345',
        'department_id' => Department::factory()->create()->id,
        'phone' => '123-456-7890',
        'dob' => '2000-01-01',
        'address' => '123 Main St',
        'city' => 'New York',
        'status' => 'active',
        'designation' => 'Student',
        'publication_count' => 0,
        'year' => 2,
        'semester' => 1,
    ]);

    $this->assertNotEquals('secret', $user->password);
    $this->assertTrue(password_verify('secret', $user->password));
});

test('user has department relationship', function () {
    $department = Department::factory()->create();
    $user = User::factory()->create(['department_id' => $department->id]);

    $this->assertInstanceOf(Department::class, $user->department);
    $this->assertEquals($department->id, $user->department->id);
});

test('user has notices relationship', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->create(['published_by' => $user->id]);

    $this->assertTrue($user->notices->contains($notice));
    $this->assertEquals(1, $user->notices->count());
});

test('user has course sessions relationship', function () {
    $teacher = User::factory()->create();
    $session = CourseSession::factory()->create(['teacher_id' => $teacher->id]);

    $this->assertTrue($teacher->courseSessions->contains($session));
    $this->assertEquals(1, $teacher->courseSessions->count());
});

test('user has enrollments relationship', function () {
    $student = User::factory()->create();
    $enrollment = Enrollment::factory()->create(['student_id' => $student->id]);

    $this->assertTrue($student->enrollments->contains($enrollment));
    $this->assertEquals(1, $student->enrollments->count());
});

test('user has publications relationship', function () {
    $user = User::factory()->create();
    $publication = Publication::factory()->create();
    $user->publications()->attach($publication);

    $this->assertTrue($user->publications->contains($publication));
    $this->assertEquals(1, $user->publications->count());
});

test('user has approved notices relationship', function () {
    $user = User::factory()->create();
    $notice = Notice::factory()->create();
    $user->approvedNotices()->attach($notice, ['is_approved' => true]);

    $this->assertTrue($user->approvedNotices->contains($notice));
    $this->assertEquals(true, $user->approvedNotices->first()->pivot->is_approved);
});

test('sends password reset notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $user->sendPasswordResetNotification($token);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('admin panel access permissions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $panel = \Filament\Facades\Filament::getPanel();

    $this->assertTrue($admin->canAccessPanel($panel));

    $regularUser = User::factory()->create();
    $this->assertFalse($regularUser->canAccessPanel($panel));
});

test('hidden attributes are not visible', function () {
    $user = User::factory()->create();
    $array = $user->toArray();

    $this->assertArrayNotHasKey('password', $array);
    $this->assertArrayNotHasKey('remember_token', $array);
});

test('datetime casting', function () {
    $user = User::factory()->create();
    $this->assertInstanceOf(\DateTimeInterface::class, $user->email_verified_at);
});

test('roles and permissions', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'editor']);
    $user->assignRole($role);

    $this->assertTrue($user->hasRole('editor'));
    $this->assertFalse($user->hasRole('admin'));
});
