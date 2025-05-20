<?php

use App\Filament\Resources\CourseSessionResource;
use App\Filament\Resources\CourseSessionResource\Pages\CreateCourseSession;
use App\Filament\Resources\CourseSessionResource\Pages\EditCourseSession;
use App\Filament\Resources\CourseSessionResource\Pages\ListCourseSessions;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    actingAs($admin);

    $this->course = Course::factory()->create();
    $this->teacher = User::factory()->create()->assignRole('teacher');
    $this->session = CourseSession::factory()->create([
        'course_id' => $this->course->id,
        'teacher_id' => $this->teacher->id,
        'session' => '2023',
    ]);
});

it('can list course sessions', function () {
    $sessions = CourseSession::factory()->count(3)->create();

    Livewire::test(ListCourseSessions::class)
        ->assertCanSeeTableRecords($sessions);
});

it('can create a course session', function () {
    Livewire::test(CreateCourseSession::class)
        ->fillForm([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher->id,
            'session' => '2024',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(CourseSession::class, [
        'course_id' => $this->course->id,
        'teacher_id' => $this->teacher->id,
        'session' => '2024',
    ]);
});

it('requires course and teacher when creating', function () {
    Livewire::test(CreateCourseSession::class)
        ->fillForm([
            'course_id' => null,
            'teacher_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'course_id' => 'required',
            'teacher_id' => 'required',
        ]);
});

it('can edit a course session', function () {
    $newCourse = Course::factory()->create();
    $newTeacher = User::factory()->create()->assignRole('teacher');

    Livewire::test(EditCourseSession::class, ['record' => $this->session->id])
        ->fillForm([
            'course_id' => $newCourse->id,
            'teacher_id' => $newTeacher->id,
            'session' => '2025',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->session->refresh();
    expect($this->session->course_id)->toBe($newCourse->id)
        ->and($this->session->teacher_id)->toBe($newTeacher->id)
        ->and($this->session->session)->toBe('2025');
});

it('can filter by session year', function () {
    $newSession = CourseSession::factory()->create(['session' => '2024']);

    Livewire::test(ListCourseSessions::class)
        ->filterTable('session', '2023')
        ->assertCanSeeTableRecords([$this->session])
        ->assertCanNotSeeTableRecords([$newSession]);
});

it('can filter by teacher', function () {
    $newTeacher = User::factory()->create()->assignRole('teacher');
    $teacherSession = CourseSession::factory()->create(['teacher_id' => $newTeacher->id]);

    Livewire::test(ListCourseSessions::class)
        ->filterTable('teacher_id', $this->teacher->id)
        ->assertCanSeeTableRecords([$this->session])
        ->assertCanNotSeeTableRecords([$teacherSession]);
});

it('can filter by course', function () {
    $newCourse = Course::factory()->create();
    $courseSession = CourseSession::factory()->create(['course_id' => $newCourse->id]);

    Livewire::test(ListCourseSessions::class)
        ->filterTable('course_id', $this->course->id)
        ->assertCanSeeTableRecords([$this->session])
        ->assertCanNotSeeTableRecords([$courseSession]);
});

it('shows correct columns in table', function () {
    Livewire::test(ListCourseSessions::class)
        ->assertTableColumnExists('course.code')
        ->assertTableColumnExists('course.name')
        ->assertTableColumnExists('course.year')
        ->assertTableColumnExists('course.semester')
        ->assertTableColumnExists('teacher.name')
        ->assertTableColumnExists('session');
});

it('uses correct model and navigation settings', function () {
    $resource = new CourseSessionResource();

    expect($resource->getModel())->toBe(CourseSession::class)
        ->and($resource->getNavigationIcon())->toBe('heroicon-o-rectangle-stack')
        ->and($resource->getNavigationGroup())->toBe('Course Management');
});
